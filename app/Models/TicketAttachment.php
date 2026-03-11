<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = [
        'ticket_id', 'file_name', 'file_path', 
        'file_type', 'file_size', 'upload_by_user_id'
    ];

    protected $casts = [
        'file_size' => 'integer'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'upload_by_user_id');
    }
}