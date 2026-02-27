<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientStatusHistory;
use App\Models\ClientTimelineEvent;
use Illuminate\Support\Facades\DB;
use App\Models\OrganizationType;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use Inertia\Inertia;


class ClientController extends Controller
{

public function index(Request $request)
{
    $clients = Client::with([
            'organizationType',
            'contacts'
        ])
        ->with('primaryContact')
        ->when($request->search, function ($q) use ($request) {
            $q->where('organization_name', 'like', "%{$request->search}%");
        })
        ->when($request->status, function ($q) use ($request) {
            $q->where('status', $request->status);
        })
        ->when($request->organization_type_id, function ($q) use ($request) {
            $q->where('organization_type_id', $request->organization_type_id);
        })
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Clients/Index', [
        'clients' => $clients,
        'filters' => $request->only(['search', 'status', 'organization_type_id']),
        'organizationTypes' => OrganizationType::select('id','name')->get(),
    ]);
}
   

public function store(StoreClientRequest $request)
{
    DB::transaction(function () use ($request) {

        $client = Client::create([
            'client_code' => 'CL-' . strtoupper(uniqid()),
            'organization_name' => $request->organization_name,
            'organization_type_id' => $request->organization_type_id,
            'address_country' => $request->address_country,
            'address_city' => $request->address_city,
            'address_line' => $request->address_line,
            'tax_reg_id' => $request->tax_reg_id,
            'preferred_contact_channel' => $request->preferred_contact_channel,
            'note' => $request->note,
            'created_by_user_id' => auth()->id(),
            'status' => 'pending'
        ]);

        // Primary Contact
        $client->contacts()->create([
            'name' => $request->primary_contact['name'],
            'email' => $request->primary_contact['email'],
            'phone' => $request->primary_contact['phone'],
            'title' => $request->primary_contact['title'] ?? null,
            'is_primary' => true,
            'contact_channel' => $request->preferred_contact_channel
        ]);

        // Additional Contacts
        if ($request->contacts) {
            foreach ($request->contacts as $contact) {
                $client->contacts()->create([
                    'name' => $contact['name'],
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'title' => $contact['title'] ?? null,
                    'is_primary' => false,
                    'contact_channel' => 'email'
                ]);
            }
        }

        // Status History (initial)
        ClientStatusHistory::create([
            'client_id' => $client->id,
            'old_status' => null,
            'new_status' => 'Pending',
            'change_reason' => 'Initial creation',
            'changed_by_user_id' => auth()->id()
        ]);

        // Timeline
        $client->timelineEvents()->create([
            'event_type' => 'profile_created',
            'description' => 'Client profile created',
            'performed_by' => auth()->id(),
        ]);
    });

    return redirect()->route('clients.index')
        ->with('success', 'Client created successfully.');
}

public function show(Client $client)
{
    $client->load([
        'organizationType',
        'technicalInfo',
        'contacts',
        'notes',
        'tasks',
        'timelineEvents',
        'statusHistory',
        'subscriptions',
        'tickets',
        'deployments',
        'currentVersion',
        'communicationLogs'
    ]);

    return Inertia::render('Clients/Show', [
        'client' => $client
    ]);
}

public function update(UpdateClientRequest $request, Client $client)
{
    DB::transaction(function () use ($request, $client) {

        $client->update([
            'organization_name' => $request->organization_name,
            'organization_type_id' => $request->organization_type_id,
            'address_country' => $request->address_country,
            'address_city' => $request->address_city,
            'address_line' => $request->address_line,
            'tax_reg_id' => $request->tax_reg_id,
            'preferred_contact_channel' => $request->preferred_contact_channel,
            'note' => $request->note,
            'updated_by_user_id' => auth()->id(),
        ]);

        // Remove old contacts
        $client->contacts()->delete();

        // Recreate Primary
        $client->contacts()->create([
            'name' => $request->primary_contact['name'],
            'email' => $request->primary_contact['email'],
            'phone' => $request->primary_contact['phone'],
            'title' => $request->primary_contact['title'] ?? null,
            'is_primary' => true,
            'contact_channel' => $request->preferred_contact_channel
        ]);

        // Recreate additional
        if ($request->contacts) {
            foreach ($request->contacts as $contact) {
                $client->contacts()->create([
                    'name' => $contact['name'],
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'title' => $contact['title'] ?? null,
                    'is_primary' => false,
                    'contact_channel' => 'email'
                ]);
            }
        }

        ClientTimelineEvent::create([
            'client_id' => $client->id,
            'event_type' => 'profile_updated',
            'description' => 'Client profile updated',
            'performed_by' => auth()->id(),
        ]);
    });

    return back()->with('success', 'Client updated successfully.');
}

public function changeStatus(Request $request, Client $client)
{
    $request->validate([
        'status' => 'required|in:pending,onboarding,active,suspended,rejected,churned',
        'reason' => 'nullable|string'
    ]);

    $oldStatus = $client->status;

    if ($oldStatus === $request->status) {
        return back();
    }

    DB::transaction(function () use ($client, $request, $oldStatus) {

        $client->update([
            'status' => $request->status
        ]);

        ClientStatusHistory::create([
            'client_id' => $client->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'change_reason' => $request->reason,
            'changed_by_user_id' => auth()->id()
        ]);

        ClientTimelineEvent::create([
            'client_id' => $client->id,
            'event_type' => 'status_changed',
            'description' => "Status changed from {$oldStatus} to {$request->status}",
            'created_by_user_id' => auth()->id()
        ]);
    });

    return back();
}

public function destroy(Client $client)
{
    ClientTimelineEvent::create([
        'client_id' => $client->id,
        'event_type' => 'profile_deleted',
        'description' => "Client {$client->organization_name} deleted",
        'performed_by' => auth()->id(),
    ]);

    $client->delete();

    return redirect()
        ->route('clients.index')
        ->with('success', 'Client deleted successfully.');
}

}
