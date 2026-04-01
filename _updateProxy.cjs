const fs = require('fs');

const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/TemplateController.php';
let content = fs.readFileSync(file, 'utf8');

const oldFunc = `    public function proxyTemplateAsset(Request $request)
    {
        $validated = $request->validate([
            'u' => 'required|url',
        ]);

        $url = (string) $validated['u'];
        $parsed = parse_url($url);
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $path = (string) ($parsed['path'] ?? '');

        if ($host === '' || $path === '') {
            abort(404);
        }

        // Allow only expected licensing/template hosts (prevents open proxy abuse)
        $allowedHosts = [];
        $licensingBase = (string) env('LICENSING_SERVER_URL', '');
        if ($licensingBase !== '') {
            $licensingHost = parse_url($licensingBase, PHP_URL_HOST);
            if ($licensingHost) {
                $allowedHosts[] = strtolower((string) $licensingHost);
            }
        }

        $extraAllowed = array_filter(array_map('trim', explode(',', (string) env('TEMPLATE_ASSET_PROXY_ALLOWED_HOSTS', ''))));
        foreach ($extraAllowed as $h) {
            $allowedHosts[] = strtolower($h);
        }

        $allowedHosts = array_values(array_unique($allowedHosts));

        $hostAllowed = empty($allowedHosts) || in_array($host, $allowedHosts, true);
        if (!$hostAllowed) {
            abort(403);
        }

        if (!str_contains($path, '/storage/templates/')) {
            abort(403);
        }

        $response = \\Illuminate\\Support\\Facades\\Http::timeout(30)
            ->withOptions(['verify' => false])
            ->get($url);

        if (!$response->successful()) {
            abort(404);
        }

        $contentType = $response->header('Content-Type') ?: 'application/octet-stream';

        return response($response->body(), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }`;

const newFunc = `    public function proxyTemplateAsset(Request $request)
    {
        $validated = $request->validate([
            'u' => 'required|url',
        ]);

        $url = (string) $validated['u'];
        $parsed = parse_url($url);
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $path = (string) ($parsed['path'] ?? '');

        // Determine correct content type early so we can fail gracefully without HTML
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $fallbackType = match ($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            default => 'text/plain',
        };

        // Helper to fail safely (returns empty content or comment, preventing JS/CSS syntax errors)
        $failSafe = function () use ($fallbackType) {
            $body = str_contains($fallbackType, 'css') || str_contains($fallbackType, 'javascript') 
                ? '/* Asset Proxy Failed or Forbidden */' 
                : '';
                
            return response($body, 200, [
                'Content-Type' => $fallbackType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        };

        if ($host === '' || $path === '') {
            return $failSafe();
        }

        // Allow only expected licensing/template hosts (prevents open proxy abuse)
        $allowedHosts = [];
        $licensingBase = (string) env('LICENSING_SERVER_URL', '');
        if ($licensingBase !== '') {
            $licensingHost = parse_url($licensingBase, PHP_URL_HOST);
            if ($licensingHost) {
                $allowedHosts[] = strtolower((string) $licensingHost);
            }
        }

        $extraAllowed = array_filter(array_map('trim', explode(',', (string) env('TEMPLATE_ASSET_PROXY_ALLOWED_HOSTS', ''))));
        foreach ($extraAllowed as $h) {
            $allowedHosts[] = strtolower($h);
        }

        $allowedHosts = array_values(array_unique($allowedHosts));

        $hostAllowed = empty($allowedHosts) || in_array($host, $allowedHosts, true);
        if (!$hostAllowed || !str_contains($path, '/storage/templates/')) {
            return $failSafe();
        }

        try {
            $response = \\Illuminate\\Support\\Facades\\Http::timeout(10)
                ->withOptions(['verify' => false])
                ->get($url);

            if (!$response->successful()) {
                return $failSafe();
            }

            $contentType = $response->header('Content-Type') ?: $fallbackType;

            return response($response->body(), 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\\Exception $e) {
            return $failSafe();
        }
    }`;

if (content.includes(oldFunc)) {
    content = content.replace(oldFunc, newFunc);
    fs.writeFileSync(file, content);
    console.log('Successfully updated proxyTemplateAsset');
} else {
    // try replacing dynamically just in case
    const startIdx = content.indexOf('public function proxyTemplateAsset(Request $request)');
    const nextFuncIdx = content.indexOf('public function index(Request $request');
    if (startIdx !== -1 && nextFuncIdx !== -1) {
         let sub = content.substring(startIdx, nextFuncIdx);
         content = content.replace(sub, newFunc + '\\n\\n    ');
         fs.writeFileSync(file, content);
         console.log('Successfully updated proxyTemplateAsset (dynamic match)');
    } else {
         console.error('Could not find proxyTemplateAsset function');
    }
}
