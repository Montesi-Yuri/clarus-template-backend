<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('manage-users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Un utente può vedere solo gli utenti del proprio tenant
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return $user->hasPermissionTo('manage-users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('manage-users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Un utente non può modificare un admin
        if ($model->isAdmin()) {
            return false;
        }

        // Un utente può modificare solo gli utenti del proprio tenant
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return $user->hasPermissionTo('manage-users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Un utente non può eliminare un admin
        if ($model->isAdmin()) {
            return false;
        }

        // Un utente può eliminare solo gli utenti del proprio tenant
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return $user->hasPermissionTo('manage-users');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
