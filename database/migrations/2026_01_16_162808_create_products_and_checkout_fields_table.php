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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD'); // Default currency from landing settings will be UI default, but store here too
            $table->text('description')->nullable();
            $table->string('label')->nullable(); // e.g. "Best Value"
            $table->boolean('is_bump')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('checkout_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_id')->constrained()->cascadeOnDelete();
            $table->string('field_name'); // e.g. billing_first_name
            $table->string('label')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_fields');
        Schema::dropIfExists('products');
    }
};
