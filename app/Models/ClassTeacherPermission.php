<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Per-section (or tenant-wide default) classroom-teacher permission toggles.
 *
 * See ClassroomPolicy for how these are consulted. Rows are cached per
 * tenant request via {@see forSection()} so a single controller call
 * does not fan out repeated DB hits when many sub-checks fire.
 */
class ClassTeacherPermission extends Eloquent
{
    use LogsActivity;

    protected $fillable = [
        'section_id',
        'can_edit_subject_marks',
        'can_correct_attendance',
        'can_edit_teacher_comments',
        'can_message_parents',
        'attendance_correction_window_hours',
        'report_card_approval',
    ];

    protected $casts = [
        'can_edit_subject_marks'    => 'boolean',
        'can_correct_attendance'    => 'boolean',
        'can_edit_teacher_comments' => 'boolean',
        'can_message_parents'       => 'boolean',
        'attendance_correction_window_hours' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Effective permissions for a section: the per-section row if it
     * exists, otherwise the tenant-wide default (section_id = NULL).
     * A synthetic model is returned when neither exists so callers can
     * always treat the return as an instance.
     */
    public static function forSection(?int $section_id): self
    {
        $key = 'ctperm:' . ($section_id ?? 'default');

        return Cache::store('array')->rememberForever($key, function () use ($section_id) {
            $row = $section_id !== null
                ? self::where('section_id', $section_id)->first()
                : null;

            $row ??= self::whereNull('section_id')->first();

            return $row ?? new self([
                'can_edit_subject_marks' => false,
                'can_correct_attendance' => true,
                'can_edit_teacher_comments' => true,
                'can_message_parents' => true,
                'attendance_correction_window_hours' => 48,
                'report_card_approval' => 'head_teacher',
            ]);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
