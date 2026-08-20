<?php

namespace App\Policies;

use App\Models\Section;
use App\Providers\AuthServiceProvider;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Classroom-teacher scope (spec §3, §4, §6, §7, §8, §9, §10).
 *
 * Ownership is `sections.teacher_id === user.id`. Per-action feature
 * flags (edit marks, correct attendance, edit comments, message parents)
 * come from ClassTeacherPermission::forSection() and are administrator
 * configurable (spec §19). Never hardcoded here.
 */
class ClassroomPolicy
{
    use HandlesAuthorization;

    public function viewClass(User $user, Section $section): bool
    {
        return $this->owns($user, $section);
    }

    public function viewStudents(User $user, Section $section): bool
    {
        return $this->owns($user, $section);
    }

    public function viewGrades(User $user, Section $section): bool
    {
        return $this->owns($user, $section);
    }

    public function viewAnalytics(User $user, Section $section): bool
    {
        return $this->owns($user, $section);
    }

    public function generateReportCards(User $user, Section $section): bool
    {
        return $this->owns($user, $section);
    }

    /**
     * Editing the auto-generated teacher comment is CT-scoped AND
     * feature-flagged: an administrator can lock comment editing on
     * specific sections (spec §19).
     */
    public function editAutoComment(User $user, Section $section): bool
    {
        return $this->owns($user, $section)
            && AuthServiceProvider::permissionsFor($section->id)->can_edit_teacher_comments;
    }

    /**
     * Spec §6 is explicit: a CT does NOT edit marks entered by another
     * subject teacher unless an administrator has flipped the flag.
     * The default is OFF.
     */
    public function editSubjectMarks(User $user, Section $section): bool
    {
        return $this->owns($user, $section)
            && AuthServiceProvider::permissionsFor($section->id)->can_edit_subject_marks;
    }

    protected function owns(User $user, Section $section): bool
    {
        return $user->user_type === 'teacher' && (int) $section->teacher_id === (int) $user->id;
    }
}
