<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RenewalReminder extends Model
{
    protected $fillable = [
        'subscription_id', 'reminder_days', 'is_sent', 'sent_at'
    ];

    protected $casts = [
        'is_sent' => 'boolean',
        'sent_at' => 'datetime'
    ];

    public function subscription()
    {
        return $this->belongsTo(ClientSubscription::class);
    }
}