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
        Schema::table('tracking_events', function (Blueprint $table) {
            $table->string('referrer')->nullable()->after('type');
            $table->string('utm_source')->nullable()->after('referrer');
            $table->text('user_agent')->nullable()->after('utm_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_events', function (Blueprint $table) {
            $table->dropColumn(['referrer', 'utm_source', 'user_agent']);
        });
    }
};
