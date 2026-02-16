<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // User Management
            'create users', 'edit users', 'delete users', 'view users',
            // Role Management
            'create roles','edit roles','delete roles','view roles',
            
            // Client Management
            'create clients', 'edit clients', 'delete clients', 'view clients',
            
            // Subscriptions
            'create subscription plans','edit subscription plans','delete subscription plans','view subscription plans',
             'create subscriptions', 'view subscriptions', 'edit subscriptions','delete subscriptions',
            
            // Tickets
            'create tickets', 'edit tickets', 'close tickets', 'view tickets',
            
            // Versions & Deployments
            'create versions','edit versions','view versions','delete versions',
            'edit deployments', 'record deployments', 'view deployments',
            
            // Communications
            'send emails', 'send sms', 'view communication logs','view communication templates',
            'create communication templates','edit communication templates','delete communication templates',
            'view communication campaigns','create communication campaigns','edit communication campaigns','delete communication campaigns',
            
            // Reports
            'export reports','create reports','view reports','view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles
        $roles = [
            'super-admin' => Permission::all()->pluck('name')->toArray(),
            'admin' => [
                'create users', 'edit users', 'view users',
                'create clients', 'edit clients', 'view clients',
                'create subscription plans','edit subscription plans','delete subscription plans','view subscription plans',
                'create subscriptions', 'view subscriptions', 'edit subscriptions','delete subscriptions',
                'create tickets', 'edit tickets', 'close tickets', 'view tickets',
                'create versions','edit versions','view versions','delete versions',
                'edit deployments', 'record deployments', 'view deployments',
                'send emails', 'send sms', 'view communication logs','view communication templates',
               'create communication templates','edit communication templates','delete communication templates',
               'view communication campaigns','create communication campaigns','edit communication campaigns',
               'delete communication campaigns',
               'export reports','create reports','view reports','view audit logs'
            ],
            'sales' => [
                'create clients', 'edit clients', 'view clients',
                'send emails', 'send sms',
            ],
            'support' => [
                'create tickets', 'edit tickets', 'close tickets', 'view tickets',
                'view clients', 'send emails',
            ],
            'deployment' => [
                'create versions','edit versions','view versions','delete versions',
            'edit deployments', 'record deployments', 'view deployments',
                'view clients',
            ],
            'finance' => [
                'view subscriptions','create subscription plans',
                'edit subscription plans','delete subscription plans','view subscription plans' , 'export reports',
            ],
            'manager' => [
                 'export reports', 'view clients',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
