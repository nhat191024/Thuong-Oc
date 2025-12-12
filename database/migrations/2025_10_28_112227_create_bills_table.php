<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\PayStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('table_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('time_in')->useCurrent();
            $table->timestamp('time_out')->nullable();
            $table->integer('total')->default(0);
            $table->integer('discount')->nullable();
            $table->integer('final_total')->default(0);
            $table->string('payment_method')->nullable();
            $table->string('pay_status')->default(PayStatus::UNPAID->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
