<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;

class TenantController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $tenants = Tenant::with('modules')->get();
        return $this->success($tenants);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('modules');
        return $this->success($tenant);
    }
} 