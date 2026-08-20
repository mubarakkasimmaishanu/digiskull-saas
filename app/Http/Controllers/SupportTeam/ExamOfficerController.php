<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Mk;
use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\Mark;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\SubjectScoreSubmission;
use App\Repositories\AssessmentScoreRepo;
use App\Repositories\AttendanceRepo;
use App\Repositories\ExamRepo;
use App\Repositories\FinancialRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\Request;

class ExamOfficerController extends Controller
{
    protected $scoreRepo, $examRepo, $myClassRepo, $attendanceRepo, $studentRepo, $financialRepo, $year;

    public function __construct(
        AssessmentScoreRepo $scoreRepo,
        ExamRepo $examRepo,
        MyClassRepo $myClassRepo,
        AttendanceRepo $attendanceRepo,
        StudentRepo $studentRepo,
        FinancialRepo $financialRepo
    ) {
        $this->scoreRepo = $scoreRepo;
        $this->examRepo = $examRepo;
        $this->myClassRepo = $myClassRepo;
        $this->attendanceRepo = $attendanceRepo;
        $this->studentRepo = $studentRepo;
        $this->financialRepo = $financialRepo;
        $this->year = Mk::getCurrentSession();
    }

    /**
     * Exam Officer Dashboard
     */
    public function index()
    {
        $d['year'] = $this->year;
        $d['exams'] = Exam::where('year', $this->year)->get();
        $d['classes'] = MyClass::with('section')->get();

        $d['submissions_count'] = [
            'total' => SubjectScoreSubmission::where('year', $this->year)->count(),
            'submitted' => SubjectScoreSubmission::where(['year' => $this->year, 'status' => 'submitted'])->count(),
            'approved' => SubjectScoreSubmission::where(['year' => $this->year, 'status' => 'approved'])->count(),
            'locked' => SubjectScoreSubmission::where(['year' => $this->year, 'status' => 'locked'])->count(),
            'rejected' => SubjectScoreSubmission::where(['year' => $this->year, 'status' => 'rejected'])->count(),
            'draft' => SubjectScoreSubmission::where(['year' => $this->year, 'status' => 'draft'])->count(),
        ];

        $d['recent_submissions'] = SubjectScoreSubmission::with(['exam', 'my_class', 'section', 'subject', 'teacher'])
            ->where('year', $this->year)
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('pages.exam_officer.dashboard', $d);
    }

