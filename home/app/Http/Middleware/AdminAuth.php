<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si NO está autenticado en la base de datos
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $rol = Auth::user()->rol;
        
        if(in_array($rol, ['admin_usuarios', 'admin_productos', 'admin'])) {
            return $next($request);
        }
        
        return redirect()->route('login')->withErrors(['email' => 'Acceso denegado: Se requieren privilegios de administración.']);
    }
}