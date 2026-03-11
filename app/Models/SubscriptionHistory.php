<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionHistory extends Model
{
    protected $table = 'subscription_histories';

    protected $fillable = [
        'subscription_id',
        'client_id',
        'action',
        'old_plan_id',
        'new_plan_id',
        'old_status',
        'new_status',
        'old_price',
        'new_price',
        'old_end_date',
        'new_end_date',
        'notes',
        'performed_by_user_id'
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'old_end_date' => 'date',
        'new_end_date' => 'date'
    ];

    public function subscription()
    {
        return $this->belongsTo(ClientSubscription::class, 'subscription_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function oldPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'old_plan_id');
    }

    public function newPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'new_plan_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}