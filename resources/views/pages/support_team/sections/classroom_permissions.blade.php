@extends('layouts.master')

{{--
    Classroom-teacher rules editor (spec §19).

    Runs in two modes, keyed on $mode / $isDefault:
      - 'default' : editing the tenant-wide row (section_id = NULL) —
                    the mass-market entry point (School Rules menu).
      - 'section' : editing a per-section override — hidden behind an
                    Advanced expander on sections.edit.

    UX shape (mass-market Nigerian school context):
      - A single 3-way preset (Strict / Balanced / Trusting) drives the
        four boolean toggles. Most admins never expand the "Customize
        individual rules" details block below it.
      - Attendance correction is a plain-language dropdown; the raw
        hours are stored server-side but never surfaced by default.
      - Report-card approval is a single Yes/No that maps to
        principal | none. `head_teacher` is still in the DB enum for
        schools that write it via import/API — we just don't expose
        that tier in the UI. Existing rows with head_teacher are
        preserved on edit (see approval JS below).
--}}

@php
    $isDefault = ($mode ?? 'section') === 'default';

    $formAction = $isDefault
        ? route('sections.school_defaults.update')
        : route('sections.classroom_permissions.update', $s->id);

    $backHref = $isDefault
        ? route('sections.index')
        : route('sections.edit', $s->id);

    $title = $isDefault
        ? 'Classroom-Teacher Rules — school defaults'
        : "Classroom-Teacher Permissions — {$s->my_class->name} {$s->name}";

    // Map the stored booleans → preset id. When they don't match a
    // canonical preset we mark 'custom' so the Advanced block opens
    // automatically and the admin sees exactly what's set.
    $presets = [
        'strict'   => ['can_edit_subject_marks'=>false,'can_correct_attendance'=>false,'can_edit_teacher_comments'=>false,'can_message_parents'=>false],
        'balanced' => ['can_edit_subject_marks'=>false,'can_correct_attendance'=>true, 'can_edit_teacher_comments'=>true, 'can_message_parents'=>true],
        'trusting' => ['can_edit_subject_marks'=>true, 'can_correct_attendance'=>true, 'can_edit_teacher_comments'=>true, 'can_message_parents'=>true],
    ];
    $current = [
        'can_edit_subject_marks'    => (bool)($perm->can_edit_subject_marks    ?? false),
        'can_correct_attendance'    => (bool)($perm->can_correct_attendance    ?? true),
        'can_edit_teacher_comments' => (bool)($perm->can_edit_teacher_comments ?? true),
        'can_message_parents'       => (bool)($perm->can_message_parents       ?? true),
    ];
    $activePreset = 'custom';
    foreach ($presets as $id => $shape) {
        if ($shape == $current) { $activePreset = $id; break; }
    }

    // Attendance window → dropdown bucket. Anything not on the bucket
    // values counts as 'custom' so we don't silently round it away.
    $windowHours  = (int)($perm->attendance_correction_window_hours ?? 48);
    $windowBucket = match ($windowHours) {
        24  => 'today',
        168 => 'week',
        720 => 'always',
        default => 'custom',
    };

    // Approval: expose as Yes/No. head_teacher rolls up as "Yes".
    $approval    = $perm->report_card_approval ?? 'head_teacher';
    $needsApprov = in_array($approval, ['head_teacher','principal'], true);
@endphp

