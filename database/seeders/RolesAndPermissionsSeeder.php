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

        // Define ALL permissions
        $permissions = [
            // User Management
            'create users', 'edit users', 'delete users', 'view users',
            
            // Role Management
            'create roles', 'edit roles', 'delete roles', 'view roles',
            
            // Client Management
            'create clients', 'edit clients', 'delete clients', 'view clients',
            'change client status', 'export clients', 'manage client contacts',
            'upload client documents', 'delete client documents', 'add client notes',
            'create client tasks', 'complete client tasks', 'view client timeline',
            
            // Subscription Plans
            'create subscription plans', 'edit subscription plans', 
            'delete subscription plans', 'view subscription plans',
            
            // Client Subscriptions
            'create subscriptions', 'view subscriptions', 'edit subscriptions', 
            'delete subscriptions', 'assign subscriptions', 'cancel subscriptions',
            'view client subscriptions', 'process renewals', 'record payments',
            'view payment history', 'view renewals report', 'export subscription reports',
            
            // Tickets
            'create tickets', 'edit tickets', 'close tickets', 'view tickets',
            'assign tickets', 'resolve tickets', 'add ticket comments',
            
            // Versions & Deployments
            'create versions', 'edit versions', 'view versions', 'delete versions',
            'edit deployments', 'record deployments', 'view deployments',
            
            // Communications
            'send emails', 'send sms', 'view communication logs', 'view communication templates',
            'create communication templates', 'edit communication templates', 'delete communication templates',
            'view communication campaigns', 'create communication campaigns', 
            'edit communication campaigns', 'delete communication campaigns',
            
            // Reports
            'export reports', 'create reports', 'view reports', 'view audit logs',
            
            // Dashboard
            'view dashboard',
            
            // ===== SETTINGS MANAGEMENT =====
            'manage settings',
            'manage users',
            'manage roles',
            'manage organization types',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Define roles with their permissions
        $roles = [
            'super-admin' => Permission::all()->pluck('name')->toArray(),
            
            'admin' => [
                // User Management
                'create users', 'edit users', 'view users',
                
                // Client Management
                'create clients', 'edit clients', 'delete clients', 'view clients',
                'change client status', 'export clients', 'manage client contacts',
                'upload client documents', 'delete client documents', 'add client notes',
                'create client tasks', 'complete client tasks', 'view client timeline',
                
                // Subscription Plans
                'create subscription plans', 'edit subscription plans', 
                'delete subscription plans', 'view subscription plans',
                
                // Client Subscriptions
                'create subscriptions', 'view subscriptions', 'edit subscriptions',
                'assign subscriptions', 'cancel subscriptions', 'view client subscriptions',
                'process renewals', 'record payments', 'view payment history',
                'view renewals report', 'export subscription reports',
                
                // Tickets
                'create tickets', 'edit tickets', 'close tickets', 'view tickets',
                'assign tickets', 'resolve tickets', 'add ticket comments',
                
                // Versions & Deployments
                'create versions', 'edit versions', 'view versions', 'delete versions',
                'edit deployments', 'record deployments', 'view deployments',
                
                // Communications
                'send emails', 'send sms', 'view communication logs', 'view communication templates',
                'create communication templates', 'edit communication templates', 'delete communication templates',
                'view communication campaigns', 'create communication campaigns', 
                'edit communication campaigns', 'delete communication campaigns',
                
                // Reports
                'export reports', 'create reports', 'view reports', 'view audit logs',
                
                // Dashboard
                'view dashboard',
                
                // ===== SETTINGS =====
                'manage settings',
                'manage users',
                'manage roles',
                'manage organization types',
            ],
            
            'sales' => [
                'create clients', 'edit clients', 'view clients',
                'change client status', 'add client notes', 'create client tasks',
                'view subscription plans', 'view client subscriptions',
                'send emails', 'send sms', 'view dashboard',
                'view reports', 'export reports',
            ],
            
            'support' => [
                'view clients', 'view client timeline',
                'create tickets', 'edit tickets', 'close tickets', 'view tickets',
                'assign tickets', 'resolve tickets', 'add ticket comments',
                'send emails', 'view subscription plans', 'view client subscriptions',
                'view dashboard',
            ],
            
            'deployment' => [
                'view clients',
                'create versions', 'edit versions', 'view versions', 'delete versions',
                'edit deployments', 'record deployments', 'view deployments',
                'view subscription plans', 'view client subscriptions',
                'view dashboard',
            ],
            
            'finance' => [
                'view clients',
                'view subscription plans', 'create subscription plans',
                'edit subscription plans', 'delete subscription plans',
                'view client subscriptions', 'process renewals', 'record payments',
                'view payment history', 'view renewals report', 'export subscription reports',
                'view reports', 'export reports', 'view dashboard',
            ],
            
            'manager' => [
                'view clients', 'view subscription plans', 'view client subscriptions',
                'view tickets', 'view versions', 'view deployments',
                'view reports', 'export reports', 'view dashboard',
                'view audit logs',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}