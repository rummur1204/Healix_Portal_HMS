<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
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
     * Get all users for the settings page
     */
    public function index()
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get list of users for dropdowns
     */
    public function list()
    {
        try {
            $this->checkPermission('manage users');

            $users = User::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        try {
            $this->checkPermission('create users');

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'roles' => 'sometimes|array',
                'roles.*' => 'exists:roles,name',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if (isset($validated['roles']) && !empty($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            DB::commit();

            // Load roles for response
            $user->load('roles');

            return response()->json([
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        try {
            $this->checkPermission('view users');

            $user = User::with('roles')->findOrFail($id);

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'is_active' => $user->is_active ?? true,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        try {
            $this->checkPermission('edit users');

            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
                'roles' => 'sometimes|array',
                'roles.*' => 'exists:roles,name',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }

            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            if (isset($validated['password']) && !empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            if (isset($validated['is_active'])) {
                $user->is_active = $validated['is_active'];
            }

            $user->save();

            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            DB::commit();

            // Load roles for response
            $user->load('roles');

            return response()->json([
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                    'is_active' => $user->is_active,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        try {
            $this->checkPermission('delete users');

            $user = User::findOrFail($id);

            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return response()->json(['error' => 'You cannot delete your own account'], 400);
            }

            DB::beginTransaction();
            $user->roles()->detach();
            $user->delete();
            DB::commit();

            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        try {
            $this->checkPermission('edit users');

            $user = User::findOrFail($id);

            // Prevent toggling your own status
            if ($user->id === auth()->id()) {
                return response()->json(['error' => 'You cannot change your own status'], 400);
            }

            $user->is_active = !($user->is_active ?? true);
            $user->save();

            return response()->json([
                'message' => 'User status updated successfully',
                'is_active' => $user->is_active,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user status: ' . $e->getMessage()], 500);
        }
    }
}