@section('page_title', $title)

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">
            @if($isDefault)
                Classroom-Teacher Rules
                <small class="text-muted">— apply to every section unless overridden</small>
            @else
                Classroom-Teacher Permissions
                <small class="text-muted">— {{ $s->my_class->name }} {{ $s->name }} (override for this section only)</small>
            @endif
        </h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        @if($isDefault)
            <p class="text-muted mb-4">
                These rules apply to <strong>every classroom teacher across the
                whole school</strong>. You almost never need to change them
                section by section — set the school-wide policy once here and
                every section inherits it.
            </p>
        @else
            <div class="alert alert-warning">
                <strong>You're overriding the school-wide rules for this section only.</strong>
                Most schools should manage rules from
                <a href="{{ route('sections.school_defaults') }}">School Rules</a>
                instead — this page is for the rare case where one section
                needs different rules from the rest.
                @if($def)
                    <br><small class="text-muted">
                        School default: marks-edit <strong>{{ $def->can_edit_subject_marks ? 'on' : 'off' }}</strong>,
                        attendance-correct <strong>{{ $def->can_correct_attendance ? 'on' : 'off' }}</strong>,
                        comment-edit <strong>{{ $def->can_edit_teacher_comments ? 'on' : 'off' }}</strong>,
                        parent-messages <strong>{{ $def->can_message_parents ? 'on' : 'off' }}</strong>.
                    </small>
                @endif
            </div>
        @endif

        <form class="ajax-update" method="post" action="{{ $formAction }}" id="ct-perm-form">
            @csrf @method('PUT')

            {{-- ============ PRESET SELECTOR ============ --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label font-weight-semibold">
                    How much can the classroom teacher do?
                </label>
                <div class="col-md-8">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" name="ct_preset" value="strict" class="form-check-input" @checked($activePreset==='strict')>
                            <strong>Strict</strong> — the CT can only view. All edits go through admin.
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" name="ct_preset" value="balanced" class="form-check-input" @checked($activePreset==='balanced')>
                            <strong>Balanced (recommended)</strong> — CT corrects attendance, edits report comments, messages parents. Cannot overwrite other teachers' marks.
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" name="ct_preset" value="trusting" class="form-check-input" @checked($activePreset==='trusting')>
                            <strong>Trusting</strong> — CT can do everything, including editing marks entered by subject teachers.
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" name="ct_preset" value="custom" class="form-check-input" @checked($activePreset==='custom')>
                            <strong>Custom</strong> — set each rule individually below.
                        </label>
                    </div>
                </div>
            </div>

            {{-- Hidden inputs the server actually validates. JS mirrors
                 the preset radios and the raw Advanced toggles into
                 these four names. --}}
            <input type="hidden" name="can_edit_subject_marks"    id="f_can_edit_subject_marks"    value="{{ (int)$current['can_edit_subject_marks'] }}">
            <input type="hidden" name="can_correct_attendance"    id="f_can_correct_attendance"    value="{{ (int)$current['can_correct_attendance'] }}">
            <input type="hidden" name="can_edit_teacher_comments" id="f_can_edit_teacher_comments" value="{{ (int)$current['can_edit_teacher_comments'] }}">
            <input type="hidden" name="can_message_parents"       id="f_can_message_parents"       value="{{ (int)$current['can_message_parents'] }}">

            <hr>

            {{-- ============ ATTENDANCE CORRECTION WINDOW ============ --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label font-weight-semibold">
                    When can the CT correct attendance?
                </label>
                <div class="col-md-8">
                    <select id="attendance_window_bucket" class="form-control">
                        <option value="today"  @selected($windowBucket==='today')>Today only</option>
                        <option value="week"   @selected($windowBucket==='week')>This week</option>
                        <option value="always" @selected($windowBucket==='always')>Anytime</option>
                        @if($windowBucket==='custom')
                            <option value="custom" selected>Custom ({{ $windowHours }} hours)</option>
                        @endif
                    </select>
                    <small class="text-muted">Corrections made outside this window are refused.</small>
                </div>
            </div>
            <input type="hidden" name="attendance_correction_window_hours" id="f_attendance_correction_window_hours" value="{{ $windowHours }}">

            {{-- ============ REPORT-CARD APPROVAL ============ --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label font-weight-semibold">
                    Must report cards be approved before publishing?
                </label>
                <div class="col-md-8">
                    <select id="approval_yesno" class="form-control">
                        <option value="yes" @selected($needsApprov)>Yes — the principal must approve</option>
                        <option value="no"  @selected(!$needsApprov)>No — the classroom teacher publishes directly</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="report_card_approval" id="f_report_card_approval" value="{{ $approval }}">

            {{-- ============ ADVANCED (raw toggles) ============ --}}
            <details class="mt-4" @if($activePreset==='custom' || $windowBucket==='custom') open @endif>
                <summary class="text-muted mb-3" style="cursor: pointer;">
                    <i class="material-symbols-rounded" style="vertical-align: middle;">tune</i>
                    Customize individual rules (advanced)
                </summary>

                <div class="p-3 border rounded" style="background: #f9f9f9;">
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label">
                            Edit marks entered by other subject teachers
                            <br><small class="text-muted">Spec §6 — leave OFF unless you want the CT to overwrite subject teachers.</small>
                        </label>
                        <div class="col-md-7">
                            <select id="raw_marks" class="form-control">
                                <option value="0" @selected(!$current['can_edit_subject_marks'])>No (recommended)</option>
                                <option value="1" @selected($current['can_edit_subject_marks'])>Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label">Correct attendance after saving</label>
                        <div class="col-md-7">
                            <select id="raw_att" class="form-control">
                                <option value="1" @selected($current['can_correct_attendance'])>Yes</option>
                                <option value="0" @selected(!$current['can_correct_attendance'])>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label">Edit auto-generated report-card comments</label>
                        <div class="col-md-7">
                            <select id="raw_comments" class="form-control">
                                <option value="1" @selected($current['can_edit_teacher_comments'])>Yes</option>
                                <option value="0" @selected(!$current['can_edit_teacher_comments'])>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label">Send messages to parents/guardians</label>
                        <div class="col-md-7">
                            <select id="raw_msg" class="form-control">
                                <option value="1" @selected($current['can_message_parents'])>Yes</option>
                                <option value="0" @selected(!$current['can_message_parents'])>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 col-form-label">
                            Attendance correction window (raw hours)
                            <br><small class="text-muted">0–720. Overrides the friendly dropdown above.</small>
                        </label>
                        <div class="col-md-7">
                            <input id="raw_hours" type="number" min="0" max="720"
                                   value="{{ $windowHours }}" class="form-control">
                        </div>
                    </div>
                </div>
            </details>

            <div class="text-right mt-4">
                <a href="{{ $backHref }}" class="btn btn-light">
                    {{ $isDefault ? 'Back to sections' : 'Back to section' }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ $isDefault ? 'Save school defaults' : 'Save section override' }}
                    <i class="material-symbols-rounded ml-2">send</i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Sync preset radios, plain-language dropdowns, and the raw Advanced
// toggles into the hidden form fields the server validates. Vanilla
// JS — no jQuery dependency for this page.
(function () {
    const PRESETS = {
        strict:   { marks:0, att:0, comments:0, msg:0 },
        balanced: { marks:0, att:1, comments:1, msg:1 },
        trusting: { marks:1, att:1, comments:1, msg:1 },
    };
    const $ = (id) => document.getElementById(id);

    const fMarks    = $('f_can_edit_subject_marks');
    const fAtt      = $('f_can_correct_attendance');
    const fComments = $('f_can_edit_teacher_comments');
    const fMsg      = $('f_can_message_parents');
    const fHours    = $('f_attendance_correction_window_hours');
    const fApproval = $('f_report_card_approval');

    const rawMarks    = $('raw_marks');
    const rawAtt      = $('raw_att');
    const rawComments = $('raw_comments');
    const rawMsg      = $('raw_msg');
    const rawHours    = $('raw_hours');

    // Preset radios → hidden fields → raw toggles (kept in sync so
    // opening Advanced always shows the truth).
    document.querySelectorAll('input[name="ct_preset"]').forEach(r => {
        r.addEventListener('change', () => {
            const p = PRESETS[r.value];
            if (!p) return; // 'custom' — don't touch, let raw toggles rule
            fMarks.value    = p.marks;    rawMarks.value    = String(p.marks);
            fAtt.value      = p.att;      rawAtt.value      = String(p.att);
            fComments.value = p.comments; rawComments.value = String(p.comments);
            fMsg.value      = p.msg;      rawMsg.value      = String(p.msg);
        });
    });

    // Raw toggles → hidden fields; a manual raw edit flips preset to 'custom'.
    const wireRaw = (rawEl, hiddenEl) => rawEl.addEventListener('change', () => {
        hiddenEl.value = rawEl.value;
        const custom = document.querySelector('input[name="ct_preset"][value="custom"]');
        if (custom) custom.checked = true;
    });
    wireRaw(rawMarks,    fMarks);
    wireRaw(rawAtt,      fAtt);
    wireRaw(rawComments, fComments);
    wireRaw(rawMsg,      fMsg);

    // Attendance window: dropdown ↔ raw hours ↔ hidden hours.
    const BUCKETS = { today: 24, week: 168, always: 720 };
    const bucket = $('attendance_window_bucket');
    bucket.addEventListener('change', () => {
        if (BUCKETS[bucket.value] !== undefined) {
            fHours.value   = BUCKETS[bucket.value];
            rawHours.value = BUCKETS[bucket.value];
        }
    });
    rawHours.addEventListener('input', () => {
        const v = Math.max(0, Math.min(720, parseInt(rawHours.value || '0', 10)));
        fHours.value = v;
        const found = Object.entries(BUCKETS).find(([, h]) => h === v);
        if (found) {
            bucket.value = found[0];
        } else {
            // Add a transient 'custom' option so the dropdown reflects reality.
            let opt = bucket.querySelector('option[value="custom"]');
            if (!opt) {
                opt = document.createElement('option');
                opt.value = 'custom';
                bucket.appendChild(opt);
            }
            opt.textContent = 'Custom (' + v + ' hours)';
            bucket.value = 'custom';
        }
    });

    // Approval Yes/No → enum. 'Yes' preserves the original head_teacher
    // tier for schools that had it set via import; otherwise defaults
    // to 'principal'. 'No' maps to 'none'.
    const approvalSel = $('approval_yesno');
    const originalApproval = fApproval.value;
    approvalSel.addEventListener('change', () => {
        if (approvalSel.value === 'no') {
            fApproval.value = 'none';
        } else {
            fApproval.value = (originalApproval === 'head_teacher') ? 'head_teacher' : 'principal';
        }
    });
})();
</script>

@endsection
