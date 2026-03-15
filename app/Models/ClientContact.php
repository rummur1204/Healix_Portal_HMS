<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    protected $fillable = [
        'client_id', 'title', 'name', 'email', 
        'phone', 'is_primary', 'contact_channel'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}