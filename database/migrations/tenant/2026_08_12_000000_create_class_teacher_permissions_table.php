<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-section classroom-teacher permission toggles (spec §19).
 *
 * A single row with `section_id = NULL` acts as the tenant-wide default so
 * every new section inherits the same policy until an administrator chooses
 * to override it. Later increments (attendance, report cards, guardian
 * messaging) resolve their gates through ClassroomPolicy which consults
 * these flags — never hardcoded in controllers.
 *
 *  - can_edit_subject_marks  : false by default (spec §6 explicit rule —
 *                              a CT never overrides another subject teacher
 *                              unless the school administrator opts in)
 *  - can_correct_attendance  : true  (matches the school's daily register
 *                              workflow — spec §5, user chose "default ON")
 *  - can_edit_teacher_comments: true (CT owns report-card comments,
 *                              user chose "default ON")
 *  - can_message_parents     : true  (CT communicates with class parents,
 *                              spec §8)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teacher_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('section_id')->nullable()->unique()
                ->comment('NULL = tenant-wide default row');

            $table->boolean('can_edit_subject_marks')->default(false);
            $table->boolean('can_correct_attendance')->default(true);
            $table->boolean('can_edit_teacher_comments')->default(true);
            $table->boolean('can_message_parents')->default(true);

            $table->unsignedSmallInteger('attendance_correction_window_hours')->default(48);

            $table->enum('report_card_approval', ['none', 'head_teacher', 'principal'])
                ->default('head_teacher');

            $table->timestamps();

            $table->foreign('section_id', 'ctperm_section_fk')
                ->references('id')->on('sections')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        // Seed the tenant-wide default row so ClassroomPolicy always has
        // something to fall back on for freshly created tenants.
        \Illuminate\Support\Facades\DB::table('class_teacher_permissions')->insert([
            'section_id' => null,
            'can_edit_subject_marks' => false,
            'can_correct_attendance' => true,
            'can_edit_teacher_comments' => true,
            'can_message_parents' => true,
            'attendance_correction_window_hours' => 48,
            'report_card_approval' => 'head_teacher',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher_permissions');
    }
};
