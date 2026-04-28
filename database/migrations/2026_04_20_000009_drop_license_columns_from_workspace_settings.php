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
        if (!Schema::hasTable('workspace_settings')) {
            return;
        }

        Schema::table('workspace_settings', function (Blueprint $table) {
            if (Schema::hasColumn('workspace_settings', 'license_key')) {
                $table->dropColumn('license_key');
            }

            if (Schema::hasColumn('workspace_settings', 'license_status')) {
                $table->dropColumn('license_status');
            }

            if (Schema::hasColumn('workspace_settings', 'license_data')) {
                $table->dropColumn('license_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('workspace_settings')) {
            return;
        }

        Schema::table('workspace_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('workspace_settings', 'license_key')) {
                $table->string('license_key')->nullable();
            }

            if (!Schema::hasColumn('workspace_settings', 'license_status')) {
                $table->string('license_status')->nullable()->default('inactive');
            }

            if (!Schema::hasColumn('workspace_settings', 'license_data')) {
                $table->json('license_data')->nullable();
            }
        });
    }
};
