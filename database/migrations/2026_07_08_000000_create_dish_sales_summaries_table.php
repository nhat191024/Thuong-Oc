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
        Schema::table('bills', function (Blueprint $table) {
            $table->index(['pay_status', 'time_out'], 'bills_sales_summary_lookup_index');
        });

        Schema::table('bill_details', function (Blueprint $table) {
            $table->index(['dish_id', 'status', 'bill_id'], 'bill_details_sales_summary_lookup_index');
        });

        Schema::create('dish_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dish_id')->unique();
            $table->unsignedBigInteger('food_id')->index();
            $table->unsignedBigInteger('cooking_method_id')->index();
            $table->string('food_name');
            $table->string('cooking_method_name');
            $table->unsignedBigInteger('total_quantity')->default(0);
            $table->unsignedBigInteger('total_revenue')->default(0);
            $table->timestamp('last_ordered_at')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['total_revenue', 'total_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dish_sales_summaries');

        Schema::table('bill_details', function (Blueprint $table) {
            $table->dropIndex('bill_details_sales_summary_lookup_index');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('bills_sales_summary_lookup_index');
        });
    }
};
