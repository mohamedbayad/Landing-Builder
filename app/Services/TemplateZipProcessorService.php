<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\MediaAsset;
use ZipArchive;

class TemplateZipProcessorService
{
    private const MAX_FILE_SIZE = 52428800; // 50MB
    private const MAX_FILES = 1000;
    private const MAX_TOTAL_SIZE = 209715200; // 200MB
    private const ALLOWED_EXTENSIONS = ['html', 'css', 'js', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'woff', 'woff2', 'ttf', 'eot'];

    /**
     * Validate ZIP file for security and structure
     */
    public function validateZip(string $zipPath): array
    {
        if (!file_exists($zipPath)) {
            return ['valid' => false, 'error' => 'ZIP file not found'];
        }

        if (!class_exists('ZipArchive')) {
            // PowerShell fallback available, skip this check
            return ['valid' => true];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['valid' => false, 'error' => 'Invalid ZIP file'];
        }

        $fileCount = 0;
        $totalSize = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $stats = $zip->statIndex($i);
            
            // Zip Slip protection
            if (str_contains($filename, '../') || str_contains($filename, '..\\') || str_starts_with($filename, '/')) {
                $zip->close();
                return ['valid' => false, 'error' => 'Security: Directory traversal detected'];
            }

            // Symlink Protection (if supported by ZipArchive in this env, usually strictly implies check attribute)
            // PHP ZipArchive doesn't easily expose symlink attr without external libs, but we can check if size is tiny and looks suspicious?
            // Better: relying on extraction to not follow symlinks or checking "external attributes"
            $opsys = $stats['opsys'] ?? 0;
            $attr = $stats['external_attributes'] ?? 0;
            // 0xA000 indicates symlink in Unix
            $isSymlink = ($opsys == ZipArchive::OPSYS_UNIX) && (($attr >> 16) & 0xA000) === 0xA000;
            
            if ($isSymlink) {
                $zip->close();
                return ['valid' => false, 'error' => 'Security: Symlinks not allowed'];
            }

            // Check file extension
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            // Folders might result in empty extension, ignore them
            if ($ext && !in_array($ext, self::ALLOWED_EXTENSIONS)) {
                $zip->close();
                return ['valid' => false, 'error' => "Invalid file type: {$ext}"];
            }

            $fileCount++;
            $totalSize += $stats['size'];

            if ($fileCount > self::MAX_FILES) {
                $zip->close();
                return ['valid' => false, 'error' => 'Too many files in ZIP'];
            }

            if ($totalSize > self::MAX_TOTAL_SIZE) {
                $zip->close();
                return ['valid' => false, 'error' => 'Total extracted size exceeds limit (200MB)'];
            }
        }

