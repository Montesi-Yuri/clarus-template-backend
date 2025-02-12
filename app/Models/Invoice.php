<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'client_id',
        'number',
        'date',
        'due_date',
        'total',
        'tax',
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('number', 'like', "%{$search}%")
                    ->orWhere('total', 'like', "%{$search}%");
            });
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        });

        $query->when($filters['date_from'] ?? null, function ($query, $date) {
            $query->where('date', '>=', $date);
        });

        $query->when($filters['date_to'] ?? null, function ($query, $date) {
            $query->where('date', '<=', $date);
        });

        $query->when($filters['customer_id'] ?? null, function ($query, $customerId) {
            $query->where('customer_id', $customerId);
        });
    }
} 