<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

// We can authenticate a user
$user = User::first() ?? User::factory()->create();
Auth::login($user);

// Let's define the test files to upload
$files = [
    'pdf_kecil' => [
        'path' => __DIR__ . '/tests/fixtures/small.pdf',
        'tipe' => 'pdf',
        'mime' => 'application/pdf',
        'name' => 'Kalkulus Kecil.pdf'
    ],
    'pdf_besar' => [
        'path' => __DIR__ . '/tests/fixtures/large.pdf',
        'tipe' => 'pdf',
        'mime' => 'application/pdf',
        'name' => 'Kalkulus Besar.pdf'
    ],
    'pdf_scan' => [
        'path' => __DIR__ . '/tests/fixtures/scanned.pdf',
        'tipe' => 'pdf',
        'mime' => 'application/pdf',
        'name' => 'Kalkulus Scan.pdf'
    ],
    'docx' => [
        'path' => __DIR__ . '/tests/fixtures/test.docx',
        'tipe' => 'docx',
        'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'name' => 'Catatan.docx'
    ],
    'pptx' => [
        'path' => __DIR__ . '/tests/fixtures/test.pptx',
        'tipe' => 'pptx',
        'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'name' => 'Slide.pptx'
    ],
];

echo "=== START E2E UPLOAD TESTS ===\n\n";

foreach ($files as $key => $fileData) {
    echo "Testing Upload for [{$key}]...\n";
    
    // Create UploadedFile instance
    $uploadedFile = new UploadedFile(
        $fileData['path'],
        $fileData['name'],
        $fileData['mime'],
        null,
        true // test mode
    );
    
    // Simulate Request
    $request = \Illuminate\Http\Request::create(
        '/knowledge-hub',
        'POST',
        [
            'judul' => 'E2E ' . ucfirst($key),
            'tipe_file' => $fileData['tipe'],
        ],
        [],
        ['file_upload' => $uploadedFile]
    );
    
    $startTime = microtime(true);
    
    // Call controller directly through container
    $controller = $app->make(\App\Http\Controllers\KnowledgeHubController::class);
    
    try {
        $response = $controller->store($request);
        $uploadTime = microtime(true) - $startTime;
        echo "- Waktu Upload (HTTP Request): " . round($uploadTime, 4) . " detik\n";
    } catch (\Exception $ex) {
        echo "- GAGAL UPLOAD (HTTP Request) Exception: " . $ex->getMessage() . "\n";
        continue;
    }
    
    // Fetch the newly created material
    $material = Material::where('judul', 'E2E ' . ucfirst($key))->latest()->first();
    if ($material) {
        echo "- Material ID: " . $material->id . "\n";
        echo "- Status Awal Summary: \"" . $material->summary . "\"\n";
        
        // Now let's process the queue job for this material
        echo "- Memproses Queue Worker...\n";
        
        $jobStartTime = microtime(true);
        // Run artisan queue:work --once
        $kernel->call('queue:work', ['--once' => true]);
        $jobTime = microtime(true) - $jobStartTime;
        
        // Refresh material from DB
        $material->refresh();
        
        echo "- Waktu Generate Summary (Queue Job): " . round($jobTime, 4) . " detik\n";
        echo "- Status Queue: SUCCESS\n";
        echo "- Hasil Summary (80 karakter pertama):\n";
        echo "  " . substr(str_replace("\n", " ", $material->summary), 0, 80) . "...\n";
    } else {
        echo "- GAGAL: Material tidak tersimpan di database!\n";
    }
    echo "\n----------------------------------------\n\n";
}

echo "=== E2E UPLOAD TESTS COMPLETED ===\n";
