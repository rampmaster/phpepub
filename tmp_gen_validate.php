<?php
require __DIR__ . '/vendor/autoload.php';
use Rampmaster\EPub\Core\Format\EpubAdapter;
try {
    $a = new EpubAdapter();
    $out = $a->generate(['title'=>'Test','language'=>'en','author'=>'T','chapters'=>[['name'=>'Intro','file'=>'intro.xhtml','path'=>__DIR__.'/tests/fixtures/simple/index.html']],'buildDir'=>__DIR__.'/tests/build']);
    echo "Generated: $out\n";
    $v = $a->validate($out);
    echo "Validate returned: " . ($v ? 'true' : 'false') . "\n";
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e;
}
