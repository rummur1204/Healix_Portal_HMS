<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionHistory extends Model
{
    protected $fillable = [
        'subscription_id', 'change_type', 'changed_field',
        'old_value', 'new_value', 'status', 'change_by_user_id'
    ];

    public function subscription()
    {
        return $this->belongsTo(ClientSubscription::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'change_by_user_id');
    }
}