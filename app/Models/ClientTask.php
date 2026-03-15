<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientTask extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'description',
        'due_date',
        'status',
        'assigned_to_user_id',
        'reminder_at',
        'created_by_user_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}