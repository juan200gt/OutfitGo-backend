<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerificarRol
{
    /**
     * Handle an incoming request.
     *
     * 
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (empty($roles)) {
            return $next($request);
        }

        // Si el usuario tiene un rol que está en la lista de permitidos
        if (in_array($user->rol, $roles)) {
            return $next($request);
        }

        // Si es admin total también lo dejamos pasar por seguridad
        if ($user->rol === 'admin') {
            return $next($request);
        }

        abort(403, 'No tienes permiso para acceder a esta área.');
    }
}
