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
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->boolean('enable_card')->default(true);
            $table->boolean('enable_paypal')->default(true);
            $table->boolean('enable_cod')->default(false); // Cash on Delivery
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['enable_card', 'enable_paypal', 'enable_cod']);
        });
    }
};
