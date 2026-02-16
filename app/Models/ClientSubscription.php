<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    protected $fillable = [
        'client_id', 'plan_id', 'start_date', 'end_date', 'trial_end_date',
        'status', 'payment_method', 'payment_status', 'discount',
        'invoice_reference', 'internal_note'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'trial_end_date' => 'date',
        'discount' => 'decimal:2'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function history()
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    public function renewalReminders()
    {
        return $this->hasMany(RenewalReminder::class);
    }
}