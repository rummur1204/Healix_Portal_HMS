<?php

namespace App\Http\Controllers;

use App\Models\ClientDeployment;
use App\Models\Client;
use App\Models\ProductVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientDeploymentController extends Controller
{
    // Index - list all deployments
    public function index()
    {
        // Eager load relationships
        $deployments = ClientDeployment::with(['client', 'version', 'deployedBy'])->get();

        // Map to safe frontend structure
        $deployments = $deployments->map(function ($d) {
            return [
                'id' => $d->id,
                'client' => $d->client ? [
                    'id' => $d->client->id,
                    'organization_name' => $d->client->organization_name
                ] : null,
                'version' => $d->version ? [
                    'id' => $d->version->id,
                    'version_name' => $d->version->version_name
                ] : null,
                'deployedBy' => $d->deployedBy ? [
                    'id' => $d->deployedBy->id,
                    'name' => $d->deployedBy->name
                ] : null,
                'deployment_date' => $d->deployment_date,
                'deployment_type' => $d->deployment_type,
                'result' => $d->result,
                'notes' => $d->notes,
            ];
        });

        return Inertia::render('ClientDeployments/Index', [
            'deployments' => $deployments
        ]);
    }

    // Create - show form
    public function create()
    {
        return Inertia::render('ClientDeployments/Create', [
            'clients' => Client::all(['id','organization_name']),
            'versions' => ProductVersion::all(['id','version_name']),
            'users' => User::all(['id','name']),
        ]);
    }

    // Store - save new deployment
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'version_id' => 'required|exists:product_versions,id',
            'deployment_date' => 'required|date',
            'deployed_by_user_id' => 'required|exists:users,id',
            'deployment_type' => 'required|in:new_install,upgrade,hotfix,rollback',
            'result' => 'required|in:success,failed,partial',
            'notes' => 'nullable|string',
        ]);

        ClientDeployment::create($data);

        return redirect()->route('client-deployments.index')
                         ->with('success', 'Deployment created successfully.');
    }

    // Edit - show form for editing
    public function edit(ClientDeployment $clientDeployment)
    {
        return Inertia::render('ClientDeployments/Edit', [
            'deployment' => $clientDeployment,
            'clients' => Client::all(['id','organization_name']),
            'versions' => ProductVersion::all(['id','version_name']),
            'users' => User::all(['id','name']),
        ]);
    }

    // Update - save changes
    public function update(Request $request, ClientDeployment $clientDeployment)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'version_id' => 'required|exists:product_versions,id',
            'deployment_date' => 'required|date',
            'deployed_by_user_id' => 'required|exists:users,id',
            'deployment_type' => 'required|in:new_install,upgrade,hotfix,rollback',
            'result' => 'required|in:success,failed,partial',
            'notes' => 'nullable|string',
        ]);

        $clientDeployment->update($data);

        return redirect()->route('client-deployments.index')
                         ->with('success', 'Deployment updated successfully.');
    }

    // Destroy - delete record
    public function destroy(ClientDeployment $clientDeployment)
    {
        $clientDeployment->delete();
        return redirect()->route('client-deployments.index')
                         ->with('success', 'Deployment deleted successfully.');
    }
}