<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    use AuthorizesRequests;

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'roles' => 'required|array',
            'roles.*' => [
                'exists:roles,id',
                function ($attribute, $value, $fail) use ($request, $user) {
                    $role = Role::find($value);
                    // Se l'utente è admin, può assegnare qualsiasi ruolo
                    if (!$user->isAdmin() && $role->tenant_id !== $user->tenant_id) {
                        $fail('Il ruolo selezionato non appartiene al tenant dell\'utente.');
                    }
                }
            ]
        ]);

        DB::table(config('permission.table_names.model_has_roles'))
            ->where('model_id', $user->id)
            ->where('model_type', get_class($user))
            ->delete();

        $roleRecords = collect($request->roles)->map(function ($roleId) use ($user) {
            return [
                'role_id' => $roleId,
                'model_type' => get_class($user),
                'model_id' => $user->id
            ];
        })->all();

        DB::table(config('permission.table_names.model_has_roles'))->insert($roleRecords);

        return response()->json([
            'message' => 'Ruoli aggiornati con successo',
            'roles' => $user->roles()->with('tenant')->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'tenant' => $role->tenant ? [
                        'id' => $role->tenant->id,
                        'name' => $role->tenant->name
                    ] : null
                ];
            })
        ]);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        // Ottieni i ruoli disponibili in base al tipo di utente
        $availableRoles = Role::when(!$user->isAdmin(), function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        })
        ->with('tenant') // Carica la relazione con il tenant
        ->get()
        ->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'tenant' => $role->tenant ? [
                    'id' => $role->tenant->id,
                    'name' => $role->tenant->company_name
                ] : null
            ];
        });

        return response()->json([
            'name' => $user->name,
            'roles' => $user->roles()->with('tenant')->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'tenant' => $role->tenant ? [
                        'id' => $role->tenant->id,
                        'name' => $role->tenant->company_name
                    ] : null
                ];
            }),
            'available_roles' => $availableRoles
        ]);
    }
} 