    /**
     * Submissions Moderation & List
     */
    public function submissions(Request $request)
    {
        $d['year'] = $this->year;
        $d['exams'] = Exam::where('year', $this->year)->get();
        $d['classes'] = MyClass::with('section')->get();

        $query = SubjectScoreSubmission::with(['exam', 'my_class', 'section', 'subject', 'teacher', 'reviewer'])
            ->where('year', $this->year);

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }
        if ($request->filled('class_id')) {
            $query->where('my_class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $d['submissions'] = $query->latest('updated_at')->paginate(20)->withQueryString();
        $d['selected_exam'] = $request->exam_id;
        $d['selected_class'] = $request->class_id;
        $d['selected_section'] = $request->section_id;
        $d['selected_status'] = $request->status;

        return view('pages.exam_officer.submissions', $d);
    }

    /**
     * Review detailed score sheet
     */
    public function review($submission_id)
    {
        $submission = SubjectScoreSubmission::with(['exam', 'my_class.class_type', 'section', 'subject', 'teacher', 'reviewer'])
            ->findOrFail($submission_id);

        $data = $this->scoreRepo->getStudentsWithScores(
            $submission->subject_id,
            $submission->section_id,
            $submission->exam_id,
            $submission->year
        );

        $data['submission'] = $submission;
        $data['year'] = $submission->year;

        return view('pages.exam_officer.review', $data);
    }

    /**
     * Approve submission
     */
    public function approve($submission_id)
    {
        try {
            $this->scoreRepo->approveSubmission($submission_id, auth()->id());
            return back()->with('flash_success', 'Score package approved successfully and locked against ordinary teacher edits.');
        } catch (\Exception $e) {
            return back()->with('flash_danger', $e->getMessage());
        }
    }

    /**
     * Reject/Return submission with remarks
     */
    public function reject(Request $request, $submission_id)
    {
        $reason = $request->input('rejection_reason', 'Returned by Examination Officer for corrections.');

        try {
            $this->scoreRepo->rejectSubmission($submission_id, auth()->id(), $reason);
            return back()->with('flash_warning', 'Score package returned to teacher for corrections.');
        } catch (\Exception $e) {
            return back()->with('flash_danger', $e->getMessage());
        }
    }

    /**
     * Toggle Lock on a submission
     */
    public function toggle_lock($submission_id)
    {
        try {
            $sub = $this->scoreRepo->toggleLock($submission_id, auth()->id());
            $msg = $sub->status === 'locked' ? 'Score package locked.' : 'Score package unlocked.';
            return back()->with('flash_info', $msg);
        } catch (\Exception $e) {
            return back()->with('flash_danger', $e->getMessage());
        }
    }

    /**
     * Audit Missing & Incomplete Scores
     */
    public function missing_scores(Request $request)
    {
        $d['year'] = $this->year;
        $d['exams'] = Exam::where('year', $this->year)->get();
        $d['classes'] = MyClass::with('section')->get();

        $exam_id = $request->input('exam_id', $d['exams']->first()?->id);
        $class_id = $request->input('class_id');

        $d['selected_exam'] = $exam_id;
        $d['selected_class'] = $class_id;

        if ($exam_id) {
            $auditData = $this->scoreRepo->getMissingScoresAudit($exam_id, $class_id, $this->year);
            $d['audit'] = $auditData['audit'];
            $d['summary'] = $auditData['summary'];
        } else {
            $d['audit'] = [];
            $d['summary'] = null;
        }

        return view('pages.exam_officer.missing_scores', $d);
    }

    /**
     * Results Generation & Positions Hub
     */
    public function results_generation(Request $request)
    {
        $d['year'] = $this->year;
        $d['exams'] = Exam::where('year', $this->year)->get();
        $d['classes'] = MyClass::with(['section', 'class_type'])->get();

        $d['selected_exam'] = $request->input('exam_id', $d['exams']->first()?->id);
        $d['selected_class'] = $request->input('class_id');
        $d['selected_section'] = $request->input('section_id');

        $d['exam_records'] = [];
        if ($d['selected_exam'] && $d['selected_class']) {
            $query = ExamRecord::with(['student', 'section', 'grade'])
                ->where([
                    'exam_id' => $d['selected_exam'],
                    'my_class_id' => $d['selected_class'],
                    'year' => $this->year,
                ]);

            if ($d['selected_section']) {
                $query->where('section_id', $d['selected_section']);
            }

            $d['exam_records'] = $query->orderBy('class_pos')->get();
        }

        return view('pages.exam_officer.results_generation', $d);
    }

    /**
     * Execute Batch Results Generation
     */
    public function generate_results(Request $request)
    {
        $exam_id = $request->input('exam_id');
        $class_id = $request->input('class_id');
        $section_id = $request->input('section_id');

        if (!$exam_id || !$class_id) {
            return back()->with('flash_danger', 'Please select both Exam and Class to generate results.');
        }

        try {
            $results = $this->scoreRepo->calculateOverallResults($class_id, $section_id, $exam_id, $this->year);
            return back()->with('flash_success', 'Results generated successfully for ' . count($results) . ' students with subject positions, overall totals, averages, and class rankings.');
        } catch (\Exception $e) {
            return back()->with('flash_danger', 'Error generating results: ' . $e->getMessage());
        }
    }

    /**
     * Student Report Card
     */
    public function report_card($student_id, $exam_id, $year = null)
    {
        $year = $year ?: $this->year;
        $exam = Exam::findOrFail($exam_id);
        $sr = StudentRecord::where(['user_id' => $student_id, 'session' => $year])->with(['user', 'my_class.class_type', 'section'])->first();

        if (!$sr) {
            $sr = StudentRecord::where('user_id', $student_id)->with(['user', 'my_class.class_type', 'section'])->latest()->first();
        }

        if (!$sr) {
            return redirect()->route('dashboard')->with('pop_error', 'Student record not found.');
        }

        // Financial Clearance Restriction (Phase 3)
        $fee_summary = $this->financialRepo->getStudentFeeSummary($student_id, $year);
        if (!$fee_summary['is_cleared'] && !Qs::userIsSuperAdmin()) {
            return view('pages.exam_officer.report_card_locked', [
                'sr' => $sr,
                'student' => $sr->user,
                'exam' => $exam,
                'year' => $year,
                'fee_summary' => $fee_summary,
            ]);
        }

        // Fetch marks for this student across all class subjects
        $subjects = $sr->my_class->subjects;
        $marks = Mark::where([
            'student_id' => $student_id,
            'exam_id' => $exam->id,
            'my_class_id' => $sr->my_class_id,
            'year' => $year,
        ])->with('grade')->get()->keyBy('subject_id');

        // Build subject rows
        $subject_rows = [];
        $total_score_sum = 0;
        $subjects_count = 0;

        foreach ($subjects as $subject) {
            $mark = $marks->get($subject->id);
            $ca1 = $mark ? $mark->tex1 : null;
            $ca2 = $mark ? $mark->tex2 : null;
            $exm = $mark ? $mark->exm : null;
            $total = $mark ? $mark->cum : null;

            if ($total !== null) {
                $total_score_sum += $total;
                $subjects_count++;
            }

            $gradeName = $mark && $mark->grade ? $mark->grade->name : $this->scoreRepo->getGradeName($total, $sr->my_class->class_type_id);
            $gradeRemark = $mark && $mark->grade ? $mark->grade->remark : '—';

            $subject_rows[] = [
                'subject' => $subject,
                'ca1' => $ca1,
                'ca2' => $ca2,
                'exm' => $exm,
                'total' => $total,
                'grade' => $gradeName,
                'remark' => $gradeRemark,
                'sub_pos' => $mark ? $mark->sub_pos : null,
            ];
        }

        // Overall ExamRecord
        $exam_record = ExamRecord::where([
            'exam_id' => $exam->id,
            'student_id' => $student_id,
            'my_class_id' => $sr->my_class_id,
            'year' => $year,
        ])->first();

        // Attendance stats from Phase 1
        $attendance_stats = $this->attendanceRepo->getStudentAttendanceStats($sr->id, $year);

        // Class and Section total students count
        $class_students_count = StudentRecord::where(['my_class_id' => $sr->my_class_id, 'session' => $year])->count();
        $section_students_count = StudentRecord::where(['section_id' => $sr->section_id, 'session' => $year])->count();

        $d = [
            'sr' => $sr,
            'student' => $sr->user,
            'exam' => $exam,
            'year' => $year,
            'subject_rows' => $subject_rows,
            'exam_record' => $exam_record,
            'total_score_sum' => $total_score_sum,
            'subjects_count' => $subjects_count,
            'calculated_average' => $subjects_count > 0 ? round($total_score_sum / $subjects_count, 1) : 0,
            'attendance_stats' => $attendance_stats,
            'class_students_count' => $class_students_count,
            'section_students_count' => $section_students_count,
        ];

        return view('pages.exam_officer.report_card', $d);
    }

    /**
     * Batch Printable Report Cards
     */
    public function bulk_report_cards(Request $request)
    {
        $exam_id = $request->input('exam_id');
        $class_id = $request->input('class_id');
        $section_id = $request->input('section_id');
        $year = $request->input('year', $this->year);

        if (!$exam_id || !$class_id) {
            return back()->with('flash_danger', 'Please select Exam and Class.');
        }

        $studentsQuery = StudentRecord::where(['my_class_id' => $class_id, 'session' => $year]);
        if ($section_id) {
            $studentsQuery->where('section_id', $section_id);
        }
        $students = $studentsQuery->with('user')->get();

        $cards = [];
        foreach ($students as $sr) {
            $cards[] = $this->buildReportCardData($sr, $exam_id, $year);
        }

        return view('pages.exam_officer.bulk_report_cards', [
            'cards' => $cards,
            'exam' => Exam::find($exam_id),
            'year' => $year,
        ]);
    }

    protected function buildReportCardData(StudentRecord $sr, $exam_id, $year): array
    {
        $exam = Exam::find($exam_id);
        $subjects = $sr->my_class->subjects;
        $marks = Mark::where([
            'student_id' => $sr->user_id,
            'exam_id' => $exam_id,
            'my_class_id' => $sr->my_class_id,
            'year' => $year,
        ])->with('grade')->get()->keyBy('subject_id');

        $subject_rows = [];
        $total_score_sum = 0;
        $subjects_count = 0;

        foreach ($subjects as $subject) {
            $mark = $marks->get($subject->id);
            $ca1 = $mark ? $mark->tex1 : null;
            $ca2 = $mark ? $mark->tex2 : null;
            $exm = $mark ? $mark->exm : null;
            $total = $mark ? $mark->cum : null;

            if ($total !== null) {
                $total_score_sum += $total;
                $subjects_count++;
            }

            $gradeName = $mark && $mark->grade ? $mark->grade->name : $this->scoreRepo->getGradeName($total, $sr->my_class->class_type_id);
            $gradeRemark = $mark && $mark->grade ? $mark->grade->remark : '—';

            $subject_rows[] = [
                'subject' => $subject,
                'ca1' => $ca1,
                'ca2' => $ca2,
                'exm' => $exm,
                'total' => $total,
                'grade' => $gradeName,
                'remark' => $gradeRemark,
                'sub_pos' => $mark ? $mark->sub_pos : null,
            ];
        }

        $exam_record = ExamRecord::where([
            'exam_id' => $exam_id,
            'student_id' => $sr->user_id,
            'my_class_id' => $sr->my_class_id,
            'year' => $year,
        ])->first();

        $attendance_stats = $this->attendanceRepo->getStudentAttendanceStats($sr->id, $year);
        $class_students_count = StudentRecord::where(['my_class_id' => $sr->my_class_id, 'session' => $year])->count();
        $section_students_count = StudentRecord::where(['section_id' => $sr->section_id, 'session' => $year])->count();
        $fee_summary = $this->financialRepo->getStudentFeeSummary($sr->user_id, $year);

        return [
            'sr' => $sr,
            'student' => $sr->user,
            'exam' => $exam,
            'year' => $year,
            'subject_rows' => $subject_rows,
            'exam_record' => $exam_record,
            'total_score_sum' => $total_score_sum,
            'subjects_count' => $subjects_count,
            'calculated_average' => $subjects_count > 0 ? round($total_score_sum / $subjects_count, 1) : 0,
            'attendance_stats' => $attendance_stats,
            'class_students_count' => $class_students_count,
            'section_students_count' => $section_students_count,
            'fee_summary' => $fee_summary,
            'is_cleared' => $fee_summary['is_cleared'],
        ];
    }
}
