<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasStepType = Schema::hasColumn('landing_pages', 'funnel_step_type');
        $hasPosition = Schema::hasColumn('landing_pages', 'funnel_position');
        $hasNextPage = Schema::hasColumn('landing_pages', 'next_landing_page_id');
        $hasMetadata = Schema::hasColumn('landing_pages', 'step_metadata');

        Schema::table('landing_pages', function (Blueprint $table) use ($hasStepType, $hasPosition, $hasNextPage, $hasMetadata) {
            if (!$hasStepType) {
                $table->string('funnel_step_type')->default('landing')->after('type')->index();
            }

            if (!$hasPosition) {
                $table->unsignedInteger('funnel_position')->default(1)->after('funnel_step_type')->index();
            }

            if (!$hasNextPage) {
                $table->foreignId('next_landing_page_id')
                    ->nullable()
                    ->after('funnel_position')
                    ->constrained('landing_pages')
                    ->nullOnDelete();
            }

            if (!$hasMetadata) {
                $table->json('step_metadata')->nullable()->after('next_landing_page_id');
            }
        });

        $landingIds = DB::table('landing_pages')
            ->select('landing_id')
            ->distinct()
            ->pluck('landing_id');

        foreach ($landingIds as $landingId) {
            $pages = DB::table('landing_pages')
                ->where('landing_id', $landingId)
                ->orderByRaw("CASE WHEN type = 'index' THEN 0 WHEN type = 'checkout' THEN 1 WHEN type = 'thankyou' THEN 2 ELSE 3 END")
                ->orderBy('id')
                ->get(['id', 'type']);

            foreach ($pages as $index => $page) {
                $nextPage = $pages->get($index + 1);
                DB::table('landing_pages')
                    ->where('id', $page->id)
                    ->update([
                        'funnel_step_type' => $this->mapStepType($page->type),
                        'funnel_position' => $index + 1,
                        'next_landing_page_id' => $nextPage?->id,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            if (Schema::hasColumn('landing_pages', 'next_landing_page_id')) {
                try {
                    $table->dropForeign(['next_landing_page_id']);
                } catch (\Throwable) {
                    // Ignore if the foreign key name does not exist in this environment.
                }
                $table->dropColumn('next_landing_page_id');
            }

            if (Schema::hasColumn('landing_pages', 'step_metadata')) {
                $table->dropColumn('step_metadata');
            }

            if (Schema::hasColumn('landing_pages', 'funnel_position')) {
                $table->dropColumn('funnel_position');
            }

            if (Schema::hasColumn('landing_pages', 'funnel_step_type')) {
                $table->dropColumn('funnel_step_type');
            }
        });
    }

    private function mapStepType(string $pageType): string
    {
        return match ($pageType) {
            'checkout' => 'checkout',
            'thankyou' => 'thank_you',
            default => 'landing',
        };
    }
};

