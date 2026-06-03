<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@siarea.web.id',
            'password' => Hash::make('adminsiarea@'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create demo employees
        $shift = Shift::first();
        
        // Employee 1
        $user1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@siarea.local',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'status' => 'active',
        ]);

        Employee::create([
            'user_id' => $user1->id,
            'shift_id' => $shift->id,
            'full_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'basic_salary' => 3000000,
            'late_deduction_amount' => 50000,
            'hire_date' => now()->format('Y-m-d'),
            'status' => 'active',
        ]);

        // Employee 2
        $user2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@siarea.local',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'status' => 'active',
        ]);

        Employee::create([
            'user_id' => $user2->id,
            'shift_id' => $shift->id,
            'full_name' => 'Siti Nurhaliza',
            'phone' => '081234567891',
            'basic_salary' => 2500000,
            'late_deduction_amount' => 50000,
            'hire_date' => now()->format('Y-m-d'),
            'status' => 'active',
        ]);
    }
}
