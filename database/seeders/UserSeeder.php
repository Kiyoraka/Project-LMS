<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin for production testing
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Teacher for production testing
        User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'teacher',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Student for production testing
        User::create([
            'name' => 'Test Student',
            'email' => 'student@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'student',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
