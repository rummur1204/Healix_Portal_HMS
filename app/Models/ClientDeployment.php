<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDeployment extends Model
{
    protected $fillable = [
        'client_id', 'version_id', 'deployment_date', 'deployed_by_user_id',
        'deployment_type', 'result', 'notes'
    ];

    protected $casts = [
        'deployment_date' => 'date'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function version()
    {
        return $this->belongsTo(ProductVersion::class);
    }

    public function deployedBy()
    {
        return $this->belongsTo(User::class, 'deployed_by_user_id');
    }

    public function currentVersions()
    {
        return $this->hasMany(ClientCurrentVersion::class, 'last_deployment_id');
    }
}