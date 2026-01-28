<?php
// Simple wrapper to run epubcheck (binary or jar) using symfony/process
// Usage:
// php bin/epubcheck.php <file>
// php bin/epubcheck.php --version

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Process\Process;

function runProcess(array $cmd, int $timeout = 120): int {
    $proc = new Process($cmd);
    $proc->setTimeout($timeout);
    try {
        $proc->run(function ($type, $buffer) {
            echo $buffer;
        });
        return $proc->getExitCode() ?? 1;
    } catch (Throwable $e) {
        fwrite(STDERR, "Process failed: " . $e->getMessage() . PHP_EOL);
        return 2;
    }
}

$argv = $_SERVER['argv'];
array_shift($argv); // remove script name

// Support --version
if (count($argv) > 0 && ($argv[0] === '--version' || $argv[0] === '-v')) {
    // Prefer checking if binary exists using shell which for robustness
    $binaryPath = trim(@shell_exec('which epubcheck 2>/dev/null')) ?: '';
    if ($binaryPath !== '') {
        $p = new Process([$binaryPath, '--version']);
        $p->run();
        $out = $p->getOutput() . $p->getErrorOutput();
        if (!empty(trim($out))) {
            echo $out;
            exit(0);
        }
    }

    // Try common jar locations
    $candidates = [
        __DIR__ . '/../epubcheck/epubcheck.jar',
        '/opt/epubcheck/epubcheck.jar',
    ];
    foreach ($candidates as $jar) {
        if (is_file($jar)) {
            return runProcess(['java', '-jar', $jar, '--version']);
        }
    }

    fwrite(STDERR, "No epubcheck binary or jar found\n");
    exit(3);
}

// Expect a file path argument or EPUB_FILE env var
if (count($argv) === 0) {
    $file = getenv('EPUB_FILE') ?: null;
    if (empty($file)) {
        fwrite(STDERR, "Usage: php bin/epubcheck.php <file>\nOr set EPUB_FILE environment variable\n");
        exit(2);
    }
} else {
    $file = $argv[0];
}

if (!is_file($file)) {
    fwrite(STDERR, "File not found: $file\n");
    exit(2);
}

// Prefer system binary
$binaryPath = trim(@shell_exec('which epubcheck 2>/dev/null')) ?: '';
if ($binaryPath !== '') {
    $exit = runProcess([$binaryPath, $file]);
    exit($exit);
}

// Otherwise try known jar locations
$candidates = [
    __DIR__ . '/../epubcheck/epubcheck.jar',
    '/opt/epubcheck/epubcheck.jar',
];
foreach ($candidates as $jar) {
    if (is_file($jar)) {
        $exit = runProcess(['java', '-jar', $jar, $file]);
        exit($exit);
    }
}

// As last resort try to find any jar under ./epubcheck lib
$found = false;
$base = __DIR__ . '/../epubcheck';
if (is_dir($base)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($it as $f) {
        if ($f->isFile() && preg_match('/epubcheck.*\\.jar$/i', $f->getFilename())) {
            $found = $f->getPathname();
            break;
        }
    }
}
if ($found) {
    exit(runProcess(['java', '-jar', $found, $file]));
}

fwrite(STDERR, "No epubcheck installation found (binary or jar).\n");
exit(3);
