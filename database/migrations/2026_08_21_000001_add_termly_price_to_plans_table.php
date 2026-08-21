<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('plans', 'termly_price')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->decimal('termly_price', 10, 2)->default(30000.00)->after('monthly_price');
            });
        }

        // Update default plans to 1-term (90 days) free trial with termly & yearly pricing
        DB::table('plans')->where('slug', 'basic')->update([
            'termly_price' => 30000.00,
            'yearly_price' => 80000.00,
            'trial_days' => 90,
        ]);

        DB::table('plans')->where('slug', 'standard')->update([
            'termly_price' => 65000.00,
            'yearly_price' => 175000.00,
            'trial_days' => 90,
        ]);

        DB::table('plans')->where('slug', 'premium')->update([
            'termly_price' => 130000.00,
            'yearly_price' => 350000.00,
            'trial_days' => 90,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('plans', 'termly_price')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('termly_price');
            });
        }
    }
};
