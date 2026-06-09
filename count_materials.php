<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Table Row Counts ===\n";
$tables = ['users', 'materials', 'sessions', 'quizzes', 'subscriptions', 'ai_usage_logs', 'exam_predictions', 'focus_sessions', 'study_sessions', 'habits', 'jobs'];
foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "- {$table}: {$count} rows\n";
    } catch (\Exception $e) {
        echo "- {$table}: ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== materials Table Indexes ===\n";
try {
    $results = DB::select("
        SELECT indexname, indexdef 
        FROM pg_indexes 
        WHERE tablename = 'materials'
    ");
    foreach ($results as $row) {
        echo "Index: {$row->indexname} | Definition: {$row->indexdef}\n";
    }
} catch (\Exception $e) {
    echo "Failed to get indexes: " . $e->getMessage() . "\n";
}
