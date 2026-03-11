<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientNote extends Model
{
    protected $fillable = [
        'client_id', 'note_type', 'title', 'content',
        'priority', 'created_by_user_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}