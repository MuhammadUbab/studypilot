<?php
$cmd = 'C:\\Users\\user\\.gemini\antigravity\\scratch\\php\\php.exe vendor/phpunit/phpunit/phpunit --filter=KnowledgeHubTest 2>&1';
echo "Running: $cmd\n";
passthru($cmd, $retval);
echo "\nExit code: $retval\n";
