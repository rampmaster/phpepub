<?php
// tools/run-phpstan.php
$cmd = ['vendor/bin/phpstan', 'clear-result-cache'];
passthru(implode(' ', array_map('escapeshellcmd', $cmd)), $exit);

$cmd = ['vendor/bin/phpstan', 'analyse', '-c', 'phpstan.neon'];
$cmd[] = '--no-progress';
passthru(implode(' ', array_map('escapeshellcmd', $cmd)), $exit);
exit($exit);
