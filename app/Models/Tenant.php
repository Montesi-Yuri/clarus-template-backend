<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'vat_number',
        'address',
        'city',
        'postal_code',
        'country'
    ];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function customers() {
        return $this->hasMany(Customer::class);
    }

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->withPivot('is_active', 'valid_until')
            ->withTimestamps();
    }

    public function hasModule($moduleName)
    {
        return $this->modules()
            ->where('name', $moduleName)
            ->where('is_active', true)
            ->whereNull('valid_until')
            ->orWhere('valid_until', '>', now())
            ->exists();
    }
} 