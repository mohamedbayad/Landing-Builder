<?php

require 'vendor/autoload.php';
require_once 'app/Services/AI/Resolvers/ModelCapabilityResolverInterface.php';
require_once 'app/Services/AI/Resolvers/GeminiModelCapabilityResolver.php';

use App\Services\AI\Resolvers\GeminiModelCapabilityResolver;

$resolver = new GeminiModelCapabilityResolver();

$testModels = [
    'gemini-2.5-flash-image',
    'gemini-1.5-pro',
    'gemini-1.5-flash',
    'text-embedding-004',
    'gemini-1.5-flash-8b',
];

echo "Testing Gemini Model Capability Resolution:\n";
echo str_repeat("=", 40) . "\n";

foreach ($testModels as $modelId) {
    echo "MODEL: $modelId\n";
    $caps = $resolver->resolveCapabilities($modelId);
    foreach ($caps as $cap => $value) {
        $status = $value ? "[YES]" : "[ NO]";
        echo "  $status $cap\n";
    }
    echo str_repeat("-", 40) . "\n";
}
echo "DONE\n";
