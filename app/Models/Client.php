<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'organization_name', 'organization_type_id',
        'primary_contact_name', 'primary_contact_email', 'primary_contact_phone',
        'address_country', 'address_city', 'address_line', 'tax_reg_id',
        'preferred_contact_channel', 'note', 'created_by_user_id',
        'updated_by_user_id', 'status', 'do_not_email', 'do_not_sms', 'do_not_market'
    ];

    protected $casts = [
        'do_not_email' => 'boolean',
        'do_not_sms' => 'boolean',
        'do_not_market' => 'boolean'
    ];

    protected static function boot()
{
    parent::boot();

    static::creating(function ($client) {
        $nextNumber = self::count() + 1;

        $client->client_code = 'CL-' . str_pad(
            $nextNumber,
            6,
            '0',
            STR_PAD_LEFT
        );
    });
}


    public function organizationType()
    {
        return $this->belongsTo(OrganizationType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function technicalInfo()
    {
        return $this->hasOne(ClientTechnicalInfo::class);
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function documents()
    {
        return $this->hasMany(ClientDoc::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(ClientStatusHistory::class)->latest();
    }

    public function notes()
    {
        return $this->hasMany(ClientNote::class);
    }

    public function timelineEvents()
    {
        return $this->hasMany(ClientTimelineEvent::class)->latest();
    }

    public function tasks()
    {
        return $this->hasMany(ClientTask::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(ClientSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(ClientSubscription::class)
            ->whereIn('status', ['active', 'trial'])
            ->latest();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function openTickets()
    {
        return $this->hasMany(Ticket::class)
            ->whereNotIn('status', ['resolved', 'closed', 'rejected']);
    }

    public function deployments()
    {
        return $this->hasMany(ClientDeployment::class)->latest();
    }

    public function currentVersion()
    {
        return $this->hasOne(ClientCurrentVersion::class);
    }

    public function communicationLogs()
    {
        return $this->hasMany(CommunicationLog::class);
    }
}