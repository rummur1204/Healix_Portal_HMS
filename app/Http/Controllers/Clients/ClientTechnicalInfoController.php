<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientTechnicalInfo;
use App\Models\ClientTimelineEvent;
use Illuminate\Http\Request;

class ClientTechnicalInfoController extends Controller
{
    public function storeOrUpdate(Request $request, Client $client)
    {
        $validated = $request->validate([
            'remote_access_enabled' => 'boolean',
            'anydesk_address' => 'nullable|string',
            'anydesk_password' => 'nullable|string',
            'server_setup_date' => 'nullable|date',
            'server_activation_date' => 'nullable|date',
            'last_activation_update_date' => 'nullable|date',
            'activation_expiry_date' => 'nullable|date',
            'windows_rearm_count' => 'nullable|integer',
            'ip_address' => 'nullable|string',
            'subnet_mask' => 'nullable|string',
            'gateway' => 'nullable|string',
            'primary_dns' => 'nullable|string',
            'secondary_dns' => 'nullable|string',
            'internet_available' => 'boolean',
            'firewall_in_use' => 'boolean',
            'firewall_brand' => 'nullable|string',
            'router_model' => 'nullable|string',
            'router_admin_password' => 'nullable|string',
            'disk_configuration' => 'nullable|string',
            'available_free_space_gb' => 'nullable|numeric',
            'backup_configured' => 'boolean',
            'backup_type' => 'nullable|in:local,cloud,hybrid',
            'backup_frequency' => 'nullable|in:daily,weekly,monthly,manual,automated',
        ]);

        if (!$request->anydesk_password) {
    unset($validated['anydesk_password']);
}

if (!$request->router_admin_password) {
    unset($validated['router_admin_password']);
}

        $techInfo = ClientTechnicalInfo::updateOrCreate(
            ['client_id' => $client->id],
            $validated
        );

        ClientTimelineEvent::create([
            'client_id' => $client->id,
            // 'event_type' => $techInfo->wasRecentlyCreated ? 'technical_info_added' : 'technical_info_updated',
            'description' => 'Technical information updated',
            'performed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Technical information saved.');
    }
}


