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
        Schema::create('food_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_food_stock_id')
                ->constrained('branch_food_stocks')
                ->cascadeOnDelete();
            $table->string('type', 30);
            $table->decimal('quantity_in_kg', 12, 3)->unsigned();
            $table->decimal('balance_before_kg', 12, 3);
            $table->decimal('balance_after_kg', 12, 3);
            $table->foreignId('bill_detail_id')
                ->nullable()
                ->constrained('bill_details')
                ->nullOnDelete();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['branch_food_stock_id', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_stock_movements');
    }
};
