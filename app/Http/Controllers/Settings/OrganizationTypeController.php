<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OrganizationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check permission helper
     */
    private function checkPermission()
    {
        if (!auth()->user()->can('manage organization types') && 
            !auth()->user()->can('manage settings')) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Get all organization types
     */
    public function index(Request $request)
    {
        try {
            $this->checkPermission();

            $types = OrganizationType::withCount('clients')
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

            return response()->json($types);
        } catch (\Exception $e) {
            Log::error('OrganizationType index error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get list for dropdowns
     */
    public function list()
    {
        try {
            $this->checkPermission();

            $types = OrganizationType::where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(['id', 'name', 'color', 'icon']);

            return response()->json($types);
        } catch (\Exception $e) {
            Log::error('OrganizationType list error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new organization type
     */
    public function store(Request $request)
    {
        try {
            $this->checkPermission();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:organization_types,name',
                'description' => 'nullable|string|max:1000',
                'color' => 'nullable|string|max:50',
                'icon' => 'nullable|string|max:100',
                'is_active' => 'sometimes|boolean',
                'display_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            // Set defaults
            $validated['color'] = $validated['color'] ?? '#14b8a6';
            $validated['icon'] = $validated['icon'] ?? 'building';
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['display_order'] = $validated['display_order'] ?? 0;
            
            // Add created_by_user_id if user is authenticated
            if (auth()->check()) {
                $validated['created_by_user_id'] = auth()->id();
            }

            DB::beginTransaction();

            $type = OrganizationType::create($validated);

            DB::commit();

            // Load clients count
            $type->loadCount('clients');

            return response()->json([
                'message' => 'Organization type created successfully',
                'data' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                    'is_active' => $type->is_active,
                    'color' => $type->color,
                    'icon' => $type->icon,
                    'display_order' => $type->display_order,
                    'clients_count' => $type->clients_count,
                    'created_at' => $type->created_at?->format('Y-m-d H:i:s'),
                ]
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('OrganizationType store query error: ' . $e->getMessage());
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrganizationType store error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create organization type: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified organization type
     */
    public function show($id)
    {
        try {
            $this->checkPermission();

            $type = OrganizationType::withCount('clients')->findOrFail($id);

            return response()->json([
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'is_active' => $type->is_active,
                'color' => $type->color ?? '#14b8a6',
                'icon' => $type->icon ?? 'building',
                'display_order' => $type->display_order ?? 0,
                'clients_count' => $type->clients_count,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Organization type not found'], 404);
        } catch (\Exception $e) {
            Log::error('OrganizationType show error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified organization type
     */
    public function update(Request $request, $id)
    {
        try {
            $this->checkPermission();

            $type = OrganizationType::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:organization_types,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'color' => 'nullable|string|max:50',
                'icon' => 'nullable|string|max:100',
                'is_active' => 'sometimes|boolean',
                'display_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            $type->update($validated);

            DB::commit();

            // Load clients count
            $type->loadCount('clients');

            return response()->json([
                'message' => 'Organization type updated successfully',
                'data' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                    'is_active' => $type->is_active,
                    'color' => $type->color,
                    'icon' => $type->icon,
                    'display_order' => $type->display_order,
                    'clients_count' => $type->clients_count,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Organization type not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrganizationType update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update organization type: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified organization type
     */
    public function destroy($id)
    {
        try {
            $this->checkPermission();

            $type = OrganizationType::findOrFail($id);

            // Check if type has clients
            if ($type->clients()->count() > 0) {
                return response()->json(['error' => 'Cannot delete organization type that is assigned to clients'], 400);
            }

            DB::beginTransaction();
            $type->delete();
            DB::commit();

            return response()->json(['message' => 'Organization type deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Organization type not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrganizationType destroy error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete organization type: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle organization type active status
     */
    public function toggleStatus($id)
    {
        try {
            $this->checkPermission();

            $type = OrganizationType::findOrFail($id);
            
            DB::beginTransaction();
            $type->is_active = !$type->is_active;
            $type->save();
            DB::commit();

            return response()->json([
                'message' => 'Organization type status updated successfully',
                'is_active' => $type->is_active,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Organization type not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrganizationType toggleStatus error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update organization type status: ' . $e->getMessage()], 500);
        }
    }
}