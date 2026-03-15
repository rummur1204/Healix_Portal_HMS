<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDoc extends Model
{
    protected $fillable = [
        'client_id', 'file_name', 'file_path', 'file_type', 
        'file_size', 'description', 'upload_by_user_id'
    ];

    protected $casts = [
        'file_size' => 'integer'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'upload_by_user_id');
    }
}