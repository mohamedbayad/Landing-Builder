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
        $shouldAddType = !Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_type');
        $shouldAddTarget = !Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_target');
        $shouldAddLandingScope = !Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_landing_id');

        if (!$shouldAddType && !$shouldAddTarget && !$shouldAddLandingScope) {
            return;
        }

        Schema::table('workspace_settings', function (Blueprint $table) use ($shouldAddType, $shouldAddTarget, $shouldAddLandingScope) {
            if ($shouldAddType) {
                $table->string('chatbot_custom_cta_type', 40)->default('form')->after('chatbot_custom_cta_text');
            }
            if ($shouldAddTarget) {
                $table->string('chatbot_custom_cta_target', 255)->nullable()->after('chatbot_custom_cta_type');
            }
            if ($shouldAddLandingScope) {
                $table->unsignedBigInteger('chatbot_custom_cta_landing_id')->nullable()->after('chatbot_custom_cta_target');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropColumns = [];
        if (Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_type')) {
            $dropColumns[] = 'chatbot_custom_cta_type';
        }
        if (Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_target')) {
            $dropColumns[] = 'chatbot_custom_cta_target';
        }
        if (Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_landing_id')) {
            $dropColumns[] = 'chatbot_custom_cta_landing_id';
        }

        if (empty($dropColumns)) {
            return;
        }

        Schema::table('workspace_settings', function (Blueprint $table) use ($dropColumns) {
            $table->dropColumn($dropColumns);
        });
    }
};
