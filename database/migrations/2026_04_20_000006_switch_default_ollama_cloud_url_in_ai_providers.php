<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $targetBaseUrl = rtrim((string) env('OLLAMA_BASE_URL', 'https://ollama.com/api/tags'), '/');

        DB::table('ai_providers')
            ->whereIn('provider', ['custom', 'ollama'])
            ->where(function ($query) {
                $query->whereNull('base_url')
                    ->orWhere('base_url', '')
                    ->orWhere('base_url', 'http://localhost:11434')
                    ->orWhere('base_url', 'http://127.0.0.1:11434')
                    ->orWhere('base_url', 'https://localhost:11434')
                    ->orWhere('base_url', 'https://127.0.0.1:11434')
                    ->orWhere('base_url', 'http://34.52.221.25:11434');
            })
            ->update([
                'base_url' => $targetBaseUrl,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this is a forward data normalization migration.
    }
};
