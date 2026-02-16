<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientTechnicalInfo extends Model
{
    protected $fillable = [
        'client_id', 'remote_access_enabled', 'anydesk_address', 'anydesk_password',
        'server_setup_date', 'server_activation_date', 'last_activation_update_date',
        'activation_expiry_date', 'windows_rearm_count', 'ip_address', 'subnet_mask',
        'gateway', 'primary_dns', 'secondary_dns', 'internet_available', 'firewall_in_use',
        'firewall_brand', 'router_model', 'router_admin_password', 'is_primary',
        'disk_configuration', 'available_free_space_gb', 'backup_configured',
        'backup_type', 'backup_frequency'
    ];

    protected $casts = [
        'remote_access_enabled' => 'boolean',
        'internet_available' => 'boolean',
        'firewall_in_use' => 'boolean',
        'is_primary' => 'boolean',
        'backup_configured' => 'boolean',
        'server_setup_date' => 'date',
        'server_activation_date' => 'date',
        'last_activation_update_date' => 'date',
        'activation_expiry_date' => 'date',
        'available_free_space_gb' => 'decimal:2'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}