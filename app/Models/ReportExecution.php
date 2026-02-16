<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportExecution extends Model
{
    protected $fillable = [
        'template_id', 'user_id', 'report_name', 'format',
        'filters_used', 'file_path', 'record_count', 'generated_at'
    ];

    protected $casts = [
        'filters_used' => 'array',
        'record_count' => 'integer',
        'generated_at' => 'datetime'
    ];

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}