<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Pay;
use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\Receipt;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Repositories\FinancialRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Services\SmsService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;

class AccountantPortalController extends Controller implements HasMiddleware
{
    protected $financialRepo;
    protected $studentRepo;
    protected $classRepo;
    protected $smsService;
    protected $year;

    public function __construct(
        FinancialRepo $financialRepo,
        StudentRepo $studentRepo,
        MyClassRepo $classRepo,
        SmsService $smsService
    ) {
        $this->financialRepo = $financialRepo;
        $this->studentRepo = $studentRepo;
        $this->classRepo = $classRepo;
        $this->smsService = $smsService;
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Get middleware assigned to the controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('teamAccount', except: ['receipts']),
        ];
    }

    /**
     * Accountant Portal Dashboard
     */
    public function dashboard(Request $request)
    {
        $session = $request->input('session', $this->year);
        $analytics = $this->financialRepo->getFinancialAnalytics($session);

        $d['year'] = $session;
        $d['analytics'] = $analytics;
        $d['recent_receipts'] = Receipt::where('year', $session)->with(['pr.student', 'pr.payment'])->latest()->take(10)->get();
        $d['debtors_sample'] = $this->financialRepo->getDebtorsList($session)->take(5);

        return view('pages.accountant.dashboard', $d);
    }

    /**
     * Searchable Student Financial Directory
     */
    public function students(Request $request)
    {
        $session = $request->input('session', $this->year);
        $class_id = $request->input('class_id');
        $status_filter = $request->input('status');
        $search = $request->input('search');

        $query = StudentRecord::where('session', $session)->with(['user', 'my_class', 'section', 'my_parent']);

        if ($class_id) {
            $query->where('my_class_id', $class_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('adm_no', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $allStudents = $query->get();
        $studentList = [];

        foreach ($allStudents as $sr) {
            $summary = $this->financialRepo->getStudentFeeSummary($sr->user_id, $session);

            if ($status_filter && $summary['status'] !== $status_filter) {
                continue;
            }

            $summary['sr'] = $sr;
            $studentList[] = $summary;
        }

        $d['year'] = $session;
        $d['classes'] = $this->classRepo->all();
        $d['selected_class'] = $class_id;
        $d['selected_status'] = $status_filter;
        $d['search'] = $search;
        $d['students'] = $studentList;

        return view('pages.accountant.students', $d);
    }

    /**
     * School Fees Invoices Management
     */
    public function invoices(Request $request)
    {
        $session = $request->input('session', $this->year);
        $class_id = $request->input('class_id');

        $query = Payment::where('year', $session)->with('my_class');
        if ($class_id) {
            $query->where(function ($q) use ($class_id) {
                $q->where('my_class_id', $class_id)->orWhereNull('my_class_id');
            });
        }

        $d['year'] = $session;
        $d['classes'] = $this->classRepo->all();
        $d['selected_class'] = $class_id;
        $d['payments'] = $query->latest()->get();

        return view('pages.accountant.invoices', $d);
    }

    /**
     * Store a new Fee Type / Invoice
     */
    public function store_invoice(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'my_class_id' => 'nullable|exists:my_classes,id',
            'description' => 'nullable|string|max:255',
        ]);

        $session = $request->input('year', $this->year);

        $payment = Payment::create([
            'title' => $request->title,
            'amount' => (int)$request->amount,
            'ref_no' => Pay::genRefCode(),
            'my_class_id' => $request->my_class_id ?: null,
            'description' => $request->description,
            'year' => $session,
        ]);

        // If class specified, auto-invoice class students
        if ($request->my_class_id) {
            $invoicedCount = $this->financialRepo->createClassInvoices($request->my_class_id, $payment->id, $session);
            return back()->with('flash_success', "Fee created and {$invoicedCount} student invoices generated.");
        }

        return back()->with('flash_success', 'General fee created successfully.');
    }

    /**
     * Bill all students in a class with a payment fee
     */
    public function bill_class(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'class_id' => 'required|exists:my_classes,id',
        ]);

        $session = $request->input('session', $this->year);
        $count = $this->financialRepo->createClassInvoices($request->class_id, $request->payment_id, $session);

        return back()->with('flash_success', "Successfully issued invoices to {$count} students.");
    }

    /**
     * Bill an individual student with a payment fee
     */
    public function bill_student(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'student_id' => 'required|exists:users,id',
        ]);

        $session = $request->input('session', $this->year);
        $this->financialRepo->createStudentInvoice($request->student_id, $request->payment_id, $session);

        return back()->with('flash_success', 'Invoice issued to student successfully.');
    }

    /**
     * View student payment records / record payment page
     */
    public function student_fees($student_id, Request $request)
    {
        $session = $request->input('session', $this->year);
        $sr = StudentRecord::where(['user_id' => $student_id, 'session' => $session])->with(['user', 'my_class', 'section', 'my_parent'])->first();

        if (!$sr) {
            $sr = StudentRecord::where('user_id', $student_id)->with(['user', 'my_class', 'section', 'my_parent'])->latest()->first();
        }

        if (!$sr) {
            return back()->with('flash_danger', 'Student record not found.');
        }

        $summary = $this->financialRepo->getStudentFeeSummary($student_id, $session);
        $available_payments = Payment::where('year', $session)
            ->where(function ($q) use ($sr) {
                $q->where('my_class_id', $sr->my_class_id)->orWhereNull('my_class_id');
            })
            ->get();

        $d['sr'] = $sr;
        $d['student'] = $sr->user;
        $d['year'] = $session;
        $d['summary'] = $summary;
        $d['available_payments'] = $available_payments;

        return view('pages.accountant.record_payment', $d);
    }

    /**
     * Store recorded payment against a PaymentRecord
     */
    public function store_payment(Request $request, $pr_id)
    {
        $request->validate([
            'amt_paid' => 'required|numeric|min:1',
            'method' => 'required|string',
            'ref_no' => 'nullable|string',
        ]);

        try {
            $session = $request->input('session', $this->year);
            $receipt = $this->financialRepo->recordPayment(
                (int)$pr_id,
                (int)$request->amt_paid,
                $request->method,
                $request->ref_no,
                $session
            );

            return back()->with('flash_success', 'Payment recorded successfully. Receipt #' . $receipt->id . ' generated.');
        } catch (\Exception $e) {
            return back()->with('flash_danger', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Printable Payment Receipt
     */
    public function receipts($receipt_id)
    {
        $receipt = Receipt::with(['pr.student', 'pr.payment', 'pr.student.student_record'])->findOrFail($receipt_id);
        $pr = $receipt->pr;
        $student = $pr->student;
        $sr = StudentRecord::where('user_id', $student->id)->with(['my_class', 'section', 'my_parent'])->first();

        $d['receipt'] = $receipt;
        $d['pr'] = $pr;
        $d['payment'] = $pr->payment;
        $d['student'] = $student;
        $d['sr'] = $sr;
        $d['year'] = $receipt->year;

        return view('pages.accountant.receipt', $d);
    }

    /**
     * Financial Analytics Report
     */
    public function analytics(Request $request)
    {
        $session = $request->input('session', $this->year);
        $class_id = $request->input('class_id');

        $analytics = $this->financialRepo->getFinancialAnalytics($session, $class_id);

        $d['year'] = $session;
        $d['classes'] = $this->classRepo->all();
        $d['selected_class'] = $class_id;
        $d['analytics'] = $analytics;

        return view('pages.accountant.analytics', $d);
    }

    /**
     * Bulk SMS Reminder Center
     */
    public function sms_reminders(Request $request)
    {
        $session = $request->input('session', $this->year);
        $class_id = $request->input('class_id');
        $status_filter = $request->input('status');

        $debtors = $this->financialRepo->getDebtorsList($session, $class_id, $status_filter);

        $d['year'] = $session;
        $d['classes'] = $this->classRepo->all();
        $d['selected_class'] = $class_id;
        $d['selected_status'] = $status_filter;
        $d['debtors'] = $debtors;

        return view('pages.accountant.sms_reminders', $d);
    }

    /**
     * Send Bulk SMS Reminders
     */
    public function send_bulk_sms(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'message' => 'required|string|min:5|max:500',
        ]);

        $recipientsData = [];
        foreach ($request->recipients as $itemJson) {
            $item = json_decode($itemJson, true);
            if ($item && !empty($item['phone'])) {
                $recipientsData[] = $item;
            }
        }

        if (empty($recipientsData)) {
            return back()->with('flash_danger', 'No valid guardian phone numbers were selected for SMS dispatch.');
        }

        $result = $this->smsService->sendBulkReminders($recipientsData, $request->message, $request->sender_id);

        return back()->with('flash_success', "Bulk SMS dispatched: {$result['sent_count']} sent successfully.");
    }
}
