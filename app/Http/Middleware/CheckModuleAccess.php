<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Module;
use Illuminate\Http\Request;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleName)
    {
        $user = $request->user();
        $module = Module::where('name', $moduleName)->first();

        if (!$module) {
            abort(404, 'Modulo non trovato');
        }

        // Verifica se il modulo è attivo per il tenant
        if (!$user->isAdmin() && !$user->tenant->hasModule($moduleName)) {
            abort(403, 'Il tuo tenant non ha accesso a questo modulo');
        }

        // Verifica se l'utente ha i permessi necessari per accedere al modulo
        if (!$module->isAccessibleBy($user)) {
            abort(403, 'Non hai i permessi necessari per accedere a questo modulo');
        }

        return $next($request);
    }
} 