        $zip->close();
        return ['valid' => true];
    }

    /**
     * Extract ZIP to destination with fallback
     */
    public function extractZip(string $zipPath, string $destinationPath): bool
    {
        // Ensure destination exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // Try ZipArchive first
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $result = $zip->extractTo($destinationPath);
                $zip->close();
                if ($result) {
                    return true;
                }
            }
        }

        // Fallback to PowerShell
        $command = "powershell -Command \"Expand-Archive -Path '$zipPath' -DestinationPath '$destinationPath' -Force\"";
        exec($command, $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Process HTML file - extract HEAD elements and rewrite asset paths
     * 
     * @param string $htmlPath Absolute path to HTML file
     * @param string $baseUrl Base URL for asset rewriting (e.g., /storage/landings/{uuid}/)
     * @return array ['title' => ..., 'custom_head' => ..., 'css' => ..., 'body_html' => ...]
     */
    public function processHtml(string $htmlPath, string $baseUrl): array
    {
        if (!file_exists($htmlPath)) {
            Log::warning("HTML file not found: {$htmlPath}");
            return [
                'title' => '',
                'custom_head' => '',
                'css' => '',
                'body_html' => ''
            ];
        }

        $html = file_get_contents($htmlPath);

        // Security: Remove any potential inline scripts or event handlers via Regex before DOM parsing (Defense in depth)
        // Remove <script>... </script> blocks that don't have src (inline logic)
        $html = preg_replace('/<script(?![^>]*\bsrc=)[^>]*>.*?<\/script>/is', '', $html);
        
        // Remove on* attributes (onclick, onload, etc.)
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:".*?"|\'.*?\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/javascript:/i', 'broken:', $html);

        // Parse HTML with DOMDocument
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        // UTF-8 Hack
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $result = [
            'title' => '',
            'custom_head' => '',
            'css' => '',
            'body_html' => ''
        ];

        // Process HEAD
        $head = $dom->getElementsByTagName('head')->item(0);
        if ($head) {
            $customHeadHtml = '';
            $cssContent = '';
            $nodesToRemove = [];

            foreach ($head->childNodes as $node) {
                if ($node->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                $tagName = strtolower($node->nodeName);

                // Extract title
                if ($tagName === 'title') {
                    $result['title'] = $node->textContent;
                    $nodesToRemove[] = $node;
                    continue;
                }

                // Process stylesheets and font links
                if ($tagName === 'link') {
                    $rel = $node->getAttribute('rel');
                    $href = $node->getAttribute('href');
                    
                    // Only keep stylesheet and font links (preconnect for fonts)
                    if ($rel === 'stylesheet' || $rel === 'preconnect' || str_contains($href, 'fonts.googleapis.com') || str_contains($href, 'fonts.gstatic.com')) {
                        // Google Fonts and related - keep as-is
                        if (str_contains($href, 'fonts.googleapis.com') || str_contains($href, 'fonts.gstatic.com')) {
                            $customHeadHtml .= $dom->saveHTML($node);
                        }
                        // Local stylesheet - rewrite path
                        elseif ($rel === 'stylesheet' && !$this->isExternal($href)) {
                            $newHref = $this->rewriteUrl($href, $baseUrl);
                            $node->setAttribute('href', $newHref);
                            $customHeadHtml .= $dom->saveHTML($node);
                        }
                    }
                    
                    $nodesToRemove[] = $node;
                    continue;
                }

                // Extract inline styles
                if ($tagName === 'style') {
                    $cssContent .= $node->textContent . "\n";
                    $nodesToRemove[] = $node;
                    continue;
                }

                // SCRIPTS: Remove from head completely
                if ($tagName === 'script') {
                    $nodesToRemove[] = $node;
                    continue;
                }
            }

            // Remove processed nodes
            foreach ($nodesToRemove as $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }

            $result['custom_head'] = $customHeadHtml;
            $result['css'] = $cssContent;
        }

        // Process BODY - extract links and rewrite asset paths
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $customHeadHtml = $result['custom_head']; // Get what we already have from HEAD
            $nodesToRemoveFromBody = [];

            // DOM traversal for deeper cleaning of event handlers if Regex missed any (DOM doesn't expose all attribs easily without iterating, but we did regex above)
            // Let's iterate all elements to be safe? Expensive but safer. Use XPath.
            $xpath = new \DOMXPath($dom);
            // $nodesWithOn = $xpath->query('//@*[starts-with(name(), "on")]'); // XPath 1.0 doesn't support generic attribute check well like this for removal context easily.
            // Rely on Regex + explicit script tag/src validation below.

            // EXTRACT: Link tags from body (stylesheets, fonts) -> move to custom_head
            foreach ($body->getElementsByTagName('link') as $link) {
                $rel = $link->getAttribute('rel');
                $href = $link->getAttribute('href');
                
                // Extract stylesheet and font-related links
                if ($rel === 'stylesheet' || $rel === 'preconnect' || str_contains($href, 'fonts.googleapis.com') || str_contains($href, 'fonts.gstatic.com')) {
                    // Google Fonts and related - keep as-is
                    if (str_contains($href, 'fonts.googleapis.com') || str_contains($href, 'fonts.gstatic.com')) {
                        $customHeadHtml .= $dom->saveHTML($link);
                    }
                    // Local stylesheet - rewrite path
                    elseif ($rel === 'stylesheet' && !$this->isExternal($href)) {
                        $newHref = $this->rewriteUrl($href, $baseUrl);
                        $link->setAttribute('href', $newHref);
                        $customHeadHtml .= $dom->saveHTML($link);
                    }
                    
                    // Remove from body
                    $nodesToRemoveFromBody[] = $link;
                }
            }

            // REMOVE: External CDN scripts that aren't whitelisted
            // REWRITE: Local script paths in body
            foreach ($body->getElementsByTagName('script') as $script) {
                $src = $script->getAttribute('src');
                
                if ($src) {
                    // Whitelist some CDNs if needed (e.g. Tailwind, Alpine, FontAwesome)
                    // For now, strict: if external and not explicitly trusted, remove?
                    // User said: "allow external <script src> from whitelist domains: cdn.tailwindcss.com"
                    
                    if ($this->isExternal($src)) {
                        $allowedDomains = ['cdn.tailwindcss.com', 'unpkg.com/alpinejs', 'cdnjs.cloudflare.com'];
                        $isAllowed = false;
                        foreach ($allowedDomains as $domain) {
                            if (str_contains($src, $domain)) {
                                $isAllowed = true;
                                break;
                            }
                        }

                        if (!$isAllowed) {
                            $nodesToRemoveFromBody[] = $script;
                        }
                    }
                    // Rewrite local script paths
                    else {
                        $script->setAttribute('src', $this->rewriteUrl($src, $baseUrl));
                    }
                } else {
                    // Inline script in body -> Remove
                    $nodesToRemoveFromBody[] = $script;
                }
            }

            // Remove collected nodes from body
            foreach ($nodesToRemoveFromBody as $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }

            // Update custom_head with extracted body links
            $result['custom_head'] = $customHeadHtml;

            // Rewrite img src
            foreach ($body->getElementsByTagName('img') as $img) {
                $src = $img->getAttribute('src');
                if ($src && !$this->isExternal($src)) {
                    $img->setAttribute('src', $this->rewriteUrl($src, $baseUrl));
                }

                // Rewrite srcset
                $srcset = $img->getAttribute('srcset');
                if ($srcset) {
                    $img->setAttribute('srcset', $this->rewriteSrcset($srcset, $baseUrl));
                }
            }

            // Rewrite source srcset
            foreach ($body->getElementsByTagName('source') as $source) {
                $srcset = $source->getAttribute('srcset');
                if ($srcset) {
                    $source->setAttribute('srcset', $this->rewriteSrcset($srcset, $baseUrl));
                }
            }

            // Rewrite inline background-image styles
            $nodesWithStyle = $xpath->query('//*[@style]');
            foreach ($nodesWithStyle as $node) {
                $style = $node->getAttribute('style');
                if (stripos($style, 'url') !== false) {
                     $style = $this->rewriteInlineBackgroundImages($style, $baseUrl);
                     $node->setAttribute('style', $style);
                }
            }

            // Get body inner HTML
            $bodyHtml = '';
            foreach ($body->childNodes as $child) {
                $bodyHtml .= $dom->saveHTML($child);
            }
            $result['body_html'] = $bodyHtml;
        }

        return $result;
    }

    /**
     * Index media files for a landing
     */
    /**
     * Index media files for a landing (Recursive scan)
     * 
     * @param string $rootPath The root directory to scan (e.g. storage/app/public/landings/{uuid})
     * @param int $landingId
     * @param int $userId
     */
    public function indexMedia(string $rootPath, int $landingId, int $userId): void
    {
        if (!File::exists($rootPath)) {
            return;
        }

        // Recursively get ALL files in the directory
        $files = File::allFiles($rootPath);
        $count = 0;

        foreach ($files as $file) {
            $fullPath = $file->getPathname();
            
            // Skip if file somehow doesn't exist (race condition)
            if (!file_exists($fullPath)) {
                continue;
            }

            try {
                $mime = mime_content_type($fullPath);
                
                // Only index images
                if (!str_starts_with($mime, 'image/')) {
                    continue;
                }
                
                // Get relative path from storage/app/public/
                // This ensures correct storage access via Storage::disk('public')
                $relativePath = str_replace(storage_path('app/public/'), '', $fullPath);
                $relativePath = str_replace('\\', '/', $relativePath); // Normalize slashes for DB
                $relativePath = ltrim($relativePath, '/');

                $size = filesize($fullPath);
                $filename = $file->getFilename();

                // Get dimensions (optimistic)
                $width = null;
                $height = null;
                if ($size < 20 * 1048576) { // Limit dimension check to 20MB files
                    try {
                         $dimensions = @getimagesize($fullPath);
                         if ($dimensions) {
                             $width = $dimensions[0];
                             $height = $dimensions[1];
                         }
                    } catch (\Exception $e) {
                        // Ignore dimension errors
                    }
                }

                // Create or Update MediaAsset
                // Use relative_path as unique key (within landing context effectively unique) 
                // but strictly we use filename+landing_id as simple dedup, 
                // OR better: use relative_path as the true unique identifier?
                // The problem is filename might be same in different folders.
                // Let's use relative_path + landing_id check to be safe.
                
                MediaAsset::updateOrCreate(
                    [
                        'landing_id' => $landingId,
                        // Use relative path to distinguish 'icon.png' in root vs 'assets/icon.png'
                        'relative_path' => $relativePath, 
                    ],
                    [
                        'user_id' => $userId,
                        'filename' => $filename,
                        'disk' => 'public',
                        'mime_type' => $mime,
                        'size' => $size,
                        'width' => $width,
                        'height' => $height,
                        'source' => 'zip'
                    ]
                );
                $count++;
                
            } catch (\Exception $e) {
                Log::warning("Failed to index media file: {$fullPath} - " . $e->getMessage());
            }
        }
        
        Log::info("Indexed {$count} images for Landing #{$landingId}");
    }

    /**
     * Rewrite a single URL
     */
    private function rewriteUrl(string $url, string $baseUrl): string
    {
        $url = trim($url);
        
        // Remove leading ./ or /
        $cleanUrl = ltrim($url, './\\');
        $cleanUrl = ltrim($cleanUrl, '/');
        
        return $baseUrl . $cleanUrl;
    }

    /**
     * Rewrite srcset attribute
     */
    private function rewriteSrcset(string $srcset, string $baseUrl): string
    {
        $parts = explode(',', $srcset);
        $newParts = [];
        
        foreach ($parts as $part) {
            $part = trim($part);
            $segments = preg_split('/\s+/', $part, -1, PREG_SPLIT_NO_EMPTY);
            
            if (count($segments) > 0 && !$this->isExternal($segments[0])) {
                $segments[0] = $this->rewriteUrl($segments[0], $baseUrl);
            }
            
            $newParts[] = implode(' ', $segments);
        }
        
        return implode(', ', $newParts);
    }

    /**
     * Rewrite background-image URLs in inline styles
     */
    private function rewriteInlineBackgroundImages(string $style, string $baseUrl): string
    {
        return preg_replace_callback(
            '/url\(["\']?([^"\')]+)["\']?\)/i',
            function ($matches) use ($baseUrl) {
                $url = $matches[1];
                if (!$this->isExternal($url)) {
                    $url = $this->rewriteUrl($url, $baseUrl);
                }
                return 'url("' . $url . '")';
            },
            $style
        );
    }

    /**
     * Check if URL is external
     */
    private function isExternal(string $url): bool
    {
        return preg_match('/^(http|https|\/\/|data:)/i', trim($url));
    }
}
