<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number', 'client_id', 'ticket_type', 'title', 'description',
        'priority', 'status', 'assigned_to_user_id', 'due_date', 'created_by_user_id', 'active' // ✅ 
    ];

    protected $casts = [
        'due_date' => 'date',
        'active' => 'boolean' 
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            $ticket->ticket_number = 'TKT-' . strtoupper(uniqid());
            $ticket->active = true; // ✅
        });
    }
     public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    } 
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(TicketTag::class, 'ticket_tag');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    

    public function history()
    {
        return $this->hasMany(TicketHistory::class)->latest();
    }

    public function featureRequest()
    {
        return $this->hasOne(FeatureRequestDetail::class);
    }

    public function slaTracking()
    {
        return $this->hasOne(SlaTracking::class);
    }
    // app/Models/Ticket.php


public function histories()
{
    return $this->hasMany(TicketHistory::class);
}

}