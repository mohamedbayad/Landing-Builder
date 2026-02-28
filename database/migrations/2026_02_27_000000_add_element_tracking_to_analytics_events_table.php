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
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('element_label')->nullable()->after('event_data');
            $table->string('element_type')->nullable()->after('element_label');
            $table->string('element_position')->nullable()->after('element_type');

            $table->index('element_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex(['element_label']);
            $table->dropColumn(['element_label', 'element_type', 'element_position']);
        });
    }
};
