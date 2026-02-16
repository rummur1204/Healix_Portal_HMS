<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'plan_name', 'billing_cycle', 'price', 'modules_included',
        'max_users', 'max_branches', 'support_level', 'notes',
        'trial_days', 'is_active', 'created_by_user_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'modules_included' => 'array',
        'is_active' => 'boolean'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(ClientSubscription::class);
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(ClientSubscription::class)
            ->whereIn('status', ['active', 'trial']);
    }
}