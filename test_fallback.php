<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PromptSetting;
use App\Services\OpenRouterService;

// Ensure prompt setting exists or set it to deepseek/deepseek-chat
PromptSetting::updateOrCreate(
    ['key' => 'ai_default_model'],
    ['value' => 'deepseek/deepseek-chat', 'description' => 'Default model']
);

echo "Model before check: " . PromptSetting::where('key', 'ai_default_model')->value('value') . "\n";

// Call checkFallback using reflection
$service = new OpenRouterService();
$method = new ReflectionMethod(OpenRouterService::class, 'checkFallback');
$method->setAccessible(true);

// Test case 1: duration <= 8
$method->invoke($service, 'deepseek/deepseek-chat', 7.5);
echo "Model after 7.5s: " . PromptSetting::where('key', 'ai_default_model')->value('value') . "\n";

// Test case 2: duration > 8
$method->invoke($service, 'deepseek/deepseek-chat', 8.5);
echo "Model after 8.5s: " . PromptSetting::where('key', 'ai_default_model')->value('value') . "\n";
