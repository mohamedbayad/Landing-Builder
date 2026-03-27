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
        Schema::table('workspace_settings', function (Blueprint $table) {
            $table->string('ai_provider')->nullable()->after('license_data');
            $table->string('ai_model')->nullable()->after('ai_provider');
            $table->text('ai_api_key')->nullable()->after('ai_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_settings', function (Blueprint $table) {
            $table->dropColumn(['ai_provider', 'ai_model', 'ai_api_key']);
        });
    }
};
