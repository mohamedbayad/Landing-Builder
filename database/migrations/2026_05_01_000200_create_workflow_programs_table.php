<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('draft')->index(); // draft|active|paused
            $table->string('trigger_type')->default('scheduled_datetime')->index();
            $table->json('trigger_config')->nullable();
            $table->string('timezone')->nullable();
            $table->json('visual_nodes')->nullable();
            $table->json('visual_edges')->nullable();
            $table->unsignedInteger('builder_version')->default(1);
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_programs');
    }
};

