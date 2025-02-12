<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('manage-roles');
    }

    public function view(User $user, Role $role): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasPermissionTo('manage-roles') && 
               $user->tenant_id === $role->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('manage-roles');
    }

    public function update(User $user, Role $role): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasPermissionTo('manage-roles') && 
               $user->tenant_id === $role->tenant_id;
    }

    public function delete(User $user, Role $role): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasPermissionTo('manage-roles') && 
               $user->tenant_id === $role->tenant_id;
    }
} 