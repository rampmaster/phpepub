<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core\Content;

use Rampmaster\EPub\Core\EPub;
use Rampmaster\EPub\Helpers\FileHelper;
use Rampmaster\EPub\Helpers\ImageHelper;
use Rampmaster\EPub\Helpers\StringHelper;
use DOMDocument;

/**
 * Handles processing of HTML content for chapters.
 */
class ContentProcessor
{
    private EPub $book;

    public function __construct(EPub $book)
    {
        $this->book = $book;
    }

    /**
     * Process external references from a HTML to the book.
     *
     * @param mixed  &$doc               (referenced)
     * @param int    $externalReferences
     * @param string $baseDir
     * @param string $htmlDir
     * @return bool
     */
    public function processChapterExternalReferences(&$doc, $externalReferences, $baseDir, $htmlDir)
    {
        if ($this->book->isFinalized() || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return false;
        }

        $backPath = preg_replace('#[^/]+/#i', "../", $htmlDir);
        $isDocAString = is_string($doc);
        $xmlDoc = null;

        if ($isDocAString) {
            $doc = StringHelper::removeComments($doc);

            $xmlDoc = new DOMDocument();
            @$xmlDoc->loadHTML($doc);
        } else {
            $xmlDoc = $doc;
        }

        $this->processChapterStyles($xmlDoc, $externalReferences, $baseDir, $htmlDir);
        $this->processChapterLinks($xmlDoc, $externalReferences, $baseDir, $htmlDir, $backPath);
        $this->processChapterImages($xmlDoc, $externalReferences, $baseDir, $htmlDir, $backPath);
        $this->processChapterSources($xmlDoc, $externalReferences, $baseDir, $htmlDir, $backPath);

        if ($isDocAString) {
            $htmlNode = $xmlDoc->getElementsByTagName("html");
            $headNode = $xmlDoc->getElementsByTagName("head");
            $bodyNode = $xmlDoc->getElementsByTagName("body");

            $htmlNS = "";
            for ($index = 0; $index < $htmlNode->item(0)->attributes->length; $index++) {
                $nodeName = $htmlNode->item(0)->attributes->item($index)->nodeName;
                $nodeValue = $htmlNode->item(0)->attributes->item($index)->nodeValue;

                if ($nodeName !== "xmlns") {
                    $htmlNS .= " $nodeName=\"$nodeValue\"";
                }
            }

            $xml = new DOMDocument('1.0', "utf-8");
            $xml->lookupPrefix("http://www.w3.org/1999/xhtml");
            $xml->preserveWhiteSpace = false;
            $xml->formatOutput = true;

            $xml2Doc = new DOMDocument('1.0', "utf-8");
            $xml2Doc->lookupPrefix("http://www.w3.org/1999/xhtml");
            $xml2Doc->loadXML("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
                . "   \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
                . "<html xmlns=\"http://www.w3.org/1999/xhtml\"" . $htmlNS . ">\n</html>\n");
            $html = $xml2Doc->getElementsByTagName("html")->item(0);
            $html->appendChild($xml2Doc->importNode($headNode->item(0), true));
            $html->appendChild($xml2Doc->importNode($bodyNode->item(0), true));

            $xml->loadXML($xml2Doc->saveXML());
            $doc = $xml->saveXML();

            if (!str_starts_with($this->book->getBookVersion(), '3.')) {
                $doc = preg_replace('#^\s*<!DOCTYPE\ .+?>\s*#im', '', $doc);
            }
        }

        return true;
    }

