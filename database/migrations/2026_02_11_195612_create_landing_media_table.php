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
        Schema::create('landing_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_id')->constrained('landings')->onDelete('cascade');
            $table->string('filename');
            $table->string('relative_path'); // Path relative to storage/app/public/landings/{uuid}/
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0); // Bytes
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamps();

            // Indexes for faster lookup/listing
            $table->index(['landing_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_media');
    }
};
