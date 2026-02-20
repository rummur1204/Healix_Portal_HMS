<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
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
     * Get all roles with permissions
     */
    public function index()
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

            $permissions = Permission::orderBy('name')
                ->get()
                ->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                });

            return response()->json([
                'success' => true,
                'roles' => $roles,
                'permissions' => $permissions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of roles for dropdowns
     */
    public function list()
    {
        try {
            $this->checkPermission('manage roles');

            $roles = Role::orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new role
     */
    public function store(Request $request)
    {
        try {
            $this->checkPermission('create roles');

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:roles,name|max:255',
                'permissions' => 'sometimes|array',
                'permissions.*' => 'exists:permissions,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $validated = $validator->validated();

            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web'
            ]);

            if (isset($validated['permissions']) && !empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();

            // Load permissions for response
            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ];
                    }),
                    'users_count' => 0,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to create role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        try {
            $this->checkPermission('view roles');

            $role = Role::with('permissions')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Role not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        try {
            $this->checkPermission('edit roles');

            $role = Role::findOrFail($id);

            // Prevent editing super-admin
            if ($role->name === 'super-admin') {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot edit super-admin role'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|unique:roles,name,' . $id,
                'permissions' => 'sometimes|array',
                'permissions.*' => 'exists:permissions,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $validated = $validator->validated();

            if (isset($validated['name'])) {
                $role->name = $validated['name'];
                $role->save();
            }

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();

            // Load permissions for response
            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ];
                    }),
                    'users_count' => $role->users()->count(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Role not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        try {
            $this->checkPermission('delete roles');

            $role = Role::findOrFail($id);

            // Prevent deleting super-admin
            if ($role->name === 'super-admin') {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete super-admin role'
                ], 400);
            }

            // Check if role has users
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete role with assigned users'
                ], 400);
            }

            DB::beginTransaction();
            
            // Remove permissions before deleting
            $role->permissions()->detach();
            $role->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Role not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete role: ' . $e->getMessage()
            ], 500);
        }
    }
}