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
        Schema::table('kitchens', function (Blueprint $table) {
            $table->dropForeign(['printer_id']);
            $table->dropColumn(['printer_id', 'auto_print']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchens', function (Blueprint $table) {
            $table->foreignId('printer_id')
                ->nullable()
                ->constrained('printers')
                ->nullOnDelete();
            $table->boolean('auto_print')->default(true);
        });
    }
};
