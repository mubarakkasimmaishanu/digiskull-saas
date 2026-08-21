@extends('layouts.saas_master')

@section('page_title', 'Onboarding Complete — School Portal Ready')

@section('content')
<div class="container my-5 py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="glass-card p-5 shadow-2xl">
                <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);">
                    <i class="material-symbols-rounded text-success" style="font-size: 56px;">check_circle</i>
                </div>
                
                <h2 class="text-white font-weight-bold mb-1">School Portal Activated!</h2>
                <h4 class="brand-gradient mb-2 font-weight-bold">{{ $tenant->name }}</h4>
                
                <div class="d-inline-flex align-items-center mb-3 px-3 py-1 rounded-pill" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3);">
                    <i class="material-symbols-rounded text-info mr-1 font-size-sm">verified</i>
                    <span class="text-info font-weight-bold font-size-xs">1-Term Free Trial Active (90 Days) &#183; &#8358;0.00 Billed Today</span>
                </div>

                <p class="text-muted mb-4 font-size-sm">
                    Your dedicated database and subdomain have been provisioned successfully. You can now log into your Super Administrator portal.
                </p>
                
                @php
                    $host = request()->getHost();
                    $tenantDomain = $tenant->domain ? $tenant->domain->domain : null;
                    $protocol = request()->secure() ? 'https://' : 'http://';
                    $port = request()->getPort();
                    $redirectUrl = $protocol . $tenantDomain;
                    if ($port && !in_array($port, [80, 443]) && !str_contains($tenantDomain, ':')) {
                        $redirectUrl .= ':' . $port;
                    }
                @endphp

                <div class="p-3 rounded-3 mb-4" style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.3);">
                    <div class="text-muted small mb-1">Your School Portal Web Address:</div>
                    <a href="{{ $redirectUrl }}" class="h5 text-info font-weight-bold text-break mb-0 d-block">{{ $redirectUrl }}</a>
                </div>

                <a href="{{ $redirectUrl }}" class="btn btn-gradient-primary btn-lg btn-block py-3 rounded-pill mb-3 font-weight-bold shadow-lg">
                    Launch School Portal <i class="material-symbols-rounded align-middle ml-1">arrow_forward</i>
                </a>

                <p class="text-muted small mb-0">
                    <i class="material-symbols-rounded spinner align-middle mr-1 font-size-xs">sync</i>
                    Auto-redirecting in <span id="countdown" class="text-info font-weight-bold">6</span> seconds...
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    var sec = 6;
    var timer = setInterval(function() {
        sec--;
        var cd = document.getElementById('countdown');
        if (cd) cd.innerHTML = sec;
        if (sec <= 0) {
            clearInterval(timer);
            window.location.href = "{{ $redirectUrl }}";
        }
    }, 1000);
</script>
@endsection
