<?php

namespace Rampmaster\EPub\Test\Security\Fuzzing;

use PHPUnit\Framework\TestCase;
use Rampmaster\EPub\Core\Format\EpubAdapter;

class FuzzingPathsTest extends TestCase
{
    private string $buildDir;

    protected function setUp(): void
    {
        $this->buildDir = __DIR__ . '/../../../../tests/build_fuzz';
        if (is_dir($this->buildDir)) {
            $this->rrmdir($this->buildDir);
        }
        mkdir($this->buildDir, 0775, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->buildDir)) {
            $this->rrmdir($this->buildDir);
        }
    }

    private function rrmdir($dir) {
        if (!is_dir($dir)) return;
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') continue;
            $path = $dir . '/' . $object;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    public function testPathTraversalRejected()
    {
        $adapter = new EpubAdapter();
        $malicious = ['name' => 'Bad', 'file' => 'bad.xhtml', 'path' => __DIR__ . '/../../../../../../etc/passwd'];
        $this->expectException(\RuntimeException::class);
        $adapter->generate([
            'title' => 'Fuzz',
            'language' => 'en',
            'author' => 'Fuzzer',
            'chapters' => [$malicious],
            'buildDir' => $this->buildDir
        ]);
    }

    public function testFileUriRejected()
    {
        $adapter = new EpubAdapter();
        $malicious = ['name' => 'FileUri', 'file' => 'f.xhtml', 'path' => 'file:///etc/passwd'];
        $this->expectException(\RuntimeException::class);
        $adapter->generate([
            'title' => 'Fuzz',
            'language' => 'en',
            'author' => 'Fuzzer',
            'chapters' => [$malicious],
            'buildDir' => $this->buildDir
        ]);
    }

    public function testRemoteUrlRedirectToLocal()
    {
        $adapter = new EpubAdapter();
        $malicious = ['name' => 'Remote', 'file' => 'r.xhtml', 'path' => 'http://example.com/../../../etc/passwd'];
        // This will not be fetched due to our guard; we expect either false content or exception when path treated as file
        $this->expectException(\RuntimeException::class);
        $adapter->generate([
            'title' => 'Fuzz',
            'language' => 'en',
            'author' => 'Fuzzer',
            'chapters' => [$malicious],
            'buildDir' => $this->buildDir
        ]);
    }

    public function testHtmlWithScriptsSanitizedOrAllowed()
    {
        $adapter = new EpubAdapter();
        $content = '<h1>Good</h1><script>alert("x")</script><p>text</p>';
        $out = $adapter->generate([
            'title' => 'Fuzz',
            'language' => 'en',
            'author' => 'Fuzzer',
            'chapters' => [[ 'name' => 'Safe', 'file' => 's.xhtml', 'content' => $content ]],
            'buildDir' => $this->buildDir
        ]);
        $this->assertFileExists($out);
        // ensure artifact inside build dir
        $this->assertStringStartsWith(realpath($this->buildDir), realpath($out));
    }

    public function testLongFileNameRejectedOrSanitized()
    {
        $adapter = new EpubAdapter();
        $long = str_repeat('a', 500) . '.xhtml';
        $this->expectException(\RuntimeException::class);
        $adapter->generate([
            'title' => 'Fuzz',
            'language' => 'en',
            'author' => 'Fuzzer',
            'chapters' => [[ 'name' => 'Long', 'file' => $long, 'content' => '<p>x</p>' ]],
            'buildDir' => $this->buildDir
        ]);
    }
}
