<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 use App\Models\Client;
use App\Models\ClientStatusHistory;
use App\Models\ClientTimelineEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreClientRequest;

class ClientController extends Controller
{

public function index(Request $request)
{
    $query = Client::query();

    // Filter by status
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    // Search by organization name
    if ($request->has('search')) {
        $query->where('organization_name', 'like', '%' . $request->search . '%');
    }

    // Filter by organization type
    if ($request->has('organization_type_id')) {
        $query->where('organization_type_id', $request->organization_type_id);
    }

    $clients = $query
        ->with('organizationType')
        ->latest()
        ->paginate(10);

    return response()->json($clients);
}
   

public function store(StoreClientRequest $request)
{
   

    return DB::transaction(function () use ($request) {

        $client = Client::create([
            ...$request->validated(),
            'created_by_user_id' => 1,
            'status' => 'pending'
        ]);

        // Initial status history
        ClientStatusHistory::create([
            'client_id' => $client->id,
            'old_status' => null,
            'new_status' => 'pending',
            'change_reason' => 'Initial creation',
            'changed_by_user_id' => 1
        ]);

        // Timeline event
        ClientTimelineEvent::create([
            'client_id' => $client->id,
            'event_type' => 'profile_created',
            'description' => 'Client profile created',
            'performed_by' => 1
        ]);

        return response()->json([
            'message' => 'Client created successfully',
            'data' => $client->load('organizationType')
        ], 201);
    });
}

}
