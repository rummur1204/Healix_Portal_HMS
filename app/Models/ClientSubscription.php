<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientSubscription extends Model
{
    use HasFactory;

    protected $table = 'client_subscriptions';

    protected $fillable = [
        'subscription_id',
        'client_id',
        'plan_id',
        'start_date',
        'end_date',
        'renewal_date',
        'trial_end_date',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by_user_id',
        'created_by_user_id',
        'payment_method',
        'payment_status',
        'discount',
        'discount_status',
        'invoice_reference',
        'internal_note',
        'renewed_at',
        'suspended_at',
        'suspension_reason',
        'reactivated_at',
        'amount'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'trial_end_date' => 'date',
        'cancelled_at' => 'datetime',
        'renewed_at' => 'datetime',
        'suspended_at' => 'datetime',
        'reactivated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'discount' => 'decimal:2'
    ];

    protected $attributes = [
        'discount_status' => 'none',
        'discount' => 0
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class, 'subscription_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentRecord::class, 'subscription_id');
    }

    public function renewalReminders(): HasMany
    {
        return $this->hasMany(RenewalReminder::class, 'subscription_id');
    }

    public function discountApprovals(): HasMany
    {
        return $this->hasMany(DiscountApproval::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    public function hasPendingDiscount(): bool
    {
        return $this->discount_status === 'pending_approval';
    }

    public function getFinalPriceAttribute(): float
    {
        $price = $this->plan?->price ?? 0;
        return max(0, $price - $this->discount);
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        $price = $this->plan?->price ?? 0;
        if ($price <= 0 || $this->discount <= 0) {
            return null;
        }
        return round(($this->discount / $price) * 100, 2);
    }
}