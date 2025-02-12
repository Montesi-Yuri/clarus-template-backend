<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends SpatieRole
{
    use HasTenantScope;

    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id',
        'display_name',
        'description',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            config('permission.table_names.role_has_permissions'),
            config('permission.column_names.role_pivot_key', 'role_id'),
            config('permission.column_names.permission_pivot_key', 'permission_id')
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.role_pivot_key', 'role_id'),
            'model_id'
        )->where('model_type', User::class);
    }

    /**
     * Override del metodo di Spatie per supportare il tenant
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (is_string($permission)) {
            $permission = $this->getPermissionClass()->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        return $this->permissions->contains($permission);
    }

    /**
     * Override del metodo di Spatie per supportare il tenant
     */
    public function hasAnyPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Override del metodo di Spatie per supportare il tenant
     */
    public function hasAllPermissions(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Override per assicurarsi che il nome del ruolo sia unico per tenant
     */
    public function getTable()
    {
        return config('permission.table_names.roles', parent::getTable());
    }

    protected static function boot()
    {
        parent::boot();

        // Assicuriamoci che ci possa essere solo un ruolo predefinito per tenant
        static::saving(function ($role) {
            if ($role->is_default) {
                static::where('tenant_id', $role->tenant_id)
                    ->where('id', '!=', $role->id)
                    ->update(['is_default' => false]);
            }
        });
    }
} 