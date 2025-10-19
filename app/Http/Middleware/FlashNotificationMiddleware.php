<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FlashNotificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Defina uma mensagem flash genérica (você pode personalizar isso)
        // session()->flash('error', 'Esta é uma mensagem de erro. Fale com desenvolvedor');
        // session()->flash('success', 'Teste de sucesso. Passou no erro');
        // session()->flash('info', 'Informações por default.');

        return $next($request);
    }
}
