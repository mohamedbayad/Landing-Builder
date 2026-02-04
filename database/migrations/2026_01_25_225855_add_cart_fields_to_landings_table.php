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
        Schema::table('landings', function (Blueprint $table) {
            $table->boolean('enable_cart')->default(false);
            $table->string('cart_bg_color')->nullable()->default('#ffffff');
            $table->string('cart_text_color')->nullable()->default('#000000');
            $table->string('cart_btn_color')->nullable()->default('#3b82f6');
            $table->string('cart_btn_text_color')->nullable()->default('#ffffff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->dropColumn([
                'enable_cart',
                'cart_bg_color',
                'cart_text_color',
                'cart_btn_color',
                'cart_btn_text_color',
            ]);
        });
    }
};
