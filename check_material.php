<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$material = \App\Models\Material::find(4);
echo "MATERIAL ID: " . $material->id . "\n";
echo "JUDUL: " . $material->judul . "\n";
echo "SUMMARY CONTENT:\n" . $material->summary . "\n";
