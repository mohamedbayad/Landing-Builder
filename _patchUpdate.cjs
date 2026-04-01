const fs = require('fs');

const updateScript = `

        // 1. Preserve external <script src="..."> tags that GrapesJS natively strips
        if (!empty($page->js)) {
            if (preg_match_all('/<script\\b[^>]*src=["\\']([^"\\']+)["\\'][^>]*><\\/script>/i', (string)$page->js, $existingSrcScripts)) {
                if (!empty($existingSrcScripts[0])) {
                    $externalScripts = implode("\\n", $existingSrcScripts[0]);
                    
                    // If validated JS doesn't contain these scripts, restore them
                    if (isset($validated['js']) && !str_contains($validated['js'], $externalScripts)) {
                        $validated['js'] = $externalScripts . "\\n\\n" . $validated['js'];
                    }
                }
            }
        }

        // 2. Clean up GrapesJS editor style contamination (added by canvas-interaction-control.js)
        if (isset($validated['html'])) {
            $validated['html'] = preg_replace('/opacity:\s*1\s*!important;\s*/i', '', $validated['html']);
            $validated['html'] = preg_replace('/visibility:\s*visible\s*!important;\s*/i', '', $validated['html']);
            $validated['html'] = preg_replace('/transform:\s*none\s*!important;\s*/i', '', $validated['html']);
            $validated['html'] = preg_replace('/filter:\s*none\s*!important;\s*/i', '', $validated['html']);
            
            // Clean up empty style attributes left behind
            $validated['html'] = str_replace(' style=""', '', $validated['html']);
        }

        $page->update($validated);
`;

const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/LandingPageController.php';
let content = fs.readFileSync(file, 'utf8');

if (!content.includes('canvas-interaction-control.js')) {
    content = content.replace(
        '$page->update($validated);',
        updateScript
    );

    fs.writeFileSync(file, content);
    console.log('Successfully patched LandingPageController.php');
} else {
    console.log('Already patched');
}
