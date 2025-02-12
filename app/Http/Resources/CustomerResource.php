<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'is_individual' => $this->is_individual,
            'is_company' => $this->is_company,
            'company_name' => $this->company_name,
            'vat_number' => $this->vat_number,
            'fiscal_code' => $this->fiscal_code,
            'sdi_code' => $this->sdi_code,
            'is_private' => $this->is_private,
            'is_public' => $this->is_public,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 