<?php

namespace Rampmaster\EPub\Test\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Rampmaster\EPub\Core\Format\EpubAdapter;

class EpubCheckIntegrationTest extends TestCase
{
    private string $buildDir;

    protected function setUp(): void
    {
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

    public function testEpubGenerationAndEpubcheckValidation()
    {
        if (!class_exists(EpubAdapter::class)) {
            $this->markTestSkipped('EpubAdapter class not available');
        }

        $adapter = new EpubAdapter();
        $fixture = __DIR__ . '/../../assets/fixtures/simple/index.html';

        $outputPath = $adapter->generate([
            'title' => 'Integration Test',
            'language' => 'en',
            'author' => 'CI',
            'chapters' => [
                ['name' => 'Intro', 'file' => 'intro.xhtml', 'path' => $fixture],
            ],
            'buildDir' => $this->buildDir,
        ]);

        $this->assertFileExists($outputPath, 'Generated epub should exist');

        // Run the console command to validate
        $cmd = ['php', __DIR__ . '/../../bin/console', 'epubcheck:validate', $outputPath];
        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run();

        // Capture output for debugging on failure
        $out = $process->getOutput() . $process->getErrorOutput();

        // Cleanup artifact
        if (is_file($outputPath)) {
            @unlink($outputPath);
        }

        $this->assertTrue($process->isSuccessful(), "epubcheck failed. Output:\n" . $out);
    }
}
