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
        // 1. Add new columns to leads and migrate data
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'type')) {
                $table->string('type')->after('id')->default('form')->index(); // 'form' or 'checkout'
            }
            
            // Customer Info
            if (!Schema::hasColumn('leads', 'first_name')) {
                $table->string('first_name')->nullable()->after('email');
                $table->string('last_name')->nullable()->after('first_name');
                $table->string('phone')->nullable()->after('last_name');
                $table->text('address')->nullable()->after('phone');
                $table->string('city')->nullable()->after('address');
                $table->string('zip')->nullable()->after('city');
                $table->string('country')->nullable()->after('zip');
            }
            
            // Order specific (extra)
            if (!Schema::hasColumn('leads', 'invoice_id')) {
                $table->string('invoice_id')->nullable()->after('transaction_id');
            }
        });

        // Migrate existing types (Raw SQL for speed/simplicity)
        // Ensure source_type column exists before querying it
        if (Schema::hasColumn('leads', 'source_type')) {
            DB::statement("UPDATE leads SET type = 'checkout' WHERE source_type LIKE '%Order%'");
            DB::statement("UPDATE leads SET type = 'form' WHERE source_type LIKE '%Form%'");
        }

        // 2. Drop polymorphic columns
        // Separate dropIndex to help SQLite
        try {
            Schema::table('leads', function (Blueprint $table) {
                // Try to drop index by explicit name to avoid Doctrine requirement
                $table->dropIndex('leads_source_type_source_id_index');
            });
        } catch (\Exception $e) {
            // Ignore if index doesn't exist or other error
        }

        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'source_type')) {
                $table->dropColumn(['source_type', 'source_id']);
            }
        });

        // 3. Drop old tables
        Schema::dropIfExists('orders');
        Schema::dropIfExists('forms');
    }

    public function down(): void
    {
        // This is a destructive migration, hard to reverse fully without backup.
        // We will just recreate basic tables and add back columns.
        
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
        
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->nullableMorphs('source');
            $table->dropColumn([
                'type', 'first_name', 'last_name', 'phone', 
                'address', 'city', 'zip', 'country', 'invoice_id'
            ]);
        });
    }
};
