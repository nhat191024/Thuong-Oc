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
        Schema::table('printers', function (Blueprint $table) {
            if (Schema::hasColumn('printers', 'character_table')) {
                $table->dropColumn('character_table');
            }

            if (Schema::hasColumn('printers', 'character_encoding')) {
                $table->dropColumn('character_encoding');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
