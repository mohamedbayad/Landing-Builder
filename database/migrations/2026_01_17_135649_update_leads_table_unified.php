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
            // Source (Polymorphic: Form or Order)
            if (!Schema::hasColumn('leads', 'source_type')) {
                $table->nullableMorphs('source'); 
            }
            
            // Status & Payment
            if (!Schema::hasColumn('leads', 'status')) {
                $table->string('status')->default('new'); 
            }
            if (!Schema::hasColumn('leads', 'payment_provider')) {
                $table->string('payment_provider')->nullable();
            }
            if (!Schema::hasColumn('leads', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('leads', 'currency')) {
                $table->string('currency', 3)->nullable();
            }
            if (!Schema::hasColumn('leads', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }

            // Product / Offer
            if (!Schema::hasColumn('leads', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            }

            // Attribution
            if (!Schema::hasColumn('leads', 'utm_source')) {
                $table->string('utm_source')->nullable();
                $table->string('utm_medium')->nullable();
                $table->string('utm_campaign')->nullable();
                $table->string('utm_term')->nullable();
                $table->string('utm_content')->nullable();
                $table->text('referrer')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'source_type', 'source_id', 
                'status', 'payment_provider', 'amount', 'currency', 'transaction_id',
                'product_id',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer'
            ]);
        });
    }
};
