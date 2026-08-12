{{-- Student identification block + "Next Term" summary box (top-right in the mockup). --}}
@php
    // Term labels + dates come from the tenant Settings singleton; the
    // fee is class-scoped so a school can charge different amounts per
    // class (e.g. next_term_fees_ss1, next_term_fees_ss2). Falls back to
    // the generic next_term_fees setting when no class-scoped value exists.
    $termBegins = Qs::getSetting('term_begins');
    $termEnds   = Qs::getSetting('term_ends');
    $ctCode     = strtolower($my_class->name ?? '');
    $nextFees   = Qs::getSetting("next_term_fees_{$ctCode}") ?: Qs::getSetting('next_term_fees');

    $classCount = \App\Helpers\Mk::getClassCount($ex->id, $my_class->id);
@endphp

<section class="ss-report__meta">
    <div class="ss-report__student">
        <h3>{{ strtoupper($my_class->name) }} CLASS</h3>
        <p><strong>Name:</strong> <span class="name">{{ $sr->user->name }}</span></p>
        <p>
            <strong>Class:</strong> {{ $my_class->name }} &nbsp;
            <strong>Sex:</strong> {{ ucfirst($sr->user->gender ?? '-') }}
        </p>
        <p>
            <strong>No. In Class:</strong> {{ $classCount }} &nbsp;
            <strong>Term/Session:</strong> {{ \App\Helpers\Mk::getSuffix($ex->term) }} Term / {{ $ex->year }}
        </p>
    </div>

    <table class="ss-report__next-term">
        <thead>
            <tr>
                <th>Next Term<br>Begins</th>
                <th>Next Term<br>Fees</th>
                <th>Next Term<br>Ends</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $termBegins ?: '—' }}</td>
                <td>{{ $nextFees ? '₦' . number_format((float) preg_replace('/[^0-9.]/', '', $nextFees)) : '—' }}</td>
                <td>{{ $termEnds ?: '—' }}</td>
            </tr>
        </tbody>
    </table>
</section>
