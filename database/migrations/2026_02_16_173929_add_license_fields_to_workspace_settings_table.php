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
            $table->string('license_key')->nullable()->after('whatsapp_template_thankyou');
            $table->string('license_status')->nullable()->default('inactive')->after('license_key');
            $table->json('license_data')->nullable()->after('license_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_settings', function (Blueprint $table) {
            //
        });
    }
};
