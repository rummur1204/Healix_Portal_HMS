<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'last_password_change',
        'last_login', 'is_active'
    ];

    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_password_change' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Remove the role_id relationship since Spatie handles it
    // Add your other relationships
    public function clientsCreated()
    {
        return $this->hasMany(Client::class, 'created_by_user_id');
    }

    public function clientsUpdated()
    {
        return $this->hasMany(Client::class, 'updated_by_user_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to_user_id');
    }

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by_user_id');
    }

    public function ticketComments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function clientNotes()
    {
        return $this->hasMany(ClientNote::class, 'created_by_user_id');
    }

    public function clientTasks()
    {
        return $this->hasMany(ClientTask::class, 'created_by_user_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(ClientTask::class, 'assigned_to_user_id');
    }

    public function subscriptionHistory()
    {
        return $this->hasMany(SubscriptionHistory::class, 'change_by_user_id');
    }

    public function deployments()
    {
        return $this->hasMany(ClientDeployment::class, 'deployed_by_user_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}