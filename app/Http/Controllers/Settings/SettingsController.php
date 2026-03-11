<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OrganizationType;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check permission helper
     */
    private function checkPermission($permission)
    {
        if (!auth()->user()->can($permission)) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Display the settings page with all data
     */
    public function index()
    {
        try {
            // Check permission for settings access
            $this->checkPermission('manage settings');

            // Get users with their roles
            $users = User::with('roles')
                ->orderBy('name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name'),
                        'is_active' => $user->is_active ?? true,
                        'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                    ];
                });

            // Get roles with permissions
            $roles = Role::with('permissions')
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                            ];
                        }),
                        'users_count' => $role->users()->count(),
                        'guard_name' => $role->guard_name,
                    ];
                });

            // Get all permissions grouped
            $permissions = Permission::orderBy('name')
                ->get()
                ->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                });

            // Get organization types with error handling
            try {
                $orgTypes = OrganizationType::withCount('clients')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($type) {
                        return [
                            'id' => $type->id,
                            'name' => $type->name,
                            'description' => $type->description,
                            'is_active' => $type->is_active ?? true,
                            'color' => $type->color ?? '#14b8a6',
                            'icon' => $type->icon ?? 'building',
                            'display_order' => $type->display_order ?? 0,
                            'clients_count' => $type->clients_count,
                            'created_at' => $type->created_at?->format('Y-m-d H:i:s'),
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Failed to fetch organization types: ' . $e->getMessage());
                $orgTypes = [];
            }

            // Log success for debugging
            Log::info('Settings page loaded successfully', [
                'users_count' => $users->count(),
                'roles_count' => $roles->count(),
                'orgTypes_count' => count($orgTypes)
            ]);

            return Inertia::render('Settings/Index', [
                'users' => $users,
                'roles' => $roles,
                'permissions' => $permissions,
                'orgTypes' => $orgTypes,
            ]);

        } catch (\Exception $e) {
            Log::error('SettingsController index error: ' . $e->getMessage());
            
            // Return empty data with error message in session
            return Inertia::render('Settings/Index', [
                'users' => [],
                'roles' => [],
                'permissions' => [],
                'orgTypes' => [],
                'error' => 'Failed to load settings: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get users for API requests
     */
    public function getUsers()
    {
        try {
            $this->checkPermission('manage users');

            $users = User::with('roles')
                ->orderBy('name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name'),
                        'is_active' => $user->is_active ?? true,
                        'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('getUsers error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get roles for API requests
     */
    public function getRoles()
    {
        try {
            $this->checkPermission('manage roles');

            $roles = Role::with('permissions')
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                            ];
                        }),
                        'users_count' => $role->users()->count(),
                        'guard_name' => $role->guard_name,
                    ];
                });

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error('getRoles error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get permissions for API requests
     */
    public function getPermissions()
    {
        try {
            $this->checkPermission('manage roles');

            $permissions = Permission::orderBy('name')
                ->get()
                ->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                });

            return response()->json($permissions);
        } catch (\Exception $e) {
            Log::error('getPermissions error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get organization types for API requests
     */
    public function getOrganizationTypes()
    {
        try {
            $this->checkPermission('manage organization types');

            $orgTypes = OrganizationType::withCount('clients')
                ->orderBy('name')
                ->get()
                ->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'description' => $type->description,
                        'is_active' => $type->is_active ?? true,
                        'color' => $type->color ?? '#14b8a6',
                        'icon' => $type->icon ?? 'building',
                        'display_order' => $type->display_order ?? 0,
                        'clients_count' => $type->clients_count,
                        'created_at' => $type->created_at?->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json($orgTypes);
        } catch (\Exception $e) {
            Log::error('getOrganizationTypes error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get dashboard stats
     */
    public function getStats()
    {
        try {
            $this->checkPermission('manage settings');

            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_roles' => Role::count(),
                'total_permissions' => Permission::count(),
                'total_org_types' => OrganizationType::count(),
                'active_org_types' => OrganizationType::where('is_active', true)->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('getStats error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Refresh all settings data
     */
    public function refresh()
    {
        try {
            $this->checkPermission('manage settings');

            // Get fresh data
            $users = User::with('roles')
                ->orderBy('name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name'),
                        'is_active' => $user->is_active ?? true,
                        'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                    ];
                });

            $roles = Role::with('permissions')
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                            ];
                        }),
                        'users_count' => $role->users()->count(),
                        'guard_name' => $role->guard_name,
                    ];
                });

            $permissions = Permission::orderBy('name')
                ->get()
                ->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                });

            $orgTypes = OrganizationType::withCount('clients')
                ->orderBy('name')
                ->get()
                ->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'description' => $type->description,
                        'is_active' => $type->is_active ?? true,
                        'color' => $type->color ?? '#14b8a6',
                        'icon' => $type->icon ?? 'building',
                        'display_order' => $type->display_order ?? 0,
                        'clients_count' => $type->clients_count,
                        'created_at' => $type->created_at?->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users,
                'roles' => $roles,
                'permissions' => $permissions,
                'orgTypes' => $orgTypes,
            ]);

        } catch (\Exception $e) {
            Log::error('refresh error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}