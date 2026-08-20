<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Section\SectionCreate;
use App\Http\Requests\Section\SectionUpdate;
use App\Models\ClassTeacherPermission;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;

class SectionController extends Controller implements HasMiddleware
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->my_class = $my_class;
        $this->user = $user;
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('teamSA', except: ['destroy']),
            new Middleware('superAdmin', only: ['destroy']),
        ];
    }

    public function index()
    {
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = $this->my_class->getAllSections();
        $d['teachers'] = $this->user->getUserByTypes(['teacher', 'admin', 'super_admin']);

        return view('pages.support_team.sections.index', $d);
    }

    public function store(SectionCreate $req)
    {
        $data = $req->all();
        $this->my_class->createSection($data);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $d['s'] = $s = $this->my_class->findSection($id);
        $d['teachers'] = $this->user->getUserByTypes(['teacher', 'admin', 'super_admin']);

        return $s === null ? Qs::goWithDanger('sections.index') : view('pages.support_team.sections.edit', $d);
    }

    public function update(SectionUpdate $req, $id)
    {
        $data = $req->only(['name', 'teacher_id']);
        $this->my_class->updateSection($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        if ($this->my_class->isActiveSection($id))
            return back()->with('pop_warning', 'Every class must have a default section, You cannot delete It.');

        if ($this->my_class->sectionHasStudent($id))
            return back()->with('pop_warning', 'This section has student(s) with it, you cannot delete it.');

        $this->my_class->deleteSection($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Classroom-teacher permission editor for a specific section
     * (spec §19). Falls through to the tenant-wide defaults when the
     * section has no override yet.
     *
     * Mass-market UX note: most schools should never open this page —
     * they configure the tenant-wide defaults once via
     * {@see school_default_permissions()} and every section inherits
     * them. This per-section editor is intentionally hidden behind an
     * "Advanced" expander on the section edit page.
     */
    public function classroom_permissions($id)
    {
        $section = $this->my_class->findSection($id);
        if (! $section) return Qs::goWithDanger('sections.index');

        $d['s']      = $section;
        $d['perm']   = ClassTeacherPermission::firstOrNew(['section_id' => $section->id]);
        $d['def']    = ClassTeacherPermission::whereNull('section_id')->first();
        $d['mode']   = 'section';

        return view('pages.support_team.sections.classroom_permissions', $d);
    }

    public function update_classroom_permissions(Request $req, $id)
    {
        $section = $this->my_class->findSection($id);
        if (! $section) return Qs::goWithDanger('sections.index');

        $data = $this->validatePermissions($req);

        ClassTeacherPermission::updateOrCreate(
            ['section_id' => $section->id],
            $data,
        );

        // ClassTeacherPermission::forSection() caches per request via
        // the array store, but we also flush proactively so any admin
        // change is visible on the CT's very next request in the same
        // worker (long-running Octane-style setups).
        Cache::store('array')->forget('ctperm:' . $section->id);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Tenant-wide default classroom-teacher rules (the row with
     * `section_id = NULL`). This is the page 90% of schools will use;
     * it governs every section that has no explicit override.
     */
    public function school_default_permissions()
    {
        $d['s']    = null;     // no section context — the view keys off this
        $d['perm'] = ClassTeacherPermission::firstOrNew(['section_id' => null]);
        $d['def']  = null;     // no fallback above the defaults
        $d['mode'] = 'default';

        return view('pages.support_team.sections.classroom_permissions', $d);
    }

    public function update_school_default_permissions(Request $req)
    {
        $data = $this->validatePermissions($req);

        ClassTeacherPermission::updateOrCreate(
            ['section_id' => null],
            $data,
        );

        // Flush the cached default row so the next resolve sees the change.
        Cache::store('array')->forget('ctperm:default');

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Shared validation for both the per-section and tenant-default editors.
     * `report_card_approval` still accepts `head_teacher` for schools that
     * write it via API/import — the UI just doesn't expose that tier anymore.
     */
    protected function validatePermissions(Request $req): array
    {
        return $req->validate([
            'can_edit_subject_marks'    => 'required|boolean',
            'can_correct_attendance'    => 'required|boolean',
            'can_edit_teacher_comments' => 'required|boolean',
            'can_message_parents'       => 'required|boolean',
            'attendance_correction_window_hours' => 'required|integer|min:0|max:720',
            'report_card_approval'      => 'required|in:none,head_teacher,principal',
        ]);
    }
}
