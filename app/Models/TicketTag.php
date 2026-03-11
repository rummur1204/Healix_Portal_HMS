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
        return $this->belongsToMany(Ticket::class, 'ticket_tag');
    }
}