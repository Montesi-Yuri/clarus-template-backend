<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasTenant;
use App\Traits\Filterable;
use Illuminate\Validation\ValidationException;

class Customer extends Model
{
    use HasFactory, HasTenant, Filterable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'is_individual',
        'is_company',
        'company_name',
        'vat_number',
        'fiscal_code',
        'sdi_code',
        'is_private',
        'is_public',
        'email',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'website',
        'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'birth_date' => 'date',
        'is_individual' => 'boolean',
        'is_company' => 'boolean',
        'is_private' => 'boolean',
        'is_public' => 'boolean',
    ];

    protected $filterable = [
        'search' => ['company_name', 'email', 'vat_number', 'first_name', 'last_name', 'fiscal_code'],
        'city',
        'country'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scope per i filtri
    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('company_name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        });

        $query->when($filters['city'] ?? null, function ($query, $city) {
            $query->where('city', $city);
        });

        $query->when($filters['country'] ?? null, function ($query, $country) {
            $query->where('country', $country);
        });

        return $query;
    }

    public static function boot()
    {
        parent::boot();

        // Gestione della validazione per creazione e aggiornamento
        static::saving(function ($customer) {
            // Saltiamo la validazione se stiamo ripristinando
            if ($customer->isDirty('deleted_at') && $customer->deleted_at === null) {
                return;
            }

            $existingCustomer = static::where('tenant_id', $customer->tenant_id)
                ->where('vat_number', $customer->vat_number)
                ->where('id', '!=', $customer->id)
                ->whereNull('deleted_at')
                ->first();

            if ($existingCustomer) {
                throw ValidationException::withMessages([
                    'vat_number' => [
                        "La partita IVA {$customer->vat_number} è già registrata per il cliente {$existingCustomer->company_name}. " .
                        "Per procedere, inserisci una partita IVA diversa."
                    ]
                ]);
            }
        });

        // Gestione della validazione per il ripristino
        static::restoring(function ($customer) {
            $existingCustomer = static::where('tenant_id', $customer->tenant_id)
                ->where('vat_number', $customer->vat_number)
                ->where('id', '!=', $customer->id)
                ->whereNull('deleted_at')
                ->first();

            if ($existingCustomer) {
                throw ValidationException::withMessages([
                    'vat_number' => [
                        "Impossibile ripristinare il cliente {$customer->company_name} poiché la partita IVA {$customer->vat_number} " .
                        "è già in uso dal cliente {$existingCustomer->company_name}. " .
                        "Per procedere con il ripristino, è necessario prima modificare la partita IVA di uno dei due clienti."
                    ]
                ]);
            }
        });
    }

    public function restore()
    {
        return parent::restore();
    }
} 