<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureRequestDetail extends Model
{
    protected $fillable = [
        'ticket_id', 'business_value', 'estimated_effort',
        'target_release', 'approval_status', 'external_link'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}