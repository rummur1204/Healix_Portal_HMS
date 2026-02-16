<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationType extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}