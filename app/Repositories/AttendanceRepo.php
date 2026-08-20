<?php

namespace App\Repositories;

use App\Helpers\Qs;
use App\Models\Attendance;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;

class AttendanceRepo
{
    /**
     * Get attendance records for a specific section on a given date.
     *
     * @param int $section_id
     * @param string $date (Y-m-d)
     * @return \Illuminate\Support\Collection Keyed by student_record_id
     */
    public function getSectionAttendanceForDate(int $section_id, string $date)
    {
        return Attendance::where('section_id', $section_id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_record_id');
    }

    /**
     * Mark/save attendance records for a section on a given date.
     *
     * @param int $section_id
     * @param string $date (Y-m-d)
     * @param array $records Array of [student_record_id => 'present'|'absent']
     * @param int|null $marked_by User ID who marked
     * @param string|null $session Academic session
     * @return int Number of updated/created records
     */
    public function markAttendanceForDate(int $section_id, string $date, array $records, ?int $marked_by = null, ?string $session = null)
    {
        $session = $session ?? Qs::getCurrentSession();
        $count = 0;

        DB::transaction(function () use ($section_id, $date, $records, $marked_by, $session, &$count) {
            foreach ($records as $sr_id => $status) {
                if (!in_array($status, ['present', 'absent'], true)) {
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'student_record_id' => (int) $sr_id,
                        'date' => $date,
                    ],
                    [
                        'section_id' => $section_id,
                        'status' => $status,
                        'marked_by' => $marked_by,
                        'session' => $session,
                    ]
                );
                $count++;
            }
        });

        return $count;
    }

    /**
     * Get total number of distinct school days attendance was taken for a section.
     *
     * @param int $section_id
     * @param string|null $session
     * @return int
     */
    public function getTotalSchoolDays(int $section_id, ?string $session = null): int
    {
        $query = Attendance::where('section_id', $section_id);

        if ($session) {
            $query->where('session', $session);
        }

        return $query->distinct('date')->count('date');
    }

    /**
     * Get attendance statistics for a single student record.
     *
     * Formula:
     * - Total School Days = Distinct dates marked for student's section
     * - Days Present = Count of 'present' records for this student
     * - Days Absent = Count of 'absent' records for this student
     * - Percentage = (Days Present / Total School Days) * 100
     *
     * @param int $student_record_id
     * @param string|null $session
     * @return array
     */
    public function getStudentAttendanceStats(int $student_record_id, ?string $session = null): array
    {
        $sr = StudentRecord::find($student_record_id);
        if (!$sr) {
            return [
                'total_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'percentage' => 0.0,
            ];
        }

        $session = $session ?? $sr->session ?? Qs::getCurrentSession();

        $total_days = $this->getTotalSchoolDays($sr->section_id, $session);

        $present_days = Attendance::where('student_record_id', $student_record_id)
            ->where('session', $session)
            ->where('status', 'present')
            ->count();

        $absent_days = Attendance::where('student_record_id', $student_record_id)
            ->where('session', $session)
            ->where('status', 'absent')
            ->count();

        $percentage = $total_days > 0
            ? round(($present_days / $total_days) * 100, 1)
            : 100.0;

        return [
            'total_days' => $total_days,
            'present_days' => $present_days,
            'absent_days' => $absent_days,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get comprehensive attendance summary for an entire section.
     *
     * @param int $section_id
     * @param string|null $session
     * @return array Array of student summaries with statistics
     */
    public function getSectionAttendanceSummary(int $section_id, ?string $session = null): array
    {
        $session = $session ?? Qs::getCurrentSession();
        $total_days = $this->getTotalSchoolDays($section_id, $session);

        // Get active students for this section
        $students = StudentRecord::where('section_id', $section_id)
            ->where('grad', 0)
            ->with(['user', 'my_parent'])
            ->get()
            ->whereNotNull('user')
            ->sortBy('user.name');

        $student_ids = $students->pluck('id')->toArray();

        // Batch aggregate present and absent counts
        $attendance_counts = Attendance::whereIn('student_record_id', $student_ids)
            ->where('session', $session)
            ->select('student_record_id', 'status', DB::raw('count(*) as count'))
            ->groupBy('student_record_id', 'status')
            ->get()
            ->groupBy('student_record_id');

        $summaries = [];

        foreach ($students as $st) {
            $student_counts = $attendance_counts->get($st->id);
            $present_days = 0;
            $absent_days = 0;

            if ($student_counts) {
                foreach ($student_counts as $row) {
                    if ($row->status === 'present') {
                        $present_days = (int) $row->count;
                    } elseif ($row->status === 'absent') {
                        $absent_days = (int) $row->count;
                    }
                }
            }

            $percentage = $total_days > 0
                ? round(($present_days / $total_days) * 100, 1)
                : 100.0;

            $summaries[] = [
                'student' => $st,
                'total_days' => $total_days,
                'present_days' => $present_days,
                'absent_days' => $absent_days,
                'percentage' => $percentage,
            ];
        }

        return [
            'total_school_days' => $total_days,
            'students_summary' => $summaries,
        ];
    }

    /**
     * Get distinct dates attendance was recorded for a section.
     *
     * @param int $section_id
     * @param int $limit
     * @return array
     */
    public function getRecentDates(int $section_id, int $limit = 10): array
    {
        return Attendance::where('section_id', $section_id)
            ->distinct('date')
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->pluck('date')
            ->map(fn($d) => is_string($d) ? $d : $d->format('Y-m-d'))
            ->toArray();
    }
}
