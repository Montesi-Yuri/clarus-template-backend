<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Yuri Montesi',
            'email' => 'yuri.montesi@clarus.it',
            'password' => Hash::make('hncUK7owsm6NrNW3KDE2'),
            'is_admin' => true
        ]);
    }
} 