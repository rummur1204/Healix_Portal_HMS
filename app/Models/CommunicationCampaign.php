<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationCampaign extends Model
{
    protected $fillable = [
        'name', 'template_id', 'send_type', 'scheduled_time',
        'target_segment', 'status', 'created_by_user_id'
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'target_segment' => 'array'
    ];

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function logs()
    {
        return $this->hasMany(CommunicationLog::class);
    }
}