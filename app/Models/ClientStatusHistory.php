<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientStatusHistory extends Model
{
    protected $fillable = [
        'client_id', 'old_status', 'new_status', 
        'change_reason', 'changed_by_user_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}