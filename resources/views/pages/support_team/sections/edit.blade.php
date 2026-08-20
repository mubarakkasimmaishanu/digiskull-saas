@extends('layouts.master')

@section('page_title', "Edit Section of {$s->my_class->name}")

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Edit Section of {{ $s->my_class->name }}</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <form class="ajax-update" method="post" action="{{ route('sections.update', $s->id) }}">
                    @csrf @method('PUT')
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input name="name" value="{{ $s->name }}" required type="text" class="form-control" placeholder="Name of Class">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold">Class </label>
                        <div class="col-lg-9">
                            <input class="form-control" id="my_class_id" disabled="disabled" type="text" value="{{ $s->my_class->name }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="teacher_id" class="col-lg-3 col-form-label font-weight-semibold">Teacher</label>
                        <div class="col-lg-9">
                            <select data-placeholder="Select Teacher" class="form-control select-search" name="teacher_id" id="teacher_id">
                                <option value=""></option>
                                @foreach($teachers as $t)
                                <option @selected($s->teacher_id == $t->id) value="{{ Qs::hash($t->id) }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Submit form <i class="material-symbols-rounded ml-2">send</i></button>
                    </div>
                </form>

                {{--
                    Per-section classroom-teacher permission override is
                    the rare-case escape hatch. The primary configuration
                    surface is the school-wide page under School Rules;
                    99% of admins should never open the block below.
                --}}
                <details class="mt-4">
                    <summary class="text-muted" style="cursor: pointer;">
                        <i class="material-symbols-rounded" style="vertical-align: middle;">tune</i>
                        Advanced — override classroom-teacher rules for this section only
                    </summary>
                    <div class="p-3 border rounded mt-2" style="background: #f9f9f9;">
                        <p class="text-muted small mb-2">
                            The school-wide defaults live under
                            <a href="{{ route('sections.school_defaults') }}">School Rules</a>
                            and cover every section. Only open the override page below
                            if this section genuinely needs different rules from the
                            rest of the school.
                        </p>
                        <a href="{{ route('sections.classroom_permissions', $s->id) }}"
                           class="btn btn-light btn-sm">
                            <i class="material-symbols-rounded mr-1">tune</i>
                            Open section override for {{ $s->my_class->name }} {{ $s->name }}
                        </a>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>

{{--Section Edit Ends--}}

@endsection
