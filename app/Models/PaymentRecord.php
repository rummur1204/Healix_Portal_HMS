<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    protected $fillable = [
        'transaction_id',
        'subscription_id',  // This is the foreign key
        'client_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'status',
        'notes',
        'recorded_by_user_id'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = 'PAY-' . strtoupper(uniqid());
            }
        });
    }

    // This is correctly specified
    public function subscription()
    {
        return $this->belongsTo(ClientSubscription::class, 'subscription_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }
}