<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientTask extends Model
{
    protected $fillable = [
        'client_id', 'title', 'description', 'priority', 'status',
        'assigned_to_user_id', 'assigned_by_user_id', 'assigned_at',
        'due_date', 'completed_date', 'created_by_user_id'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'assigned_at' => 'datetime'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}