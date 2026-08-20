<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectRecord;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Owns the "does this teacher teach this (subject, section)?" question
 * used for score entry, subject-attendance, subject-tabulation and any
 * other subject-scoped teacher action. Admins/super_admins bypass via
 * {@see \App\Providers\AuthServiceProvider::boot()}'s Gate::before().
 */
class SubjectRecordPolicy
{
    use HandlesAuthorization;

    /**
     * Can the user enter/update scores for (subject, section)?
     */
    public function enterScore(User $user, Subject|int $subject, Section|int $section): bool
    {
        return $this->ownsPair(
            $user,
            $subject instanceof Subject ? $subject->id : (int) $subject,
            $section instanceof Section ? $section->id : (int) $section,
        );
    }

    public function viewOwnScores(User $user, Subject|int $subject, Section|int $section): bool
    {
        return $this->enterScore($user, $subject, $section);
    }

    /**
     * Broad "does the teacher own any subject in this class"? — cheap
     * gate for surfacing a class in menus without leaking sibling classes.
     */
    public function viewClass(User $user, int $my_class_id): bool
    {
        if ($user->user_type !== 'teacher') return false;

        return SubjectRecord::where('teacher_id', $user->id)
            ->whereHas('section', fn($q) => $q->where('my_class_id', $my_class_id))
            ->exists();
    }

    protected function ownsPair(User $user, int $subject_id, int $section_id): bool
    {
        if ($user->user_type !== 'teacher') return false;

        return SubjectRecord::where([
            'subject_id' => $subject_id,
            'section_id' => $section_id,
            'teacher_id' => $user->id,
        ])->exists();
    }
}
