<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CheckCustomerTenant
{
    use HttpResponses;

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $this->error('Unauthorized', 401);
        }

        if ($request->route('customer')) {
            $customer = $request->route('customer');
            if ($customer->tenant_id !== Auth::user()->tenant_id) {
                return $this->error('Unauthorized', 403);
            }
        }
        
        return $next($request);
    }
} 