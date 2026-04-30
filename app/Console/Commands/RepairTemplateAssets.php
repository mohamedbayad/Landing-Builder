<?php

namespace App\Console\Commands;

use App\Models\LandingPage;
use App\Models\TemplatePage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RepairTemplateAssets extends Command
{
    protected $signature = 'templates:repair-assets {--dry-run : Preview changes without saving} {--template-pages-only : Repair template pages only}';

    protected $description = 'Repair broken imported page assets where head markup was stored in CSS.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $templatePagesOnly = (bool) $this->option('template-pages-only');

        $this->info('Scanning template pages...');
        $templateStats = $this->repairTemplatePages($dryRun);

        $landingStats = ['scanned' => 0, 'repaired' => 0, 'skipped' => 0];
        if (!$templatePagesOnly) {
            $this->info('Scanning landing pages...');
            $landingStats = $this->repairLandingPages($dryRun);
        }

        $this->newLine();
        $this->info('Done.');
        $this->line("Template pages: scanned={$templateStats['scanned']} repaired={$templateStats['repaired']} skipped={$templateStats['skipped']}");
        if (!$templatePagesOnly) {
            $this->line("Landing pages: scanned={$landingStats['scanned']} repaired={$landingStats['repaired']} skipped={$landingStats['skipped']}");
        }

        if ($dryRun) {
            $this->warn('Dry-run mode enabled: no database updates were saved.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{scanned:int,repaired:int,skipped:int}
     */
    protected function repairTemplatePages(bool $dryRun): array
    {
        $stats = ['scanned' => 0, 'repaired' => 0, 'skipped' => 0];

        TemplatePage::query()
            ->orderBy('id')
            ->chunkById(200, function ($pages) use (&$stats, $dryRun): void {
                foreach ($pages as $page) {
                    $stats['scanned']++;

                    if (!$this->looksBroken((string) ($page->css ?? ''), (string) ($page->js ?? ''))) {
                        $stats['skipped']++;
                        continue;
                    }

                    $repaired = $this->repairPagePayload(
                        (string) ($page->html ?? ''),
                        (string) ($page->css ?? ''),
                        (string) ($page->js ?? '')
                    );

                    if (!$this->hasMeaningfulChange((string) ($page->html ?? ''), (string) ($page->css ?? ''), (string) ($page->js ?? ''), $repaired)) {
                        $stats['skipped']++;
                        continue;
                    }

                    $stats['repaired']++;
                    $this->line(" - template_page #{$page->id} repaired");

                    if (!$dryRun) {
                        $page->update([
                            'html' => $repaired['html'],
                            'css' => $repaired['css'],
                            'js' => $repaired['js'],
                        ]);
                    }
                }
            });

        return $stats;
    }

    /**
     * @return array{scanned:int,repaired:int,skipped:int}
     */
    protected function repairLandingPages(bool $dryRun): array
    {
        $stats = ['scanned' => 0, 'repaired' => 0, 'skipped' => 0];

        LandingPage::query()
            ->orderBy('id')
            ->chunkById(200, function ($pages) use (&$stats, $dryRun): void {
                foreach ($pages as $page) {
                    $stats['scanned']++;

                    if (!$this->looksBroken((string) ($page->css ?? ''), (string) ($page->js ?? ''))) {
                        $stats['skipped']++;
                        continue;
                    }

                    $repaired = $this->repairPagePayload(
                        (string) ($page->html ?? ''),
                        (string) ($page->css ?? ''),
                        (string) ($page->js ?? '')
                    );

                    if (!$this->hasMeaningfulChange((string) ($page->html ?? ''), (string) ($page->css ?? ''), (string) ($page->js ?? ''), $repaired)) {
                        $stats['skipped']++;
                        continue;
                    }

                    $stats['repaired']++;
                    $this->line(" - landing_page #{$page->id} repaired");

                    if (!$dryRun) {
                        $page->update([
                            'html' => $repaired['html'],
                            'css' => $repaired['css'],
                            'js' => $repaired['js'],
                        ]);
                    }
                }
            });

        return $stats;
    }

    protected function looksBroken(string $css, string $js = ''): bool
    {
        $cssBroken = $css !== ''
            && preg_match('/<(?:!doctype|html|head|body|meta|title|link|script|style)\b/i', $css) === 1;
        $jsBroken = $js !== ''
            && (
                preg_match('/^\s*@import\s+url\([^)]+\)\s*;?\s*$/mi', $js) === 1
                || preg_match('/<style\b[^>]*>.*?<\/style>/is', $js) === 1
            );

        return $cssBroken || $jsBroken;
    }

    /**
     * @return array{html:string,css:string,js:string}
     */
    protected function repairPagePayload(string $html, string $css, string $existingJs): array
    {
        $document = '<html><head>' . $css . '</head><body>' . $html . '</body></html>';

        $headHtml = '';
        if (preg_match('/<head\b[^>]*>(.*?)<\/head>/is', $document, $headMatch)) {
            $headHtml = (string) ($headMatch[1] ?? '');
        }

        $bodyHtml = $html;
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $document, $bodyMatch)) {
            $bodyHtml = (string) ($bodyMatch[1] ?? '');
        }

        [, $headCss, $headJs] = $this->extractAssetsFromMarkup($headHtml, true);
        [$bodyWithoutAssets, $bodyCss, $bodyJs] = $this->extractAssetsFromMarkup($bodyHtml, true);

        $mergedCss = trim(implode("\n\n", array_filter([$headCss, $bodyCss])));
        $extractedJs = trim(implode("\n\n", array_filter([$headJs, $bodyJs])));
        $mergedCss = $this->sanitizeCssPayload($mergedCss);
        $mergedJs = $this->mergeJs($extractedJs, $existingJs);
        $mergedJs = $this->sanitizeJsPayload($mergedJs);
        $mergedJs = $this->normalizeScriptTagsForModules($mergedJs);

        // If CSS extraction produced nothing but original CSS was valid, preserve original CSS.
        if ($mergedCss === '' && trim($css) !== '' && !$this->looksBroken($css, '')) {
            $mergedCss = trim($css);
        }

        // Best-effort recovery for templates that load CSS as /assets/css/style.css
        if ($mergedCss === '') {
            $recoveredCss = $this->recoverCssImportsFromJs($mergedJs);
            if ($recoveredCss !== '') {
                $mergedCss = $recoveredCss;
            }
        }

        return [
            'html' => trim($bodyWithoutAssets) !== '' ? trim($bodyWithoutAssets) : trim($html),
            'css' => $mergedCss,
            'js' => $mergedJs,
        ];
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    protected function extractAssetsFromMarkup(string $markup, bool $stripFromMarkup): array
    {
        if ($markup === '') {
            return ['', '', ''];
        }

        $workingMarkup = $markup;
        $cssParts = [];
        $jsParts = [];

        if (preg_match_all('/<link\b[^>]*>/i', $workingMarkup, $linkMatches)) {
            foreach ($linkMatches[0] as $tag) {
                if (preg_match('/\brel\s*=\s*["\']?stylesheet["\']?/i', $tag) !== 1) {
                    continue;
                }
                if (preg_match('/\bhref\s*=\s*["\']([^"\']+)["\']/i', $tag, $hrefMatch) === 1) {
                    $href = trim((string) ($hrefMatch[1] ?? ''));
                    if ($href !== '') {
                        $cssParts[] = "@import url('" . addslashes($href) . "');";
                    }
                }
                if ($stripFromMarkup) {
                    $workingMarkup = str_replace($tag, '', $workingMarkup);
                }
            }
        }

        if (preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $workingMarkup, $styleMatches)) {
            foreach ($styleMatches[1] as $cssBlock) {
                $cssText = trim((string) $cssBlock);
                if ($cssText !== '') {
                    $cssParts[] = $cssText;
                }
            }
            if ($stripFromMarkup) {
                $workingMarkup = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $workingMarkup) ?? $workingMarkup;
            }
        }

        if (preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/is', $workingMarkup, $scriptMatches, PREG_SET_ORDER)) {
            foreach ($scriptMatches as $scriptMatch) {
                $fullTag = (string) ($scriptMatch[0] ?? '');
                $attrs = trim((string) ($scriptMatch[1] ?? ''));
                $inner = trim((string) ($scriptMatch[2] ?? ''));

                if (preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $attrs) === 1) {
                    $jsParts[] = '<script ' . $attrs . '></script>';
                } elseif ($inner !== '') {
                    $jsParts[] = "<script>\n" . $inner . "\n</script>";
                }

                if ($stripFromMarkup && $fullTag !== '') {
                    $workingMarkup = str_replace($fullTag, '', $workingMarkup);
                }
            }
        }

        return [
            $stripFromMarkup ? trim($workingMarkup) : trim($markup),
            trim(implode("\n\n", $cssParts)),
            trim(implode("\n\n", $jsParts)),
        ];
    }

    protected function mergeJs(string $extractedJs, string $existingJs): string
    {
        $existingJs = trim($existingJs);
        $extractedJs = trim($extractedJs);

        if ($existingJs === '') {
            return $extractedJs;
        }

        if ($extractedJs === '') {
            return $existingJs;
        }

        if (str_contains($existingJs, $extractedJs)) {
            return $existingJs;
        }

        if (str_contains($extractedJs, $existingJs)) {
            return $extractedJs;
        }

        return trim($extractedJs . "\n\n" . $existingJs);
    }

    protected function sanitizeCssPayload(string $css): string
    {
        if ($css === '') {
            return '';
        }

        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $css) ?? $css;
        $clean = preg_replace('/<\/?(?:html|head|body|meta|title|link)[^>]*>/i', '', $clean) ?? $clean;

        return trim((string) $clean);
    }

    protected function sanitizeJsPayload(string $js): string
    {
        if ($js === '') {
            return '';
        }

        $clean = preg_replace('/^\s*@import\s+url\([^)]+\)\s*;?\s*$/mi', '', $js) ?? $js;
        $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $clean) ?? $clean;

        if (preg_match_all('/<script\b[^>]*>.*?<\/script>/is', $clean, $scriptBlocks) && !empty($scriptBlocks[0])) {
            $scripts = array_map(fn ($block) => trim((string) $block), $scriptBlocks[0]);
            return trim(implode("\n\n", array_filter($scripts)));
        }

        return trim($clean);
    }

    protected function normalizeScriptTagsForModules(string $js): string
    {
        if ($js === '') {
            return '';
        }

        return preg_replace_callback('/<script\b([^>]*)src=["\']([^"\']+)["\']([^>]*)><\/script>/i', function ($matches) {
            $before = trim((string) ($matches[1] ?? ''));
            $src = trim((string) ($matches[2] ?? ''));
            $after = trim((string) ($matches[3] ?? ''));

            $attrs = trim($before . ' src="' . $src . '" ' . $after);
            $attrs = preg_replace('/\s+/', ' ', $attrs) ?? $attrs;

            if ($this->isEsModuleScriptSource($src) && !preg_match('/\btype\s*=\s*["\']module["\']/i', $attrs)) {
                $attrs .= ' type="module"';
            }

            return '<script ' . trim($attrs) . '></script>';
        }, $js) ?? $js;
    }

    protected function isEsModuleScriptSource(string $src): bool
    {
        $path = (string) parse_url($src, PHP_URL_PATH);
        if ($path === '' || !str_starts_with($path, '/storage/')) {
            return false;
        }

        $relative = ltrim(substr($path, strlen('/storage/')), '/');
        if ($relative === '') {
            return false;
        }

        $absolute = storage_path('app/public/' . $relative);
        if (!File::exists($absolute)) {
            return false;
        }

        $content = (string) File::get($absolute);

        return preg_match('/^\s*(?:import\s+.+from\s+|import\s+[\'"]|export\s+)/m', $content) === 1;
    }

    /**
     * @param array{html:string,css:string,js:string} $next
     */
    protected function hasMeaningfulChange(string $html, string $css, string $js, array $next): bool
    {
        return trim($html) !== trim($next['html'])
            || trim($css) !== trim($next['css'])
            || trim($js) !== trim($next['js']);
    }

    protected function recoverCssImportsFromJs(string $js): string
    {
        if ($js === '') {
            return '';
        }

        preg_match_all('/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i', $js, $matches);
        $srcs = $matches[1] ?? [];
        if (empty($srcs)) {
            return '';
        }

        $imports = [];

        foreach ($srcs as $src) {
            $path = (string) parse_url((string) $src, PHP_URL_PATH);
            if ($path === '' || !str_starts_with($path, '/storage/')) {
                continue;
            }

            $relative = ltrim(substr($path, strlen('/storage/')), '/');
            if (!str_contains($relative, '/assets/js/')) {
                continue;
            }

            $candidateRelative = preg_replace('#/assets/js/[^/]+$#', '/assets/css/style.css', $relative);
            if (!is_string($candidateRelative) || $candidateRelative === '') {
                continue;
            }

            $absolute = storage_path('app/public/' . $candidateRelative);
            if (!File::exists($absolute)) {
                continue;
            }

            $imports[] = "@import url('/storage/" . str_replace('\\', '/', $candidateRelative) . "');";
        }

        $imports = array_values(array_unique($imports));
        return trim(implode("\n\n", $imports));
    }
}
