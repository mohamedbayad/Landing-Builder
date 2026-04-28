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
        $shouldAddEnabled = !Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_enabled');
        $shouldAddText = !Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_text');

        if (!$shouldAddEnabled && !$shouldAddText) {
            return;
        }

        Schema::table('workspace_settings', function (Blueprint $table) use ($shouldAddEnabled, $shouldAddText) {
            if ($shouldAddEnabled) {
                $table->boolean('chatbot_custom_cta_enabled')->default(false)->after('ai_role_assignments');
            }
            if ($shouldAddText) {
                $table->string('chatbot_custom_cta_text', 120)->nullable()->after('chatbot_custom_cta_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropColumns = [];
        if (Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_enabled')) {
            $dropColumns[] = 'chatbot_custom_cta_enabled';
        }
        if (Schema::hasColumn('workspace_settings', 'chatbot_custom_cta_text')) {
            $dropColumns[] = 'chatbot_custom_cta_text';
        }

        if (empty($dropColumns)) {
            return;
        }

        Schema::table('workspace_settings', function (Blueprint $table) use ($dropColumns) {
            $table->dropColumn($dropColumns);
        });
    }
};
