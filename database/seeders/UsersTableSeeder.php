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
        // 1. Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@healix.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // 2. Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@healix.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // 3. Sales
        $sales = User::create([
            'name' => 'Sales User',
            'email' => 'sales@healix.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $sales->assignRole('sales');

        // 4. Support
        $support = User::create([
            'name' => 'Support User',
            'email' => 'support@healix.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $support->assignRole('support');

        // 5. Deployment/Tech
        $tech = User::create([
            'name' => 'Tech User',
            'email' => 'tech@healix.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $tech->assignRole('deployment');

        // 6. Finance
        $finance = User::create([
            'name' => 'Finance User',
            'email' => 'finance@healix.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $finance->assignRole('finance');

        // 7. Manager (Read-only)
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@healix.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('manager');

        $this->command->info('Users seeded successfully!');
    }
}