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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
     * =============================================
     * USER MANAGEMENT CRUD METHODS
     * =============================================
     */

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
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        try {
            $this->checkPermission('manage users');

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'roles' => 'array',
                'is_active' => 'boolean',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if (!empty($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeUser error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing user
     */
    public function updateUser(Request $request, $id)
    {
        try {
            $this->checkPermission('manage users');

            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'roles' => 'array',
                'is_active' => 'boolean',
            ]);

            DB::beginTransaction();

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? $user->is_active,
            ]);

            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateUser error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a user
     */
    public function destroyUser($id)
    {
        try {
            $this->checkPermission('manage users');

            $user = User::findOrFail($id);
            
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return response()->json(['error' => 'You cannot delete your own account'], 403);
            }

            DB::beginTransaction();
            $user->roles()->detach();
            $user->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('destroyUser error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * =============================================
     * ROLE MANAGEMENT CRUD METHODS
     * =============================================
     */

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

            return response()->json([
                'roles' => $roles,
                'permissions' => $this->getPermissions()->getData()
            ]);

        } catch (\Exception $e) {
            Log::error('getRoles error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new role
     */
    public function storeRole(Request $request)
    {
        try {
            $this->checkPermission('manage roles');

            $validated = $request->validate([
                'name' => 'required|string|unique:roles,name',
                'permissions' => 'array',
            ]);

            DB::beginTransaction();

            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
            
            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'role' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeRole error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing role
     */
    public function updateRole(Request $request, $id)
    {
        try {
            $this->checkPermission('manage roles');

            $role = Role::findOrFail($id);

            $validated = $request->validate([
                'name' => ['required', 'string', Rule::unique('roles')->ignore($role->id)],
                'permissions' => 'array',
            ]);

            DB::beginTransaction();

            $role->update(['name' => $validated['name']]);
            
            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateRole error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a role
     */
    public function destroyRole($id)
    {
        try {
            $this->checkPermission('manage roles');

            $role = Role::findOrFail($id);
            
            // Prevent deleting admin role
            if ($role->name === 'admin') {
                return response()->json(['error' => 'Cannot delete admin role'], 403);
            }

            DB::beginTransaction();
            $role->permissions()->detach();
            $role->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('destroyRole error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * =============================================
     * PERMISSION METHODS
     * =============================================
     */

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
     * =============================================
     * ORGANIZATION TYPE CRUD METHODS
     * =============================================
     */

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
     * Store a new organization type
     */
    public function storeOrganizationType(Request $request)
    {
        try {
            $this->checkPermission('manage organization types');

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:organization_types',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'color' => 'nullable|string',
                'icon' => 'nullable|string',
                'display_order' => 'nullable|integer',
            ]);

            $orgType = OrganizationType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Organization type created successfully',
                'orgType' => $orgType
            ]);

        } catch (\Exception $e) {
            Log::error('storeOrganizationType error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing organization type
     */
    public function updateOrganizationType(Request $request, $id)
    {
        try {
            $this->checkPermission('manage organization types');

            $orgType = OrganizationType::findOrFail($id);

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('organization_types')->ignore($orgType->id)],
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'color' => 'nullable|string',
                'icon' => 'nullable|string',
                'display_order' => 'nullable|integer',
            ]);

            $orgType->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Organization type updated successfully',
                'orgType' => $orgType
            ]);

        } catch (\Exception $e) {
            Log::error('updateOrganizationType error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an organization type
     */
    public function destroyOrganizationType($id)
    {
        try {
            $this->checkPermission('manage organization types');

            $orgType = OrganizationType::findOrFail($id);
            
            // Check if type has clients
            if ($orgType->clients()->count() > 0) {
                return response()->json(['error' => 'Cannot delete organization type that has associated clients'], 403);
            }

            $orgType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Organization type deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('destroyOrganizationType error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * =============================================
     * UTILITY METHODS
     * =============================================
     */

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

            return response()->json([
                'success' => true,
                'users' => $this->getUsers()->getData(),
                'roles' => $this->getRoles()->getData()->roles,
                'permissions' => $this->getPermissions()->getData(),
                'orgTypes' => $this->getOrganizationTypes()->getData(),
            ]);

        } catch (\Exception $e) {
            Log::error('refresh error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}