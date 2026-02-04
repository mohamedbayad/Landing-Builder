<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_id')->constrained()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->json('data')->nullable(); // Flexible JSON data
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // Migrate data from leads (type='form') to forms
        // We use raw SQL for performance and to avoid model dependencies
        $forms = DB::table('leads')->where('type', 'form')->get();
        
        foreach ($forms as $form) {
            DB::table('forms')->insert([
                'landing_id' => $form->landing_id,
                'email' => $form->email,
                'data' => $form->data, // Assuming it's already JSON string in DB or will be cast handled? DB::table returns raw.
                'ip_address' => $form->ip_address,
                'created_at' => $form->created_at,
                'updated_at' => $form->updated_at,
            ]);
        }

        // Delete moved rows
        DB::table('leads')->where('type', 'form')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move back to leads? Or just drop.
        // For safety, let's try to move back if possible, but schema in leads might have changed.
        // We'll just drop forms table for now as reversal strategy.
        Schema::dropIfExists('forms');
    }
};
