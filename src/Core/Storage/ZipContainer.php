<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core\Storage;

use Rampmaster\EPub\Helpers\FileHelper;

/**
 * Wrapper for ZipArchive to handle EPUB package creation.
 */
class ZipContainer
{
    private \ZipArchive $zip;
    private string $zipPath;

    public function __construct()
    {
        $this->zipPath = tempnam(sys_get_temp_dir(), 'epub_');
        if ($this->zipPath === false) {
            throw new \RuntimeException('Unable to create temporary file for epub zip');
        }

        $this->zip = new \ZipArchive();
        if ($this->zip->open($this->zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to open temporary zip archive');
        }
    }

    public function __destruct()
    {
        if (is_file($this->zipPath)) {
            @unlink($this->zipPath);
        }
    }

    public function addFromString(string $path, string $content): bool
    {
        return $this->zip->addFromString($path, $content);
    }

    public function addFile(string $filePath, string $entryName): bool
    {
        return $this->zip->addFile($filePath, $entryName);
    }

    public function addEmptyDir(string $dirName): bool
    {
        return $this->zip->addEmptyDir($dirName);
    }

    public function setCompression(string $entryName, int $compressionMethod): bool
    {
        return $this->zip->setCompressionName($entryName, $compressionMethod);
    }

    public function getBook(): string
    {
        $this->zip->close();
        if (!is_file($this->zipPath)) {
            throw new \RuntimeException('Epub temporary file missing');
        }
        $data = file_get_contents($this->zipPath);
        if ($data === false) {
            throw new \RuntimeException('Unable to read epub temporary file');
        }
        return $data;
    }

    public function getBookSize(): int
    {
        $this->zip->close();
        return is_file($this->zipPath) ? filesize($this->zipPath) : 0;
    }

    public function saveBook(string $fileName, string $baseDir = '.'): string
    {
        if (!str_ends_with($fileName, ".epub")) {
            $fileName .= ".epub";
        }

        $fh = fopen($baseDir . '/' . $fileName, "w");
        if (!$fh) {
            throw new \RuntimeException("Unable to open file for writing: {$baseDir}/{$fileName}");
        }

        fputs($fh, $this->getBook());
        fclose($fh);

        return $fileName;
    }
}
