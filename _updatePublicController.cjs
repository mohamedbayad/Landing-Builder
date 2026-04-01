const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/PublicLandingController.php';
let content = fs.readFileSync(file, 'utf8');

const helper = `
    /**
     * Resolves local storage path from asset URL (proxy or direct)
     */
    protected function resolveLocalStoragePathFromAssetUrl(string $url): ?string
    {
        $decoded = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5);
        if ($decoded === '') {
            return null;
        }

        if (str_starts_with($decoded, '/storage/')) {
            return parse_url($decoded, PHP_URL_PATH) ?: null;
        }

        $parsed = parse_url($decoded);
        $path = $parsed['path'] ?? '';

        if ($path === '/template-asset-proxy') {
            parse_str($parsed['query'] ?? '', $q);
            if (!empty($q['u']) && is_string($q['u'])) {
                return $this->resolveLocalStoragePathFromAssetUrl($q['u']);
            }
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        $pos = stripos($path, '/storage/');
        if ($pos !== false) {
            return substr($path, $pos);
        }

        return null;
    }

    /**
     * Aggressive asset inlining and cleaning for preview/live pages.
     * Prevents 404 proxy files or missing local files from causing CSS/JS syntax errors.
     */
    protected function normalizePreviewAssets(string $html): string
    {
        // 1. Process <link rel="stylesheet">
        if (preg_match_all('/<link\\b[^>]*>/i', $html, $links)) {
            foreach ($links[0] as $tag) {
                if (!preg_match('/\\brel\\s*=\\s*["\\']?stylesheet["\\']?/i', $tag) || preg_match('/\\bhref\\s*=\\s*["\\']?(https?:\\/\\/(fonts|cdn|unpkg|cdnjs|kit\\.fontawesome)[^"\\']*)["\\']?/i', $tag)) {
                    continue;
                }

                if (!preg_match('/\\bhref\\s*=\\s*["\\']([^"\\']+)["\\']/i', $tag, $hrefMatch)) {
                    continue;
                }

                $href = $hrefMatch[1];
                $path = $this->resolveLocalStoragePathFromAssetUrl($href) ?? '';
                
                if ($path === '') {
                    continue;
                }

                $relative = ltrim(substr($path, strlen('/storage/')), '/');
                $absolute = storage_path('app/public/' . $relative);
                
                if (\\Illuminate\\Support\\Facades\\File::exists($absolute)) {
                    $css = \\Illuminate\\Support\\Facades\\File::get($absolute);
                    if (!empty($css)) {
                        $inlineTag = "<style>\\n/* Inlined: {$path} */\\n" . $css . "\\n</style>";
                        $html = str_replace($tag, $inlineTag, $html);
                    } else {
                        $html = str_replace($tag, '', $html); // empty
                    }
                } else {
                    // Dead link (e.g. proxy failing). Strip it to prevent HTML syntax crash.
                    $html = str_replace($tag, '', $html);
                }
            }
        }

        // 2. Process <script src="...">
        if (preg_match_all('/<script\\b[^>]*src=["\\']([^"\\']+)["\\'][^>]*><\\/script>/i', $html, $scripts, PREG_SET_ORDER)) {
            foreach ($scripts as $scriptMatch) {
                $tag = $scriptMatch[0];
                $src = $scriptMatch[1];
                
                if (preg_match('/^(https?:)?\\/\\/(cdn|unpkg|cdnjs|www\\.google)/i', $src)) {
                    continue; // Skip external CDNs
                }

                $path = $this->resolveLocalStoragePathFromAssetUrl($src) ?? '';
                if ($path === '') {
                    continue;
                }

                $relative = ltrim(substr($path, strlen('/storage/')), '/');
                $absolute = storage_path('app/public/' . $relative);
                
                if (\\Illuminate\\Support\\Facades\\File::exists($absolute)) {
                    $js = \\Illuminate\\Support\\Facades\\File::get($absolute);
                    if (!empty($js)) {
                        preg_match('/type=["\\']([^"\\']+)["\\']/', $tag, $typeMatch);
                        $typeObj = !empty($typeMatch) ? ' type="' . $typeMatch[1] . '"' : '';
                        
                        $inlineTag = "<script{$typeObj}>\\n/* Inlined: {$path} */\\n" . $js . "\\n</script>";
                        $html = str_replace($tag, $inlineTag, $html);
                    } else {
                        $html = str_replace($tag, '', $html);
                    }
                } else {
                    $html = str_replace($tag, '', $html);
                }
            }
        }

        return $html;
    }
`;

if (!content.includes('normalizePreviewAssets')) {
    const classEndIdx = content.lastIndexOf('}');
    content = content.substring(0, classEndIdx) + helper + '\\n}' + content.substring(classEndIdx + 1);
    
    // Inject it into show() and preview()
    const parts = content.split('$html = view("landing_page", $data)->render();'.replace(/"/g, "'"));
    if (parts.length > 1) {
        content = parts.join('$html = view("landing_page", $data)->render();\\n        $html = $this->normalizePreviewAssets($html);'.replace(/"/g, "'"));
        fs.writeFileSync(file, content);
        console.log('Successfully injected asset normalizer into PublicLandingController.php');
    } else {
        console.error('Could not find render call');
    }
} else {
    console.log('Normalizer already exists');
}
