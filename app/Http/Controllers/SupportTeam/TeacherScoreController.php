<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Mk;
use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectRecord;
use App\Repositories\AssessmentScoreRepo;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Http\Request;

class TeacherScoreController extends Controller
{
    protected $scoreRepo, $examRepo, $myClassRepo, $year;

    public function __construct(AssessmentScoreRepo $scoreRepo, ExamRepo $examRepo, MyClassRepo $myClassRepo)
    {
        $this->scoreRepo = $scoreRepo;
        $this->examRepo = $examRepo;
        $this->myClassRepo = $myClassRepo;
        $this->year = Mk::getCurrentSession();
    }

    /**
     * Show score entry interface for a subject and class section
     */
    public function index($subject_id, $section_id, $exam_id = null)
    {
        $subject = Subject::findOrFail($subject_id);
        $section = Section::with('my_class.class_type')->findOrFail($section_id);

        // Security check: teacher must own this (subject, section) or be Admin
        if (!Qs::userIsTeamSA() && !Qs::userOwnsSubject($subject_id, $section_id)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        // Available exams for session
        $exams = Exam::where('year', $this->year)->get();
        if ($exams->isEmpty()) {
            // If no exam exists, fetch all or create placeholder
            $exams = Exam::all();
        }

        $active_exam = $exam_id ? Exam::find($exam_id) : $exams->first();
        if (!$active_exam) {
            return redirect()->route('teacher.my_subjects')->with('flash_danger', 'No active examination found for this session. Please contact the Examination Officer.');
        }

        $data = $this->scoreRepo->getStudentsWithScores($subject->id, $section->id, $active_exam->id, $this->year);
        $data['exams'] = $exams;
        $data['selected_exam'] = $active_exam;
        $data['year'] = $this->year;

        return view('pages.teacher.subject_scores', $data);
    }

    /**
     * Save draft scores
     */
    public function save(Request $request, $subject_id, $section_id)
    {
        // Security check
        if (!Qs::userIsTeamSA() && !Qs::userOwnsSubject($subject_id, $section_id)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $exam_id = $request->input('exam_id');
        $scores = $request->input('scores', []);

        try {
            $this->scoreRepo->saveDraftScores($subject_id, $section_id, $exam_id, $this->year, $scores, auth()->id());
            return back()->with('flash_success', 'Draft scores saved and positions updated successfully.');
        } catch (\Exception $e) {
            return back()->with('flash_danger', $e->getMessage());
        }
    }

    /**
     * Submit score sheet for Exam Officer approval
     */
    public function submit(Request $request, $subject_id, $section_id)
    {
        // Security check
        if (!Qs::userIsTeamSA() && !Qs::userOwnsSubject($subject_id, $section_id)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $exam_id = $request->input('exam_id');
        $scores = $request->input('scores', []);

        try {
            // First save any latest edits
            if (!empty($scores)) {
                $this->scoreRepo->saveDraftScores($subject_id, $section_id, $exam_id, $this->year, $scores, auth()->id());
            }

            // Submit package
            $this->scoreRepo->submitScores($subject_id, $section_id, $exam_id, $this->year, auth()->id());
            return back()->with('flash_success', 'Scores successfully submitted to the Examination Officer for review.');
        } catch (\Exception $e) {
            return back()->with('flash_danger', $e->getMessage());
        }
    }
}
