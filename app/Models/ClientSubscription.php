<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    protected $fillable = [
        'subscription_id',
        'client_id', 
        'plan_id',
        'start_date',
        'end_date',
        'renewal_date',
        'trial_end_date',
        'status',
        'payment_method',
        'payment_status',
        'discount',
        'invoice_reference',
        'internal_note',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by_user_id',
        'created_by_user_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'trial_end_date' => 'date',
        'cancelled_at' => 'datetime',
        'discount' => 'decimal:2'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function history()
    {
        return $this->hasMany(SubscriptionHistory::class, 'subscription_id');
    }

    // FIXED: Added explicit foreign key 'subscription_id'
    public function payments()
    {
        return $this->hasMany(PaymentRecord::class, 'subscription_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }
}