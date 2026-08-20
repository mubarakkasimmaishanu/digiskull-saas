<?php

namespace App\Repositories;

use App\Helpers\Pay;
use App\Helpers\Qs;
use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\Receipt;
use App\Models\Section;
use App\Models\StudentRecord;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialRepo
{
    /**
     * Get student fee summary for a session
     */
    public function getStudentFeeSummary($student_id, string $session): array
    {
        $records = PaymentRecord::where('student_id', $student_id)
            ->where('year', $session)
            ->with('payment')
            ->get();

        $total_billed = 0;
        $total_paid = 0;
        $total_balance = 0;
        $has_invoices = $records->count() > 0;

        foreach ($records as $pr) {
            $payment_amount = $pr->payment ? (int)$pr->payment->amount : 0;
            $paid_amount = (int)($pr->amt_paid ?? 0);
            $balance = $pr->balance !== null ? (int)$pr->balance : ($payment_amount - $paid_amount);

            $total_billed += $payment_amount;
            $total_paid += $paid_amount;
            $total_balance += max(0, $balance);
        }

        // Determine Status
        if (!$has_invoices) {
            $status = 'no_fees';
            $status_label = 'No Fees Billed';
        } elseif ($total_balance <= 0 && $total_paid >= $total_billed && $total_billed > 0) {
            $status = 'paid';
            $status_label = 'Paid in Full';
        } elseif ($total_paid > 0 && $total_balance > 0) {
            $status = 'partially_paid';
            $status_label = 'Partially Paid';
        } else {
            $status = 'not_paid';
            $status_label = 'Not Paid';
        }

        return [
            'student_id' => $student_id,
            'session' => $session,
            'has_invoices' => $has_invoices,
            'records_count' => $records->count(),
            'total_billed' => $total_billed,
            'total_paid' => $total_paid,
            'total_balance' => $total_balance,
            'status' => $status,
            'status_label' => $status_label,
            'is_cleared' => !$has_invoices || $total_balance <= 0,
            'records' => $records,
        ];
    }

    /**
     * Check if a student is financially cleared to view/generate final report card
     */
    public function isStudentClearedForReportCard($student_id, string $session): bool
    {
        $summary = $this->getStudentFeeSummary($student_id, $session);
        return $summary['is_cleared'];
    }

    /**
     * Bulk create invoices for all students in a class
     */
    public function createClassInvoices($class_id, $payment_id, string $session): int
    {
        $payment = Payment::findOrFail($payment_id);
        $students = StudentRecord::where('my_class_id', $class_id)
            ->where('session', $session)
            ->get();

        $count = 0;
        foreach ($students as $sr) {
            $created = PaymentRecord::firstOrCreate(
                [
                    'payment_id' => $payment->id,
                    'student_id' => $sr->user_id,
                    'year' => $session,
                ],
                [
                    'ref_no' => Pay::genRefCode(),
                    'amt_paid' => 0,
                    'balance' => $payment->amount,
                    'paid' => false,
                ]
            );

            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Create an invoice for an individual student
     */
    public function createStudentInvoice($student_id, $payment_id, string $session): PaymentRecord
    {
        $payment = Payment::findOrFail($payment_id);

        return PaymentRecord::firstOrCreate(
            [
                'payment_id' => $payment->id,
                'student_id' => $student_id,
                'year' => $session,
            ],
            [
                'ref_no' => Pay::genRefCode(),
                'amt_paid' => 0,
                'balance' => $payment->amount,
                'paid' => false,
            ]
        );
    }

    /**
     * Record payment against a PaymentRecord and issue Receipt
     */
    public function recordPayment(int $pr_id, int $amt_paid, string $method = 'cash', ?string $ref_no = null, ?string $session = null): Receipt
    {
        $session = $session ?: Qs::getCurrentSession();
        $pr = PaymentRecord::with('payment')->findOrFail($pr_id);
        $payment_amount = $pr->payment ? (int)$pr->payment->amount : 0;

        $new_amt_paid = (int)($pr->amt_paid ?? 0) + $amt_paid;
        $new_balance = max(0, $payment_amount - $new_amt_paid);
        $is_paid = $new_balance <= 0;

        DB::transaction(function () use ($pr, $new_amt_paid, $new_balance, $is_paid, $ref_no) {
            $updateData = [
                'amt_paid' => $new_amt_paid,
                'balance' => $new_balance,
                'paid' => $is_paid,
            ];
            if ($ref_no && empty($pr->ref_no)) {
                $updateData['ref_no'] = $ref_no;
            }
            $pr->update($updateData);
        });

        // Create timestamped receipt
        $receipt = Receipt::create([
            'pr_id' => $pr->id,
            'amt_paid' => $amt_paid,
            'balance' => $new_balance,
            'year' => $session,
        ]);

        return $receipt;
    }

    /**
     * Get comprehensive financial analytics
     */
    public function getFinancialAnalytics(string $session, $class_id = null, $section_id = null): array
    {
        $studentsQuery = StudentRecord::where('session', $session)->with(['user', 'my_class', 'section', 'my_parent']);
        if ($class_id) {
            $studentsQuery->where('my_class_id', $class_id);
        }
        if ($section_id) {
            $studentsQuery->where('section_id', $section_id);
        }
        $students = $studentsQuery->get();

        $total_billed = 0;
        $total_collected = 0;
        $total_outstanding = 0;

        $count_paid = 0;
        $count_partially_paid = 0;
        $count_not_paid = 0;
        $count_no_fees = 0;

        $student_summaries = [];

        foreach ($students as $sr) {
            $summary = $this->getStudentFeeSummary($sr->user_id, $session);
            $summary['sr'] = $sr;
            $student_summaries[] = $summary;

            $total_billed += $summary['total_billed'];
            $total_collected += $summary['total_paid'];
            $total_outstanding += $summary['total_balance'];

            switch ($summary['status']) {
                case 'paid':
                    $count_paid++;
                    break;
                case 'partially_paid':
                    $count_partially_paid++;
                    break;
                case 'not_paid':
                    $count_not_paid++;
                    break;
                case 'no_fees':
                default:
                    $count_no_fees++;
                    break;
            }
        }

        // Class-by-class breakdown
        $classes = MyClass::with('section')->get();
        $class_breakdown = [];
        foreach ($classes as $c) {
            $cStudents = collect($student_summaries)->filter(function ($item) use ($c) {
                return $item['sr']->my_class_id == $c->id;
            });

            $cBilled = $cStudents->sum('total_billed');
            $cCollected = $cStudents->sum('total_paid');
            $cOutstanding = $cStudents->sum('total_balance');
            $cPaidCount = $cStudents->where('status', 'paid')->count();
            $cPartialCount = $cStudents->where('status', 'partially_paid')->count();
            $cUnpaidCount = $cStudents->where('status', 'not_paid')->count();

            // Sections breakdown
            $sections_breakdown = [];
            foreach ($c->section as $sec) {
                $secStudents = $cStudents->filter(function ($item) use ($sec) {
                    return $item['sr']->section_id == $sec->id;
                });

                $sections_breakdown[] = [
                    'section' => $sec,
                    'total_students' => $secStudents->count(),
                    'total_billed' => $secStudents->sum('total_billed'),
                    'total_collected' => $secStudents->sum('total_paid'),
                    'total_outstanding' => $secStudents->sum('total_balance'),
                    'paid_count' => $secStudents->where('status', 'paid')->count(),
                    'partial_count' => $secStudents->where('status', 'partially_paid')->count(),
                    'unpaid_count' => $secStudents->where('status', 'not_paid')->count(),
                ];
            }

            $class_breakdown[] = [
                'class' => $c,
                'total_students' => $cStudents->count(),
                'total_billed' => $cBilled,
                'total_collected' => $cCollected,
                'total_outstanding' => $cOutstanding,
                'paid_count' => $cPaidCount,
                'partial_count' => $cPartialCount,
                'unpaid_count' => $cUnpaidCount,
                'sections' => $sections_breakdown,
            ];
        }

        return [
            'session' => $session,
            'total_students' => $students->count(),
            'total_billed' => $total_billed,
            'total_collected' => $total_collected,
            'total_outstanding' => $total_outstanding,
            'collection_rate' => $total_billed > 0 ? round(($total_collected / $total_billed) * 100, 1) : 0,
            'count_paid' => $count_paid,
            'count_partially_paid' => $count_partially_paid,
            'count_not_paid' => $count_not_paid,
            'count_no_fees' => $count_no_fees,
            'class_breakdown' => $class_breakdown,
            'student_summaries' => $student_summaries,
        ];
    }

    /**
     * Get list of debtors with auto-resolved guardian contact information
     */
    public function getDebtorsList(string $session, $class_id = null, $status_filter = null): Collection
    {
        $studentsQuery = StudentRecord::where('session', $session)
            ->with(['user', 'my_class', 'section', 'my_parent']);

        if ($class_id) {
            $studentsQuery->where('my_class_id', $class_id);
        }

        $students = $studentsQuery->get();
        $debtors = collect();

        foreach ($students as $sr) {
            $summary = $this->getStudentFeeSummary($sr->user_id, $session);

            if ($status_filter) {
                if ($summary['status'] !== $status_filter) {
                    continue;
                }
            } else {
                // Return any student with an unpaid balance
                if ($summary['total_balance'] <= 0 || in_array($summary['status'], ['paid', 'no_fees'])) {
                    continue;
                }
            }

            // Auto-resolve guardian details
            $guardian_name = 'Guardian / Parent';
            $guardian_phone = null;

            if ($sr->my_parent) {
                $guardian_name = $sr->my_parent->name ?: $guardian_name;
                $guardian_phone = $sr->my_parent->phone ?: ($sr->my_parent->phone2 ?: null);
            }
            if (!$guardian_phone && $sr->phone) {
                $guardian_phone = $sr->phone;
            }
            if (!$guardian_phone && $sr->user && $sr->user->phone) {
                $guardian_phone = $sr->user->phone;
            }

            $debtors->push([
                'student_record' => $sr,
                'student' => $sr->user,
                'my_class' => $sr->my_class,
                'section' => $sr->section,
                'total_billed' => $summary['total_billed'],
                'total_paid' => $summary['total_paid'],
                'total_balance' => $summary['total_balance'],
                'status' => $summary['status'],
                'status_label' => $summary['status_label'],
                'guardian_name' => $guardian_name,
                'guardian_phone' => $guardian_phone,
            ]);
        }

        return $debtors;
    }
}
