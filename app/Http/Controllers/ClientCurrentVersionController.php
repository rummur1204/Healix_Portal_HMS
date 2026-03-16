<?php

namespace App\Http\Controllers;

use App\Models\ClientCurrentVersion;
use App\Models\Client;
use App\Models\ProductVersion;
use App\Models\ClientDeployment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientCurrentVersionController extends Controller
{
    // Index - list all current versions
    public function index()
    {
        $currentVersions = ClientCurrentVersion::with(['client', 'version', 'lastDeployment'])->get();

        $currentVersions = $currentVersions->map(function($c) {
            return [
                'id' => $c->id,
                'client' => $c->client ? ['id' => $c->client->id, 'organization_name' => $c->client->organization_name] : null,
                'version' => $c->version ? ['id' => $c->version->id, 'version_name' => $c->version->version_name] : null,
                'current_app_version' => $c->current_app_version,
                'current_db_version' => $c->current_db_version,
                'last_deployment' => $c->lastDeployment ? ['id' => $c->lastDeployment->id] : null,
                'last_deployment_date' => $c->last_deployment_date,
            ];
        });

        return Inertia::render('ClientCurrentVersions/Index', [
            'currentVersions' => $currentVersions
        ]);
    }

    // Create - show form
    public function create()
    {
        return Inertia::render('ClientCurrentVersions/Create', [
            'clients' => Client::all(['id','organization_name']),
            'versions' => ProductVersion::all(['id','version_name']),
            'deployments' => ClientDeployment::all(['id','deployment_date']),
        ]);
    }

    // Store - save new current version
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id|unique:client_current_versions,client_id',
            'version_id' => 'required|exists:product_versions,id',
            'current_app_version' => 'required|string',
            'current_db_version' => 'required|string',
            'last_deployment_id' => 'nullable|exists:client_deployments,id',
            'last_deployment_date' => 'nullable|date',
        ]);

        ClientCurrentVersion::create($data);

        return redirect()->route('client-current-versions.index')
                         ->with('success','Current version added successfully.');
    }

    // Edit - show form
    public function edit(ClientCurrentVersion $clientCurrentVersion)
    {
        return Inertia::render('ClientCurrentVersions/Edit', [
            'currentVersion' => $clientCurrentVersion,
            'clients' => Client::all(['id','organization_name']),
            'versions' => ProductVersion::all(['id','version_name']),
            'deployments' => ClientDeployment::all(['id','deployment_date']),
        ]);
    }

    // Update - save changes
    public function update(Request $request, ClientCurrentVersion $clientCurrentVersion)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id|unique:client_current_versions,client_id,'.$clientCurrentVersion->id,
            'version_id' => 'required|exists:product_versions,id',
            'current_app_version' => 'required|string',
            'current_db_version' => 'required|string',
            'last_deployment_id' => 'nullable|exists:client_deployments,id',
            'last_deployment_date' => 'nullable|date',
        ]);

        $clientCurrentVersion->update($data);

        return redirect()->route('client-current-versions.index')
                         ->with('success','Current version updated successfully.');
    }

    // Destroy
    public function destroy(ClientCurrentVersion $clientCurrentVersion)
    {
        $clientCurrentVersion->delete();
        return redirect()->route('client-current-versions.index')
                         ->with('success','Current version deleted successfully.');
    }
}