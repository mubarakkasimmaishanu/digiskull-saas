@extends('layouts.master')

@section('page_title', 'Subject Teacher Dashboard - My Assigned Subjects')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <div>
            <h5 class="card-title font-weight-bold mb-0">
                <i class="material-symbols-rounded align-middle mr-1 text-primary">menu_book</i>
                Subject Teacher Dashboard: Assigned Subjects &amp; Classes
            </h5>
            <span class="text-muted font-size-sm">Access student rosters and score entries through your assigned subjects and classes</span>
        </div>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="alert alert-info border-0 alert-dismissible mb-4">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <div class="d-flex align-items-start">
                <i class="material-symbols-rounded mr-2 font-size-24">info</i>
                <div>
                    <strong>Subject Teacher Access Model:</strong>
                    The system assigns subjects to you at the class/section level (e.g. <em>Teacher &rarr; Subject &rarr; Assigned Class &rarr; Students</em>).
                    Students in your assigned classes are automatically available in your roster via their class membership.
                </div>
            </div>
        </div>

        @if(count($grouped_subjects) > 0)
            <div class="row">
                @foreach($grouped_subjects as $sub_id => $group)
                    @php
                        $subject = $group['subject'];
                    @endphp
                    <div class="col-md-6 col-xl-4 mb-3">
                        <div class="card border shadow-sm h-100 rounded-lg">
                            <div class="card-header bg-slate-800 text-white d-flex justify-content-between align-items-center py-2">
                                <h6 class="font-weight-bold mb-0 text-white">
                                    <i class="material-symbols-rounded font-size-sm mr-1 align-middle">book</i>
                                    {{ $subject->name }}
                                </h6>
                                <span class="badge badge-primary">{{ $subject->slug }}</span>
                            </div>

                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="text-muted font-size-sm font-weight-bold">Assigned Class Tier:</span>
                                    <span class="badge badge-secondary ml-1">{{ $group['class_name'] }}</span>
                                </div>

                                <h6 class="font-weight-semibold text-primary mt-3 mb-2 font-size-sm border-bottom pb-1">
                                    <i class="material-symbols-rounded font-size-xs align-middle mr-1">school</i>
                                    Assigned Class:
                                </h6>

                                <div class="list-group list-group-flush mb-3">
                                    @foreach($group['assignments'] as $assign)
                                        <div class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="font-size-sm">{{ $assign['class_name'] }}</strong>
                                                <div class="text-muted font-size-xs">
                                                    {{ $assign['students_count'] }} Students (Class Membership)
                                                </div>
                                            </div>
                                            @if($assign['section_id'])
                                                <a href="{{ route('teacher.subject_students', [$subject->id, $assign['section_id']]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="material-symbols-rounded font-size-sm mr-1">visibility</i> View Students
                                                </a>
                                            @else
                                                <span class="badge badge-secondary">All Classes</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-warning border-0 text-center py-4">
                <i class="material-symbols-rounded font-size-48 text-warning mb-2">menu_book</i>
                <h6 class="font-weight-bold">No Subjects Assigned</h6>
                <p class="text-muted mb-0">You have not been assigned to teach any subjects yet. An administrator can assign you to subjects in the Subjects management module.</p>
            </div>
        @endif
    </div>
</div>

@endsection
