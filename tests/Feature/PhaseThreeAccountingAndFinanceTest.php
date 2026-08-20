<?php

namespace Tests\Feature;

use App\Helpers\Mk;
use App\Helpers\Pay;
use App\Helpers\Qs;
use App\Models\ClassType;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\Receipt;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\Tenant;
use App\Repositories\AssessmentScoreRepo;
use App\Repositories\AttendanceRepo;
use App\Repositories\FinancialRepo;
use App\Services\SmsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PhaseThreeAccountingAndFinanceTest extends TestCase
{
    use DatabaseTransactions;

    protected $tenant;
    protected $sessionYear = '2026-2027';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://demo.localhost']);
        $this->withServerVariables([
            'HTTP_HOST' => 'demo.localhost',
            'SERVER_NAME' => 'demo.localhost',
        ]);
        $this->tenant = Tenant::first();
        if ($this->tenant) {
            tenancy()->initialize($this->tenant);
        }
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            tenancy()->end();
        }
        parent::tearDown();
    }

    /**
     * Helper to create an isolated test class, section, teacher, and student
     */
    protected function createTestEnvironment(string $suffix = '')
    {
        $suffix = $suffix ?: uniqid();
        $classType = ClassType::first() ?: ClassType::create(['name' => 'JSS ' . $suffix, 'code' => 'J' . substr($suffix, 0, 2), 'subjects_considered' => 10]);

        $uniqueName = 'JSS 1 ' . $suffix . '_' . rand(1000, 9999);
        $class = MyClass::create([
            'name' => $uniqueName,
            'class_type_id' => $classType->id,
        ]);

        $section = Section::create([
            'name' => 'A_' . rand(100, 999),
            'my_class_id' => $class->id,
            'active' => 1,
        ]);

        $randUid = uniqid();

        // Parent User
        $parentUser = User::create([
            'name' => 'Alhaji Parent ' . $suffix,
            'username' => 'par_' . $randUid,
            'email' => 'par_' . $randUid . '@example.com',
            'password' => bcrypt('secret'),
            'user_type' => 'parent',
            'religion' => 'Islam',
            'phone' => '08031234567',
            'code' => 'P' . substr(md5(uniqid(mt_rand(), true)), 0, 8),
            'password_updated_at' => now(),
        ]);

        // Student User
        $studentUser = User::create([
            'name' => 'Ibrahim Student ' . $suffix,
            'username' => 'stu_' . $randUid,
            'email' => 'stu_' . $randUid . '@example.com',
            'password' => bcrypt('secret'),
            'user_type' => 'student',
            'religion' => 'Islam',
            'gender' => 'Male',
            'code' => 'S' . substr(md5(uniqid(mt_rand(), true)), 0, 8),
            'password_updated_at' => now(),
        ]);

        $studentRecord = StudentRecord::create([
            'user_id' => $studentUser->id,
            'my_class_id' => $class->id,
            'section_id' => $section->id,
            'my_parent_id' => $parentUser->id,
            'adm_no' => 'ADM/' . strtoupper($randUid),
            'session' => $this->sessionYear,
            'ps_name' => 'Primary Academy',
            'p_status' => 'Together',
        ]);

        // Accountant User
        $accountantUser = User::create([
            'name' => 'Bursar Danladi ' . $suffix,
            'username' => 'acc_' . $randUid,
            'email' => 'acc_' . $randUid . '@example.com',
            'password' => bcrypt('secret'),
            'user_type' => 'accountant',
            'religion' => 'Islam',
            'code' => 'A' . substr(md5(uniqid(mt_rand(), true)), 0, 8),
            'password_updated_at' => now(),
        ]);

        // Super Admin User
        $superAdmin = User::create([
            'name' => 'Principal Admin ' . $suffix,
            'username' => 'adm_' . $randUid,
            'email' => 'adm_' . $randUid . '@example.com',
            'password' => bcrypt('secret'),
            'user_type' => 'super_admin',
            'religion' => 'Islam',
            'code' => 'SA' . substr(md5(uniqid(mt_rand(), true)), 0, 8),
            'password_updated_at' => now(),
        ]);

        // Teacher User
        $teacherUser = User::create([
            'name' => 'Mr Teacher ' . $suffix,
            'username' => 'tch_' . $randUid,
            'email' => 'tch_' . $randUid . '@example.com',
            'password' => bcrypt('secret'),
            'user_type' => 'teacher',
            'religion' => 'Islam',
            'code' => 'T' . substr(md5(uniqid(mt_rand(), true)), 0, 8),
            'password_updated_at' => now(),
        ]);

        return compact(
            'class',
            'section',
            'parentUser',
            'studentUser',
            'studentRecord',
            'accountantUser',
            'superAdmin',
            'teacherUser'
        );
    }

    /**
     * 1. Test Invoicing: Class-wide & Individual Invoicing
     */
    public function test_fee_invoicing_class_and_individual()
    {
        $env = $this->createTestEnvironment('inv');
        $financialRepo = app(FinancialRepo::class);

        // Create a fee
        $payment = Payment::create([
            'title' => '1st Term Tuition Fees',
            'amount' => 50000,
            'ref_no' => Pay::genRefCode(),
            'my_class_id' => $env['class']->id,
            'year' => $this->sessionYear,
        ]);

        // Class Invoicing
        $count = $financialRepo->createClassInvoices($env['class']->id, $payment->id, $this->sessionYear);
        $this->assertEquals(1, $count);

        // Check payment record
        $pr = PaymentRecord::where([
            'payment_id' => $payment->id,
            'student_id' => $env['studentUser']->id,
            'year' => $this->sessionYear,
        ])->first();

        $this->assertNotNull($pr);
        $this->assertEquals(0, $pr->amt_paid);
        $this->assertEquals(50000, $pr->balance);
        $this->assertFalse((bool)$pr->paid);

        // Check summary
        $summary = $financialRepo->getStudentFeeSummary($env['studentUser']->id, $this->sessionYear);
        $this->assertEquals(50000, $summary['total_billed']);
        $this->assertEquals(0, $summary['total_paid']);
        $this->assertEquals(50000, $summary['total_balance']);
        $this->assertEquals('not_paid', $summary['status']);
        $this->assertFalse($summary['is_cleared']);
    }

    /**
     * 2. Test Payment Recording: Partial & Full Settlement with Receipts
     */
    public function test_payment_recording_partial_and_full_settlement()
    {
        $env = $this->createTestEnvironment('pay');
        $financialRepo = app(FinancialRepo::class);

        $payment = Payment::create([
            'title' => 'Tuition & Development Fee',
            'amount' => 60000,
            'ref_no' => Pay::genRefCode(),
            'my_class_id' => $env['class']->id,
            'year' => $this->sessionYear,
        ]);

        $financialRepo->createClassInvoices($env['class']->id, $payment->id, $this->sessionYear);
        $pr = PaymentRecord::where(['student_id' => $env['studentUser']->id, 'payment_id' => $payment->id])->first();

        // 1st Installment: Pay 20,000 (Partial)
        $receipt1 = $financialRepo->recordPayment($pr->id, 20000, 'Cash', 'TEL-' . uniqid(), $this->sessionYear);
        $pr->refresh();

        $this->assertEquals(20000, $pr->amt_paid);
        $this->assertEquals(40000, $pr->balance);
        $this->assertFalse((bool)$pr->paid);
        $this->assertEquals(20000, $receipt1->amt_paid);
        $this->assertEquals(40000, $receipt1->balance);

        $summary1 = $financialRepo->getStudentFeeSummary($env['studentUser']->id, $this->sessionYear);
        $this->assertEquals('partially_paid', $summary1['status']);
        $this->assertFalse($summary1['is_cleared']);

        // 2nd Installment: Pay 40,000 (Full Settlement)
        $receipt2 = $financialRepo->recordPayment($pr->id, 40000, 'Bank Transfer', 'POS-' . uniqid(), $this->sessionYear);
        $pr->refresh();

        $this->assertEquals(60000, $pr->amt_paid);
        $this->assertEquals(0, $pr->balance);
        $this->assertTrue((bool)$pr->paid);
        $this->assertEquals(40000, $receipt2->amt_paid);
        $this->assertEquals(0, $receipt2->balance);

        $summary2 = $financialRepo->getStudentFeeSummary($env['studentUser']->id, $this->sessionYear);
        $this->assertEquals('paid', $summary2['status']);
        $this->assertTrue($summary2['is_cleared']);
    }

    /**
     * 3. Test Report Card Financial Lock & Automatic Unlocking
     */
    public function test_report_card_locked_when_unpaid_and_unlocked_when_paid()
    {
        $env = $this->createTestEnvironment('lock');
        $financialRepo = app(FinancialRepo::class);

        $exam = Exam::create([
            'name' => '1st Term Examination ' . uniqid(),
            'term' => 1,
            'category_id' => 1,
            'class_type_id' => $env['class']->class_type_id,
            'exam_student_position_by_value' => 'total',
            'year' => $this->sessionYear,
        ]);

        $payment = Payment::create([
            'title' => 'Mandatory School Fees',
            'amount' => 45000,
            'ref_no' => Pay::genRefCode(),
            'my_class_id' => $env['class']->id,
            'year' => $this->sessionYear,
        ]);

        $financialRepo->createClassInvoices($env['class']->id, $payment->id, $this->sessionYear);

        // Act as teacher/exam officer viewing report card while fees are unpaid
        $response = $this->actingAs($env['teacherUser'])
            ->get("http://demo.localhost/exam-officer/report-card/{$env['studentUser']->id}/{$exam->id}/{$this->sessionYear}");

        $response->assertStatus(200);
        // Expect locked banner and warning
        $response->assertSee('Report Card Locked — School Fees Not Paid');
        $response->assertSee('₦45,000');

        // Now accountant records payment
        $pr = PaymentRecord::where(['student_id' => $env['studentUser']->id, 'payment_id' => $payment->id])->first();
        $financialRepo->recordPayment($pr->id, 45000, 'Cash', 'REC-CLEAR', $this->sessionYear);

        // Verify report card is now automatically unlocked
        $responseAfterPay = $this->actingAs($env['teacherUser'])
            ->get("http://demo.localhost/exam-officer/report-card/{$env['studentUser']->id}/{$exam->id}/{$this->sessionYear}");

        $responseAfterPay->assertStatus(200);
        $responseAfterPay->assertDontSee('Report Card Locked — School Fees Not Paid');
        $responseAfterPay->assertSee('STUDENT ACADEMIC PERFORMANCE REPORT');
    }

    /**
     * 4. Test Financial Analytics Aggregations
     */
    public function test_financial_analytics_aggregations()
    {
        $env = $this->createTestEnvironment('anl');
        $financialRepo = app(FinancialRepo::class);

        $payment = Payment::create([
            'title' => 'General Session Fees',
            'amount' => 30000,
            'ref_no' => Pay::genRefCode(),
            'my_class_id' => $env['class']->id,
            'year' => $this->sessionYear,
        ]);

        $financialRepo->createClassInvoices($env['class']->id, $payment->id, $this->sessionYear);

        $analytics = $financialRepo->getFinancialAnalytics($this->sessionYear, $env['class']->id);

        $this->assertEquals(30000, $analytics['total_billed']);
        $this->assertEquals(0, $analytics['total_collected']);
        $this->assertEquals(30000, $analytics['total_outstanding']);
        $this->assertEquals(1, $analytics['count_not_paid']);
        $this->assertNotEmpty($analytics['class_breakdown']);
    }

    /**
     * 5. Test Bulk SMS Reminders with Auto-Extracted Guardian Phone
     */
    public function test_bulk_sms_reminders_auto_extracts_guardian_phone()
    {
        $env = $this->createTestEnvironment('sms');
        $financialRepo = app(FinancialRepo::class);
        $smsService = app(SmsService::class);

        $payment = Payment::create([
            'title' => 'Tuition Fees',
            'amount' => 40000,
            'ref_no' => Pay::genRefCode(),
            'my_class_id' => $env['class']->id,
            'year' => $this->sessionYear,
        ]);

        $financialRepo->createClassInvoices($env['class']->id, $payment->id, $this->sessionYear);

        // Fetch debtors list
        $debtors = $financialRepo->getDebtorsList($this->sessionYear, $env['class']->id);
        $this->assertCount(1, $debtors);

        $firstDebtor = $debtors->first();
        $this->assertEquals('08031234567', $firstDebtor['guardian_phone']);
        $this->assertEquals($env['parentUser']->name, $firstDebtor['guardian_name']);

        // Test dispatch via SmsService
        $recipients = [
            [
                'phone' => $firstDebtor['guardian_phone'],
                'name' => $firstDebtor['guardian_name'],
                'student_name' => $firstDebtor['student']->name,
                'balance' => $firstDebtor['total_balance'],
            ]
        ];

        $template = "Dear {name}, ward {student} has unpaid fees of N{balance} at {school}.";
        $result = $smsService->sendBulkReminders($recipients, $template, 'DIGISKUL');

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['sent_count']);
        $this->assertEquals(0, $result['failed_count']);
    }

    /**
     * 6. Test Security: Role-Based Authorization
     */
    public function test_security_role_isolation()
    {
        $env = $this->createTestEnvironment('sec');

        $subject = Subject::create([
            'name' => 'Sec Subject ' . uniqid(),
            'slug' => 'sec-' . uniqid(),
            'my_class_id' => $env['class']->id,
            'teacher_id' => $env['teacherUser']->id,
        ]);

        $exam = Exam::create([
            'name' => 'Sec Exam ' . uniqid(),
            'term' => 1,
            'category_id' => 1,
            'class_type_id' => $env['class']->class_type_id,
            'exam_student_position_by_value' => 'total',
            'year' => $this->sessionYear,
        ]);

        // Accountant cannot access teacher score submit
        $response1 = $this->actingAs($env['accountantUser'])
            ->post("http://demo.localhost/teacher/my-subjects/{$subject->id}/{$env['section']->id}/scores/submit", [
                'exam_id' => $exam->id,
            ]);
        $this->assertTrue(in_array($response1->status(), [403, 302]));

        // Accountant can access accountant dashboard
        $response2 = $this->actingAs($env['accountantUser'])
            ->get("http://demo.localhost/accountant");
        $response2->assertStatus(200);

        // Regular teacher cannot access accountant dashboard
        $response3 = $this->actingAs($env['teacherUser'])
            ->get("http://demo.localhost/accountant");
        $this->assertTrue(in_array($response3->status(), [403, 302]));
    }

    /**
     * 7. Full End-to-End Workflow: Phase 1 -> Phase 2 -> Phase 3 Integrated
     */
    public function test_complete_end_to_end_workflow()
    {
        $env = $this->createTestEnvironment('e2e');
        $financialRepo = app(FinancialRepo::class);
        $scoreRepo = app(AssessmentScoreRepo::class);
        $attendanceRepo = app(AttendanceRepo::class);

        // Step A: Phase 1 — Subject configuration & Attendance
        $subject = Subject::create([
            'name' => 'Mathematics ' . uniqid(),
            'slug' => 'math-' . uniqid(),
            'my_class_id' => $env['class']->id,
            'teacher_id' => $env['teacherUser']->id,
        ]);

        $attendanceRepo->markAttendanceForDate(
            $env['section']->id,
            Carbon::now()->format('Y-m-d'),
            [$env['studentRecord']->id => 'present'],
            $env['teacherUser']->id,
            $this->sessionYear
        );

        // Step B: Phase 2 — Exam and Scores
        $exam = Exam::create([
            'name' => 'Annual Promotion Exam ' . uniqid(),
            'term' => 3,
            'category_id' => 1,
            'class_type_id' => $env['class']->class_type_id,
            'exam_student_position_by_value' => 'total',
            'year' => $this->sessionYear,
        ]);

        Grade::firstOrCreate([
            'name' => 'A',
            'class_type_id' => $env['class']->class_type_id,
        ], [
            'mark_from' => 70,
            'mark_to' => 100,
            'remark' => 'Excellent',
            'point' => 5,
            'credit' => 1,
        ]);

        $scoreRepo->saveDraftScores(
            $subject->id,
            $env['section']->id,
            $exam->id,
            $this->sessionYear,
            [$env['studentUser']->id => ['ca1' => 18, 'ca2' => 19, 'exm' => 55]],
            $env['teacherUser']->id
        );

        $submission = $scoreRepo->submitScores(
            $subject->id,
            $env['section']->id,
            $exam->id,
            $this->sessionYear,
            $env['teacherUser']->id
        );

        $scoreRepo->approveSubmission($submission->id, $env['superAdmin']->id);
        $scoreRepo->calculateOverallResults($env['class']->id, $env['section']->id, $exam->id, $this->sessionYear);

        // Step C: Phase 3 — Fee Invoicing
        $payment = Payment::create([
            'title' => 'Promotional Term School Fees',
            'amount' => 50000,
            'ref_no' => Pay::genRefCode(),
            'my_class_id' => $env['class']->id,
            'year' => $this->sessionYear,
        ]);

        $financialRepo->createClassInvoices($env['class']->id, $payment->id, $this->sessionYear);

        // Check report card is locked
        $resLocked = $this->actingAs($env['teacherUser'])
            ->get("http://demo.localhost/exam-officer/report-card/{$env['studentUser']->id}/{$exam->id}/{$this->sessionYear}");
        $resLocked->assertStatus(200);
        $resLocked->assertSee('Report Card Locked — School Fees Not Paid');

        // Step D: Accountant Records Payment
        $pr = PaymentRecord::where(['student_id' => $env['studentUser']->id, 'payment_id' => $payment->id])->first();
        $receipt = $financialRepo->recordPayment($pr->id, 50000, 'Cash', 'RC-FINAL', $this->sessionYear);
        $this->assertNotNull($receipt);

        // Step E: Report Card is Unlocked and shows full academic & attendance report
        $resUnlocked = $this->actingAs($env['teacherUser'])
            ->get("http://demo.localhost/exam-officer/report-card/{$env['studentUser']->id}/{$exam->id}/{$this->sessionYear}");
        $resUnlocked->assertStatus(200);
        $resUnlocked->assertDontSee('Report Card Locked — School Fees Not Paid');
        $resUnlocked->assertSee('STUDENT ACADEMIC PERFORMANCE REPORT');
        $resUnlocked->assertSee('92'); // Total score: 18 + 19 + 55 = 92
        $resUnlocked->assertSee('100%'); // Attendance percentage
    }
}
