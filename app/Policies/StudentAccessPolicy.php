<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\SubjectRecord;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * A teacher can access a student iff either
 *   (a) the student's section is the teacher's classroom (CT scope), or
 *   (b) the teacher teaches at least one subject in the student's section.
 *
 * The student is passed as their user id (StudentRecord.user_id); we
 * translate that to the StudentRecord row internally. Guardian contact
 * viewing is gated separately for a smaller access surface.
 */
class StudentAccessPolicy
{
    use HandlesAuthorization;

    public function viewProfile(User $user, int $student_user_id): bool
    {
        return $this->accessible($user, $student_user_id);
    }

    public function viewGrades(User $user, int $student_user_id): bool
    {
        return $this->accessible($user, $student_user_id);
    }

    public function viewAttendance(User $user, int $student_user_id): bool
    {
        return $this->accessible($user, $student_user_id);
    }

    /**
     * Guardian contact info is CT-only (spec §4 "where permitted").
     */
    public function viewGuardianContacts(User $user, int $student_user_id): bool
    {
        if ($user->user_type !== 'teacher') return false;

        $sr = StudentRecord::where('user_id', $student_user_id)->first();
        if (! $sr) return false;

        return Section::where(['id' => $sr->section_id, 'teacher_id' => $user->id])->exists();
    }

    protected function accessible(User $user, int $student_user_id): bool
    {
        if ($user->user_type !== 'teacher') return false;

        $sr = StudentRecord::where('user_id', $student_user_id)->first();
        if (! $sr) return false;

        // Classroom-teacher route
        if (Section::where(['id' => $sr->section_id, 'teacher_id' => $user->id])->exists()) {
            return true;
        }

        // Subject-teacher route
        return SubjectRecord::where([
            'section_id' => $sr->section_id,
            'teacher_id' => $user->id,
        ])->exists();
    }
}
