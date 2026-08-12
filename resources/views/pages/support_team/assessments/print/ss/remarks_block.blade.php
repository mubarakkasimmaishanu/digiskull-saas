{{--
  Three-tier remarks block at the foot of the report:
    - CLASS TUTOR (t_comment)      | signature
    - EXAMINATION OFFICER          | (shares the sig/date column with Principal)
    - PRINCIPAL   (p_comment)      | date

  The Form Master/Mistress column on the right holds the signature +
  date cells (rowspan) so the block mirrors the mockup exactly.
--}}
@php
    $tutorComment       = $asr->t_comment            ?: '—';
    $examOfficerComment = $asr->exam_officer_comment ?: '—';
    $principalComment   = $asr->p_comment            ?: '—';
    $today              = now()->format('d/m/Y');
@endphp

<table class="ss-report__remarks">
    <thead>
        <tr>
            <th style="width:150px;">Class Tutor</th>
            <th style="text-align:left;">Remarks</th>
            <th style="width:160px; text-align:center;">Form Master/Mistress</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>Class Tutor</th>
            <td class="remark-body">{{ $tutorComment }}</td>
            <td class="signature">
                SIGNATURE
                <span class="label">Signed</span>
            </td>
        </tr>
        <tr>
            <th>Examination Officer</th>
            <td class="remark-body">{{ $examOfficerComment }}</td>
            <td class="signature" rowspan="2">
                {{ $today }}
                <span class="label">Date</span>
            </td>
        </tr>
        <tr>
            <th>Principal</th>
            <td class="remark-body">{{ $principalComment }}</td>
        </tr>
    </tbody>
</table>
