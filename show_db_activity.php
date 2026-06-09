<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Active DB Connections & Queries ===\n";
try {
    $activities = DB::select("
        SELECT pid, state, query, query_start, now() - query_start AS duration, wait_event_type, wait_event
        FROM pg_stat_activity
        WHERE query NOT LIKE '%pg_stat_activity%' AND state IS NOT NULL
        ORDER BY duration DESC
    ");
    foreach ($activities as $act) {
        echo "PID: {$act->pid} | State: {$act->state} | Duration: {$act->duration} | Wait Event: {$act->wait_event_type}/{$act->wait_event}\nQuery: {$act->query}\n----------------------------------------\n";
    }
} catch (\Exception $e) {
    echo "Error querying pg_stat_activity: " . $e->getMessage() . "\n";
}

echo "\n=== Database Locks ===\n";
try {
    $locks = DB::select("
        SELECT locktype, database, relation::regclass, mode, granted, pid
        FROM pg_locks
        ORDER BY pid
    ");
    foreach ($locks as $lock) {
        echo "PID: {$lock->pid} | Lock Type: {$lock->locktype} | Relation: {$lock->relation} | Mode: {$lock->mode} | Granted: " . ($lock->granted ? 'YES' : 'NO') . "\n";
    }
} catch (\Exception $e) {
    echo "Error querying pg_locks: " . $e->getMessage() . "\n";
}
