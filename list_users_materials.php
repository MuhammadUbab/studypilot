<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Users list and their materials count ===\n";
$users = DB::table('users')->get();
foreach ($users as $u) {
    $materialsCount = DB::table('materials')->where('user_id', $u->id)->count();
    $studySessionsCount = DB::table('study_sessions')->where('user_id', $u->id)->count();
    echo "- ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Materials: {$materialsCount} | StudySessions: {$studySessionsCount}\n";
}

echo "\n=== Materials list ===\n";
$materials = DB::table('materials')->get();
foreach ($materials as $m) {
    echo "- ID: {$m->id} | User ID: {$m->user_id} | Title: {$m->judul} | Created At: {$m->created_at}\n";
}
