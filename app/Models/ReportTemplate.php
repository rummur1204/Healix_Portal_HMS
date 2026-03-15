<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = [
        'report_name', 'report_type', 'filters', 
        'columns', 'is_active', 'created_by_user_id'
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'is_active' => 'boolean'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function executions()
    {
        return $this->hasMany(ReportExecution::class);
    }
}