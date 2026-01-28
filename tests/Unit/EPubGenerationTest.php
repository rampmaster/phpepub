<?php
namespace Rampmaster\EPub\Test\Unit;

use PHPUnit\Framework\TestCase;
use Rampmaster\EPub\Core\Format\EpubAdapter;
use ZipArchive;

class EPubGenerationTest extends TestCase {
    public function setUp(): void {
        // Ensure build dir is clean
        $build = __DIR__ . '/../build';
        if (is_dir($build)) {
            $this->rrmdir($build);
        }
        mkdir($build, 0775, true);
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

    public function testGenerateEpubFromFixture() {
        $adapter = new EpubAdapter();
        $fixture = __DIR__ . '/fixtures/simple/index.html';
        $output = $adapter->generate([
            'title' => 'Test Fixture',
            'language' => 'en',
            'author' => 'Tester',
            'chapters' => [
                ['name' => 'Intro', 'file' => 'intro.xhtml', 'path' => $fixture]
            ],
            'buildDir' => __DIR__ . '/build'
        ]);

        $this->assertFileExists($output, 'El archivo epub debe existir');

        // Validate using adapter
        $valid = $adapter->validate($output);

        $this->assertTrue($valid, 'El epub debe ser válido según las comprobaciones disponibles');

        // Open zip and assert presence of mimetype and OEBPS/book.opf
        $zip = new ZipArchive();
        $res = $zip->open($output);
        $this->assertTrue($res === true, 'No se pudo abrir el archivo epub como zip');

        $this->assertNotFalse($zip->locateName('mimetype', ZipArchive::FL_NODIR), 'mimetype debe existir');
        //TODO: Migrate to new system
        //$this->assertNotFalse($zip->locateName('OEBPS/book.opf', ZipArchive::FL_NODIR), 'OEBPS/book.opf debe existir');

        $zip->close();
    }

    public function testFixturesExist(): void
    {
        $this->assertFileExists(__DIR__ . '/fixtures/simple/index.html');
    }

    public function testBasicGenerationPlaceholder(): void
    {
        // Placeholder kept for compatibility
        $this->assertTrue(true);
    }
}
