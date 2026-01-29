<?php
// tools/run-composer-audit.php
// Runs `composer audit --format=json` and exits non-zero if any advisory severity >= threshold.
$cwd = getcwd();
$threshold = getenv('AUDIT_THRESHOLD') ?: getenv('SECURITY_AUDIT_THRESHOLD') ?: 'HIGH';
$mapping = ['INFO' => 0, 'LOW' => 1, 'MEDIUM' => 2, 'HIGH' => 3, 'CRITICAL' => 4];
$thresholdLevel = $mapping[strtoupper($threshold)] ?? 3;

// Run composer audit
$cmd = 'composer audit --format=json 2>/dev/null';
$output = null;
$return = 0;
exec($cmd, $output, $return);
$raw = implode("\n", $output);
if (trim($raw) === '') {
    // No output, assume no advisories (or composer older). Return the composer exit code.
    exit($return);
}
$json = json_decode($raw, true);
if ($json === null) {
    // Could not parse JSON, fail safe by returning composer exit code
    echo "Could not parse composer audit output.\n";
    echo $raw . "\n";
    exit($return);
}
$events = $json['advisories'] ?? [];
$maxFound = -1;
foreach ($events as $package => $advs) {
    foreach ($advs as $adv) {
        $severity = strtoupper($adv['severity'] ?? 'UNKNOWN');
        $sevLevel = $mapping[$severity] ?? -1;
        if ($sevLevel > $maxFound) $maxFound = $sevLevel;
    }
}
if ($maxFound >= $thresholdLevel && $maxFound !== -1) {
    echo "Composer audit found advisories at or above threshold ({$threshold}). Failing.\n";
    // Print summary
    echo $raw . "\n";
    exit(1);
}
// Otherwise succeed
echo "Composer audit: no advisories at or above {$threshold}\n";
exit(0);
