<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dish_sales_summaries', function (Blueprint $table) {
            $table->dropUnique(['summary_date', 'dish_id']);
            $table->foreignId('branch_id')
                ->nullable()
                ->after('summary_date')
                ->constrained('branches')
                ->cascadeOnDelete();
            $table->unique(['summary_date', 'branch_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::table('dish_sales_summaries', function (Blueprint $table) {
            $table->dropUnique(['summary_date', 'branch_id', 'dish_id']);
            $table->dropConstrainedForeignId('branch_id');
            $table->unique(['summary_date', 'dish_id']);
        });
    }
};
