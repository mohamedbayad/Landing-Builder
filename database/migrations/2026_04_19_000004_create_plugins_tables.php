<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('icon')->nullable();
            $table->json('manifest');
            $table->json('requires')->nullable();
            $table->json('hooks')->nullable();
            $table->json('settings_schema')->nullable();
            $table->json('permissions')->nullable();
            $table->json('assets')->nullable();
            $table->string('runtime_entry')->nullable();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workspace_plugins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('inactive');
            $table->json('approved_permissions')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('auto_update')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'plugin_id']);
        });

        Schema::create('plugin_storage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->longText('value')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'plugin_id', 'key']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_storage_items');
        Schema::dropIfExists('workspace_plugins');
        Schema::dropIfExists('plugins');
    }
};

