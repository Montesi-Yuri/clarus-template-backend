<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $roles = Role::with(['permissions', 'tenant'])
            ->when(!$request->user()->isAdmin(), function ($query) use ($request) {
                $query->where('tenant_id', $request->user()->tenant_id);
            })
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'is_default' => $role->is_default,
                    'permissions' => $role->permissions->pluck('name'),
                    'users_count' => $role->users()->count(),
                    'tenant' => $role->tenant ? [
                        'id' => $role->tenant->id,
                        'company_name' => $role->tenant->company_name
                    ] : null
                ];
            });

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 
                'string', 
                Rule::unique('roles')->where(function ($query) use ($request) {
                    return $query->where('tenant_id', $request->tenant_id)
                                ->where('guard_name', 'web');
                })
            ],
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
            'tenant_id' => 'nullable|exists:tenants,id'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_default' => $request->is_default,
            'guard_name' => 'web',
            'tenant_id' => $request->tenant_id
        ]);

        $role->givePermissionTo($request->permissions);

        return response()->json([
            'message' => 'Ruolo creato con successo',
            'role' => $role->load('permissions')
        ]);
    }

    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
            'is_default' => $role->is_default,
            'permissions' => $role->permissions->pluck('name'),
            'users_count' => $role->users()->count()
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $request->validate([
            'name' => [
                'required', 
                'string', 
                Rule::unique('roles')->where(function ($query) use ($request, $role) {
                    return $query->where('tenant_id', $request->user()->tenant_id)
                                ->where('guard_name', 'web')
                                ->where('id', '!=', $role->id);
                })
            ],
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_default' => $request->is_default
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Ruolo aggiornato con successo',
            'role' => $role->load('permissions')
        ]);
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Non è possibile eliminare un ruolo assegnato a degli utenti'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Ruolo eliminato con successo'
        ]);
    }

    public function permissions()
    {
        $permissions = Permission::all()->groupBy('group')->map(function ($group) {
            return $group->map(function ($permission) {
                return [
                    'name' => $permission->name,
                    'display_name' => $permission->display_name
                ];
            });
        });

        return response()->json($permissions);
    }

    public function getAvailableTenants()
    {
        $user = request()->user();
        
        // Se l'utente è admin, può vedere tutti i tenant
        if ($user->isAdmin()) {
            $tenants = \App\Models\Tenant::select('id', 'company_name')->get();
        } else {
            // Altrimenti vede solo il proprio tenant
            $tenants = \App\Models\Tenant::where('id', $user->tenant_id)
                ->select('id', 'company_name')
                ->get();
        }

        return response()->json($tenants);
    }
} 