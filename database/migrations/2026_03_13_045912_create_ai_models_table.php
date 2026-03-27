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
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_provider_id')->constrained('ai_providers')->onDelete('cascade');
            $table->string('name'); // The actual model string, e.g., "gpt-4o"
            
            // Capabilities (What the model CAN do)
            $table->boolean('supports_text_generation')->default(false);
            $table->boolean('supports_image_generation')->default(false);
            $table->boolean('supports_vision')->default(false);
            $table->boolean('supports_embeddings')->default(false);
            $table->boolean('supports_audio')->default(false);
            
            // Role Assignments (What the model is CURRENTLY assigned to do)
            $table->boolean('is_default_text_generation')->default(false);
            $table->boolean('is_default_image_generation')->default(false);
            $table->boolean('is_default_vision')->default(false);
            $table->boolean('is_default_embeddings')->default(false);
            $table->boolean('is_default_audio')->default(false);
            
            $table->json('raw_metadata')->nullable(); // For raw API responses regarding model specs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
