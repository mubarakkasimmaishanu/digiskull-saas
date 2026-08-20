<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\ClassTeacherPermission;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\SubjectRecord;
use App\Policies\AttendancePolicy;
use App\Policies\ClassroomPolicy;
use App\Policies\StudentAccessPolicy;
use App\Policies\SubjectRecordPolicy;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Policy + Gate registrations for the teacher module (spec §14).
 *
 * Two ownership scopes underpin everything:
 *   - subjectOwnership : a teacher owns a (subject, section) pair iff a
 *                        SubjectRecord row assigns them.
 *   - classroomOwnership: a teacher is the classroom teacher of a
 *                        section iff sections.teacher_id matches.
 *
 * Admins/super_admins are unconditionally allowed via {@see Gate::before()}
 * — the existing `teamSA` middleware already gates admin-facing routes;
 * this global before() only trips when a teacher-only route explicitly
 * checks a gate. It keeps existing admin flows untouched.
 *
 * Laravel 12 removed the `Illuminate\Foundation\Support\Providers\AuthServiceProvider`
 * base class, so this extends the plain ServiceProvider and registers
 * policies via Gate::policy() directly (auto-discovery would miss us
 * anyway: the User model lives at `App\User`, not `App\Models\User`).
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(SubjectRecord::class, SubjectRecordPolicy::class);
        Gate::policy(Section::class,       ClassroomPolicy::class);
        Gate::policy(Attendance::class,    AttendancePolicy::class);
        Gate::policy(StudentRecord::class, StudentAccessPolicy::class);

        // Admins/super_admins bypass teacher-scope checks.
        Gate::before(function (User $user, string $ability) {
            if (in_array($user->user_type, ['super_admin', 'admin'], true)) {
                return true;
            }
            return null; // fall through to policies
        });

        // Convenience gates that don't hang off a specific model.
        Gate::define('teacher.view_dashboard', fn(User $u) => $u->user_type === 'teacher');
        Gate::define('teacher.view_duties',    fn(User $u) => $u->user_type === 'teacher');

        Gate::define('classroom.act_as_ct', function (User $u, int $section_id) {
            return $u->user_type === 'teacher'
                && Section::where(['id' => $section_id, 'teacher_id' => $u->id])->exists();
        });
    }

    /**
     * Convenience accessor for policies that need effective per-section
     * feature flags (attendance correction, comment editing, ...).
     */
    public static function permissionsFor(?int $section_id): ClassTeacherPermission
    {
        return ClassTeacherPermission::forSection($section_id);
    }
}
