<?php

namespace Rampmaster\EPub\Test\Unit\Security;

use PHPUnit\Framework\TestCase;
use Rampmaster\EPub\Helpers\FileHelper;

class FileHelperPathTest extends TestCase
{
    public function testIsPathInsideTrue()
    {
        $repoRoot = realpath(__DIR__ . '/../../..');
        $tmpFile = __DIR__ . '/../../../assets/fixtures/simple/index.html';
        $this->assertFileExists($tmpFile, 'Fixture path should exist for test');
        $this->assertTrue(FileHelper::isPathInside($tmpFile, $repoRoot));
    }

    public function testIsPathInsideFalse()
    {
        $repoRoot = realpath(__DIR__ . '/../../..');
        // choose a path outside repo (root)
        $outside = DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'passwd';
        $this->assertFalse(FileHelper::isPathInside($outside, $repoRoot));
    }

    public function testIsSafeBuildDir()
    {
        $repoRoot = realpath(__DIR__ . '/../../..');
        $build = $repoRoot . '/tests/build';
        @mkdir($build, 0775, true);
        $this->assertTrue(FileHelper::isSafeBuildDir($build, $repoRoot));
        @rmdir($build);
    }
}
