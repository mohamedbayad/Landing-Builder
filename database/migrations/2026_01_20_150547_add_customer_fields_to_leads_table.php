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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'first_name')) $table->string('first_name')->nullable();
            if (!Schema::hasColumn('leads', 'last_name')) $table->string('last_name')->nullable();
            if (!Schema::hasColumn('leads', 'phone')) $table->string('phone')->nullable();
            if (!Schema::hasColumn('leads', 'address')) $table->string('address')->nullable();
            if (!Schema::hasColumn('leads', 'city')) $table->string('city')->nullable();
            if (!Schema::hasColumn('leads', 'zip')) $table->string('zip')->nullable();
            if (!Schema::hasColumn('leads', 'country')) $table->string('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'phone', 'address', 'city', 'zip', 'country']);
        });
    }
};
