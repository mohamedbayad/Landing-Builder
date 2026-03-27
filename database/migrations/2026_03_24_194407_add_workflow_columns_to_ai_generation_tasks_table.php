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
        Schema::table('ai_generation_tasks', function (Blueprint $table) {
            $table->string('current_phase')->nullable()->after('status');
            $table->json('product_identity')->nullable()->after('result_data');
            $table->json('conversion_blueprint')->nullable()->after('product_identity');
            $table->json('page_structure')->nullable()->after('conversion_blueprint');
            $table->json('generated_images')->nullable()->after('page_structure');
            $table->json('builder_payload')->nullable()->after('generated_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generation_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'current_phase',
                'product_identity',
                'conversion_blueprint',
                'page_structure',
                'generated_images',
                'builder_payload'
            ]);
        });
    }
};
