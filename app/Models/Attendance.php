<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Attendance extends Eloquent
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'student_record_id',
        'section_id',
        'date',
        'status',
        'marked_by',
        'session',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student_record()
    {
        return $this->belongsTo(StudentRecord::class, 'student_record_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function marked_by_user()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
