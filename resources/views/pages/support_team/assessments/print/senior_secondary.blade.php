{{--
  Senior Secondary School Report Card (new UI, purple theme).

  This is the top-level print page introduced by the Report Cards
  refactor. It reuses the same `$asr`, `$sr`, `$my_class`, `$ex`,
  `$subjects`, `$assessments_records`, `$skills`, `$class_type`,
  `$settings` variables that `print_detailed.blade.php` receives so
  the controller wiring stays parallel with the existing print flows.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Report Card (SS) — {{ $sr->user->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/report_card_ss.css') }}">
    @laravelPWA
</head>
<body>
    @php
        // Tenant-scoped palette. Qs::getReportTheme() returns a whitelisted
        // + hex-validated set of colors so a rogue settings row can't inject
        // CSS. Any slot the tenant hasn't set falls back to a neutral default
        // baked into the stylesheet.
        $theme = \App\Helpers\Qs::getReportTheme();
        $themeStyle = sprintf(
            '--sr-primary:%s;--sr-soft:%s;--sr-border:%s;--sr-accent:%s;',
            $theme['primary'], $theme['soft'], $theme['border'], $theme['accent']
        );
    @endphp

    <article class="ss-report" style="{{ $themeStyle }}">
        @include('pages.support_team.assessments.print.ss.header')

        @include('pages.support_team.assessments.print.ss.meta')

        @include('pages.support_team.assessments.print.ss.subjects_table')

        @include('pages.support_team.assessments.print.ss.skills')

        @include('pages.support_team.assessments.print.ss.attendance')

        @include('pages.support_team.assessments.print.ss.remarks_block')
    </article>

    <script>
        // Auto-open the print dialog once the page (and its stylesheet) is
        // fully loaded, matching the behaviour of the existing detailed /
        // minimal print variants. Users can still cancel and read on-screen.
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
