{{-- School header: logo (left), school title + tagline + report banner (centre), crest placeholder (right). --}}
@php
    $logoPath = $settings->where('type', 'logo')->value('description');
    $schoolTagline = $settings->where('type', 'tagline')->value('description') ?: 'Excellence in Education';
    $classType = strtoupper(($class_type->name ?? 'Senior Secondary'));
@endphp

<header class="ss-report__header">
    <div class="ss-report__logo">
        @if($logoPath)
            <img src="{{ tenant_asset($logoPath) }}" alt="{{ Qs::getSetting('system_name') }} logo">
        @else
            LOGO
        @endif
    </div>

    <div class="ss-report__title">
        <h1 class="ss-report__title-school">{{ strtoupper(Qs::getSetting('system_name')) }}</h1>
        <p class="ss-report__title-tagline">{{ $schoolTagline }}</p>
        <h2 class="ss-report__title-report">{{ $classType }} SCHOOL REPORT</h2>
    </div>

    <div class="ss-report__crest">CREST</div>
</header>