    /**
     * Process style tags in a DOMDocument. Styles will be passed as CSS files and reinserted into the document.
     *
     * @param DOMDocument &$xmlDoc            (referenced)
     * @param int         $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param string      $baseDir            Default is "", meaning it is pointing to the document root.
     * @param string      $htmlDir            The path to the parent HTML file's directory from the root of the archive.
     *
     * @return bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    private function processChapterStyles(&$xmlDoc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "")
    {
        if ($this->book->isFinalized() || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return false;
        }
        // process inlined CSS styles in style tags.
        $styles = $xmlDoc->getElementsByTagName("style");
        $styleCount = $styles->length;
        for ($styleIdx = 0; $styleIdx < $styleCount; $styleIdx++) {
            $style = $styles->item($styleIdx);

            $styleData = preg_replace('#[/\*\s]*\<\!\[CDATA\[[\s\*/]*#im', "", $style->nodeValue);
            $styleData = preg_replace('#[/\*\s]*\]\]\>[\s\*/]*#im', "", $styleData);

            $this->processCSSExternalReferences($styleData, $externalReferences, $baseDir, $htmlDir);
            $style->nodeValue = "\n" . trim($styleData) . "\n";
        }

        return true;
    }

    /**
     * Process images referenced from an CSS file to the book.
     *
     * $externalReferences determins how the function will handle external references.
     *
     * @param string &$cssFile           (referenced)
     * @param int    $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param string $baseDir            Default is "", meaning it is pointing to the document root.
     * @param string $cssDir             The of the CSS file's directory from the root of the archive.
     *
     * @return bool  FALSE if unsuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    public function processCSSExternalReferences(&$cssFile, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $cssDir = "")
    {
        if ($this->book->isFinalized() || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return false;
        }

        $backPath = preg_replace('#[^/]+/#i', "../", $cssDir);
        $imgs = null;
        preg_match_all('#url\s*\([\'\"\s]*(.+?)[\'\"\s]*\)#im', $cssFile, $imgs, PREG_SET_ORDER);

        $itemCount = count($imgs);
        for ($idx = 0; $idx < $itemCount; $idx++) {
            $img = $imgs[$idx];
            if ($externalReferences === EPub::EXTERNAL_REF_REMOVE_IMAGES || $externalReferences === EPub::EXTERNAL_REF_REPLACE_IMAGES) {
                $cssFile = str_replace($img[0], "", $cssFile);
            } else {
                $source = $img[1];

                $pathData = pathinfo($source);
                $internalSrc = $pathData['basename'];
                $internalPath = "";
                $isSourceExternal = false;

                if ($this->resolveImage($source, $internalPath, $internalSrc, $isSourceExternal, $baseDir, $cssDir)) {
                    $cssFile = str_replace($img[0], "url('" . $backPath . $internalPath . "')", $cssFile);
                } elseif ($isSourceExternal) {
                    $cssFile = str_replace($img[0], "", $cssFile); // External image is missing
                } // else do nothing, if the image is local, and missing, assume it's been generated.
            }
        }

        return true;
    }

    /**
     * Process link tags in a DOMDocument. Linked files will be loaded into the archive, and the link src will be rewritten to point to that location.
     * Link types text/css will be passed as CSS files.
     *
     * @param DOMDocument &$xmlDoc            (referenced)
     * @param int         $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param string      $baseDir            Default is "", meaning it is pointing to the document root.
     * @param string      $htmlDir            The path to the parent HTML file's directory from the root of the archive.
     * @param string      $backPath           The path to get back to the root of the archive from $htmlDir.
     *
     * @return bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    private function processChapterLinks(&$xmlDoc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "", $backPath = "")
    {
        if ($this->book->isFinalized() || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return false;
        }
        // process link tags.
        $links = $xmlDoc->getElementsByTagName("link");
        $linkCount = $links->length;
        for ($linkIdx = 0; $linkIdx < $linkCount; $linkIdx++) {
            /** @var $link \DOMElement */
            $link = $links->item($linkIdx);
            $source = $link->attributes->getNamedItem("href")->nodeValue;
            $sourceData = null;

            $pathData = pathinfo($source);
            $internalSrc = $pathData['basename'];

            if (preg_match('#^(http|ftp)s?://#i', $source) == 1) {
                $urlinfo = parse_url($source);

                if (strpos($urlinfo['path'], $baseDir . "/") !== false) {
                    $internalSrc = substr($urlinfo['path'], strpos($urlinfo['path'], $baseDir . "/") + strlen($baseDir) + 1);
                }

                @$sourceData = FileHelper::getFileContents($source);
            } else {
                if (strpos($source, "/") === 0) {
                    @$sourceData = file_get_contents($this->book->docRoot . $source);
                } else {
                    @$sourceData = file_get_contents($this->book->docRoot . $baseDir . "/" . $source);
                }
            }

            if (!empty($sourceData)) {
                if (!array_key_exists($internalSrc, $this->book->fileList)) {
                    $mime = $link->attributes->getNamedItem("type")->nodeValue;
                    if (empty($mime)) {
                        $mime = "text/plain";
                    }
                    if ($mime === "text/css") {
                        $this->processCSSExternalReferences($sourceData, $externalReferences, $baseDir, $htmlDir);
                        $this->book->addCSSFile($internalSrc, $internalSrc, $sourceData, EPub::EXTERNAL_REF_IGNORE, $baseDir);
                        $link->setAttribute("href", $backPath . $internalSrc);
                    } else {
                        $this->book->addFile($internalSrc, $internalSrc, $sourceData, $mime);
                    }
                    $this->book->fileList[$internalSrc] = $source;
                } else {
                    $link->setAttribute("href", $backPath . $internalSrc);
                }
            } // else do nothing, if the link is local, and missing, assume it's been generated.
        }

        return true;
    }

    /**
     * Process img tags in a DOMDocument.
     * $externalReferences will determine what will happen to these images, and the img src will be rewritten accordingly.
     *
     * @param DOMDocument &$xmlDoc            (referenced)
     * @param int         $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param string      $baseDir            Default is "", meaning it is pointing to the document root.
     * @param string      $htmlDir            The path to the parent HTML file's directory from the root of the archive.
     * @param string      $backPath           The path to get back to the root of the archive from $htmlDir.
     *
     * @return bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    private function processChapterImages(&$xmlDoc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "", $backPath = "")
    {
        if ($this->book->isFinalized() || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return false;
        }
        // process img tags.
        $postProcDomElememts = [];
        $images = $xmlDoc->getElementsByTagName("img");
        $itemCount = $images->length;

        for ($idx = 0; $idx < $itemCount; $idx++) {
            /** @var $img \DOMElement */
            $img = $images->item($idx);

            if ($externalReferences === EPub::EXTERNAL_REF_REMOVE_IMAGES) {
                $postProcDomElememts[] = $img;
            } else {
                if ($externalReferences === EPub::EXTERNAL_REF_REPLACE_IMAGES) {
                    $altNode = $img->attributes->getNamedItem("alt");
                    $alt = "image";
                    if ($altNode !== null && strlen($altNode->nodeValue) > 0) {
                        $alt = $altNode->nodeValue;
                    }
                    $postProcDomElememts[] = [
                        $img,
                        StringHelper::createDomFragment($xmlDoc, "<em>[" . $alt . "]</em>"),
                    ];
                } else {
                    $source = $img->attributes->getNamedItem("src")->nodeValue;

                    $parsedSource = parse_url($source);
                    $internalSrc = FileHelper::sanitizeFileName(urldecode(pathinfo($parsedSource['path'], PATHINFO_BASENAME)));
                    $internalPath = "";
                    $isSourceExternal = false;

                    if ($this->resolveImage($source, $internalPath, $internalSrc, $isSourceExternal, $baseDir, $htmlDir)) {
                        $img->setAttribute("src", $backPath . $internalPath);
                    } else {
                        if ($isSourceExternal) {
                            $postProcDomElememts[] = $img; // External image is missing
                        }
                    } // else do nothing, if the image is local, and missing, assume it's been generated.
                }
            }
        }

        foreach ($postProcDomElememts as $target) {
            if (is_array($target)) {
                $target[0]->parentNode->replaceChild($target[1], $target[0]);
            } else {
                $target->parentNode->removeChild($target);
            }
        }

        return true;
    }

    /**
     * Process source tags in a DOMDocument.
     * $externalReferences will determine what will happen to these images, and the img src will be rewritten accordingly.
     *
     * @param DOMDocument &$xmlDoc            (referenced)
     * @param int         $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param string      $baseDir            Default is "", meaning it is pointing to the document root.
     * @param string      $htmlDir            The path to the parent HTML file's directory from the root of the archive.
     * @param string      $backPath           The path to get back to the root of the archive from $htmlDir.
     *
     * @return bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    private function processChapterSources(&$xmlDoc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "", $backPath = "")
    {
        if ($this->book->isFinalized() || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return false;
        }

        if (!str_starts_with($this->book->getBookVersion(), '3.')) {
            // ePub 2 does not support multimedia formats, and they must be removed.
            $externalReferences = EPub::EXTERNAL_REF_REMOVE_IMAGES;
        }

        $postProcDomElememts = [];
        $images = $xmlDoc->getElementsByTagName("source");
        $itemCount = $images->length;
        for ($idx = 0; $idx < $itemCount; $idx++) {
            /** @var $img \DOMElement */
            $img = $images->item($idx);
            if ($externalReferences === EPub::EXTERNAL_REF_REMOVE_IMAGES) {
                $postProcDomElememts[] = $img;
            } else {
                if ($externalReferences === EPub::EXTERNAL_REF_REPLACE_IMAGES) {
                    $altNode = $img->attributes->getNamedItem("alt");
                    $alt = "image";
                    if ($altNode !== null && strlen($altNode->nodeValue) > 0) {
                        $alt = $altNode->nodeValue;
                    }
                    $postProcDomElememts[] = [
                        $img,
                        StringHelper::createDomFragment($xmlDoc, "[" . $alt . "]"),
                    ];
                } else {
                    $source = $img->attributes->getNamedItem("src")->nodeValue;

                    $parsedSource = parse_url($source);
                    $internalSrc = FileHelper::sanitizeFileName(urldecode(pathinfo($parsedSource['path'], PATHINFO_BASENAME)));
                    $internalPath = "";
                    $isSourceExternal = false;

                    if ($this->resolveMedia($source, $internalPath, $internalSrc, $isSourceExternal, $baseDir, $htmlDir)) {
                        $img->setAttribute("src", $backPath . $internalPath);
                    } else {
                        if ($isSourceExternal) {
                            $postProcDomElememts[] = $img; // External image is missing
                        }
                    } // else do nothing, if the image is local, and missing, assume it's been generated.
                }
            }
        }

        return true;
    }

    /**
     * Resolve an image src and determine it's target location and add it to the book.
     *
     * @param string $source            Image Source link.
     * @param string &$internalPath     (referenced) Return value, will be set to the target path and name in the book.
     * @param string &$internalSrc      (referenced) Return value, will be set to the target name in the book.
     * @param string &$isSourceExternal (referenced) Return value, will be set to TRUE if the image originated from a full URL.
     * @param string $baseDir           Default is "", meaning it is pointing to the document root.
     * @param string $htmlDir           The path to the parent HTML file's directory from the root of the archive.
     *
     * @return bool
     */
    public function resolveImage($source, &$internalPath, &$internalSrc, &$isSourceExternal, $baseDir = "", $htmlDir = "")
    {
        if ($this->book->isFinalized()) {
            return false;
        }
        $imageData = null;

        if (preg_match('#^(http|ftp)s?://#i', $source) == 1) {
            $urlinfo = parse_url($source);

            if (strpos($urlinfo['path'], $baseDir . "/") !== false) {
                $internalSrc = FileHelper::sanitizeFileName(urldecode(substr($urlinfo['path'], strpos($urlinfo['path'], $baseDir . "/") + strlen($baseDir) + 1)));
            }
            $internalPath = $urlinfo["scheme"] . "/" . $urlinfo["host"] . "/" . pathinfo($urlinfo["path"], PATHINFO_DIRNAME);
            $isSourceExternal = true;
            $imageData = ImageHelper::getImage($this->book, $source);
        } else {
            if (strpos($source, "/") === 0) {
                $internalPath = pathinfo($source, PATHINFO_DIRNAME);

                $path = $source;
                if (!file_exists($path)) {
                    $path = $this->book->docRoot . $path;
                }

                $imageData = ImageHelper::getImage($this->book, $path);
            } else {
                $internalPath = $htmlDir . "/" . preg_replace('#^[/\.]+#', '', pathinfo($source, PATHINFO_DIRNAME));

                $path = $baseDir . "/" . $source;
                if (!file_exists($path)) {
                    $path = $this->book->docRoot . $path;
                }

                $imageData = ImageHelper::getImage($this->book, $path);
            }
        }
        if ($imageData !== false) {
            $iSrcInfo = pathinfo($internalSrc);

            if (!empty($imageData['ext']) && (!isset($iSrcInfo['extension']) || $imageData['ext'] != $iSrcInfo['extension'])) {
                $internalSrc = $iSrcInfo['filename'] . "." . $imageData['ext'];
            }
            $internalPath = Path::canonicalize("images/" . $internalPath . "/" . $internalSrc);
            if (!array_key_exists($internalPath, $this->book->fileList)) {
                $this->book->addFile($internalPath, "i_" . $internalSrc, $imageData['image'], $imageData['mime']);
                $this->book->fileList[$internalPath] = $source;
            }

            return true;
        }

        return false;
    }

    /**
     * Resolve a media src and determine it's target location and add it to the book.
     *
     * @param string $source           Source link.
     * @param string $internalPath     (referenced) Return value, will be set to the target path and name in the book.
     * @param string $internalSrc      (referenced) Return value, will be set to the target name in the book.
     * @param string $isSourceExternal (referenced) Return value, will be set to TRUE if the image originated from a full URL.
     * @param string $baseDir          Default is "", meaning it is pointing to the document root.
     * @param string $htmlDir          The path to the parent HTML file's directory from the root of the archive.
     *
     * @return bool
     */
    public function resolveMedia($source, &$internalPath, &$internalSrc, &$isSourceExternal, $baseDir = "", $htmlDir = "")
    {
        if ($this->book->isFinalized()) {
            return false;
        }
        $mediaPath = null;
        $tmpFile = null;

        if (preg_match('#^(http|ftp)s?://#i', $source) == 1) {
            $urlInfo = parse_url($source);

            if (strpos($urlInfo['path'], $baseDir . "/") !== false) {
                $internalSrc = substr($urlInfo['path'], strpos($urlInfo['path'], $baseDir . "/") + strlen($baseDir) + 1);
            }
            $internalPath = $urlInfo["scheme"] . "/" . $urlInfo["host"] . "/" . pathinfo($urlInfo["path"], PATHINFO_DIRNAME);
            $isSourceExternal = true;
            $mediaPath = FileHelper::getFileContents($source, true);
            $tmpFile = $mediaPath;
        } else {
            if (strpos($source, "/") === 0) {
                $internalPath = pathinfo($source, PATHINFO_DIRNAME);

                $mediaPath = $source;
                if (!file_exists($mediaPath)) {
                    $mediaPath = $this->book->docRoot . $mediaPath;
                }
            } else {
                $internalPath = $htmlDir . "/" . preg_replace('#^[/\.]+#', '', pathinfo($source, PATHINFO_DIRNAME));

                $mediaPath = $baseDir . "/" . $source;
                if (!file_exists($mediaPath)) {
                    $mediaPath = $this->book->docRoot . $mediaPath;
                }
            }
        }

        if ($mediaPath !== false) {
            $mime = MimeHelper::getMimeTypeFromExtension(pathinfo($source, PATHINFO_EXTENSION));
            $internalPath = Path::canonicalize("media/" . $internalPath . "/" . $internalSrc);

            if (
                !array_key_exists($internalPath, $this->book->fileList) &&
                $this->book->addLargeFile($internalPath, "m_" . $internalSrc, $mediaPath, $mime)
            ) {
                $this->book->fileList[$internalPath] = $source;
            }
            if (isset($tmpFile)) {
                unlink($tmpFile);
            }

            return true;
        }

        return false;
    }
}
