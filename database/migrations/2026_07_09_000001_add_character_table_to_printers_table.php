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
            $table->unsignedTinyInteger('character_table')
                ->default(27)
                ->after('timeout');
            $table->string('character_encoding', 40)
                ->default('CP1258')
                ->after('character_table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropColumn(['character_table', 'character_encoding']);
        });
    }
};
