<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Clear log
file_put_contents('storage/logs/laravel.log', '');

$user = User::where('email', 'student@studypilot.com')->first();
if (!$user) {
    echo "No user found!\n";
    exit;
}

// Log in the user in session by setting session data or manually logging in
Auth::login($user);

$material = Material::where('user_id', $user->id)->first();

echo "=== Dispatching GET /knowledge-hub via HTTP Kernel ===\n";
$start = microtime(true);
$request = Request::create('/knowledge-hub', 'GET');
// We need to associate the session to the request
$request->setLaravelSession($app['session']->driver());
$response = $kernel->handle($request);
$html = $response->getContent();
echo "Status: " . $response->getStatusCode() . "\n";
echo "Index page loaded in " . round(microtime(true) - $start, 4) . " seconds. HTML size: " . strlen($html) . "\n";

echo "\n=== Dispatching GET /knowledge-hub/" . $material->id . " via HTTP Kernel ===\n";
$start = microtime(true);
$request = Request::create('/knowledge-hub/' . $material->id, 'GET');
$request->setLaravelSession($app['session']->driver());
$response = $kernel->handle($request);
$html = $response->getContent();
echo "Status: " . $response->getStatusCode() . "\n";
echo "Show page loaded in " . round(microtime(true) - $start, 4) . " seconds. HTML size: " . strlen($html) . "\n";

echo "\n=== Executed Queries from Log ===\n";
echo file_get_contents('storage/logs/laravel.log');
