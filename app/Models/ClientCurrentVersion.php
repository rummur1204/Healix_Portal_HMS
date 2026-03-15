<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCurrentVersion extends Model
{
    protected $fillable = [
        'client_id', 'version_id', 'current_app_version', 
        'current_db_version', 'last_deployment_id', 'last_deployment_date'
    ];

    protected $casts = [
        'last_deployment_date' => 'date'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function version()
    {
        return $this->belongsTo(ProductVersion::class);
    }

    public function lastDeployment()
    {
        return $this->belongsTo(ClientDeployment::class, 'last_deployment_id');
    }
}