<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    protected $table = 'ticket_history'; // singular table

    protected $fillable = [
        'ticket_id',
        'changed_field',
        'old_value',
        'new_value',
        'changed_by_user_id'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
