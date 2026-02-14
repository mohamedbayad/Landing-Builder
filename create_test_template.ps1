# PowerShell Script: Create test template ZIP
# Structure:
# /index.html
# /assets/css/style.css
# /assets/js/index.js
# /media/imgs/example.jpeg

$tempDir = Join-Path $env:TEMP ("test_template_" + (Get-Date -Format "yyyyMMddHHmmss"))
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
New-Item -ItemType Directory -Path "$tempDir/assets/css" -Force | Out-Null
New-Item -ItemType Directory -Path "$tempDir/assets/js" -Force | Out-Null
New-Item -ItemType Directory -Path "$tempDir/media/imgs" -Force | Out-Null

# Create index.html
$indexHtml = @'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Landing Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Test Landing Page</h1>
        <p>This is a test landing page with proper structure.</p>
        <img src="/media/imgs/example.jpeg" alt="Example Image">
        
        <div class="bg-test" style="background-image: url(/media/imgs/example.jpeg);">
            Background image test
        </div>
    </div>
    
    <script src="/assets/js/index.js"></script>
</body>
</html>
'@
Set-Content -Path "$tempDir/index.html" -Value $indexHtml

# Create style.css
$css = @'
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f5f5f5;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #2563eb;
}

img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.bg-test {
    width: 300px;
    height: 200px;
    background-size: cover;
    background-position: center;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}
'@
Set-Content -Path "$tempDir/assets/css/style.css" -Value $css

# Create index.js
$js = @'
console.log('Test Landing Page Loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    const heading = document.querySelector('h1');
    if (heading) {
        heading.style.transition = 'color 0.3s';
        heading.addEventListener('mouseenter', function() {
            this.style.color = '#1d4ed8';
        });
        heading.addEventListener('mouseleave', function() {
            this.style.color = '#2563eb';
        });
    }
});
'@
Set-Content -Path "$tempDir/assets/js/index.js" -Value $js

# Create a simple test image (1x1 red pixel PNG)
$imageBytes = [System.Convert]::FromBase64String("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==")
[System.IO.File]::WriteAllBytes("$tempDir/media/imgs/example.jpeg", $imageBytes)
[System.IO.File]::WriteAllBytes("$tempDir/screenshot.png", $imageBytes)

# Create ZIP
$zipPath = Join-Path (Get-Location) "test_template.zip"
if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

Compress-Archive -Path "$tempDir/*" -DestinationPath $zipPath

# Clean up
Remove-Item -Path $tempDir -Recurse -Force

Write-Host "✅ ZIP created successfully: test_template.zip`n" -ForegroundColor Green
Write-Host "Structure:" -ForegroundColor Cyan
Write-Host "  /index.html"
Write-Host "  /assets/css/style.css"
Write-Host "  /assets/js/index.js"
Write-Host "  /media/imgs/example.jpeg"
Write-Host "  /screenshot.png`n"
Write-Host "Expected behavior after import:" -ForegroundColor Yellow
Write-Host "  ✅ <link rel='stylesheet' href='/storage/landings/{uuid}/assets/css/style.css'> → HEAD" -ForegroundColor Green
Write-Host "  ✅ Google Fonts → HEAD" -ForegroundColor Green
Write-Host "  ✅ <img src='/storage/landings/{uuid}/media/imgs/example.jpeg'> → BODY" -ForegroundColor Green
Write-Host "  ✅ <script src='/storage/landings/{uuid}/assets/js/index.js'> → BODY" -ForegroundColor Green
Write-Host "  ✅ background-image: url('/storage/landings/{uuid}/media/imgs/example.jpeg') → BODY" -ForegroundColor Green
