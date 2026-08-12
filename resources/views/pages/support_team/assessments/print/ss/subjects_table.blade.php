{{--
  Subjects table with the SS report columns:
  SUBJECT | 1st C.A. | 2nd C.A. | Exam Score | Total | Position | Grade | Teacher's Comment | Remarks

  1st/2nd C.A. are derived from the existing assessment_records via the
  Mk::getCA1/getCA2 helpers so no schema change is required. The per-subject
  "Teacher's Comment" reuses the grade's remark text (per decision), and
  "Remarks" mirrors it so the table matches the mockup's two-column shape.
--}}
<table class="ss-report__subjects">
    <thead>
        <tr>
            <th style="text-align:left;">Subjects</th>
            <th>1st<br>C.A.</th>
            <th>2nd<br>C.A.</th>
            <th>Exam<br>Score</th>
            <th>Total<br>Score</th>
            <th>Subj.<br>Position</th>
            <th>Grade</th>
            <th>Teacher's Comment</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subjects as $sub)
            @php
                $as = $assessments_records
                        ->where('subject_id', $sub->id)
                        ->where('exam_id', $ex->id)
                        ->first();

                $ca1        = \App\Helpers\Mk::getCA1($as);
                $ca2        = \App\Helpers\Mk::getCA2($as);
                $examScore  = \App\Helpers\Mk::getExamScore($as);
                $total      = $as?->total;
                $position   = $as && $as->sub_pos ? \App\Helpers\Mk::getSuffix($as->sub_pos) : '—';
                $gradeName  = $as && $as->grade ? $as->grade->name : '—';
                $remark     = $as && $as->grade ? $as->grade->remark : '—';
            @endphp
            <tr>
                <td class="subject-name">{{ $sub->name }}</td>
                <td>{{ $ca1 ?? '—' }}</td>
                <td>{{ $ca2 ?? '—' }}</td>
                <td>{{ $examScore ?? '—' }}</td>
                <td>{{ $total ?? '—' }}</td>
                <td>{!! $position !!}</td>
                <td class="grade">{{ $gradeName }}</td>
                <td class="remark">{{ $remark }}</td>
                <td class="remark">{{ $remark }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
