<?php

/**
 * PHPePub
 * <FileHelper.php description here>
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2015- A. Grandt
 * @license   GNU LGPL 2.1
 */

declare(strict_types=1);

namespace Rampmaster\EPub\Helpers;

use Rampmaster\EPub\Core\EPub;
use Rampmaster\EPub\Core\StaticData;
use Symfony\Component\Filesystem\Path;

class FileHelper
{
    protected static $isCurlInstalled;

    protected static $isFileGetContentsInstalled;

    protected static $isFileGetContentsExtInstalled;

    /**
     * @return mixed
     */
    public static function getIsCurlInstalled()
    {
        if (!isset(self::$isCurlInstalled)) {
            self::$isCurlInstalled = extension_loaded('curl') && function_exists('curl_version');
        }
        return self::$isCurlInstalled;
    }

    /**
     * @return mixed
     */
    public static function getIsFileGetContentsInstalled()
    {
        if (!isset(self::$isFileGetContentsInstalled)) {
            self::$isFileGetContentsInstalled = function_exists('file_get_contents');
        }
        return self::$isFileGetContentsInstalled;
    }

    /**
     * @return mixed
     */
    public static function getIsFileGetContentsExtInstalled()
    {
        if (!isset(self::$isFileGetContentsExtInstalled)) {
            self::$isFileGetContentsExtInstalled = self::getIsFileGetContentsInstalled() && ini_get('allow_url_fopen');
        }
        return self::$isFileGetContentsExtInstalled;
    }

    /**
     * Remove disallowed characters from string to get a nearly safe filename
     *
     * @param string $fileName
     *
     * @return mixed|string
     */
    public static function sanitizeFileName($fileName)
    {
        $fileName1 = str_replace(StaticData::$forbiddenCharacters, '', $fileName);
        $fileName2 = preg_replace('/[\s-]+/', '-', $fileName1);

        return trim($fileName2, '.-_');
    }

    /**
     * Get file contents, using curl if available, else file_get_contents
     *
     * @param string $source
     * @param bool   $toTempFile
     *
     * @return bool|mixed|null|string
     */
    public static function getFileContents($source, $toTempFile = false)
    {
        $isExternal = preg_match('#^(http|ftp)s?://#i', $source) == 1;

        if ($isExternal && FileHelper::getIsCurlInstalled()) {
            $ch = curl_init();
            $outFile = null;
            $fp = null;

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, str_replace(" ", "%20", $source));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 4096);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects
            curl_setopt($ch, CURLOPT_ENCODING, ""); // handle all encodings
            curl_setopt($ch, CURLOPT_USERAGENT, "EPub (Version " . EPub::VERSION . ") by A. Grandt"); // who am i
            curl_setopt($ch, CURLOPT_AUTOREFERER, true); // set referer on redirect
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // timeout on connect
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // timeout on response
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // stop after 10 redirects
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disabled SSL Cert checks

            if ($toTempFile) {
                $outFile = tempnam(sys_get_temp_dir(), "EPub_v" . EPub::VERSION . "_");
                $fp = fopen($outFile, "w+b");
                curl_setopt($ch, CURLOPT_FILE, $fp);

                $res = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);
                fclose($fp);
            } else {
                $res = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);
            }

            if ($info['http_code'] == 200 && $res != false) {
                if ($toTempFile) {
                    return $outFile;
                }

                return $res;
            }

            return false;
        }

        if (FileHelper::getIsFileGetContentsInstalled() && (!$isExternal || FileHelper::getIsFileGetContentsExtInstalled())) {
            @$data = file_get_contents($source);

            return $data;
        }

        return false;
    }

    /**
     * Cleanup the filepath, and remove leading . and / characters.
     *
     * Sometimes, when a path is generated from multiple fragments,
     *  you can get something like "../data/html/../images/image.jpeg"
     * ePub files don't work well with that, this will normalize that
     *  example path to "data/images/image.jpeg"
     *
     * @param string $fileName
     *
     * @return string normalized filename
     */
    public static function normalizeFileName($fileName)
    {
        return preg_replace('#^[/\.]+#i', "", Path::canonicalize($fileName));
    }

    /**
     * Sanitize a path intended to be stored in the EPUB ZIP archive.
     * Returns the normalized safe path, or FALSE if the path is unsafe.
     *
     * @param string $fileName
     * @param bool $allowMeta Allow entries under META-INF/ (default false)
     * @return string|false
     */
    public static function sanitizeZipPath(string $fileName, bool $allowMeta = false)
    {
        // Remove NUL bytes and normalize separators
        $fileName = str_replace("\0", '', $fileName);
        $fileName = str_replace('\\', '/', $fileName);

        // Canonicalize (resolves .. and .)
        $canonical = Path::canonicalize($fileName);

        // Remove leading ./ or / sequences
        $canonical = preg_replace('#^[/\\\.]+#', '', $canonical);

        // Reject empty
        if ($canonical === '' || $canonical === '.' || $canonical === '..') {
            return false;
        }

        // Reject any remaining upward traversal segments
        $parts = explode('/', $canonical);
        foreach ($parts as $part) {
            if ($part === '..') {
                return false;
            }
        }

        // Reject absolute Windows drive letters
        if (preg_match('#^[A-Za-z]:#', $canonical)) {
            return false;
        }

        // If not allowing META-INF, ensure path does not start with META-INF/
        if (!$allowMeta) {
            if (str_starts_with(strtolower($canonical), 'meta-inf/')) {
                return false;
            }
        }

        // Final sanitize: remove disallowed characters from each segment
        $segments = array_map(function ($seg) {
            return preg_replace('/[\x00-\x1F\x7F]/', '', $seg);
        }, $parts);

        $safe = implode('/', $segments);
        // Prevent leading dots, hyphens issues
        $safe = trim($safe, './\\ ');

        return $safe;
    }

    /**
     * Check whether a filesystem path is inside a base directory.
     * Returns true only if both realpath($path) and realpath($baseDir) exist and
     * the resolved path is located under the base directory.
     */
    public static function isPathInside(string $path, string $baseDir): bool
    {
        $rp = @realpath($path);
        $rb = @realpath($baseDir);
        // If realpath failed for the path but the path exists or looks absolute, try canonicalize
        if ($rp === false) {
            if (file_exists($path) || str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('#^[A-Za-z]:#', $path)) {
                $rp = Path::canonicalize($path);
            }
        }
        if ($rb === false) {
            $rb = Path::canonicalize($baseDir);
        }
        // If $rp is still false, it means the path doesn't exist and couldn't be canonicalized as absolute
        if ($rp === '' || $rb === '') {
            return false;
        }

        $rb = rtrim($rb, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return str_starts_with($rp, $rb);
    }

    /**
     * Check whether a build directory is safe (inside repository root).
     * Project root is inferred relative to this file if not provided.
     */
    public static function isSafeBuildDir(string $buildDir, ?string $repoRoot = null): bool
    {
        if ($repoRoot === null) {
            // repo root is four levels up from src/Helpers
            $repoRoot = dirname(__DIR__, 3);
        }
        $rb = @realpath($repoRoot);
        $bd = @realpath($buildDir);
        if ($rb === false || $bd === false) {
            return false;
        }
        $rb = rtrim($rb, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return str_starts_with($bd, $rb);
    }
}
