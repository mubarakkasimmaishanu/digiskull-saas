{{--============ TEACHER WORKSPACES ============--}}
@if(Qs::userIsTeacher())
<li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-xs">Teacher Workspaces</div> <i class="material-symbols-rounded" title="Teacher Workspaces">school</i></li>

@if(Qs::userIsClassSectionTeacher())
<li class="nav-item">
    <a href="{{ route('teacher.my_class') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['teacher.my_class', 'teacher.class_detail', 'teacher.academic_review']) ? 'active' : '' }}">
        <i class="material-symbols-rounded">meeting_room</i>
        <span>My Class (Class Teacher)</span>
    </a>
</li>
@endif

<li class="nav-item">
    <a href="{{ route('teacher.my_subjects') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['teacher.my_subjects', 'teacher.subject_students', 'teacher.scores.index']) ? 'active' : '' }}">
        <i class="material-symbols-rounded">menu_book</i>
        <span>My Subjects</span>
    </a>
</li>
@endif
