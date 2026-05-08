<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Muestra el formulario de login
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($request->email === 'outfitgotfg@gmail.com' && $request->password === 'OutfitGo123') {
            $user = User::firstOrCreate(
                ['email' => 'outfitgotfg@gmail.com'],
                [
                    'name' => 'Admin Global',
                    'password' => Hash::make('OutfitGo123'),
                    'rol' => 'admin',
                ]
            );

            if (!Hash::check('OutfitGo123', $user->password) || $user->rol !== 'admin') {
                $user->password = Hash::make('OutfitGo123');
                $user->rol = 'admin';
                $user->save();
            }

            Auth::login($user);
            $request->session()->regenerate();
            
            return view('login', ['show_admin_buttons' => true]);
        }

        // Login normal para otros administradores
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $rol = Auth::user()->rol;

            // Redirigir según rol por defecto
            if (in_array($rol, ['admin_usuarios', 'admin'])) {
                return redirect()->route('admin.usuarios.index');
            } else if ($rol === 'admin_productos') {
                return redirect()->route('admin.productos.index'); 
            }
            
            // Si es un cliente normal
            Auth::logout();
            return back()->withErrors(['email' => 'Tu usuario no tiene rol de administrador.']);
        }

        return back()->withErrors(['email' => 'Credenciales de administrador incorrectos.']);
    }

    // Para cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }   
}