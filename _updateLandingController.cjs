const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/LandingPageController.php';
let content = fs.readFileSync(file, 'utf8');

// Update method signature to pass by reference
content = content.replace(
    'protected function inlineLocalStylesFromHead(string $headHtml): string',
    'protected function inlineLocalStylesFromHead(string &$headHtml): string'
);

// We need to find the body of inlineLocalStylesFromHead
const startIdx = content.indexOf('protected function inlineLocalStylesFromHead(string &$headHtml): string');
if (startIdx !== -1) {
    const nextFunc = 'protected function normalizeEditorAssetUrls(string $html): string';
    const endIdx = content.indexOf(nextFunc);
    if (endIdx !== -1) {
        let newBody = `    protected function inlineLocalStylesFromHead(string &$headHtml): string
    {
        if ($headHtml === '') {
            return '';
        }

        if (!preg_match_all('/<link\\b[^>]*>/i', $headHtml, $links)) {
            return '';
        }

        $chunks = [];
        foreach ($links[0] as $tag) {
            if (!preg_match('/\\brel\\s*=\\s*["\\']?stylesheet["\\']?/i', $tag)) {
                continue;
            }

            if (!preg_match('/\\bhref\\s*=\\s*["\\']([^"\\']+)["\\']/i', $tag, $hrefMatch)) {
                continue;
            }

            $href = $hrefMatch[1];
            $path = $this->resolveLocalStoragePathFromAssetUrl($href) ?? '';
            
            // Always strip dead proxy/local links to prevent HTML 404 crashes
            $isLocalMissing = false;
            
            if ($path === '' || !str_starts_with($path, '/storage/') || !str_ends_with(strtolower($path), '.css')) {
                // Not local or not CSS, leave it alone.
                continue;
            }

            $relative = ltrim(substr($path, strlen('/storage/')), '/');
            $absolute = storage_path('app/public/' . $relative);
            
            if (\\Illuminate\\Support\\Facades\\File::exists($absolute)) {
                $css = \\Illuminate\\Support\\Facades\\File::get($absolute);
                if (!empty($css)) {
                    $chunks[] = "/* inlined: {$path} */\\n" . $css;
                }
            } else {
                $isLocalMissing = true;
            }
            
            // Strip the <link> tag from headHtml since we've either inlined it OR it's a dead local/proxy link that will crash.
            $headHtml = str_replace($tag, '', $headHtml);
        }
        
        // Also do scripts while we are at it! Let's refactor this immediately by passing by ref
        if (preg_match_all('/<script\\b[^>]*src=["\\']([^"\\']+)["\\'][^>]*><\\/script>/i', $headHtml, $scripts, PREG_SET_ORDER)) {
            foreach ($scripts as $scriptMatch) {
                $tag = $scriptMatch[0];
                $src = $scriptMatch[1];
                $path = $this->resolveLocalStoragePathFromAssetUrl($src) ?? '';
                
                if ($path === '' || !str_starts_with($path, '/storage/') || !str_ends_with(strtolower($path), '.js')) {
                    continue;
                }

                $relative = ltrim(substr($path, strlen('/storage/')), '/');
                $absolute = storage_path('app/public/' . $relative);
                
                if (\\Illuminate\\Support\\Facades\\File::exists($absolute)) {
                    $js = \\Illuminate\\Support\\Facades\\File::get($absolute);
                    if (!empty($js)) {
                        $chunks[] = "/* inlined script: {$path} */\\n</style><script>\\n" . $js . "\\n</script><style>\\n";
                    }
                }
                
                // Strip the script tag
                $headHtml = str_replace($tag, '', $headHtml);
            }
        }

        return trim(implode("\\n\\n", array_unique($chunks)));
    }

    `;
        const oldBody = content.substring(startIdx, endIdx);
        content = content.replace(oldBody, newBody);
    }
}

// Now update the caller in update method
content = content.replace(
    '$inlinedCss = $this->inlineLocalStylesFromHead($editorCustomHead);',
    '$inlinedCss = $this->inlineLocalStylesFromHead($editorCustomHead);\n        // Re-assign the stripped head strictly to the model settings\n        $landingHead = $editorCustomHead;'
);

fs.writeFileSync(file, content);
console.log('Successfully updated LandingPageController inline script injection.');
