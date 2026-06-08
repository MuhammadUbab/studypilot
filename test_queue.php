<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Material;
use App\Jobs\GenerateSummaryJob;

$user = User::first();
if (!$user) {
    $user = User::factory()->create();
}

$material = Material::create([
    'user_id' => $user->id,
    'judul' => 'Test Queue Material ' . time(),
    'tipe_file' => 'pdf',
    'file_url' => 'http://example.com/test.pdf',
    'summary' => 'Ringkasan sedang diproses, silakan buka kembali beberapa saat lagi.'
]);

dispatch(new GenerateSummaryJob($material, 'Materi: Test Queue Material', 'System: Ringkas materi ini'));

echo "DISPATCHED_ID: " . $material->id . "\n";
