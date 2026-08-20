<div class="sidebar sidebar-main sidebar-expand-md">
    <!-- Sidebar mobile toggler -->
    <div class="sidebar-mobile-toggler text-center">
        <a href="javascript:;" class="sidebar-mobile-main-toggle">
            <i class="material-symbols-rounded">arrow_back</i>
        </a>
        Navigation
        <a href="javascript:;" class="sidebar-mobile-expand">
            <i class="material-symbols-rounded">format_indent_increase</i>
            <i class="material-symbols-rounded">format_indent_decrease</i>
        </a>
    </div>
    <!-- /sidebar mobile toggler -->

    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- User menu -->
        <div class="sidebar-user">
            <div class="card-body">
                <div class="media">
                    <div class="mr-3">
                        <a href="{{ route('my_account') }}"><img src="{{ Usr::getTenantAwarePhoto(auth()->user()->photo) }}" width="38" height="38" class="rounded-circle" alt="photo"></a>
                    </div>

                    <div class="media-body">
                        <div class="media-title font-weight-semibold">{{ auth()->user()->name }}</div>
                        <div class="font-size-sm opacity-50 d-flex">
                            <i class="material-symbols-rounded">person</i> &nbsp;{{ ucwords(str_replace('_', ' ', auth()->user()->user_type)) }}
                        </div>
                    </div>

                    <div class="ml-3 align-self-center">
                        <a href="{{ route('my_account') }}"><i class="material-symbols-rounded">settings</i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->

        {{--
            Sidebar IA follows the refined domain tree we agreed on:
              Classes → Students, Classroom Teachers, Subjects
              Timetable
              Duty Roster           [unbuilt — "Soon"]
              Attendance            [unbuilt — "Soon"]
              Academic Records      (consolidates the old Exams + Assessments + Marks maze)
              Report Cards          [unbuilt — "Soon"]
              Finance               (Payments today; the rest "Soon")
              Settings              (Users, School Rules, Dormitories, Library, Query Builder — kept out of the daily workflow)

            Nodes marked "Soon" render as a disabled <span> with a badge so
            admins see the roadmap without hitting a 404. Convert to a live
            <a href> once the underlying controller lands.
        --}}

        <!-- Main navigation -->
        <div class="card card-sidebar-mobile border-0">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                {{--============ DASHBOARD ============--}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['dashboard', 'home']) ? 'active' : '' }}">
                        <i class="material-symbols-rounded">{{ Route::is('dashboard') ? 'dashboard' : 'home' }}</i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{--============ CLASSES ============
                    Groups the day-to-day roster admin: the classes
                    themselves, the students in them, the classroom
                    teacher assignments, and the subjects taught. --}}
                @php
                    $classesRoutes = [
                        'classes.index','classes.edit',
                        'students.create','students.list','students.edit','students.show',
                        'students.promotion','students.promotion_manage','students.graduated',
                        'students.id_cards','students.id_cards_manage','students.manage_permissions',
                        'sections.index','sections.edit','sections.classroom_permissions',
                        'subjects.index','subjects.edit',
                    ];
                @endphp
                @if(Qs::userIsTeamSATCL())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), $classesRoutes) ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="javascript:;" class="nav-link"><i class="material-symbols-rounded">domain</i> <span>Classes</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Classes">
                        @if(Qs::userIsTeamSA())
                        <li class="nav-item">
                            <a href="{{ route('classes.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['classes.index','classes.edit']) ? 'active' : '' }}">Manage Classes</a>
                        </li>
                        @endif

                        {{--Students (nested — the biggest daily-work sub-tree)--}}
                        <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.create','students.list','students.edit','students.show','students.promotion','students.promotion_manage','students.graduated','students.id_cards','students.id_cards_manage']) ? 'nav-item-expanded' : '' }}">
                            <a href="javascript:;" class="nav-link {{ in_array(Route::currentRouteName(), ['students.list','students.edit','students.show']) ? 'active' : '' }}">Students</a>
                            <ul class="nav nav-group-sub">
                                @if(Qs::userIsTeamSA())
                                <li class="nav-item"><a href="{{ route('students.create') }}" class="nav-link {{ Route::is('students.create') ? 'active' : '' }}">Admit Student</a></li>
                                @endif

                                {{--Per-class student list (kept as inline loop for
                                    parity with the previous menu; MyClass is a small
                                    table so the N+1 stays cheap). --}}
                                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.list','students.edit','students.show']) ? 'nav-item-expanded' : '' }}">
                                    <a href="javascript:;" class="nav-link">Student Information</a>
                                    <ul class="nav nav-group-sub">
                                        @foreach(App\Models\MyClass::orderBy('name')->get() as $c)
                                            <li class="nav-item"><a href="{{ route('students.list', $c->id) }}" class="nav-link">{{ $c->name }}</a></li>
                                        @endforeach
                                    </ul>
                                </li>

                                @if(Qs::userIsTeamSA())
                                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.promotion','students.promotion_manage']) ? 'nav-item-expanded' : '' }}">
                                    <a href="javascript:;" class="nav-link">Promotion</a>
                                    <ul class="nav nav-group-sub">
                                        <li class="nav-item"><a href="{{ route('students.promotion') }}" class="nav-link {{ Route::is('students.promotion') ? 'active' : '' }}">Promote Students</a></li>
                                        <li class="nav-item"><a href="{{ route('students.promotion_manage') }}" class="nav-link {{ Route::is('students.promotion_manage') ? 'active' : '' }}">Manage Promotions</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item"><a href="{{ route('students.graduated') }}" class="nav-link {{ Route::is('students.graduated') ? 'active' : '' }}">Graduated</a></li>
                                    @if(Qs::userIsSuperAdmin())
                                    <li class="nav-item"><a href="{{ route('students.id_cards') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.id_cards','students.id_cards_manage']) ? 'active' : '' }}">ID Cards</a></li>
                                    @endif
                                @endif
                            </ul>
                        </li>

                        {{--Classroom Teachers — the friendly label for the
                            per-section teacher_id assignment screen. This is
                            what a proprietor actually looks for when they ask
                            "who's teaching JSS2A?". Sections themselves stay
                            hidden as an implementation detail. --}}
                        @if(Qs::userIsTeamSA())
                        <li class="nav-item">
                            <a href="{{ route('sections.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['sections.index','sections.edit']) ? 'active' : '' }}">Classroom Teachers</a>
                        </li>

                        {{--Subjects (and, contextually, subject teachers) --}}
                        <li class="nav-item">
                            <a href="{{ route('subjects.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['subjects.index','subjects.edit']) ? 'active' : '' }}">Subjects &amp; Subject Teachers</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                {{--============ TIMETABLE ============--}}
                @if(Qs::userIsAcademic())
                <li class="nav-item">
                    <a href="{{ route('tt.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['tt.index','ttr.edit','ttr.show','ttr.manage']) ? 'active' : '' }}">
                        <i class="material-symbols-rounded">schedule</i> <span>Timetable</span>
                    </a>
                </li>
                @endif

                {{--============ DUTY ROSTER (unbuilt) ============--}}
                @if(Qs::userIsTeamSA())
                <li class="nav-item" title="Coming soon — DutyPolicy is scaffolded; controller and views are next">
                    <span class="nav-link text-muted" style="cursor: not-allowed;">
                        <i class="material-symbols-rounded">assignment_ind</i>
                        <span>Duty Roster</span>
                        <span class="badge badge-light ml-2" style="margin-left: auto;">Soon</span>
                    </span>
                </li>
                @endif

                {{--============ ATTENDANCE ============--}}
                @php
                    $attendanceRoutes = ['attendance.index', 'attendance.show', 'attendance.section_report', 'attendance.student_report'];
                @endphp
                @if(Qs::userIsTeamSATC())
                <li class="nav-item">
                    <a href="{{ route('attendance.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), $attendanceRoutes) ? 'active' : '' }}">
                        <i class="material-symbols-rounded">fact_check</i> <span>Attendance</span>
                    </a>
                </li>
                @endif

                {{--============ ACADEMIC RECORDS ============
                    Consolidates every scoring screen (CAs + terminal Exams +
                    Marks + Grades + Divisions + Tabulation) under one node.
                    We keep every existing route working so no controller
                    changes are needed for this step — this is pure IA. --}}
                @php
                    $academicRoutes = [
                        'assessments.list','assessments.index','assessments.manage','assessments.progressive','assessments.bulk','assessments.year_selector','assessments.show',
                        'exams.index','exams.edit',
                        'marks.index','marks.convert','marks.manage','marks.bulk','marks.tabulation','marks.show','marks.batch',
                        'grades.index','grades.edit',
                        'divisions.index','divisions.edit',
                    ];
                @endphp
                @if(Qs::userIsTeamSAT())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), $academicRoutes) ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="javascript:;" class="nav-link"><i class="material-symbols-rounded">article</i> <span>Academic Records</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Academic Records">

                        {{--The two screens teachers use every day, at the top--}}
                        @if(Qs::userIsClassSectionTeacher() || Qs::userIsTeamSA())
                        <li class="nav-item"><a href="{{ route('assessments.progressive') }}" class="nav-link {{ Route::is('assessments.progressive') ? 'active' : '' }}">Progressive Sheet</a></li>
                        @endif

                        <li class="nav-item"><a href="{{ route('assessments.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['assessments.index','assessments.manage']) ? 'active' : '' }}">Continuous Assessments (CA)</a></li>
                        <li class="nav-item"><a href="{{ route('assessments.bulk') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['assessments.bulk','assessments.show','assessments.year_selector']) ? 'active' : '' }}">Assessment Sheet</a></li>

                        {{--Terminal exam scoring--}}
                        <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['marks.index','marks.convert','marks.batch','marks.manage']) ? 'nav-item-expanded' : '' }}">
                            <a href="javascript:;" class="nav-link">Exam Marks</a>
                            <ul class="nav nav-group-sub">
                                <li class="nav-item"><a href="{{ route('marks.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.index','marks.manage']) ? 'active' : '' }}">Enter Marks</a></li>
                                @if(Qs::userIsTeamSAT())
                                <li class="nav-item"><a href="{{ route('marks.batch') }}" class="nav-link {{ Route::is('marks.batch') ? 'active' : '' }}">Batch Marks</a></li>
                                @endif
                                <li class="nav-item"><a href="{{ route('marks.convert') }}" class="nav-link {{ Route::is('marks.convert') ? 'active' : '' }}">Convert</a></li>
                            </ul>
                        </li>

                        <li class="nav-item"><a href="{{ route('marks.bulk') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.bulk','marks.show']) ? 'active' : '' }}">Marksheet</a></li>

                        @if(Qs::userIsTeamSA())
                        <li class="nav-item"><a href="{{ route('marks.tabulation') }}" class="nav-link {{ Route::is('marks.tabulation') ? 'active' : '' }}">Tabulation Sheet</a></li>

                        {{--Configuration (below the daily-use items) --}}
                        <li class="nav-item"><a href="{{ route('assessments.list') }}" class="nav-link {{ Route::is('assessments.list') ? 'active' : '' }}">CA Setup</a></li>
                        <li class="nav-item"><a href="{{ route('exams.index') }}" class="nav-link {{ Route::is('exams.index') ? 'active' : '' }}">Exam Setup</a></li>
                        <li class="nav-item"><a href="{{ route('grades.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['grades.index','grades.edit']) ? 'active' : '' }}">Grades</a></li>
                        <li class="nav-item"><a href="{{ route('divisions.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['divisions.index','divisions.edit']) ? 'active' : '' }}">Divisions</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                {{--============ EXAMINATION OFFICER WORKSPACE ============--}}
                @if(Qs::userIsExamOfficer() || Qs::userIsTeamSA())
                @php
                    $examOfficerRoutes = [
                        'exam_officer.dashboard',
                        'exam_officer.submissions',
                        'exam_officer.review',
                        'exam_officer.missing_scores',
                        'exam_officer.results_generation',
                        'exam_officer.report_card',
                        'exam_officer.bulk_report_cards',
                    ];
                @endphp
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), $examOfficerRoutes) ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="javascript:;" class="nav-link"><i class="material-symbols-rounded">assignment_turned_in</i> <span>Exam Officer</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Exam Officer">
                        <li class="nav-item"><a href="{{ route('exam_officer.dashboard') }}" class="nav-link {{ Route::is('exam_officer.dashboard') ? 'active' : '' }}">Officer Dashboard</a></li>
                        <li class="nav-item"><a href="{{ route('exam_officer.submissions') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['exam_officer.submissions', 'exam_officer.review']) ? 'active' : '' }}">Score Submissions</a></li>
                        <li class="nav-item"><a href="{{ route('exam_officer.missing_scores') }}" class="nav-link {{ Route::is('exam_officer.missing_scores') ? 'active' : '' }}">Missing Scores Audit</a></li>
                        <li class="nav-item"><a href="{{ route('exam_officer.results_generation') }}" class="nav-link {{ Route::is('exam_officer.results_generation') ? 'active' : '' }}">Results &amp; Rankings</a></li>
                    </ul>
                </li>
                @endif

                {{--============ ACCOUNTANT & FINANCE WORKSPACE (Phase 3) ============--}}
                @if(Qs::userIsTeamAccount())
                @php
                    $accountantRoutes = [
                        'accountant.dashboard',
                        'accountant.students',
                        'accountant.student_fees',
                        'accountant.invoices',
                        'accountant.analytics',
                        'accountant.sms_reminders',
                        'accountant.receipts',
                        'payments.index','payments.create','payments.edit','payments.manage','payments.show','payments.invoice','payments.receipts'
                    ];
                @endphp
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), $accountantRoutes) ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="javascript:;" class="nav-link"><i class="material-symbols-rounded">account_balance</i> <span>Accountant</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Accountant">
                        <li class="nav-item"><a href="{{ route('accountant.dashboard') }}" class="nav-link {{ Route::is('accountant.dashboard') ? 'active' : '' }}">Financial Dashboard</a></li>
                        <li class="nav-item"><a href="{{ route('accountant.students') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['accountant.students', 'accountant.student_fees']) ? 'active' : '' }}">Student Directory &amp; Fees</a></li>
                        <li class="nav-item"><a href="{{ route('accountant.invoices') }}" class="nav-link {{ Route::is('accountant.invoices') ? 'active' : '' }}">Fee Invoices &amp; Billing</a></li>
                        <li class="nav-item"><a href="{{ route('accountant.analytics') }}" class="nav-link {{ Route::is('accountant.analytics') ? 'active' : '' }}">Revenue Analytics</a></li>
                        <li class="nav-item"><a href="{{ route('accountant.sms_reminders') }}" class="nav-link {{ Route::is('accountant.sms_reminders') ? 'active' : '' }}">Bulk SMS Reminders</a></li>
                        <li class="nav-item"><a href="{{ route('payments.manage') }}" class="nav-link {{ Route::is('payments.manage') ? 'active' : '' }}">Payment Records</a></li>
                    </ul>
                </li>
                @endif

                {{-- Any user-type-specific menu injected here (teacher's
                     personal menu, parent's ward-list, etc). Preserved from
                     the previous IA — do not remove. --}}
                @include('pages.'.Qs::getUserType().'.menu')

                {{--============ SETTINGS ============
                    Everything that isn't a daily-workflow item — user admin,
                    boarding, library, dev tools, and school-wide rules —
                    lives here so it doesn't clutter the primary tree. --}}
                @if(Qs::userIsTeamSA())
                @php
                    $settingsRoutes = [
                        'users.index','users.show','users.edit',
                        'sections.school_defaults','sections.school_defaults.update',
                        'dorms.index','dorms.edit',
                        'books.index','books.edit','book_requests.index','book_requests.edit',
                        'query_builder.index','query_builder.select',
                    ];
                @endphp
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), $settingsRoutes) ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="javascript:;" class="nav-link"><i class="material-symbols-rounded">settings</i> <span>Settings</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Settings">
                        <li class="nav-item"><a href="{{ route('sections.school_defaults') }}" class="nav-link {{ Route::is('sections.school_defaults') ? 'active' : '' }}">School Rules</a></li>
                        <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['users.index','users.show','users.edit']) ? 'active' : '' }}">Users &amp; Staff</a></li>
                        <li class="nav-item"><a href="{{ route('dorms.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['dorms.index','dorms.edit']) ? 'active' : '' }}">Dormitories</a></li>

                        @if(Qs::userIsTeamLibrary())
                        <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['books.index','book_requests.index','books.edit','book_requests.edit']) ? 'nav-item-expanded' : '' }}">
                            <a href="javascript:;" class="nav-link">Library</a>
                            <ul class="nav nav-group-sub">
                                <li class="nav-item"><a href="{{ route('books.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['books.index','books.edit']) ? 'active' : '' }}">Books</a></li>
                                <li class="nav-item"><a href="{{ route('book_requests.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['book_requests.index','book_requests.edit']) ? 'active' : '' }}">Requests</a></li>
                            </ul>
                        </li>
                        @endif

                        <li class="nav-item"><a href="{{ route('query_builder.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['query_builder.index','query_builder.select']) ? 'active' : '' }}">Query Builder</a></li>
                    </ul>
                </li>
                @endif

                {{--============ MY ACCOUNT ============--}}
                <li class="nav-item">
                    <a href="{{ route('my_account') }}" class="nav-link {{ Route::is('my_account') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">account_circle</i> <span>My Account</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
