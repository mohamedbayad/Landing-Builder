<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active')->after('password');
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('category')->nullable()->after('description');
            $table->enum('visibility', ['public', 'private', 'internal'])->default('public')->after('is_active');
            $table->foreignId('owner_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('zip_file_path')->nullable()->after('storage_path');
        });

        DB::table('templates')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function ($template) {
                $baseSlug = Str::slug((string) $template->name);
                $slug = $baseSlug !== '' ? $baseSlug : ('template-' . $template->id);

                DB::table('templates')
                    ->where('id', $template->id)
                    ->update([
                        'slug' => $slug . '-' . $template->id,
                        'category' => DB::raw("COALESCE(category, 'general')"),
                    ]);
            });

        Schema::table('templates', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropForeign(['owner_user_id']);
            $table->dropColumn(['owner_user_id', 'slug', 'category', 'visibility', 'zip_file_path']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'company_name', 'status']);
        });
    }
};
