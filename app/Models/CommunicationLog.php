<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    protected $fillable = [
        'campaign_id', 'template_id', 'sender_user_id', 'channel',
        'recipient_email', 'recipient_phone', 'client_id', 'subject',
        'message', 'status', 'recipients_count', 'sent_at',
        'provider_response', 'error_message'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'recipients_count' => 'integer'
    ];

    public function campaign()
    {
        return $this->belongsTo(CommunicationCampaign::class);
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}