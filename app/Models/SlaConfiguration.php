<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaConfiguration extends Model
{
    protected $fillable = [
        'sla_name', 'priority', 'first_response_hrs', 
        'resolution_hrs', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}