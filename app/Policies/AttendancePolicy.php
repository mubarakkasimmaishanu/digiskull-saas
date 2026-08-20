<?php

namespace App\Policies;

use App\Models\Section;
use App\Providers\AuthServiceProvider;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Attendance actions (spec §5) — daily class register.
 *
 * `mark`   is unconditional for the classroom teacher of the section.
 * `correct` additionally requires the school's feature flag AND falls
 *          inside the configurable correction window; both come from
 *          ClassTeacherPermission::forSection().
 */
class AttendancePolicy
{
    use HandlesAuthorization;

    public function mark(User $user, Section $section): bool
    {
        return $this->isCt($user, $section);
    }

    public function viewClassRegister(User $user, Section $section): bool
    {
        return $this->isCt($user, $section);
    }

    /**
     * @param  \DateTimeInterface|string  $original_date  date the row was originally created
     */
    public function correct(User $user, Section $section, \DateTimeInterface|string $original_date): bool
    {
        if (! $this->isCt($user, $section)) return false;

        $perms = AuthServiceProvider::permissionsFor($section->id);
        if (! $perms->can_correct_attendance) return false;

        $when = CarbonImmutable::parse($original_date);
        $windowHours = (int) $perms->attendance_correction_window_hours;

        return $when->diffInHours(CarbonImmutable::now()) <= $windowHours;
    }

    protected function isCt(User $user, Section $section): bool
    {
        return $user->user_type === 'teacher' && (int) $section->teacher_id === (int) $user->id;
    }
}
