<?php

require __DIR__ . '/../../vendor/autoload.php';

use Rampmaster\EPub\Core\Format\Azw3Adapter;

// This example requires 'ebook-convert' (Calibre) to be installed and in the system PATH.

try {
    $adapter = new Azw3Adapter();

    echo "Generating AZW3 file...\n";

    $outputPath = $adapter->generate([
        'title' => 'Kindle Export Example',
        'language' => 'en',
        'author' => 'Kindle User',
        'chapters' => [
            [
                'name' => 'Start',
                'content' => '<h1>Hello Kindle</h1><p>This book was converted to AZW3 format automatically.</p>'
            ]
        ],
        // Optional: specify build directory
        'buildDir' => __DIR__
    ]);

    echo "Success! File created at: $outputPath\n";

} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure Calibre (ebook-convert) is installed.\n";
}
