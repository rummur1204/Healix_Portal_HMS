<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientTimelineEvent extends Model
{
    protected $fillable = [
        'client_id', 'event_type', 'description', 
        'metadata', 'performed_by'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}