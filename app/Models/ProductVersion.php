<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVersion extends Model
{
    protected $fillable = [
        'version_name', 'release_date', 'environment', 
        'release_notes', 'build_id', 'app_version', 
        'db_schema_version', 'is_active'
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function deployments()
    {
        return $this->hasMany(ClientDeployment::class);
    }

    public function currentVersions()
    {
        return $this->hasMany(ClientCurrentVersion::class);
    }
}