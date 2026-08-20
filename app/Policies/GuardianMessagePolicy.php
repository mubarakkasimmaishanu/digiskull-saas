<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\StudentRecord;
use App\Providers\AuthServiceProvider;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Guardian messaging (spec §8). A CT can only message guardians of
 * students in their assigned classroom, and only when the school has
 * left `can_message_parents` on for that section.
 */
class GuardianMessagePolicy
{
    use HandlesAuthorization;

    /**
     * Message the guardian of a specific student.
     */
    public function messageGuardianOf(User $user, int $student_user_id): bool
    {
        if ($user->user_type !== 'teacher') return false;

        $sr = StudentRecord::where('user_id', $student_user_id)->first();
        if (! $sr) return false;

        return $this->ctOfWithFeature($user, $sr->section_id);
    }

    /**
     * Announcement to the whole class.
     */
    public function announceToSection(User $user, Section $section): bool
    {
        return $this->ctOfWithFeature($user, $section->id);
    }

    protected function ctOfWithFeature(User $user, int $section_id): bool
    {
        $isCt = Section::where(['id' => $section_id, 'teacher_id' => $user->id])->exists();
        if (! $isCt) return false;

        return AuthServiceProvider::permissionsFor($section_id)->can_message_parents;
    }
}
