<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Repositories\AttendanceRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AttendanceController extends Controller implements HasMiddleware
{
    protected $my_class, $student, $attendance, $user;

    public function __construct(
        MyClassRepo $my_class,
        StudentRepo $student,
        AttendanceRepo $attendance,
        UserRepo $user
    ) {
        $this->my_class = $my_class;
        $this->student = $student;
        $this->attendance = $attendance;
        $this->user = $user;
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('teamSATC'),
        ];
    }

    /**
     * Attendance entry point / section selector.
     */
    public function index()
    {
        $is_teacher = Qs::userIsTeacher();

        if ($is_teacher && !Qs::userIsTeamSA()) {
            $d['sections'] = Section::where('teacher_id', auth()->id())
                ->with('my_class')
                ->orderBy('name')
                ->get();
            $d['my_classes'] = $d['sections']->pluck('my_class')->unique('id');
        } else {
            $d['sections'] = $this->my_class->getAllSections();
            $d['my_classes'] = $this->my_class->all();
        }

        $d['is_teacher'] = $is_teacher;
        $d['today'] = date('Y-m-d');

        return view('pages.support_team.attendance.index', $d);
    }

    /**
     * Display daily attendance register for a specific section and date.
     */
    public function show($sec_id, $date = null)
    {
        $sec_id = is_numeric($sec_id) ? (int)$sec_id : Qs::decodeHash($sec_id);
        $section = $this->my_class->findSection($sec_id);

        if (!$section) {
            return Qs::goWithDanger('attendance.index');
        }

        // Security check: must be class teacher of this section OR admin/super_admin
        if (!Qs::userIsClassTeacherOf($sec_id) && !Qs::userIsTeamSA()) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        // Default date to today if not supplied or invalid
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $d['section'] = $section;
        $d['my_class'] = $section->my_class;
        $d['selected_date'] = $date;
        $d['formatted_date'] = Carbon::parse($date)->format('l, j F Y');
        $d['students'] = $this->student->findStudentsBySection($sec_id)
            ->whereNotNull('user')
            ->sortBy('user.name');

        // Existing records for this date
        $d['attendance_records'] = $this->attendance->getSectionAttendanceForDate($sec_id, $date);
        $d['recent_dates'] = $this->attendance->getRecentDates($sec_id, 7);
        $d['total_school_days'] = $this->attendance->getTotalSchoolDays($sec_id);

        return view('pages.support_team.attendance.manage', $d);
    }

    /**
     * Store or update attendance records for a section on a given date.
     */
    public function store(Request $request, $sec_id, $date)
    {
        $sec_id = is_numeric($sec_id) ? (int)$sec_id : Qs::decodeHash($sec_id);
        $section = $this->my_class->findSection($sec_id);

        if (!$section) {
            return Qs::json(__('msg.rnf'), false);
        }

        // Security check: Must be class teacher of this section OR admin
        if (!Qs::userIsClassTeacherOf($sec_id) && !Qs::userIsTeamSA()) {
            return Qs::json(__('msg.denied'), false);
        }

        $request->validate([
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent',
        ]);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $session = Qs::getCurrentSession();
        $this->attendance->markAttendanceForDate($sec_id, $date, $request->attendance, auth()->id(), $session);

        if ($request->ajax()) {
            return Qs::json(__('msg.store_ok'), true);
        }

        return back()->with('flash_success', 'Attendance recorded successfully for ' . Carbon::parse($date)->format('M d, Y'));
    }

    /**
     * Section cumulative attendance report.
     */
    public function section_report($sec_id)
    {
        $sec_id = is_numeric($sec_id) ? (int)$sec_id : Qs::decodeHash($sec_id);
        $section = $this->my_class->findSection($sec_id);

        if (!$section) {
            return Qs::goWithDanger('attendance.index');
        }

        // Security check: class teacher or admin
        if (!Qs::userIsClassTeacherOf($sec_id) && !Qs::userIsTeamSA()) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $d['section'] = $section;
        $d['my_class'] = $section->my_class;
        $d['session'] = Qs::getCurrentSession();
        $d['report'] = $this->attendance->getSectionAttendanceSummary($sec_id);
        $d['recent_dates'] = $this->attendance->getRecentDates($sec_id, 10);

        return view('pages.support_team.attendance.section_report', $d);
    }

    /**
     * Individual student attendance report.
     */
    public function student_report($sr_id)
    {
        $sr_id = is_numeric($sr_id) ? (int)$sr_id : Qs::decodeHash($sr_id);
        $sr = StudentRecord::with(['user', 'my_class', 'section', 'my_parent'])->find($sr_id);

        if (!$sr) {
            return Qs::goWithDanger();
        }

        // Security check: class teacher of student, subject teacher, parent, student themselves, or admin
        $user_id = auth()->id();
        $is_ct = Qs::userIsClassTeacherOf($sr->section_id);
        $is_parent = Qs::userIsMyChild($sr->user_id, $user_id);
        $is_self = $user_id === $sr->user_id;
        $is_admin = Qs::userIsTeamSA();

        if (!$is_ct && !$is_parent && !$is_self && !$is_admin) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $d['sr'] = $sr;
        $d['stats'] = $this->attendance->getStudentAttendanceStats($sr->id);
        $d['history'] = \App\Models\Attendance::where('student_record_id', $sr->id)
            ->orderBy('date', 'desc')
            ->get();

        return view('pages.support_team.attendance.student_report', $d);
    }
}
