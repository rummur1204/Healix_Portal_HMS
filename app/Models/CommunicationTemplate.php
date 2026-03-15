<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    protected $fillable = [
        'name', 'channel', 'subject', 'body', 
        'variables', 'created_by_user_id', 'is_active'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function campaigns()
    {
        return $this->hasMany(CommunicationCampaign::class);
    }

    public function logs()
    {
        return $this->hasMany(CommunicationLog::class);
    }
}