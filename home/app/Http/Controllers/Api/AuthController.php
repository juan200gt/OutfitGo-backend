<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'codigo_postal' => $request->codigo_postal,
            'provincia' => $request->provincia,
            'telefono' => $request->telefono,   
            'is_active' => true,
            // Registrar si el usuario marco la casilla de la newsletter al registrarse
            'newsletter' => $request->boolean('newsletter'),
        ]);

        // Disparar el evento de registro (esto envía el correo de verificación si MustVerifyEmail está implementado)
        event(new Registered($user));

        return response()->json([
            'message' => 'Usuario registrado exitosamente. Por favor, revisa tu correo para verificar tu cuenta.',
            'user' => $user,
        ], 201);
    }

    /**
     * Iniciar sesión.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Si la cuenta del usuario esta suspendida no puede entrar.
        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta ha sido suspendida. Contacta con el administrador.'],
            ]);
        }

        // Si el correo no ha sido verificado y no es administrador, no puede entrar.
        if ($user && !$user->hasVerifiedEmail() && !Str::startsWith($user->rol, 'admin')) {
            throw ValidationException::withMessages([
                'email' => ['Su correo no está verificado. Por favor, revisa tu bandeja de entrada.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        // Se eliminan todos los tokens del usuario actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Cierre de sesión exitoso',
        ]);
    }

    /**
     * Actualizar perfil del usuario autenticado.
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Validamos los datos
        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:8', // Opcional, por si quiere cambiarla
            'direccion'     => 'nullable|string|max:255',
            'ciudad'        => 'nullable|string|max:100',
            'provincia'     => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'telefono'      => 'nullable|string|max:20',
        ]);

        // Si el usuario escribió una contraseña nueva, la encriptamos. Si la dejó vacía, la quitamos para no sobreescribirla con nada.
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Actualizamos la base de datos
        $user->update($validated);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user'    => $user
        ]);
    }

    /**
     * Verificar el correo electrónico.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);
        $frontendUrl = env('FRONTEND_URL', 'https://outfitgo.duckdns.org');

        // 1. Caso de Error: Enlace inválido
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response($this->getHtmlResponse(
                false, 
                'Enlace Inválido', 
                'El enlace de verificación ha expirado o es incorrecto. Por favor, solicita un nuevo correo de verificación desde la aplicación.',
                $frontendUrl
            ));
        }

        // 2. Caso de éxito: Ya verificado
        if ($user->hasVerifiedEmail()) {
            return response($this->getHtmlResponse(
                true, 
                'Cuenta ya Activada', 
                'Tu correo electrónico ya había sido verificado previamente. Ya puedes iniciar sesión en la tienda.',
                $frontendUrl
            ));
        }

        // 3. Caso de éxito: Verificado ahora
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response($this->getHtmlResponse(
            true, 
            '¡Correo Verificado con Éxito!', 
            'Tu cuenta ha sido activada correctamente. Todo está listo para que accedas a tu catálogo de moda.',
            $frontendUrl
        ));
    }

    /**
     * Generar plantilla HTML premium para la pantalla de respuesta.
     */
    private function getHtmlResponse(bool $success, string $title, string $message, string $frontendUrl): string
    {
        $iconColor = $success ? 'text-emerald-500 bg-emerald-50' : 'text-rose-500 bg-rose-50';
        $iconSvg = $success 
            ? '<svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            : '<svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
        $buttonColor = $success ? 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' : 'bg-slate-700 hover:bg-slate-800 focus:ring-slate-600';
        $buttonText = $success ? 'Ir a la Tienda (Iniciar Sesión)' : 'Volver a la Tienda';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OutfitGo | Verificación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-3xl shadow-xl p-8 max-w-md w-full text-center border border-slate-100 transition-all duration-300 transform hover:scale-[1.01]">
        <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-6 {$iconColor}">
            {$iconSvg}
        </div>
        <h1 class="text-2xl font-bold text-slate-800 mb-3">{$title}</h1>
        <p class="text-slate-500 text-sm leading-relaxed mb-8">{$message}</p>
        <a href="{$frontendUrl}/login" class="inline-block w-full text-white font-semibold px-6 py-3.5 rounded-2xl transition-all duration-200 shadow-lg hover:shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-offset-2 {$buttonColor}">
            {$buttonText}
        </a>
        <p class="text-xs text-slate-400 mt-6">&copy; 2026 OutfitGo. Todos los derechos reservados.</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Enviar el link de restablecimiento de contraseña.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Te hemos enviado por correo el enlace para restablecer tu contraseña.']);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Restablecer la contraseña.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Tu contraseña ha sido restablecida exitosamente.']);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
