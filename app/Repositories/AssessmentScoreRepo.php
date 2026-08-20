<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\SubjectScoreSubmission;
use Illuminate\Support\Facades\DB;

class AssessmentScoreRepo
{
    /**
     * Get or create a submission tracking record
     */
    public function getSubmission($exam_id, $class_id, $section_id, $subject_id, $year): SubjectScoreSubmission
    {
        return SubjectScoreSubmission::firstOrCreate(
            [
                'exam_id' => $exam_id,
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'subject_id' => $subject_id,
                'year' => $year,
            ],
            [
                'status' => 'draft',
            ]
        );
    }

    /**
     * Retrieve students in class section with active score records
     */
    public function getStudentsWithScores($subject_id, $section_id, $exam_id, $year): array
    {
        $section = Section::with('my_class.class_type')->findOrFail($section_id);
        $subject = Subject::findOrFail($subject_id);
        $exam = Exam::findOrFail($exam_id);
        $class_type_id = $section->my_class->class_type_id;

        $submission = $this->getSubmission($exam->id, $section->my_class_id, $section->id, $subject->id, $year);

        // Fetch students in this section for the active session
        $students = StudentRecord::where([
            'section_id' => $section->id,
            'session' => $year,
        ])->with('user')->get()->sortBy(fn($sr) => $sr->user->name ?? '');

        // Fetch existing marks
        $marks = Mark::where([
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'section_id' => $section->id,
            'year' => $year,
        ])->get()->keyBy('student_id');

        $rows = [];
        foreach ($students as $sr) {
            $mark = $marks->get($sr->user_id);
            $ca1 = $mark ? $mark->tex1 : null;
            $ca2 = $mark ? $mark->tex2 : null;
            $exm = $mark ? $mark->exm : null;

            $total = null;
            if ($ca1 !== null || $ca2 !== null || $exm !== null) {
                $total = (int)($ca1 ?? 0) + (int)($ca2 ?? 0) + (int)($exm ?? 0);
            }

            $grade = $total !== null ? $this->findGradeForScore($total, $class_type_id) : null;

            $rows[] = [
                'student_record' => $sr,
                'user' => $sr->user,
                'mark' => $mark,
                'ca1' => $ca1,
                'ca2' => $ca2,
                'exm' => $exm,
                'total' => $total,
                'grade' => $grade,
                'sub_pos' => $mark ? $mark->sub_pos : null,
            ];
        }

        return [
            'section' => $section,
            'my_class' => $section->my_class,
            'subject' => $subject,
            'exam' => $exam,
            'submission' => $submission,
            'rows' => $rows,
        ];
    }

    /**
     * Save draft scores for a subject and section
     */
    public function saveDraftScores($subject_id, $section_id, $exam_id, $year, array $scoresData, $teacher_id): SubjectScoreSubmission
    {
        $section = Section::with('my_class')->findOrFail($section_id);
        $submission = $this->getSubmission($exam_id, $section->my_class_id, $section_id, $subject_id, $year);

        if (!$submission->isEditableByTeacher() && !(auth()->check() && \App\Helpers\Qs::userIsTeamSA())) {
            throw new \Exception("Scores are currently {$submission->status} and locked from teacher modification.");
        }

        $class_type_id = $section->my_class->class_type_id;

        DB::transaction(function () use ($scoresData, $subject_id, $section, $exam_id, $year, $submission, $teacher_id, $class_type_id) {
            foreach ($scoresData as $student_id => $scores) {
                $ca1 = isset($scores['ca1']) && $scores['ca1'] !== '' ? (int)$scores['ca1'] : null;
                $ca2 = isset($scores['ca2']) && $scores['ca2'] !== '' ? (int)$scores['ca2'] : null;
                $exm = isset($scores['exm']) && $scores['exm'] !== '' ? (int)$scores['exm'] : null;

                $total = null;
                if ($ca1 !== null || $ca2 !== null || $exm !== null) {
                    $total = (int)($ca1 ?? 0) + (int)($ca2 ?? 0) + (int)($exm ?? 0);
                }

                $grade = $total !== null ? $this->findGradeForScore($total, $class_type_id) : null;

                Mark::updateOrCreate(
                    [
                        'student_id' => $student_id,
                        'subject_id' => $subject_id,
                        'my_class_id' => $section->my_class_id,
                        'section_id' => $section->id,
                        'exam_id' => $exam_id,
                        'year' => $year,
                    ],
                    [
                        'tex1' => $ca1,
                        'tex2' => $ca2,
                        'exm' => $exm,
                        'cum' => $total,
                        'cum_ave' => $total,
                        'grade_id' => $grade ? $grade->id : null,
                    ]
                );
            }

            // Calculate subject positions
            $this->calculateSubjectPositions($subject_id, $section->my_class_id, $section->id, $exam_id, $year);

            // Update submission record
            $submission->update([
                'teacher_id' => $teacher_id,
                'status' => $submission->status === 'rejected' ? 'rejected' : 'draft',
            ]);
        });

        return $submission->fresh();
    }

