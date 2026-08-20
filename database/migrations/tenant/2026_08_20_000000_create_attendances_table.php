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
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_record_id')->index('attendances_student_record_id_foreign');
            $table->unsignedInteger('section_id')->index('attendances_section_id_foreign');
            $table->date('date')->index();
            $table->enum('status', ['present', 'absent'])->default('present');
            $table->unsignedBigInteger('marked_by')->nullable()->index('attendances_marked_by_foreign');
            $table->string('session', 30)->nullable()->index();
            $table->timestamps();

            $table->unique(['student_record_id', 'date'], 'student_date_unique');

            $table->foreign('student_record_id')
                ->references('id')->on('student_records')
                ->onDelete('cascade');

            $table->foreign('section_id')
                ->references('id')->on('sections')
                ->onDelete('cascade');

            $table->foreign('marked_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
