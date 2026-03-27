<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landings', function (Blueprint $table) {
            if (!Schema::hasColumn('landings', 'content_type')) {
                $table->string('content_type')->default('landing')->after('status');
            }
            if (!Schema::hasColumn('landings', 'source')) {
                $table->string('source')->default('manual')->after('content_type');
            }
            if (!Schema::hasColumn('landings', 'is_template')) {
                $table->boolean('is_template')->default(false)->after('source');
            }
            if (!Schema::hasColumn('landings', 'category')) {
                $table->string('category')->nullable()->after('is_template');
            }
            if (!Schema::hasColumn('landings', 'visibility')) {
                $table->string('visibility')->default('private')->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('landings', function (Blueprint $table) {
            if (Schema::hasColumn('landings', 'visibility')) {
                $table->dropColumn('visibility');
            }
            if (Schema::hasColumn('landings', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('landings', 'is_template')) {
                $table->dropColumn('is_template');
            }
            if (Schema::hasColumn('landings', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('landings', 'content_type')) {
                $table->dropColumn('content_type');
            }
        });
    }
};

