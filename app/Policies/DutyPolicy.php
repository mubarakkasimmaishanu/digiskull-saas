<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Duty-roster actions (spec §12). The `duty_assignments` table is
 * introduced by Increment 5; this policy is defined now so
 * later increments can reference it without touching auth wiring.
 *
 * Ownership is `duty_assignments.user_id === user.id`. Since the
 * table doesn't exist yet, checks return true only for teachers and
 * defer the row check to the controller until Increment 5 wires up
 * the model — the concrete queries land there.
 */
class DutyPolicy
{
    use HandlesAuthorization;

    public function viewOwnDuties(User $user): bool
    {
        return $user->user_type === 'teacher';
    }

    /**
     * Marking a duty as complete is limited to the assignee. The
     * assignment id is validated in the controller against the
     * still-unbuilt `duty_assignments` table (Increment 5).
     */
    public function markComplete(User $user, int $duty_assignment_id): bool
    {
        if ($user->user_type !== 'teacher') return false;

        // Defensive: table lands in Increment 5 — treat missing as deny.
        if (! \Illuminate\Support\Facades\Schema::hasTable('duty_assignments')) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::table('duty_assignments')
            ->where('id', $duty_assignment_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function requestSwap(User $user, int $duty_assignment_id): bool
    {
        return $this->markComplete($user, $duty_assignment_id);
    }
}
