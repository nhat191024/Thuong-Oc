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
        Schema::create('table_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date')->index();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->uuid('table_id');
            $table->unsignedInteger('table_number');
            $table->string('table_name')->nullable();
            $table->unsignedInteger('bill_count')->default(0);
            $table->unsignedBigInteger('gross_revenue')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('net_revenue')->default(0);
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['summary_date', 'branch_id', 'table_id']);
            $table->index(['net_revenue', 'bill_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_sales_summaries');
    }
};
