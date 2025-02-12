<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'enabled',
        'is_core',
        'order',
        'requires_permission'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'is_core' => 'boolean',
        'requires_permission' => 'boolean'
    ];

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)
            ->withPivot('is_active', 'valid_until')
            ->withTimestamps();
    }

    public function isAccessibleBy(User $user): bool
    {
        // Gli admin hanno accesso a tutti i moduli attivi
        if ($user->isAdmin()) {
            return $this->enabled;
        }

        // Verifica se il modulo è attivo per il tenant dell'utente
        $tenantModule = $user->tenant?->modules()
            ->where('module_id', $this->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->first();

        if (!$tenantModule) {
            return false;
        }

        // Se il modulo richiede permessi, verifica che l'utente li abbia
        if ($this->requires_permission) {
            return $user->hasPermissionTo("access-{$this->name}");
        }

        return true;
    }
} 