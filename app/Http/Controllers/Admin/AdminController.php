<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $admins = User::where('is_admin', true)->get();
        return $this->success($admins);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => true,
        ]);

        return $this->success($admin, 'Admin created successfully', 201);
    }

    public function removeAdmin(User $user)
    {
        if (!$user->is_admin) {
            return $this->error('User is not an admin', 400);
        }

        $user->update(['is_admin' => false]);
        return $this->success(null, 'Admin privileges removed successfully');
    }
} 