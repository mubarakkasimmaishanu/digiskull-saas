<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassType;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\SubjectRecord;
use App\Models\Tenant;
use App\Repositories\AttendanceRepo;
use App\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseOneFoundationTest extends TestCase
{
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first();
        if ($this->tenant) {
            tenancy()->initialize($this->tenant);
        }
    }

    public function test_classes_and_class_level_subjects_configuration()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        // 1. Create Class Type (e.g. JSS)
        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);

        // 2. Create Classes: JSS 1, JSS 2
        $jss1 = MyClass::create(['name' => 'JSS 1 ' . rand(100, 999), 'class_type_id' => $ct->id]);
        $jss2 = MyClass::create(['name' => 'JSS 2 ' . rand(100, 999), 'class_type_id' => $ct->id]);

        $this->assertNotNull($jss1->id);
        $this->assertNotNull($jss2->id);

        // 3. Configure Class-Level Subjects for JSS 1
        $math_jss1 = Subject::create([
            'name' => 'Mathematics',
            'my_class_id' => $jss1->id,
            'slug' => 'MTH-' . rand(100, 999),
            'core' => 1
        ]);
        $eng_jss1 = Subject::create([
            'name' => 'English Studies',
            'my_class_id' => $jss1->id,
            'slug' => 'ENG-' . rand(100, 999),
            'core' => 1
        ]);
        $sci_jss1 = Subject::create([
            'name' => 'Basic Science',
            'my_class_id' => $jss1->id,
            'slug' => 'BSC-' . rand(100, 999),
            'core' => 1
        ]);

        // Configure Subjects for JSS 2
        $math_jss2 = Subject::create([
            'name' => 'Mathematics',
            'my_class_id' => $jss2->id,
            'slug' => 'MTH-' . rand(100, 999),
            'core' => 1
        ]);
        $tech_jss2 = Subject::create([
            'name' => 'Basic Technology',
            'my_class_id' => $jss2->id,
            'slug' => 'BTECH-' . rand(100, 999),
            'core' => 1
        ]);

        $this->assertEquals(3, $jss1->subjects()->count());
        $this->assertTrue($jss1->subjects->pluck('name')->contains('Mathematics'));
        $this->assertTrue($jss1->subjects->pluck('name')->contains('English Studies'));
        $this->assertTrue($jss1->subjects->pluck('name')->contains('Basic Science'));
    }

    public function test_student_registration_and_automatic_subject_inheritance()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::create(['name' => 'JSS 1 ' . rand(100, 999), 'class_type_id' => $ct->id]);
        $jss2 = MyClass::create(['name' => 'JSS 2 ' . rand(100, 999), 'class_type_id' => $ct->id]);

        Subject::create(['name' => 'Mathematics', 'my_class_id' => $jss1->id, 'slug' => 'MTH-' . rand(100, 999), 'core' => 1]);
        Subject::create(['name' => 'English Studies', 'my_class_id' => $jss1->id, 'slug' => 'ENG-' . rand(100, 999), 'core' => 1]);
        Subject::create(['name' => 'Basic Technology', 'my_class_id' => $jss2->id, 'slug' => 'BTECH-' . rand(100, 999), 'core' => 1]);

        $sec_a = Section::create([
            'name' => 'A',
            'my_class_id' => $jss1->id,
            'active' => 1,
        ]);

        // Register Student User
        $randCode = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $user = User::create([
            'name' => 'AMINA SANI',
            'username' => 'TEST/ST/' . $randCode,
            'email' => 'amina_' . $randCode . '@test.com',
            'user_type' => 'student',
            'gender' => 'Female',
            'religion' => 'Islam',
            'dob' => '2010-05-12',
            'password' => Hash::make('student'),
            'code' => 'C_' . $randCode,
        ]);

        $sr = StudentRecord::create([
            'user_id' => $user->id,
            'my_class_id' => $jss1->id,
            'section_id' => $sec_a->id,
            'session' => '2026-2027',
            'adm_no' => $user->username,
            'ps_name' => 'Primary Academy',
            'p_status' => 'Living Together',
        ]);

        // Verify: Student automatically inherits subjects through class relationship
        $inherited_subjects = $sr->my_class->subjects;
        $this->assertTrue($inherited_subjects->pluck('name')->contains('Mathematics'));
        $this->assertTrue($inherited_subjects->pluck('name')->contains('English Studies'));

        // Changing Student from JSS 1 to JSS 2
        $sec_jss2 = Section::create([
            'name' => 'A',
            'my_class_id' => $jss2->id,
            'active' => 1,
        ]);

        $sr->update([
            'my_class_id' => $jss2->id,
            'section_id' => $sec_jss2->id,
        ]);

        $sr->refresh();
        $new_inherited_subjects = $sr->my_class->subjects;
        $this->assertTrue($new_inherited_subjects->pluck('name')->contains('Basic Technology'));
        $this->assertFalse($new_inherited_subjects->pluck('name')->contains('Mathematics'));
    }

    public function test_attendance_marking_and_automatic_statistics()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::create(['name' => 'JSS 1 ' . rand(100, 999), 'class_type_id' => $ct->id]);
        $section = Section::create(['name' => 'ATT_A_' . rand(100, 999), 'my_class_id' => $jss1->id]);

        $teacher = User::create([
            'name' => 'MR. MUSA IBRAHIM',
            'username' => 'musa_' . uniqid(),
            'email' => 'musa_' . uniqid() . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Islam',
            'password' => Hash::make('teacher'),
            'password_updated_at' => now(),
            'code' => 'TCHR' . rand(1000, 9999),
        ]);

        $section->update(['teacher_id' => $teacher->id]);

        $student_user = User::create([
            'name' => 'KABIRU ALIYU',
            'username' => 'ST/' . uniqid(),
            'email' => 'kabiru_' . uniqid() . '@test.com',
            'user_type' => 'student',
            'religion' => 'Islam',
            'password' => Hash::make('student'),
            'password_updated_at' => now(),
            'code' => 'STU' . rand(1000, 9999),
        ]);

        $sr = StudentRecord::create([
            'user_id' => $student_user->id,
            'my_class_id' => $jss1->id,
            'section_id' => $section->id,
            'session' => '2026-2027',
            'adm_no' => $student_user->username,
            'ps_name' => 'Academy',
            'p_status' => 'Together',
        ]);

        $attRepo = new AttendanceRepo();

        // Mark 5 days of attendance: 4 Present, 1 Absent
        $dates = ['2026-09-01', '2026-09-02', '2026-09-03', '2026-09-04', '2026-09-05'];
        $statuses = ['present', 'present', 'present', 'absent', 'present'];

        foreach ($dates as $idx => $date) {
            $attRepo->markAttendanceForDate(
                $section->id,
                $date,
                [$sr->id => $statuses[$idx]],
                $teacher->id,
                '2026-2027'
            );
        }

        // Verify calculations
        $stats = $attRepo->getStudentAttendanceStats($sr->id, '2026-2027');

        $this->assertEquals(5, $stats['total_days'], 'Total school days opened must be 5');
        $this->assertEquals(4, $stats['present_days'], 'Days present must be 4');
        $this->assertEquals(1, $stats['absent_days'], 'Days absent must be 1');
        $this->assertEquals(80.0, $stats['percentage'], 'Percentage must be (4/5)*100 = 80%');

        // Verify summary
        $summary = $attRepo->getSectionAttendanceSummary($section->id, '2026-2027');
        $this->assertEquals(5, $summary['total_school_days']);
        $this->assertNotEmpty($summary['students_summary']);
    }

    public function test_teacher_role_based_security_and_scoping()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::create(['name' => 'JSS 1 ' . rand(100, 999), 'class_type_id' => $ct->id]);
        $jss2 = MyClass::create(['name' => 'JSS 2 ' . rand(100, 999), 'class_type_id' => $ct->id]);

        $sec1 = Section::create(['name' => 'A', 'my_class_id' => $jss1->id]);
        $sec2 = Section::create(['name' => 'A', 'my_class_id' => $jss2->id]);

        // Teacher 1 is Class Teacher for JSS 1
        $teacher1 = User::create([
            'name' => 'TEACHER ONE',
            'username' => 't1_' . uniqid(),
            'email' => 't1_' . uniqid() . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Islam',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'T1' . rand(1000, 9999),
        ]);
        $sec1->update(['teacher_id' => $teacher1->id]);

        // Teacher 2 is Class Teacher for JSS 2
        $teacher2 = User::create([
            'name' => 'TEACHER TWO',
            'username' => 't2_' . uniqid(),
            'email' => 't2_' . uniqid() . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Islam',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'T2' . rand(1000, 9999),
        ]);
        $sec2->update(['teacher_id' => $teacher2->id]);

        // Subject Teacher assignment: Teacher 1 teaches Mathematics in JSS 1
        $math = Subject::create(['name' => 'Mathematics', 'my_class_id' => $jss1->id, 'slug' => 'MTH-' . rand(100, 999), 'core' => 1]);
        SubjectRecord::create([
            'subject_id' => $math->id,
            'section_id' => $sec1->id,
            'teacher_id' => $teacher1->id,
        ]);

        $domain = $this->tenant->domains->first()->domain ?? 'demo.localhost';
        $this->withServerVariables(['HTTP_HOST' => $domain]);

        // Verify: Teacher 1 can access JSS 1 class detail
        $this->actingAs($teacher1);
        $response = $this->get("http://{$domain}/teacher/my-class/{$sec1->id}");
        $response->assertStatus(200);

        // Security check: Teacher 1 is denied access to Teacher 2's JSS 2 class detail
        $response2 = $this->get("http://{$domain}/teacher/my-class/{$sec2->id}");
        $response2->assertRedirect("http://{$domain}/dashboard");

        // Verify: Teacher 1 can access assigned subject JSS 1
        $response3 = $this->get("http://{$domain}/teacher/my-subjects/{$math->id}/{$sec1->id}");
        $response3->assertStatus(200);

        // Security check: Teacher 2 is denied access to Teacher 1's subject
        $this->actingAs($teacher2);
        $response4 = $this->get("http://{$domain}/teacher/my-subjects/{$math->id}/{$sec1->id}");
        $response4->assertRedirect("http://{$domain}/dashboard");
    }

    public function test_attendance_http_endpoints_and_role_protection()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('Tenant not initialized.');
        }

        $ct = ClassType::firstOrCreate(['code' => 'J'], ['name' => 'Junior Secondary School', 'subjects_considered' => 9]);
        $jss1 = MyClass::create(['name' => 'JSS 1 ' . rand(100, 999), 'class_type_id' => $ct->id]);
        $sec = Section::create(['name' => 'A', 'my_class_id' => $jss1->id]);

        $teacher = User::create([
            'name' => 'TEACHER ATT TEST',
            'username' => 'att_t_' . uniqid(),
            'email' => 'att_t_' . uniqid() . '@test.com',
            'user_type' => 'teacher',
            'religion' => 'Islam',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'ATT' . rand(1000, 9999),
        ]);
        $sec->update(['teacher_id' => $teacher->id]);

        $st_user = User::create([
            'name' => 'STUDENT ATT TEST',
            'username' => 'att_st_' . uniqid(),
            'email' => 'att_st_' . uniqid() . '@test.com',
            'user_type' => 'student',
            'religion' => 'Islam',
            'password' => Hash::make('password'),
            'password_updated_at' => now(),
            'code' => 'ATTS' . rand(1000, 9999),
        ]);

        $sr = StudentRecord::create([
            'user_id' => $st_user->id,
            'my_class_id' => $jss1->id,
            'section_id' => $sec->id,
            'session' => '2026-2027',
            'adm_no' => $st_user->username,
            'ps_name' => 'Primary',
            'p_status' => 'Living Together',
        ]);

        $domain = $this->tenant->domains->first()->domain ?? 'demo.localhost';
        $this->withServerVariables(['HTTP_HOST' => $domain]);

        $this->actingAs($teacher);

        // 1. Attendance index page
        $res1 = $this->get("http://{$domain}/attendance");
        $res1->assertStatus(200);

        // 2. Attendance register page for date
        $test_date = '2026-10-01';
        $res2 = $this->get("http://{$domain}/attendance/{$sec->id}/{$test_date}");
        $res2->assertStatus(200);

        // 3. Mark attendance via POST
        $res3 = $this->post("http://{$domain}/attendance/{$sec->id}/{$test_date}", [
            'attendance' => [
                $sr->id => 'present',
            ],
        ]);
        $res3->assertStatus(302); // Redirect back with flash_success

        // Verify row in database
        $this->assertDatabaseHas('attendances', [
            'section_id' => $sec->id,
            'student_record_id' => $sr->id,
            'date' => $test_date,
            'status' => 'present',
        ]);

        // 4. Cumulative section report page
        $res4 = $this->get("http://{$domain}/attendance/{$sec->id}/report");
        $res4->assertStatus(200);

        // 5. Student report page
        $res5 = $this->get("http://{$domain}/attendance/student/{$sr->id}");
        $res5->assertStatus(200);
    }
}
