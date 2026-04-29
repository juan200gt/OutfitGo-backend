<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Mail\RecomendacionProductoMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Producto;

class AdminUsuarioController extends Controller
{
    
    public function show($id)
    {
        $usuario = User::with(['orders' => function($query) {
            $query->orderByDesc('created_at');
        }])->findOrFail($id);


        return view('usuarios.show', compact('usuario'));
    }

    public function index(Request $request)
    {
        
        $totalUsuarios = User::count();
        $activos = User::where('is_active', true)->count();
        $suspendidos = User::where('is_active', false)->count();

        // 1. Capturamos los parámetros
        $buscar = $request->input('buscar');
        
        // 2. Consulta a la base de datos
        $usuarios = User::when($buscar, function ($query, $buscar) {
            return $query->where('name', 'LIKE', "%{$buscar}%")
                         ->orWhere('email', 'LIKE', "%{$buscar}%");
        })
        ->orderBy('id', 'asc') 
        ->paginate(10)
        ->appends($request->all()); 

        return view('usuarios.index', compact('usuarios', 'totalUsuarios', 'activos', 'suspendidos', 'buscar'));
    }



    // Muestra el formulario para crear un usuario
    public function create()
    {
        return view('usuarios.create');
    }

    // Muestra el formulario con los datos del usuario a editar
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }


    // Guarda el usuario
    public function store(Request $request)
    {
        $reglas = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|max:255',
            'rol' => 'required|string|in:cliente,admin_productos,admin_usuarios',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
        ];

        // 2. TRADUCCIONES
        $mensajes = [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'Necesitamos un correo electrónico.',
            'email.unique' => 'Ese correo ya está siendo usado por otro usuario.',
        ];

        // 3. Le pasamos ambos bloques a Laravel
        $request->validate($reglas, $mensajes);


        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'rol' => $request->rol,
            'is_active' => true, 
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'provincia' => $request->provincia,
            'codigo_postal' => $request->codigo_postal,
            'telefono' => $request->telefono,
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    // Actualiza el usuario
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        // 1. Añadimos las validaciones de la dirección
        $reglas = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'rol' => 'required|string|in:cliente,admin_productos,admin_usuarios',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
        ];


        // 2. TRADUCCIONES
        $mensajes = [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'Necesitamos un correo electrónico.',
            'email.unique' => 'Ese correo ya está siendo usado por otro usuario.',
        ];

        // 3. Le pasamos ambos bloques a Laravel
        $request->validate($reglas, $mensajes);


        // 2. Actualizamos los datos básicos
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->rol = $request->rol;

        // 3. Actualizamos los datos de envío
        $usuario->direccion = $request->direccion;
        $usuario->ciudad = $request->ciudad;
        $usuario->provincia = $request->provincia;
        $usuario->codigo_postal = $request->codigo_postal;
        $usuario->telefono = $request->telefono;

        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->password);
        }

        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    // Suspende o Reactiva al usuario en lugar de borrarlo
    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->email === 'adminUsuarios@gmail.com' || $usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes suspender tu propia cuenta.');
        }

        // Cambiamos el estado al contrario del que tenga 
        $usuario->is_active = !$usuario->is_active;
        $usuario->save();

        $mensaje = $usuario->is_active ? 'Usuario reactivado.' : 'Usuario suspendido temporalmente.';
        return back()->with('success', $mensaje);
    }


    public function forceDelete($id)
    {
        $usuario = User::findOrFail($id);

        // No dejarse borrar a sí mismo ni al admin principal
        if ($usuario->email === 'adminUsuarios@gmail.com' || $usuario->id === auth()->id()) {
            return back()->with('error', 'Acción prohibida: No puedes eliminar permanentemente tu propia cuenta.');
        }

        $usuario->delete(); 

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado permanentemente de la base de datos.');
    }

    // 4. ENVIAR RECOMENDACIÓN
    public function enviarRecomendacion($id)
    {
        $usuario = User::findOrFail($id);

        // 1. Buscamos la última compra del usuario
        $ultimaOrden = Order::with('orderItems.producto')
            ->where('user_id', $usuario->id)
            ->latest('created_at')
            ->first();

        if (!$ultimaOrden || $ultimaOrden->orderItems->isEmpty()) {
            return redirect()->back()->with('error', 'Este usuario aún no ha realizado ninguna compra válida para recomendarle algo.');
        }

        // 2. Cogemos los datos del primer producto de esa última compra
        $primerItem = $ultimaOrden->orderItems->first();
        $categoriaId = $primerItem->producto->categoria_id;
        $productoCompradoId = $primerItem->producto_id;

        // 3. Buscamos un producto similar (misma categoría, distinto ID, con stock)
        $recomendado = Producto::where('categoria_id', $categoriaId)
            ->where('id', '!=', $productoCompradoId)
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->first();

        if ($recomendado) {
            // 4. Enviamos el correo
            Mail::to($usuario->email)->send(new RecomendacionProductoMail($usuario, $recomendado));
            
            return redirect()->back()->with('success', '¡Correo de recomendación enviado a ' . $usuario->email . ' con éxito!');
        }

        return redirect()->back()->with('error', 'No hay productos similares en el catálogo para recomendarle ahora mismo.');
    }

}