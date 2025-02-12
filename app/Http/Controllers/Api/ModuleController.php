<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $modules = Module::orderBy('order')
            ->get()
            ->filter(function ($module) use ($request) {
                return $module->isAccessibleBy($request->user());
            })
            ->map(function ($module) use ($request) {
                $tenant = $request->user()->tenant;
                $tenantModule = $tenant ? $tenant->modules()->where('module_id', $module->id)->first() : null;

                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'display_name' => $module->display_name,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'enabled' => $module->enabled,
                    'is_core' => $module->is_core,
                    'order' => $module->order,
                    'requires_permission' => $module->requires_permission,
                    'is_active' => $tenantModule ? $tenantModule->pivot->is_active : false,
                    'valid_until' => $tenantModule ? $tenantModule->pivot->valid_until : null,
                    'is_accessible' => $module->isAccessibleBy($request->user())
                ];
            });

        return response()->json($modules);
    }

    public function getActiveModules(Request $request)
    {
        $user = $request->user();

        // Se l'utente è admin di sistema, mostra tutti i moduli attivi
        if ($user->isAdmin()) {
            $modules = Module::where('enabled', true)
                ->orderBy('order')
                ->get()
                ->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                        'display_name' => $module->display_name,
                        'description' => $module->description,
                        'icon' => $module->icon,
                        'requires_permission' => $module->requires_permission
                    ];
                });

            return response()->json($modules);
        }

        // Per gli utenti normali, filtra per tenant e permessi
        $modules = Module::whereHas('tenants', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                });
        })
        ->orderBy('order')
        ->get()
        ->filter(function ($module) use ($user) {
            return $module->isAccessibleBy($user);
        })
        ->map(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'display_name' => $module->display_name,
                'description' => $module->description,
                'icon' => $module->icon,
                'requires_permission' => $module->requires_permission
            ];
        });

        return response()->json($modules);
    }
} 