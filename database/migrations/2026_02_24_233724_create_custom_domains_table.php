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
        Schema::create('custom_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('landing_page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('domain', 255)->unique();
            $table->enum('domain_type', ['subdomain', 'custom'])->default('custom');
            $table->string('verification_token', 64)->nullable();
            $table->enum('status', ['pending', 'verified', 'active', 'error'])->default('pending');
            $table->string('error_message')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['domain', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_domains');
    }
};
