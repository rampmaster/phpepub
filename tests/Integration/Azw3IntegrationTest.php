<?php

namespace Rampmaster\EPub\Test\Integration;

use PHPUnit\Framework\TestCase;
use Rampmaster\EPub\Core\Format\Azw3Adapter;
use Symfony\Component\Process\Process;

class Azw3IntegrationTest extends TestCase
{
    private string $buildDir;

    protected function setUp(): void
    {
        // Check for ebook-convert
        $process = new Process(['which', 'ebook-convert']);
        $process->run();
        if (!$process->isSuccessful() || empty(trim($process->getOutput()))) {
            $this->markTestSkipped('ebook-convert (Calibre) not found. Skipping AZW3 integration test.');
        }

        $this->buildDir = __DIR__ . '/../../build';
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

    private function rrmdir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            $path = $dir . '/' . $object;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    public function testAzw3Generation()
    {
        $adapter = new Azw3Adapter();

        // Create a simple fixture content
        $content = '<h1>Chapter 1</h1><p>This is a test for AZW3 conversion.</p>';

        $outputPath = $adapter->generate([
            'title' => 'AZW3 Integration Test',
            'language' => 'en',
            'author' => 'CI',
            'chapters' => [
                ['name' => 'Chapter 1', 'file' => 'chap1.xhtml', 'content' => $content],
            ],
            'buildDir' => $this->buildDir,
        ]);

        $this->assertFileExists($outputPath, 'Generated AZW3 file should exist');
        $this->assertStringEndsWith('.azw3', $outputPath);
        $this->assertGreaterThan(0, filesize($outputPath), 'AZW3 file should not be empty');
    }
}
