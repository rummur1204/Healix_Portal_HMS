<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationType extends Model
{
    use HasFactory;

    protected $table = 'organization_types';

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'is_active',
        'display_order',
        'created_by_user_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the clients for this organization type
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'organization_type_id');
    }

    /**
     * Get the user who created this organization type
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scope a query to only active types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query in proper order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get the color with fallback
     */
    public function getColorAttribute($value)
    {
        return $value ?? '#14b8a6';
    }

    /**
     * Get the icon with fallback
     */
    public function getIconAttribute($value)
    {
        return $value ?? 'building';
    }
}