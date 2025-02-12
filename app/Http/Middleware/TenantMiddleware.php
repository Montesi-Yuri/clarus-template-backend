<?php

namespace App\Http\Middleware;

use Closure;

class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        // Assicurati che il tenant venga gestito correttamente qui
        $tenant = app('tenant'); // Questo è ok
        // NON:
        // $tenant($request); // Questo causerebbe l'errore
        
        return $next($request);
    }
} 