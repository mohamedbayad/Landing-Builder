<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('landing_settings', 'form_automation_id')) {
                $table->foreignId('form_automation_id')
                    ->nullable()
                    ->after('enable_cod')
                    ->constrained('email_automations')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('landing_settings', 'checkout_automation_id')) {
                $table->foreignId('checkout_automation_id')
                    ->nullable()
                    ->after('form_automation_id')
                    ->constrained('email_automations')
                    ->nullOnDelete();
            }
        });

        Schema::table('form_endpoints', function (Blueprint $table) {
            if (!Schema::hasColumn('form_endpoints', 'default_automation_id')) {
                $table->foreignId('default_automation_id')
                    ->nullable()
                    ->after('workspace_id')
                    ->constrained('email_automations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            if (Schema::hasColumn('landing_settings', 'checkout_automation_id')) {
                $table->dropConstrainedForeignId('checkout_automation_id');
            }

            if (Schema::hasColumn('landing_settings', 'form_automation_id')) {
                $table->dropConstrainedForeignId('form_automation_id');
            }
        });

        Schema::table('form_endpoints', function (Blueprint $table) {
            if (Schema::hasColumn('form_endpoints', 'default_automation_id')) {
                $table->dropConstrainedForeignId('default_automation_id');
            }
        });
    }
};

