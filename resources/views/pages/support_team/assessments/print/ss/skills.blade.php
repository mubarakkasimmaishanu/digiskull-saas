{{--
  Two-column skills grid: Affective on the left, Psychomotor on the right.

  The mockup renders Affective as a numbered checklist and Psychomotor as
  a "A B C D E" rating strip with a check under the achieved band. Both
  columns are driven by the shared $skills collection filtered by skill_type,
  and the ratings come from exam_records.af / exam_records.ps stored as
  a comma-separated 1-5 string. A 1-5 rating is mapped onto the E-A band
  (5 => A, 1 => E) so the printout stays readable without any schema change.
--}}
@php
    /**
     * Turn a numeric 1-5 rating into a check position across the
     * A B C D E band. Returns null when the rating is missing so the
     * row renders as blank instead of pinning to a default.
     */
    $mapBand = function ($ratingCsv, $index) {
        if (!$ratingCsv) return null;
        $parts = explode(',', $ratingCsv);
        if (!isset($parts[$index])) return null;
        $val = (int) $parts[$index];
        return match ($val) {
            5 => 'A',
            4 => 'B',
            3 => 'C',
            2 => 'D',
            1 => 'E',
            default => null,
        };
    };

    $affective   = $skills ? $skills->where('skill_type', 'AF')->values() : collect();
    $psychomotor = $skills ? $skills->where('skill_type', 'PS')->values() : collect();
@endphp

<section class="ss-report__skills">
    <div class="ss-report__skill-card">
        <h4>Assessments in Affective Domain</h4>
        <table>
            <tbody>
                @forelse($affective as $af)
                    @php $band = $mapBand($asr->af, $loop->index); @endphp
                    <tr>
                        <td>{{ $loop->iteration }}. {{ $af->name }}</td>
                        <td style="text-align:right; width:24px; color: var(--sr-primary); font-weight:700;">
                            {{ $band ? '✓' : '' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" style="color: var(--sr-muted); font-style: italic;">No affective traits configured for this class type.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ss-report__skill-card">
        <h4>Psychomotor Domain</h4>
        <table>
            <thead>
                <tr>
                    <th>Skill Criteria</th>
                    <th class="rating-head">A&nbsp;&nbsp;B&nbsp;&nbsp;C&nbsp;&nbsp;D&nbsp;&nbsp;E</th>
                    <th class="rating-head">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($psychomotor as $ps)
                    @php
                        $band = $mapBand($asr->ps, $loop->index);
                        // Build the "  ✓  " strip so the check sits under the right letter.
                        $bandRow = collect(['A','B','C','D','E'])
                            ->map(fn($b) => $b === $band ? '✓' : '·')
                            ->implode(' ');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}. {{ $ps->name }}</td>
                        <td class="rating">{{ $bandRow }}</td>
                        <td class="remark">{{ $band ? "Rated {$band}" : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="color: var(--sr-muted); font-style: italic;">No psychomotor skills configured for this class type.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
