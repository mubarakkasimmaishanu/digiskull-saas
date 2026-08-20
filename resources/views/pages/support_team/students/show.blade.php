@extends('layouts.master')

@section('page_title', 'Student Profile - ' . $sr->user->name)

@section('content')

<div class="row">
    <div class="col-md-3 text-center">
        <div class="card">
            <div class="card-body">
                <img style="width: 90%; height: 90%" src="{{ Usr::getTenantAwarePhoto($sr->user->photo) }}" alt="photo" class="rounded-circle">
                <br>
                <h3 class="mt-3">{{ $sr->user->name }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="nav-item">
                        <a href="#basic-info" class="nav-link active" data-toggle="tab">Basic Info</a>
                    </li>
                    <li class="nav-item">
                        <a href="#school-info" class="nav-link" data-toggle="tab">School Info</a>
                    </li>
                    <li class="nav-item">
                        <a href="#subjects-info" class="nav-link" data-toggle="tab">Inherited Subjects</a>
                    </li>
                    <li class="nav-item">
                        <a href="#attendance-info" class="nav-link" data-toggle="tab">Attendance</a>
                    </li>
                </ul>

                <div class="tab-content">
                    {{--Basic Info--}}
                    <div class="tab-pane fade show active" id="basic-info">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="font-weight-bold">Name</td>
                                    <td>{{ $sr->user->name }}</td>
                                </tr>
                                @if($sr->my_parent_id)
                                <tr>
                                    <td class="font-weight-bold">Parent</td>
                                    <td>
                                        <span><a target="_blank" href="{{ route('users.show', Qs::hash($sr->my_parent_id)) }}">{{ $sr->my_parent->name }}</a></span>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="font-weight-bold">Gender</td>
                                    <td>{{ $sr->user->gender }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Address</td>
                                    <td>{{ $sr->user->address }}</td>
                                </tr>
                                @if($sr->user->email)
                                <tr>
                                    <td class="font-weight-bold">Email</td>
                                    <td>{{ $sr->user->email }}</td>
                                </tr>
                                @endif
                                @if($sr->user->phone)
                                <tr>
                                    <td class="font-weight-bold">Phone</td>
                                    <td>{{ $sr->user->phone.' '.$sr->user->phone2 }}</td>
                                </tr>
                                @endif
                                @if($sr->user->dob)
                                <tr>
                                    <td class="font-weight-bold">Birthday</td>
                                    <td>{{ $sr->user->dob }}</td>
                                </tr>
                                @endif
                                @if($sr->user->bg_id)
                                <tr>
                                    <td class="font-weight-bold">Blood Group</td>
                                    <td>{{ $sr->user->blood_group->name }}</td>
                                </tr>
                                @endif
                                @if($sr->user->nal_id)
                                <tr>
                                    <td class="font-weight-bold">Nationality</td>
                                    <td>{{ $sr->user->nationality->name }}</td>
                                </tr>
                                @endif
                                @if($sr->user->state_id)
                                <tr>
                                    <td class="font-weight-bold">State</td>
                                    <td>{{ $sr->user->state->name }}</td>
                                </tr>
                                @endif
                                @if($sr->user->lga_id)
                                <tr>
                                    <td class="font-weight-bold">LGA</td>
                                    <td>{{ $sr->user->lga->name }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="font-weight-bold">Religion</td>
                                    <td>{{ $sr->user->religion }}</td>
                                </tr>
                                @if($sr->food_taboos)
                                <tr>
                                    <td class="font-weight-bold">Food Taboos</td>
                                    <td>{{$sr->food_taboos }}</td>
                                </tr>
                                @endif
                                @if($sr->disability)
                                <tr>
                                    <td class="font-weight-bold">Disability</td>
                                    <td>{{$sr->disability }}</td>
                                </tr>
                                @endif
                                @if($sr->chp)
                                <tr>
                                    <td class="font-weight-bold">Chronic Health Problem</td>
                                    <td>{{$sr->chp }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{--School Info--}}
                    <div class="tab-pane fade show" id="school-info">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="font-weight-bold">Status</td>
                                    <td>{{ $sr->grad == 1 ? "Graduate ({$sr->grad_date})" : 'Active' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Admission Number</td>
                                    <td>{{ $sr->adm_no }}</td>
                                </tr>
                                @if($sr->p_status)
                                <tr>
                                    <td class="font-weight-bold">Parental status</td>
                                    <td>{{ $sr->p_status }}</td>
                                </tr>
                                @endif
                                @if($sr->ps_name)
                                <tr>
                                    <td class="font-weight-bold">Primary School</td>
                                    <td>{{ $sr->ps_name }}</td>
                                </tr>
                                @endif
                                @if($sr->ss_name)
                                <tr>
                                    <td class="font-weight-bold">Secondary School</td>
                                    <td>{{ $sr->ss_name }}</td>
                                </tr>
                                @endif
                                @if($sr->house_no)
                                <tr>
                                    <td class="font-weight-bold">House Number</td>
                                    <td>{{ $sr->house_no }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="font-weight-bold">Class</td>
                                    <td>{{ $sr->my_class->name }} - {{ $sr->section->name }}</td>
                                </tr>
                                @if($sr->user->nal_id)
                                <tr>
                                    <td class="font-weight-bold">Nationality</td>
                                    <td>{{ $sr->user->nationality->name }}</td>
                                </tr>
                                @endif
                                @if($sr->user->state_id)
                                <tr>
                                    <td class="font-weight-bold">State</td>
                                    <td>{{ $sr->user->state->name }}</td>
                                </tr>
                                @endif
                                @if($sr->user->lga_id)
                                <tr>
                                    <td class="font-weight-bold">LGA</td>
                                    <td>{{ $sr->user->lga->name }}</td>
                                </tr>
                                @endif
                                @if($sr->date_admitted)
                                <tr>
                                    <td class="font-weight-bold">Date Admitted</td>
                                    <td>{{$sr->date_admitted }}</td>
                                </tr>
                                @endif
                                @if($sr->birth_certificate && Qs::userIsTeamSA())
                                <tr>
                                    <td class="font-weight-bold">Birth Certificate</td>
                                    <td><a href="{{ tenant_asset($sr->birth_certificate) }}">View</a></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{--Inherited Subjects--}}
                    <div class="tab-pane fade" id="subjects-info">
                        <div class="alert alert-info border-0 mb-3">
                            <i class="material-symbols-rounded align-middle mr-1">info</i>
                            Subjects are configured at the <strong>Class Level ({{ $sr->my_class->name ?? 'Class' }})</strong>. This student automatically inherits all subjects offered by their class.
                        </div>
                        <table class="table table-bordered table-striped">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th style="width: 50px;">S/N</th>
                                    <th>Subject Name</th>
                                    <th>Short Code</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $class_subjects = $sr->my_class ? $sr->my_class->subjects()->orderBy('name')->get() : collect([]);
                                @endphp
                                @forelse($class_subjects as $sub)
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td class="font-weight-bold">{{ $sub->name }}</td>
                                        <td><span class="badge badge-light font-weight-semibold">{{ $sub->slug }}</span></td>
                                        <td>{{ $sub->core ? 'Core Subject' : 'Elective' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No subjects configured for this class yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{--Attendance Info--}}
                    <div class="tab-pane fade" id="attendance-info">
                        @php
                            $attRepo = app(\App\Repositories\AttendanceRepo::class);
                            $attStats = $attRepo->getStudentAttendanceStats($sr->id);
                        @endphp
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card card-body bg-light border text-center mb-2">
                                    <h4 class="font-weight-bold text-primary mb-0">{{ $attStats['total_days'] }}</h4>
                                    <span class="text-muted font-size-xs">Days School Opened</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-body bg-light border text-center mb-2">
                                    <h4 class="font-weight-bold text-success mb-0">{{ $attStats['present_days'] }}</h4>
                                    <span class="text-muted font-size-xs">Days Present</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-body bg-light border text-center mb-2">
                                    <h4 class="font-weight-bold text-danger mb-0">{{ $attStats['absent_days'] }}</h4>
                                    <span class="text-muted font-size-xs">Days Absent</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-3 bg-light border rounded mb-3">
                            <div>
                                <span class="font-weight-bold">Attendance Rate:</span>
                                <span class="badge {{ $attStats['percentage'] >= 75 ? 'badge-success' : ($attStats['percentage'] >= 50 ? 'badge-warning' : 'badge-danger') }} font-size-sm ml-2">
                                    {{ $attStats['percentage'] }}%
                                </span>
                            </div>
                            <a href="{{ route('attendance.student_report', $sr->id) }}" class="btn btn-sm btn-primary">
                                <i class="material-symbols-rounded font-size-sm mr-1">history</i> View Full Attendance Log
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{--Student Profile Ends--}}

@endsection
