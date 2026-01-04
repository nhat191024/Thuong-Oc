<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\BillDetailStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bill_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('dish_id')->nullable();
            $table->string('custom_dish_name')->nullable();
            $table->integer('quantity');
            $table->integer('price');
            $table->string('note')->nullable();
            $table->unsignedBigInteger('custom_kitchen_id')->nullable();
            $table->string('status')->default(BillDetailStatus::WAITING->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_details');
    }
};
