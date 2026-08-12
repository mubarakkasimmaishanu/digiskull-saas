{{--
  Attendance strip.

  There is no attendances table in the codebase yet (see the Report Cards
  refactor decision log). We render the row with placeholders so the
  layout matches the mockup exactly; once the Attendance module lands,
  swap the ??-fallback values for real numbers pulled from an
  AttendanceStatsService keyed by (student_id, term, year).
--}}
@php
    $timesOpened  = $asr->days_open      ?? '—';
    $daysAbsent   = $asr->days_absent    ?? '—';
    $daysPresent  = $asr->days_present   ?? '—';
    $totalMarks   = $asr->total          ?? '—';
    $averageMarks = $asr->ave            ?? '—';
@endphp

<section class="ss-report__attendance">
    <div>No. Of times school opened: <span class="value">{{ $timesOpened }}</span></div>
    <div>No. Of Days absent from school: <span class="value">{{ $daysAbsent }}</span></div>
    <div>No. Of Days present in School: <span class="value">{{ $daysPresent }}</span></div>

    <div class="row-2">
        <div>Total: <span class="value">{{ $totalMarks }}</span></div>
        <div>Average: <span class="value">{{ $averageMarks }}</span></div>
    </div>
</section>
