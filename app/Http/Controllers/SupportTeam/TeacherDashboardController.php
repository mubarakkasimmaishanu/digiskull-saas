<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectRecord;
use App\Repositories\AttendanceRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TeacherDashboardController extends Controller implements HasMiddleware
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
            new Middleware('teamSAT'),
        ];
    }

    /**
     * Class Teacher View: Displays assigned classes/sections for the teacher.
     */
    public function my_class()
    {
        $teacher_id = auth()->id();
        $is_admin = Qs::userIsTeamSA();

        if ($is_admin) {
            $sections = Section::with(['my_class.subjects', 'teacher'])->orderBy('my_class_id')->get();
        } else {
            $sections = Section::where('teacher_id', $teacher_id)
                ->with(['my_class.subjects', 'teacher'])
                ->orderBy('my_class_id')
                ->get();
        }

        $today = date('Y-m-d');

        $sections_data = $sections->map(function ($sec) use ($today) {
            $students_count = $this->student->findStudentsBySection($sec->id)->count();
            $attendance_marked_today = Attendance::where('section_id', $sec->id)
                ->whereDate('date', $today)
                ->exists();
            $total_school_days = $this->attendance->getTotalSchoolDays($sec->id);
            $inherited_subjects_count = $sec->my_class ? $sec->my_class->subjects->count() : 0;

            return [
                'section' => $sec,
                'class_name' => $sec->my_class->name ?? 'N/A',
                'section_name' => $sec->name,
                'students_count' => $students_count,
                'attendance_marked_today' => $attendance_marked_today,
                'total_school_days' => $total_school_days,
                'inherited_subjects_count' => $inherited_subjects_count,
            ];
        });

        $d['sections_data'] = $sections_data;
        $d['is_admin'] = $is_admin;
        $d['today'] = $today;

        return view('pages.teacher.my_classes', $d);
    }

    /**
     * Class Teacher Section Detail: Displays student roster, inherited class subjects, and attendance.
     */
    public function class_detail($sec_id)
    {
        $sec_id = is_numeric($sec_id) ? (int)$sec_id : Qs::decodeHash($sec_id);
        $section = Section::with(['my_class.subjects', 'teacher'])->find($sec_id);

        if (!$section) {
            return Qs::goWithDanger('teacher.my_class');
        }

        // Security check: Must be class teacher of this section OR admin
        if (!Qs::userIsClassTeacherOf($sec_id) && !Qs::userIsTeamSA()) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $today = date('Y-m-d');
        $students = $this->student->findStudentsBySection($sec_id)
            ->whereNotNull('user')
            ->sortBy('user.name');

        $inherited_subjects = $section->my_class
            ? $section->my_class->subjects()->orderBy('name')->get()
            : collect([]);

        $attendance_today = $this->attendance->getSectionAttendanceForDate($sec_id, $today);
        $attendance_summary = $this->attendance->getSectionAttendanceSummary($sec_id);

        $d['section'] = $section;
        $d['my_class'] = $section->my_class;
        $d['students'] = $students;
        $d['inherited_subjects'] = $inherited_subjects;
        $d['today'] = $today;
        $d['attendance_today'] = $attendance_today;
        $d['attendance_summary'] = $attendance_summary;
        $d['total_school_days'] = $attendance_summary['total_school_days'] ?? 0;

        return view('pages.teacher.class_detail', $d);
    }

    /**
     * Subject Teacher View: Displays assigned subjects and corresponding classes.
     */
    public function my_subjects()
    {
        $teacher_id = auth()->id();
        $is_admin = Qs::userIsTeamSA();

        if ($is_admin) {
            $subject_records = SubjectRecord::with(['subject.my_class', 'section.my_class', 'teacher'])
                ->get();
        } else {
            $subject_records = SubjectRecord::where('teacher_id', $teacher_id)
                ->with(['subject.my_class', 'section.my_class', 'teacher'])
                ->get();
        }

        // Group by subject
        $grouped_subjects = [];

        foreach ($subject_records as $rec) {
            if (!$rec->subject) {
                continue;
            }

            $sub_id = $rec->subject->id;
            if (!isset($grouped_subjects[$sub_id])) {
                $grouped_subjects[$sub_id] = [
                    'subject' => $rec->subject,
                    'class_name' => $rec->subject->my_class->name ?? 'All Classes',
                    'assignments' => [],
                ];
            }

            $students_count = 0;
            if ($rec->section_id) {
                $students_count = $this->student->findStudentsBySection($rec->section_id)->count();
            } elseif ($rec->subject->my_class_id) {
                $students_count = $this->student->findStudentsByClass($rec->subject->my_class_id)->count();
            }

            $grouped_subjects[$sub_id]['assignments'][] = [
                'record_id' => $rec->id,
                'section' => $rec->section,
                'section_id' => $rec->section_id,
                'section_name' => $rec->section->name ?? 'All Sections',
                'class_name' => $rec->section->my_class->name ?? ($rec->subject->my_class->name ?? 'N/A'),
                'students_count' => $students_count,
            ];
        }

        $d['grouped_subjects'] = $grouped_subjects;
        $d['is_admin'] = $is_admin;

        return view('pages.teacher.my_subjects', $d);
    }

    /**
     * Subject Teacher Class View: Displays all students in assigned class/section for that subject.
     * Note: Student list is automatically derived from class membership.
     */
    public function subject_students($subject_id, $section_id)
    {
        $subject_id = (int)$subject_id;
        $section_id = (int)$section_id;

        $subject = Subject::with('my_class')->find($subject_id);
        $section = Section::with('my_class')->find($section_id);

        if (!$subject || !$section) {
            return Qs::goWithDanger('teacher.my_subjects');
        }

        // Security check: Must own this subject-section assignment OR be admin
        if (!Qs::userOwnsSubject($subject_id, $section_id) && !Qs::userIsTeamSA()) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        // Automatically fetch students by class/section membership (no manual student selection)
        $students = $this->student->findStudentsBySection($section_id)
            ->whereNotNull('user')
            ->sortBy('user.name');

        $d['subject'] = $subject;
        $d['section'] = $section;
        $d['my_class'] = $section->my_class;
        $d['students'] = $students;
        $d['session'] = Qs::getCurrentSession();

        return view('pages.teacher.subject_students', $d);
    }

    /**
     * Class Teacher Academic Review: Displays all student scores across all subjects in this section.
     * Note: Read-only for class teachers unless granted explicit can_edit_subject_marks permission.
     */
    public function academic_review($sec_id, $exam_id = null)
    {
        $sec_id = is_numeric($sec_id) ? (int)$sec_id : Qs::decodeHash($sec_id);
        $section = Section::with(['my_class.subjects', 'my_class.class_type', 'teacher'])->find($sec_id);

        if (!$section) {
            return Qs::goWithDanger('teacher.my_class');
        }

        // Security check
        if (!Qs::userIsClassTeacherOf($sec_id) && !Qs::userIsTeamSA()) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $year = Qs::getCurrentSession();
        $exams = \App\Models\Exam::where('year', $year)->get();
        if ($exams->isEmpty()) {
            $exams = \App\Models\Exam::all();
        }

        $active_exam = $exam_id ? \App\Models\Exam::find($exam_id) : $exams->first();

        $students = $this->student->findStudentsBySection($sec_id)
            ->whereNotNull('user')
            ->sortBy('user.name');

        $subjects = $section->my_class
            ? $section->my_class->subjects()->orderBy('name')->get()
            : collect([]);

        $marks = collect([]);
        $exam_records = collect([]);

        if ($active_exam) {
            $marks = \App\Models\Mark::where([
                'exam_id' => $active_exam->id,
                'section_id' => $sec_id,
                'year' => $year,
            ])->get();

            $exam_records = \App\Models\ExamRecord::where([
                'exam_id' => $active_exam->id,
                'section_id' => $sec_id,
                'year' => $year,
            ])->get()->keyBy('student_id');
        }

        $ct_perm = \App\Models\ClassTeacherPermission::forSection($sec_id);

        $d = [
            'section' => $section,
            'my_class' => $section->my_class,
            'students' => $students,
            'subjects' => $subjects,
            'exams' => $exams,
            'selected_exam' => $active_exam,
            'marks' => $marks,
            'exam_records' => $exam_records,
            'year' => $year,
            'can_edit_subject_marks' => $ct_perm->can_edit_subject_marks || Qs::userIsTeamSA(),
        ];

        return view('pages.teacher.academic_review', $d);
    }
}
