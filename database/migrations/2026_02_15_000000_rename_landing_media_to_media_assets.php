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
        // 1. Rename table if it hasn't been done yet
        if (Schema::hasTable('landing_media') && !Schema::hasTable('media_assets')) {
            Schema::rename('landing_media', 'media_assets');
        }

        Schema::table('media_assets', function (Blueprint $table) {
            // 2. Add new columns
            if (!Schema::hasColumn('media_assets', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('media_assets', 'template_id')) {
                $table->foreignId('template_id')->nullable()->after('landing_id')->constrained('templates')->nullOnDelete();
            }
            if (!Schema::hasColumn('media_assets', 'source')) {
                $table->string('source')->default('manual')->after('mime_type'); // manual, zip, grapesjs
            }
            if (!Schema::hasColumn('media_assets', 'disk')) {
                $table->string('disk')->default('public')->after('relative_path');
            }
            if (!Schema::hasColumn('media_assets', 'hash')) {
                $table->string('hash')->nullable()->after('size');
            }

            // 3. Modify existing columns
            $table->unsignedBigInteger('landing_id')->nullable()->change();
            
            // 4. Indexes
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['template_id']);
            $table->dropColumn(['user_id', 'template_id', 'source', 'disk', 'hash']);
            // Reverting nullable change is tricky if data exists, but we can try
            // $table->unsignedBigInteger('landing_id')->nullable(false)->change(); 
        });

        if (Schema::hasTable('media_assets') && !Schema::hasTable('landing_media')) {
            Schema::rename('media_assets', 'landing_media');
        }
    }
};
