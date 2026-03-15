<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionHistory extends Model
{
    use HasFactory;

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
        'old_end_date' => 'datetime',
        'new_end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ClientSubscription::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'old_plan_id');
    }

    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'new_plan_id');
    }

    public function getActionLabelAttribute()
    {
        $labels = [
            'created' => 'Created',
            'assigned' => 'Assigned',
            'updated' => 'Updated',
            'renewed' => 'Renewed',
            'cancelled' => 'Cancelled',
            'suspended' => 'Suspended',
            'reactivated' => 'Reactivated',
            'activated' => 'Activated',
            'deleted' => 'Deleted',
            'upgraded' => 'Upgraded',
            'downgraded' => 'Downgraded',
            'status_changed' => 'Status Changed',
            'payment_received' => 'Payment Received'
        ];
        
        return $labels[$this->action] ?? ucfirst($this->action);
    }

    public function getChangesAttribute()
    {
        $changes = [];
        
        if ($this->old_plan_id && $this->new_plan_id) {
            $changes[] = "Plan changed";
        }
        
        if ($this->old_status && $this->new_status) {
            $changes[] = "Status: {$this->old_status} → {$this->new_status}";
        }
        
        if ($this->old_price && $this->new_price) {
            $changes[] = "Price: $" . number_format($this->old_price, 2) . " → $" . number_format($this->new_price, 2);
        }
        
        if ($this->old_end_date && $this->new_end_date) {
            $changes[] = "End date: " . $this->old_end_date->format('Y-m-d') . " → " . $this->new_end_date->format('Y-m-d');
        }
        
        return $changes;
    }
}