    /**
     * Submit scores package for review
     */
    public function submitScores($subject_id, $section_id, $exam_id, $year, $teacher_id): SubjectScoreSubmission
    {
        $section = Section::findOrFail($section_id);
        $submission = $this->getSubmission($exam_id, $section->my_class_id, $section_id, $subject_id, $year);

        if (!$submission->isEditableByTeacher() && !(auth()->check() && \App\Helpers\Qs::userIsTeamSA())) {
            throw new \Exception("Scores cannot be submitted because their status is {$submission->status}.");
        }

        $this->calculateSubjectPositions($subject_id, $section->my_class_id, $section->id, $exam_id, $year);

        $submission->update([
            'status' => 'submitted',
            'teacher_id' => $teacher_id,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        return $submission->fresh();
    }

    /**
     * Approve submission package
     */
    public function approveSubmission(int $submission_id, int $officer_id): SubjectScoreSubmission
    {
        $submission = SubjectScoreSubmission::findOrFail($submission_id);
        $submission->update([
            'status' => 'approved',
            'reviewed_by' => $officer_id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        return $submission->fresh();
    }

    /**
     * Reject/Return submission package for correction
     */
    public function rejectSubmission(int $submission_id, int $officer_id, ?string $reason = null): SubjectScoreSubmission
    {
        $submission = SubjectScoreSubmission::findOrFail($submission_id);
        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => $officer_id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason ?: 'Returned by Examination Officer for corrections.',
        ]);

        return $submission->fresh();
    }

    /**
     * Toggle lock on a submission
     */
    public function toggleLock(int $submission_id, int $officer_id): SubjectScoreSubmission
    {
        $submission = SubjectScoreSubmission::findOrFail($submission_id);
        $newStatus = $submission->status === 'locked' ? 'approved' : 'locked';

        $submission->update([
            'status' => $newStatus,
            'reviewed_by' => $officer_id,
            'reviewed_at' => now(),
        ]);

        return $submission->fresh();
    }

    /**
     * Dynamically match grade configuration by score and class type
     */
    public function findGradeForScore(float|int $score, ?int $class_type_id = null): ?Grade
    {
        $score = round($score);

        // 1. Check grade configured for specific class type
        if ($class_type_id) {
            $grade = Grade::where('class_type_id', $class_type_id)
                ->where('mark_from', '<=', $score)
                ->where('mark_to', '>=', $score)
                ->first();

            if ($grade) return $grade;
        }

        // 2. Check general grade without class type
        $generalGrade = Grade::whereNull('class_type_id')
            ->where('mark_from', '<=', $score)
            ->where('mark_to', '>=', $score)
            ->first();

        if ($generalGrade) return $generalGrade;

        // 3. Fallback: create dynamic/virtual grade if table is empty
        return null;
    }

    /**
     * Get Grade letter / name with default fallback
     */
    public function getGradeName(float|int|null $score, ?int $class_type_id = null): string
    {
        if ($score === null) return '—';
        $grade = $this->findGradeForScore($score, $class_type_id);
        if ($grade) return $grade->name;

        // Default standard scale fallback if no grades configured in DB
        $s = round($score);
        return match (true) {
            $s >= 70 => 'A',
            $s >= 60 => 'B',
            $s >= 50 => 'C',
            $s >= 45 => 'D',
            $s >= 40 => 'E',
            default => 'F',
        };
    }

    /**
     * Calculate subject positions with standard competition ranking (1, 2, 2, 4)
     */
    public function calculateSubjectPositions($subject_id, $class_id, $section_id, $exam_id, $year): void
    {
        $marks = Mark::where([
            'exam_id' => $exam_id,
            'subject_id' => $subject_id,
            'section_id' => $section_id,
            'year' => $year,
        ])->whereNotNull('cum')->orderBy('cum', 'DESC')->get();

        if ($marks->isEmpty()) return;

        $rank = 1;
        $prevScore = null;
        $sameRankCount = 0;

        foreach ($marks as $index => $mark) {
            if ($prevScore !== null && (int)$mark->cum === (int)$prevScore) {
                // Tie: assign same rank
                $mark->update(['sub_pos' => $rank]);
                $sameRankCount++;
            } else {
                // Advance rank by previous ties
                $rank += $sameRankCount;
                $sameRankCount = 1;
                $prevScore = $mark->cum;
                $mark->update(['sub_pos' => $rank]);
            }
        }
    }

    /**
     * Calculate and persist overall class/section results (Totals, Averages, Positions)
     */
    public function calculateOverallResults($class_id, $section_id, $exam_id, $year): array
    {
        $studentsQuery = StudentRecord::where('session', $year);
        if ($section_id) {
            $studentsQuery->where('section_id', $section_id);
        } else {
            $studentsQuery->where('my_class_id', $class_id);
        }
        $students = $studentsQuery->get();

        $class = MyClass::with('class_type')->find($class_id);
        $class_type_id = $class ? $class->class_type_id : null;

        $studentResults = [];

        foreach ($students as $sr) {
            $marks = Mark::where([
                'student_id' => $sr->user_id,
                'exam_id' => $exam_id,
                'my_class_id' => $sr->my_class_id,
                'year' => $year,
            ])->whereNotNull('cum')->get();

            $total = $marks->sum('cum');
            $count = $marks->count();
            $ave = $count > 0 ? round($total / $count, 2) : 0;
            $grade = $this->findGradeForScore($ave, $class_type_id);

            $exr = ExamRecord::updateOrCreate(
                [
                    'exam_id' => $exam_id,
                    'student_id' => $sr->user_id,
                    'my_class_id' => $sr->my_class_id,
                    'section_id' => $sr->section_id,
                    'year' => $year,
                ],
                [
                    'total' => $total,
                    'ave' => $ave,
                    'grade_id' => $grade ? $grade->id : null,
                ]
            );

            $studentResults[] = [
                'record_id' => $exr->id,
                'student_id' => $sr->user_id,
                'section_id' => $sr->section_id,
                'total' => $total,
                'ave' => $ave,
            ];
        }

        // Calculate section positions and class positions with competition ranking
        $this->rankExamRecords($exam_id, $class_id, $section_id, $year);

        return $studentResults;
    }

    /**
     * Rank exam records with tie support
     */
    protected function rankExamRecords($exam_id, $class_id, $section_id, $year): void
    {
        // 1. Rank within Section
        if ($section_id) {
            $secRecords = ExamRecord::where([
                'exam_id' => $exam_id,
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'year' => $year,
            ])->whereNotNull('total')->orderBy('total', 'DESC')->get();

            $this->applyRanks($secRecords, 'pos');
        }

        // 2. Rank across entire Class
        $classRecords = ExamRecord::where([
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
            'year' => $year,
        ])->whereNotNull('total')->orderBy('total', 'DESC')->get();

        $this->applyRanks($classRecords, 'class_pos');
    }

    protected function applyRanks($records, string $column): void
    {
        if ($records->isEmpty()) return;

        $rank = 1;
        $prevScore = null;
        $sameRankCount = 0;

        foreach ($records as $record) {
            if ($prevScore !== null && (float)$record->total === (float)$prevScore) {
                $record->update([$column => $rank]);
                $sameRankCount++;
            } else {
                $rank += $sameRankCount;
                $sameRankCount = 1;
                $prevScore = $record->total;
                $record->update([$column => $rank]);
            }
        }
    }

    /**
     * Missing Scores Auditor
     * Audits unsubmitted subjects and missing CA1/CA2/Exam scores
     */
    public function getMissingScoresAudit($exam_id, $class_id = null, $year = null): array
    {
        $year = $year ?: Exam::find($exam_id)?->year ?: (new \App\Repositories\MyClassRepo())->getCurrentSession();

        $classesQuery = MyClass::with(['subjects', 'section']);
        if ($class_id) {
            $classesQuery->where('id', $class_id);
        }
        $classes = $classesQuery->get();

        $audit = [];
        $totalSubjectsCount = 0;
        $submittedCount = 0;
        $approvedCount = 0;
        $lockedCount = 0;
        $draftOrMissingCount = 0;
        $incompleteStudentsCount = 0;

        foreach ($classes as $class) {
            $classItem = [
                'class' => $class,
                'sections' => [],
            ];

            foreach ($class->section as $section) {
                $sectionItem = [
                    'section' => $section,
                    'subjects' => [],
                    'missing_students' => [],
                ];

                $students = StudentRecord::where([
                    'section_id' => $section->id,
                    'session' => $year,
                ])->with('user')->get();

                foreach ($class->subjects as $subject) {
                    $totalSubjectsCount++;
                    $submission = SubjectScoreSubmission::where([
                        'exam_id' => $exam_id,
                        'my_class_id' => $class->id,
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'year' => $year,
                    ])->first();

                    $status = $submission ? $submission->status : 'draft';
                    if ($status === 'approved') $approvedCount++;
                    elseif ($status === 'locked') $lockedCount++;
                    elseif ($status === 'submitted') $submittedCount++;
                    else $draftOrMissingCount++;

                    // Check student scores completeness
                    $marks = Mark::where([
                        'exam_id' => $exam_id,
                        'my_class_id' => $class->id,
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'year' => $year,
                    ])->get()->keyBy('student_id');

                    $missingInSubject = [];
                    foreach ($students as $sr) {
                        $mark = $marks->get($sr->user_id);
                        $missing = [];
                        if (!$mark || $mark->tex1 === null) $missing[] = 'CA 1';
                        if (!$mark || $mark->tex2 === null) $missing[] = 'CA 2';
                        if (!$mark || $mark->exm === null) $missing[] = 'Exam';

                        if (!empty($missing)) {
                            $missingInSubject[] = [
                                'student' => $sr->user,
                                'adm_no' => $sr->adm_no,
                                'missing' => $missing,
                            ];
                            $incompleteStudentsCount++;
                        }
                    }

                    $sectionItem['subjects'][] = [
                        'subject' => $subject,
                        'submission' => $submission,
                        'status' => $status,
                        'missing_scores' => $missingInSubject,
                    ];
                }

                $classItem['sections'][] = $sectionItem;
            }

            $audit[] = $classItem;
        }

        return [
            'audit' => $audit,
            'summary' => [
                'total_packages' => $totalSubjectsCount,
                'submitted' => $submittedCount,
                'approved' => $approvedCount,
                'locked' => $lockedCount,
                'draft_or_missing' => $draftOrMissingCount,
                'incomplete_students' => $incompleteStudentsCount,
                'is_ready_for_results' => ($draftOrMissingCount === 0 && $incompleteStudentsCount === 0),
            ],
        ];
    }
}
