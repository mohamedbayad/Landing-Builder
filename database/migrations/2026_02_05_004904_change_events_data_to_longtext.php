<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changes events_data from TEXT (64KB) to LONGTEXT (4GB) to prevent truncation.
     */
    public function up(): void
    {
        // Use raw SQL to ensure LONGTEXT is used (Laravel's Schema builder may use TEXT)
        // LONGTEXT supports up to 4GB of data, enough for any DOM snapshot
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement('ALTER TABLE session_recordings MODIFY events_data LONGTEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to JSON (which uses LONGTEXT in MySQL 5.7.8+)
        Schema::table('session_recordings', function (Blueprint $table) {
            $table->json('events_data')->change();
        });
    }
};
