<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassTeacherPermission;
use App\Models\ClassType;
use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\SubjectRecord;
use App\Models\SubjectScoreSubmission;
use App\Models\Tenant;
use App\Repositories\AssessmentScoreRepo;
use App\Repositories\AttendanceRepo;
use App\User;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Facades\Tenancy;
use Tests\TestCase;

class PhaseTwoAcademicAssessmentTest extends TestCase
{
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first();
        if ($this->tenant) {
            Tenancy::initialize($this->tenant);
        }
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            Tenancy::end();
        }
        parent::tearDown();
    }

    /**
     * Test 1: Subject Teacher Score Entry (CA1, CA2, Exam) and Automatic Calculations (Total, Dynamic Grades, Ties)
     * Verifies AC 1, 2, 3, 4, 5, 6.
     */
    public function test_subject_teacher_score_entry_and_automatic_calculations()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $session = '2026-2027';

        $uid1 = uniqid();
        // 1. Setup Class, Section, Subject, Exam, and Configurable Grades
        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::firstOrCreate(['name' => 'JSS 1'], ['class_type_id' => $ct->id]);
        $sec = Section::create(['name' => 'S1_' . $uid1, 'my_class_id' => $jss1->id]);
        $math = Subject::create(['name' => 'Mathematics ' . $uid1, 'my_class_id' => $jss1->id, 'slug' => 'MTH' . substr($uid1, 0, 4), 'core' => 1]);

        $exam = Exam::create([
            'name' => 'First Term Examination ' . $uid1,
            'year' => $session,
            'term' => 1,
            'category_id' => 1,
            'class_type_id' => $ct->id,
            'exam_student_position_by_value' => 'total',
        ]);

        // Configure custom grading scale
        Grade::firstOrCreate(['class_type_id' => $ct->id, 'name' => 'A'], ['mark_from' => 70, 'mark_to' => 100, 'remark' => 'Excellent', 'point' => 5, 'credit' => 1]);
        Grade::firstOrCreate(['class_type_id' => $ct->id, 'name' => 'B'], ['mark_from' => 60, 'mark_to' => 69, 'remark' => 'Very Good', 'point' => 4, 'credit' => 1]);
        Grade::firstOrCreate(['class_type_id' => $ct->id, 'name' => 'C'], ['mark_from' => 50, 'mark_to' => 59, 'remark' => 'Good', 'point' => 3, 'credit' => 1]);
        Grade::firstOrCreate(['class_type_id' => $ct->id, 'name' => 'F'], ['mark_from' => 0, 'mark_to' => 49, 'remark' => 'Fail', 'point' => 0, 'credit' => 0]);

        // 2. Create Subject Teacher and assign to Mathematics
        $teacher = User::create([
            'name' => 'MR. OKECHUKWU MATH',
            'username' => 'tmath_' . $uid1,
            'email' => 'tmath_' . $uid1 . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Christianity',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'TM' . substr(md5($uid1), 0, 8),
        ]);

        SubjectRecord::create([
            'subject_id' => $math->id,
            'section_id' => $sec->id,
            'teacher_id' => $teacher->id,
        ]);

        // 3. Create 3 Students in Section
        $students = [];
        $names = ['AMINA SANI', 'BASHIR BELLO', 'CHIDINMA OKAFOR'];
        foreach ($names as $idx => $name) {
            $user = User::create([
                'name' => $name,
                'username' => 'ST/' . $uid1 . '_' . $idx,
                'email' => 'st_' . $uid1 . '_' . $idx . '@test.com',
                'user_type' => 'student',
                'religion' => 'Islam',
                'gender' => $idx === 1 ? 'Male' : 'Female',
                'password' => Hash::make('password'),
                'password_updated_at' => now(),
                'code' => 'S' . substr(md5($uid1 . $idx), 0, 8),
            ]);

            $sr = StudentRecord::create([
                'user_id' => $user->id,
                'my_class_id' => $jss1->id,
                'section_id' => $sec->id,
                'session' => $session,
                'adm_no' => $user->username,
                'ps_name' => 'Primary Academy',
                'p_status' => 'Together',
            ]);

            $students[] = ['user' => $user, 'sr' => $sr];
        }

        // 4. Save Draft Scores:
        // Student 0: CA1=18, CA2=17, Exam=50 -> Total=85 (Grade A)
        // Student 1: CA1=15, CA2=15, Exam=40 -> Total=70 (Grade A) [Tie with Student 2]
        // Student 2: CA1=16, CA2=14, Exam=40 -> Total=70 (Grade A) [Tie with Student 1]
        $scoreRepo = new AssessmentScoreRepo();
        $scoresPayload = [
            $students[0]['user']->id => ['ca1' => 18, 'ca2' => 17, 'exm' => 50],
            $students[1]['user']->id => ['ca1' => 15, 'ca2' => 15, 'exm' => 40],
            $students[2]['user']->id => ['ca1' => 16, 'ca2' => 14, 'exm' => 40],
        ];

        $sub = $scoreRepo->saveDraftScores($math->id, $sec->id, $exam->id, $session, $scoresPayload, $teacher->id);

        $this->assertEquals('draft', $sub->status);
        $this->assertEquals($teacher->id, $sub->teacher_id);

        // 5. Verify Totals, Dynamic Grades, and Positions
        $m0 = Mark::where(['student_id' => $students[0]['user']->id, 'subject_id' => $math->id, 'exam_id' => $exam->id])->first();
        $m1 = Mark::where(['student_id' => $students[1]['user']->id, 'subject_id' => $math->id, 'exam_id' => $exam->id])->first();
        $m2 = Mark::where(['student_id' => $students[2]['user']->id, 'subject_id' => $math->id, 'exam_id' => $exam->id])->first();

        $this->assertEquals(85, $m0->cum, 'Total must equal CA1 + CA2 + Exam (18+17+50 = 85)');
        $this->assertEquals(70, $m1->cum, 'Total must equal CA1 + CA2 + Exam (15+15+40 = 70)');
        $this->assertEquals(70, $m2->cum, 'Total must equal CA1 + CA2 + Exam (16+14+40 = 70)');

        $this->assertEquals('A', $m0->grade->name);
        $this->assertEquals('A', $m1->grade->name);
        $this->assertEquals('A', $m2->grade->name);

        // Subject Ranking & Tie Handling: 85 -> 1st, 70 -> 2nd, 70 -> 2nd
        $this->assertEquals(1, $m0->sub_pos, 'Student 0 must be 1st');
        $this->assertEquals(2, $m1->sub_pos, 'Student 1 must be tied for 2nd');
        $this->assertEquals(2, $m2->sub_pos, 'Student 2 must be tied for 2nd');
    }

    /**
     * Test 2: Submission Lifecycle (Draft -> Submitted -> Approved/Locked & Rejected/Returned)
     * Verifies AC 8, 9, 10, 11.
     */
    public function test_score_submission_workflow_lifecycle()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $session = '2026-2027';

        $uid = uniqid();
        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::firstOrCreate(['name' => 'JSS 1'], ['class_type_id' => $ct->id]);
        $sec = Section::create(['name' => 'S2_' . $uid, 'my_class_id' => $jss1->id]);
        $eng = Subject::create(['name' => 'English Studies ' . $uid, 'my_class_id' => $jss1->id, 'slug' => 'ENG' . substr($uid, 0, 4), 'core' => 1]);

        $exam = Exam::create([
            'name' => 'First Term Examination ' . $uid,
            'year' => $session,
            'term' => 1,
            'category_id' => 1,
            'class_type_id' => $ct->id,
            'exam_student_position_by_value' => 'total',
        ]);

        $teacher = User::create([
            'name' => 'MR. ENGLISH TEACHER',
            'username' => 'teng_' . $uid,
            'email' => 'teng_' . $uid . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Christianity',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'TE' . substr(md5($uid), 0, 8),
        ]);

        $examOfficer = User::create([
            'name' => 'MRS. EXAM OFFICER',
            'username' => 'eo_' . $uid,
            'email' => 'eo_' . $uid . '@test.com',
            'user_type' => 'admin',
            'religion' => 'Islam',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'EO' . substr(md5($uid . 'eo'), 0, 8),
        ]);

        $scoreRepo = new AssessmentScoreRepo();

        // 1. Initial State: Draft
        $submission = $scoreRepo->getSubmission($exam->id, $jss1->id, $sec->id, $eng->id, $session);
        $this->assertTrue($submission->isDraft());
        $this->assertTrue($submission->isEditableByTeacher());

        // 2. Teacher Submits Scores
        $submitted = $scoreRepo->submitScores($eng->id, $sec->id, $exam->id, $session, $teacher->id);
        $this->assertTrue($submitted->isSubmitted());
        $this->assertFalse($submitted->isEditableByTeacher());
        $this->assertNotNull($submitted->submitted_at);

        // 3. Exam Officer Rejects / Returns for correction
        $rejected = $scoreRepo->rejectSubmission($submitted->id, $examOfficer->id, 'Please double check CA 1 for student 3.');
        $this->assertTrue($rejected->isRejected());
        $this->assertTrue($rejected->isEditableByTeacher(), 'Rejected package must be editable by teacher for corrections');
        $this->assertEquals('Please double check CA 1 for student 3.', $rejected->rejection_reason);

        // 4. Teacher re-submits
        $reSubmitted = $scoreRepo->submitScores($eng->id, $sec->id, $exam->id, $session, $teacher->id);
        $this->assertTrue($reSubmitted->isSubmitted());

        // 5. Exam Officer Approves
        $approved = $scoreRepo->approveSubmission($reSubmitted->id, $examOfficer->id);
        $this->assertTrue($approved->isApproved());
        $this->assertFalse($approved->isEditableByTeacher(), 'Approved package must be locked against teacher edits');

        // 6. Exam Officer Locks
        $locked = $scoreRepo->toggleLock($approved->id, $examOfficer->id);
        $this->assertTrue($locked->isLocked());
    }

    /**
     * Test 3: Missing Scores Auditor & Overall Results Calculation with Rankings
     * Verifies AC 7, 9, 12, 13, 14.
     */
    public function test_missing_scores_audit_and_overall_results_calculation()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $session = '2026-2027';

        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::firstOrCreate(['name' => 'JSS 1'], ['class_type_id' => $ct->id]);
        $sec = Section::create(['name' => 'S3_' . rand(100, 999), 'my_class_id' => $jss1->id]);

        $math = Subject::create(['name' => 'Mathematics ' . rand(100, 999), 'my_class_id' => $jss1->id, 'slug' => 'MTH' . rand(10, 99), 'core' => 1]);
        $eng = Subject::create(['name' => 'English Studies ' . rand(100, 999), 'my_class_id' => $jss1->id, 'slug' => 'ENG' . rand(10, 99), 'core' => 1]);

        $exam = Exam::create([
            'name' => 'First Term Examination ' . rand(100, 999),
            'year' => $session,
            'term' => 1,
            'category_id' => 1,
            'class_type_id' => $ct->id,
            'exam_student_position_by_value' => 'total',
        ]);

        $uid3 = uniqid();
        $stUser = User::create([
            'name' => 'ZAINAB DANLADI',
            'username' => 'ST/' . $uid3,
            'email' => 'zainab_' . $uid3 . '@test.com',
            'user_type' => 'student',
            'religion' => 'Islam',
            'gender' => 'Female',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'STZ' . substr(md5($uid3), 0, 8),
        ]);

        $sr = StudentRecord::create([
            'user_id' => $stUser->id,
            'my_class_id' => $jss1->id,
            'section_id' => $sec->id,
            'session' => $session,
            'adm_no' => $stUser->username,
            'ps_name' => 'Central Primary',
            'p_status' => 'Together',
        ]);

        $scoreRepo = new AssessmentScoreRepo();

        // 1. Audit Missing Scores when Math is recorded but English is missing
        $scoreRepo->saveDraftScores($math->id, $sec->id, $exam->id, $session, [
            $stUser->id => ['ca1' => 20, 'ca2' => 20, 'exm' => 50], // Math Total = 90
        ], 1);

        $audit = $scoreRepo->getMissingScoresAudit($exam->id, $jss1->id, $session);
        $this->assertGreaterThan(0, $audit['summary']['draft_or_missing']);
        $this->assertFalse($audit['summary']['is_ready_for_results']);

        // 2. Complete English score
        $scoreRepo->saveDraftScores($eng->id, $sec->id, $exam->id, $session, [
            $stUser->id => ['ca1' => 15, 'ca2' => 15, 'exm' => 50], // English Total = 80
        ], 1);

        // 3. Calculate Overall Results for the Class
        $results = $scoreRepo->calculateOverallResults($jss1->id, $sec->id, $exam->id, $session);

        $this->assertNotEmpty($results);
        $exr = ExamRecord::where(['student_id' => $stUser->id, 'exam_id' => $exam->id])->first();

        $this->assertEquals(170, $exr->total, 'Overall total must equal Math(90) + English(80) = 170');
        $this->assertEquals(85.0, $exr->ave, 'Overall average must equal 170 / 2 = 85.0%');
        $this->assertEquals(1, $exr->pos, 'Section position must be 1st');
        $this->assertEquals(1, $exr->class_pos, 'Class position must be 1st');
    }

    /**
     * Test 4: Report Card Generation with Integrated Phase 1 Attendance
     * Verifies AC 14, 15.
     */
    public function test_report_card_generation_with_phase1_attendance()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $session = '2026-2027';

        $uid4 = uniqid();
        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::firstOrCreate(['name' => 'JSS 1'], ['class_type_id' => $ct->id]);
        $sec = Section::create(['name' => 'S4_' . $uid4, 'my_class_id' => $jss1->id]);
        $math = Subject::create(['name' => 'Mathematics ' . $uid4, 'my_class_id' => $jss1->id, 'slug' => 'MTH' . substr($uid4, 0, 4), 'core' => 1]);

        $exam = Exam::create([
            'name' => 'First Term Examination ' . $uid4,
            'year' => $session,
            'term' => 1,
            'category_id' => 1,
            'class_type_id' => $ct->id,
            'exam_student_position_by_value' => 'total',
        ]);

        $stUser = User::create([
            'name' => 'FATIMA BELLO',
            'username' => 'ST/' . $uid4,
            'email' => 'fatima_' . $uid4 . '@test.com',
            'user_type' => 'student',
            'religion' => 'Islam',
            'gender' => 'Female',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'STF' . substr(md5($uid4), 0, 8),
        ]);

        $sr = StudentRecord::create([
            'user_id' => $stUser->id,
            'my_class_id' => $jss1->id,
            'section_id' => $sec->id,
            'session' => $session,
            'adm_no' => $stUser->username,
            'ps_name' => 'Primary',
            'p_status' => 'Together',
        ]);

        // Phase 1 Attendance: Mark 4 present, 1 absent out of 5 days (80% rate)
        $attRepo = new AttendanceRepo();
        $dates = ['2026-11-01', '2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05'];
        $statuses = ['present', 'present', 'present', 'absent', 'present'];
        foreach ($dates as $i => $d) {
            $attRepo->markAttendanceForDate($sec->id, $d, [$sr->id => $statuses[$i]], 1, $session);
        }

        // Phase 2 Score: Math = 85
        $scoreRepo = new AssessmentScoreRepo();
        $scoreRepo->saveDraftScores($math->id, $sec->id, $exam->id, $session, [
            $stUser->id => ['ca1' => 18, 'ca2' => 17, 'exm' => 50],
        ], 1);

        $domain = $this->tenant->domains->first()->domain ?? 'demo.localhost';
        $this->withServerVariables(['HTTP_HOST' => $domain]);

        $admin = User::create([
            'name' => 'EXAM CONTROLLER',
            'username' => 'adm_' . $uid4,
            'email' => 'adm_' . $uid4 . '@test.com',
            'user_type' => 'admin',
            'religion' => 'Islam',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'ADM' . substr(md5($uid4 . 'adm'), 0, 8),
        ]);

        $this->actingAs($admin);

        // HTTP GET: View Report Card
        $response = $this->get("http://{$domain}/exam-officer/report-card/{$stUser->id}/{$exam->id}/{$session}");
        $response->assertStatus(200);

        // Verify HTML contents contain both Assessment Scores and Phase 1 Attendance
        $response->assertSee('FATIMA BELLO');
        $response->assertSee('85'); // Math total
        $response->assertSee('5 Days'); // Total school days
        $response->assertSee('4 Days'); // Days present
        $response->assertSee('1 Days'); // Days absent
        $response->assertSee('80%'); // Attendance percentage
    }

    /**
     * Test 5: Class Teacher Academic Review Scoping & Protection against Overwriting
     * Verifies AC 11, 15.
     */
    public function test_class_teacher_cannot_overwrite_subject_scores_without_permission()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $session = '2026-2027';

        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::firstOrCreate(['name' => 'JSS 1'], ['class_type_id' => $ct->id]);
        $sec = Section::create(['name' => 'S5_' . rand(100, 999), 'my_class_id' => $jss1->id]);
        $math = Subject::firstOrCreate(
            ['name' => 'Mathematics', 'my_class_id' => $jss1->id],
            ['slug' => 'MTH', 'core' => 1]
        );

        $exam = Exam::firstOrCreate(
            ['name' => 'First Term Examination', 'year' => $session],
            [
                'term' => 1,
                'category_id' => 1,
                'class_type_id' => $ct->id,
                'exam_student_position_by_value' => 'total',
            ]
        );

        // Class Teacher
        $uid5 = uniqid();
        $classTeacher = User::create([
            'name' => 'CLASS TEACHER CT',
            'username' => 'ct_' . $uid5,
            'email' => 'ct_' . $uid5 . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Islam',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'CT' . substr(md5($uid5), 0, 8),
        ]);
        $sec->update(['teacher_id' => $classTeacher->id]);

        // Subject Teacher for Math
        $subjectTeacher = User::create([
            'name' => 'MATH SPECIALIST',
            'username' => 'ms_' . $uid5,
            'email' => 'ms_' . $uid5 . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Christianity',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'MS' . substr(md5($uid5 . 'ms'), 0, 8),
        ]);

        SubjectRecord::create([
            'subject_id' => $math->id,
            'section_id' => $sec->id,
            'teacher_id' => $subjectTeacher->id,
        ]);

        $domain = $this->tenant->domains->first()->domain ?? 'demo.localhost';
        $this->withServerVariables(['HTTP_HOST' => $domain]);

        // Class teacher can access Academic Review (Read-only)
        $this->actingAs($classTeacher);
        $res1 = $this->get("http://{$domain}/teacher/my-class/{$sec->id}/academic-review/{$exam->id}");
        $res1->assertStatus(200);
        $res1->assertSee('Read-Only Review');

        // Class teacher cannot post score modifications to Subject Teacher's scores
        $res2 = $this->post("http://{$domain}/teacher/my-subjects/{$math->id}/{$sec->id}/scores/save", [
            'exam_id' => $exam->id,
            'scores' => [1 => ['ca1' => 20]],
        ]);
        $res2->assertRedirect("http://{$domain}/dashboard"); // Denied
    }
}
