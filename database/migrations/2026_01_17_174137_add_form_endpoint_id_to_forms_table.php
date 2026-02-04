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
        Schema::table('forms', function (Blueprint $table) {
            $table->foreignId('form_endpoint_id')->nullable()->constrained()->nullOnDelete()->after('landing_id');
            // Make landing_id nullable for endpoint-only submissions
            $table->unsignedBigInteger('landing_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['form_endpoint_id']);
            $table->dropColumn('form_endpoint_id');
            // We cannot easily revert landing_id to not nullable without data loss risk, so we skip that or enforce it if empty.
            // For now, simpler to just drop the new column.
        });
    }
};
