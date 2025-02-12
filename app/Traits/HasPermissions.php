<?php

namespace App\Traits;

use Illuminate\Support\Facades\Gate;

trait HasPermissions
{
    public static function bootHasPermissions()
    {
        static::creating(function ($model) {
            if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create-' . $model->getTable())) {
                abort(403, 'Unauthorized action.');
            }
        });

        static::updating(function ($model) {
            if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update-' . $model->getTable())) {
                abort(403, 'Unauthorized action.');
            }
        });

        static::deleting(function ($model) {
            if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete-' . $model->getTable())) {
                abort(403, 'Unauthorized action.');
            }
        });
    }

    public static function authorizeResource()
    {
        Gate::define('viewAny', function ($user, $model) {
            return $user->isAdmin() || $user->hasPermission('view-' . $model->getTable());
        });

        Gate::define('view', function ($user, $model) {
            return $user->isAdmin() || $user->hasPermission('view-' . $model->getTable());
        });

        Gate::define('create', function ($user, $model) {
            return $user->isAdmin() || $user->hasPermission('create-' . $model->getTable());
        });

        Gate::define('update', function ($user, $model) {
            return $user->isAdmin() || $user->hasPermission('update-' . $model->getTable());
        });

        Gate::define('delete', function ($user, $model) {
            return $user->isAdmin() || $user->hasPermission('delete-' . $model->getTable());
        });
    }
} 