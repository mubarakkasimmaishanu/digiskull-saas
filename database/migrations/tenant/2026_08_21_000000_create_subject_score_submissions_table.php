<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject_score_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('exam_id')->index();
            $table->unsignedInteger('my_class_id')->index();
            $table->unsignedInteger('section_id')->index();
            $table->unsignedInteger('subject_id')->index();
            $table->string('year');
            $table->enum('status', ['draft', 'submitted', 'approved', 'locked', 'rejected'])->default('draft');
            $table->unsignedBigInteger('teacher_id')->nullable()->index();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'my_class_id', 'section_id', 'subject_id', 'year'], 'subject_submission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_score_submissions');
    }
};
