<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Define users array
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@healix.com',
                'role' => 'super-admin'
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@healix.com',
                'role' => 'admin'
            ],
            [
                'name' => 'Sales User',
                'email' => 'sales@healix.com',
                'role' => 'sales'
            ],
            [
                'name' => 'Support User',
                'email' => 'support@healix.com',
                'role' => 'support'
            ],
            [
                'name' => 'Tech User',
                'email' => 'tech@healix.com',
                'role' => 'deployment'
            ],
            [
                'name' => 'Finance User',
                'email' => 'finance@healix.com',
                'role' => 'finance'
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@healix.com',
                'role' => 'manager'
            ]
        ];

        foreach ($users as $userData) {
            // FirstOrCreate prevents duplicate emails
            $user = User::firstOrCreate(
                ['email' => $userData['email']], // Check by email
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            // Assign role if user was just created or if we want to ensure role is assigned
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }

        $this->command->info('Users seeded successfully! Duplicates were skipped.');
    }
}