<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the Examination Officer's remark to exam_records so the Senior
 * Secondary report card can render the three-tier remarks block
 * (Class Tutor / Examination Officer / Principal) shown in the UI.
 *
 * Kept nullable so this migration is safe to run against tenants that
 * already have exam_records populated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_records', function (Blueprint $table) {
            $table->string('exam_officer_comment')->nullable()->after('p_comment');
        });
    }

    public function down(): void
    {
        Schema::table('exam_records', function (Blueprint $table) {
            $table->dropColumn('exam_officer_comment');
        });
    }
};
