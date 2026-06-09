<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Clear the log file so we only see new queries
file_put_contents('storage/logs/laravel.log', '');

$user = User::where('email', 'student@studypilot.com')->first();
if (!$user) {
    echo "No user found!\n";
    exit;
}
Auth::login($user);

$material = Material::where('user_id', $user->id)->first();
if (!$material) {
    $material = Material::create([
        'user_id' => $user->id,
        'judul' => 'Kalkulus Uji Performa',
        'tipe_file' => 'pdf',
        'file_url' => 'http://example.com/test.pdf',
        'summary' => 'Ringkasan kalkulus uji performa...'
    ]);
}

echo "=== Loading and rendering index page (GET /knowledge-hub) ===\n";
$start = microtime(true);
try {
    $request = \Illuminate\Http\Request::create('/knowledge-hub', 'GET');
    $controller = $app->make(\App\Http\Controllers\KnowledgeHubController::class);
    $response = $controller->index();
    $html = $response->render();
    echo "Index page rendered in " . round(microtime(true) - $start, 4) . " seconds. HTML size: " . strlen($html) . "\n";
} catch (\Exception $e) {
    echo "Index page failed: " . $e->getMessage() . "\n";
}

echo "\n=== Loading and rendering show page (GET /knowledge-hub/" . $material->id . ") ===\n";
$start = microtime(true);
try {
    $request = \Illuminate\Http\Request::create('/knowledge-hub/' . $material->id, 'GET');
    $controller = $app->make(\App\Http\Controllers\KnowledgeHubController::class);
    $response = $controller->show($material);
    $html = $response->render();
    echo "Show page rendered in " . round(microtime(true) - $start, 4) . " seconds. HTML size: " . strlen($html) . "\n";
} catch (\Exception $e) {
    echo "Show page failed: " . $e->getMessage() . "\n";
}

echo "\n=== Executed Queries from Log ===\n";
echo file_get_contents('storage/logs/laravel.log');
