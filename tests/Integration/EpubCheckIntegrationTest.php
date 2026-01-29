<?php

namespace Rampmaster\EPub\Test\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Rampmaster\EPub\Core\Format\EpubAdapter;
use Rampmaster\EPub\Core\EPub;

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

    /**
     * @dataProvider epubVersionProvider
     */
    public function testEpubGenerationAndEpubcheckValidation(string $version)
    {
        $this->runEpubValidationTest($version);
    }

    public static function epubVersionProvider(): array
    {
        return [
            'EPUB 2.0.1' => [EPub::BOOK_VERSION_EPUB2],
            'EPUB 3.0' => [EPub::BOOK_VERSION_EPUB3],
            'EPUB 3.0.1' => [EPub::BOOK_VERSION_EPUB301],
            'EPUB 3.1' => [EPub::BOOK_VERSION_EPUB31],
            'EPUB 3.2' => [EPub::BOOK_VERSION_EPUB32],
        ];
    }

    public function testEpub2CompatibilityWithHtml5Content()
    {
        if (!class_exists(EpubAdapter::class)) {
            $this->markTestSkipped('EpubAdapter class not available');
        }

        $adapter = new EpubAdapter();
        // HTML5 content with section, nav, and epub:type attribute
        $html5Content = '
            <section epub:type="chapter">
                <header><h1>Chapter Title</h1></header>
                <p>Some content.</p>
                <nav><ul><li><a href="#">Link</a></li></ul></nav>
                <footer><p>Footer content</p></footer>
            </section>
        ';

        $outputPath = $adapter->generate([
            'title' => 'EPUB 2 Compatibility Test',
            'language' => 'en',
            'author' => 'CI',
            'version' => EPub::BOOK_VERSION_EPUB2,
            'chapters' => [
                ['name' => 'HTML5 Chapter', 'file' => 'chap1.xhtml', 'content' => $html5Content],
            ],
            'buildDir' => $this->buildDir,
        ]);

        $this->assertFileExists($outputPath, 'Generated epub should exist');

        // Validate with epubcheck
        $cmd = ['php', __DIR__ . '/../../bin/console', 'epubcheck:validate', $outputPath];
        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run();

        $out = $process->getOutput() . $process->getErrorOutput();

        // Cleanup
        if (is_file($outputPath)) {
            @unlink($outputPath);
        }

        $this->assertTrue($process->isSuccessful(), "EPUB 2.0.1 validation failed with HTML5 input. Output:\n" . $out);
    }

    private function runEpubValidationTest(string $version)
    {
        if (!class_exists(EpubAdapter::class)) {
            $this->markTestSkipped('EpubAdapter class not available');
        }

        $adapter = new EpubAdapter();
        $fixture = __DIR__ . '/../../assets/fixtures/simple/index.html';

        // Create a temporary fixture file if it doesn't exist
        if (!file_exists($fixture)) {
            $fixtureDir = dirname($fixture);
            if (!is_dir($fixtureDir)) {
                mkdir($fixtureDir, 0777, true);
            }
            file_put_contents($fixture, '<!doctype html><html><head><meta charset="utf-8" /><title>Fixture Simple</title></head><body><h1>Fixture</h1><p>Contenido de prueba.</p></body></html>');
        }

        $outputPath = $adapter->generate([
            'title' => 'Integration Test ' . $version,
            'language' => 'en',
            'author' => 'CI',
            'version' => $version,
            'chapters' => [
                ['name' => 'Intro', 'file' => 'intro.xhtml', 'path' => $fixture],
                ['name' => 'Fragment', 'file' => 'frag.xhtml', 'content' => '<p>Just a fragment</p>'],
            ],
            'buildDir' => $this->buildDir,
        ]);

        $this->assertFileExists($outputPath, 'Generated epub should exist');

        // Run the console command to validate
        // We use the bin/console command which wraps epubcheck
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

        $this->assertTrue($process->isSuccessful(), "epubcheck failed for version $version. Output:\n" . $out);
    }
}
