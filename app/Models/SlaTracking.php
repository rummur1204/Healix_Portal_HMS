<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaTracking extends Model
{
    protected $fillable = [
        'ticket_id', 'first_response_due', 'first_response_actual',
        'resolution_due', 'resolution_actual', 'is_breached', 'breach_reason'
    ];

    protected $casts = [
        'is_breached' => 'boolean',
        'first_response_due' => 'datetime',
        'first_response_actual' => 'datetime',
        'resolution_due' => 'datetime',
        'resolution_actual' => 'datetime'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}