<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dish_sales_summaries', function (Blueprint $table) {
            $table->dropUnique('dish_sales_summaries_dish_id_unique');
            $table->date('summary_date')->nullable()->after('id')->index();
        });

        DB::table('dish_sales_summaries')
            ->whereNull('summary_date')
            ->update(['summary_date' => now()->toDateString()]);

        Schema::table('dish_sales_summaries', function (Blueprint $table) {
            $table->unique(['summary_date', 'dish_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dish_sales_summaries', function (Blueprint $table) {
            $table->dropUnique(['summary_date', 'dish_id']);
            $table->dropColumn('summary_date');
            $table->unique('dish_id');
        });
    }
};
