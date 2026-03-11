<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTag extends Model
{
    protected $fillable = ['name', 'color', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    public function tickets()
    {
        // Correct pivot table and keys
        return $this->belongsToMany(
            Ticket::class,           // Related model
            'ticket_tag_ticket',     // Pivot table name
            'ticket_tag_id',         // Foreign key on pivot for this model
            'ticket_id'              // Foreign key on pivot for the related model
        );
    }
}