<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core;

use Grandt\BinStringStatic;
use DOMDocument;
use DOMXPath;
use Rampmaster\EPub\Core\Content\ContentProcessor;
use Rampmaster\EPub\Core\Storage\ZipContainer;
use Rampmaster\EPub\Core\Structure\Ncx;
use Rampmaster\EPub\Core\Structure\NCX\NavPoint;
use Rampmaster\EPub\Core\Structure\Opf;
use Rampmaster\EPub\Core\Structure\OPF\DublinCore;
use Rampmaster\EPub\Core\Structure\OPF\Item;
use Rampmaster\EPub\Core\Structure\OPF\MarcCode;
use Rampmaster\EPub\Core\Structure\OPF\MetaValue;
use Rampmaster\EPub\Core\Structure\OPF\Reference;
use Rampmaster\EPub\Core\Structure\Smil;
use Rampmaster\EPub\Helpers\FileHelper;
use Rampmaster\EPub\Helpers\ImageHelper;
use Rampmaster\EPub\Helpers\MimeHelper;
use Rampmaster\EPub\Helpers\StringHelper;
use Rampmaster\EPub\Helpers\URLHelper;
use Symfony\Component\Filesystem\Path;

class EPub
{
    public const VERSION = '4.0.6';

    public const IDENTIFIER_UUID = 'UUID';
    public const IDENTIFIER_URI = 'URI';
    public const IDENTIFIER_ISBN = 'ISBN';

    public const EXTERNAL_REF_IGNORE = 0;
    public const EXTERNAL_REF_ADD = 1;
    public const EXTERNAL_REF_REMOVE_IMAGES = 2;
    public const EXTERNAL_REF_REPLACE_IMAGES = 3;

    public const DIRECTION_LEFT_TO_RIGHT = 'ltr';
    public const DIRECTION_RIGHT_TO_LEFT = 'rtl';

    public const BOOK_VERSION_EPUB2 = '2.0';
    public const BOOK_VERSION_EPUB3 = '3.0';
    public const BOOK_VERSION_EPUB301 = '3.0.1';
    public const BOOK_VERSION_EPUB31 = '3.1';
    public const BOOK_VERSION_EPUB32 = '3.2';

    public $viewportMap = [
        "small" => ['width' => 600, 'height' => 800],
        "medium" => ['width' => 720, 'height' => 1280],
        "720p" => ['width' => 720, 'height' => 1280],
        "ipad" => ['width' => 768, 'height' => 1024],
        "large" => ['width' => 1080, 'height' => 1920],
        "2k" => ['width' => 1080, 'height' => 1920],
        "1080p" => ['width' => 1080, 'height' => 1920],
        "ipad3" => ['width' => 1536, 'height' => 2048],
        "4k" => ['width' => 2160, 'height' => 3840],
    ];

    public $splitDefaultSize = 250000;
    public $maxImageWidth = 768;
    public $maxImageHeight = 1024;
    public $isGifImagesEnabled = false;
    public $isReferencesAddedToToc = true;
    public $referencesOrder = null;
    public $pluginDir = 'extLib';
    public $isLogging = true;
    public $encodeHTML = false;

    private $bookVersion = EPub::BOOK_VERSION_EPUB2;
    private ZipContainer $zip;
    private ContentProcessor $contentProcessor;
    private $title = '';
    private $language = 'en';
    private $identifier = '';
    private $identifierType = '';
    private $description = '';
    private $author = '';
    private $authorSortKey = '';
    private $publisherName = '';
    private $publisherURL = '';
    private $date = 0;
    private $rights = '';
    private $coverage = '';
    private $relation = '';
    private $sourceURL = '';
    private $chapterCount = 0;
    private ?Opf $opf = null;
    private ?Ncx $ncx = null;
    private $isFinalized = false;
    private $isInitialized = false;
    private $isCoverImageSet = false;
    private $buildTOC = false;
    private $tocTitle = null;
    private $tocFileName = null;
    private $tocNavAdded = false;
    private $tocCSSClass = null;
    private $tocAddReferences = false;
    private $tocCssFileName = null;
    public $fileList = [];
    private $writingDirection = EPub::DIRECTION_LEFT_TO_RIGHT;
    private $languageCode = 'en';
    private $dateformat = 'Y-m-d\TH:i:s.000000P';
    private $dateformatShort = 'Y-m-d';
    private $headerDateFormat = "D, d M Y H:i:s T";
    public $docRoot = null;
    private $bookRoot = 'OEBPS/';
    private $EPubMark = true;
    private $generator = '';
    private $log = null;
    private $htmlContentHeader = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n<title></title>\n</head>\n<body>\n";
    private $htmlContentFooter = "</body>\n</html>\n";
    private $viewport = null;
    private $dangermode = false;
    private $accessibilitySummary = null;
    private $accessModes = [];
    private $accessibilityFeatures = [];
    private $accessibilityHazards = [];
    private $accessibilityConformsTo = null;
    private $smilFiles = [];

    public function __construct($bookVersion = EPub::BOOK_VERSION_EPUB2, $languageCode = 'en', $writingDirection = EPub::DIRECTION_LEFT_TO_RIGHT)
    {
        $this->bookVersion = $bookVersion;
        $this->writingDirection = $writingDirection;
        $this->languageCode = $languageCode;
        $this->log = new Logger('EPub', $this->isLogging);
        if ($this->isLogging) {
            $this->log->logLine('EPub class version....: ' . self::VERSION);
            $this->log->dumpInstalledModules();
        }
        $this->setUp();
    }

    private function setUp()
    {
        $this->referencesOrder = [
            Reference::COVER => 'Cover Page',
            Reference::TITLE_PAGE => 'Title Page',
            Reference::ACKNOWLEDGEMENTS => 'Acknowledgements',
            Reference::BIBLIOGRAPHY => 'Bibliography',
            Reference::COLOPHON => 'Colophon',
            Reference::COPYRIGHT_PAGE => 'Copyright',
            Reference::DEDICATION => 'Dedication',
            Reference::EPIGRAPH => 'Epigraph',
            Reference::FOREWORD => 'Foreword',
            Reference::TABLE_OF_CONTENTS => 'Table of Contents',
            Reference::NOTES => 'Notes',
            Reference::PREFACE => 'Preface',
            Reference::TEXT => 'First Page',
            Reference::LIST_OF_ILLUSTRATIONS => 'List of Illustrations',
            Reference::LIST_OF_TABLES => 'List of Tables',
            Reference::GLOSSARY => 'Glossary',
            Reference::INDEX => 'Index',
        ];
        $this->docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/';
        $this->zip = new ZipContainer();
        $this->zip->addFromString('mimetype', 'application/epub+zip');
        $this->zip->setCompression('mimetype', \ZipArchive::CM_STORE);
        $this->zip->addEmptyDir('META-INF');
        $this->contentProcessor = new ContentProcessor($this);
        $this->ncx = new Ncx(null, null, null, $this->languageCode, $this->writingDirection);
        $this->opf = new Opf();
        $this->chapterCount = 0;
        $this->ncx->setBook($this);
    }

    public function __destruct()
    {
        unset($this->bookVersion, $this->maxImageWidth, $this->maxImageHeight);
        unset($this->splitDefaultSize, $this->isGifImagesEnabled, $this->isReferencesAddedToToc);
        unset($this->zip, $this->title, $this->language, $this->identifier, $this->identifierType);
        unset($this->description, $this->author, $this->authorSortKey, $this->publisherName);
        unset($this->publisherURL, $this->date, $this->rights, $this->coverage, $this->relation);
        unset($this->sourceURL, $this->chapterCount, $this->opf, $this->ncx, $this->isFinalized);
        unset($this->isCoverImageSet, $this->fileList, $this->writingDirection, $this->languageCode);
        unset($this->referencesOrder, $this->dateformat, $this->dateformatShort, $this->headerDateFormat);
        unset($this->bookRoot, $this->docRoot, $this->EPubMark, $this->generator, $this->log, $this->isLogging);
        unset($this->encodeHTML, $this->htmlContentHeader, $this->htmlContentFooter);
        unset($this->buildTOC, $this->tocTitle, $this->tocCSSClass, $this->tocAddReferences);
        unset($this->tocFileName, $this->tocCssFileName, $this->viewport);
    }

    public function addChapter($chapterName, $fileName, $chapterData = null, $autoSplit = false, $externalReferences = EPub::EXTERNAL_REF_IGNORE, $baseDir = "")
    {
        if ($this->isFinalized) {
            return false;
        }
        $fileName = Path::canonicalize($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);
        $navPoint = false;
        $chapter = $chapterData;
        if ($autoSplit && is_string($chapterData) && mb_strlen($chapterData) > $this->splitDefaultSize) {
            $splitter = new EPubChapterSplitter();
            $splitter->setSplitSize($this->splitDefaultSize);
            $chapterArray = $splitter->splitChapter($chapterData);
            if (count($chapterArray) > 1) {
                $chapter = $chapterArray;
            }
        }
        if (!empty($chapter) && is_string($chapter)) {
            if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
                $htmlDirInfo = pathinfo($fileName);
                $htmlDir = preg_replace('#^[/\.]+#i', "", $htmlDirInfo["dirname"] . "/");
                $this->contentProcessor->processChapterExternalReferences($chapter, $externalReferences, $baseDir, $htmlDir);
            }
            if ($this->encodeHTML === true) {
                $chapter = StringHelper::encodeHtml($chapter);
            }
            $this->chapterCount++;
            $this->addFile($fileName, "chapter" . $this->chapterCount, $chapter, "application/xhtml+xml");
            $this->extractIdAttributes("chapter" . $this->chapterCount, $chapter);
            $this->opf->addItemRef("chapter" . $this->chapterCount);
            $navPoint = new NavPoint(StringHelper::decodeHtmlEntities($chapterName), $fileName, "chapter" . $this->chapterCount);
            $this->ncx->addNavPoint($navPoint);
            $this->ncx->chapterList[$chapterName] = $navPoint;
        } elseif (is_array($chapter)) {
            $this->log->logLine("addChapter: \$chapterName: $chapterName ; \$fileName: $fileName ; ");
            $fileNameParts = pathinfo($fileName);
            $extension = $fileNameParts['extension'];
            $name = $fileNameParts['filename'];
            $partCount = 0;
            $this->chapterCount++;
            foreach ($chapter as $k => $v) {
                if ($this->encodeHTML === true) {
                    $v = StringHelper::encodeHtml($v);
                }
                $partCount++;
                $partFileName = $name . "_" . $partCount . "." . $extension;
                if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
                    $htmlDirInfo = pathinfo($partFileName);
                    $htmlDir = preg_replace('#^[/\.]+#i', "", ($htmlDirInfo["dirname"] ?? "") . "/");
                    $this->contentProcessor->processChapterExternalReferences($v, $externalReferences, $baseDir, $htmlDir);
                }
                $this->addFile($partFileName, $name . "_" . $partCount, $v, "application/xhtml+xml");
                $this->extractIdAttributes($name . "_" . $partCount, $v);
                $this->opf->addItemRef($name . "_" . $partCount);
            }
            $partName = $name . "_1." . $extension;
            $navPoint = new NavPoint(StringHelper::decodeHtmlEntities($chapterName), $partName, $partName);
            $this->ncx->addNavPoint($navPoint);
            $this->ncx->chapterList[$chapterName] = $navPoint;
        } elseif (!isset($chapterData) && strpos($fileName, "#") > 0) {
            $this->chapterCount++;
            $id = preg_split("/[#]/", $fileName);
            if (sizeof($id) == 2 && $this->isLogging) {
                $name = preg_split('/[\.]/', $id[0]);
                if (sizeof($name) > 1) {
                    $name = $name[0];
                }
                $rv = $this->opf->getItemByHref($name, true);
                if ($rv != false) {
                    foreach ($rv as $item) {
                        if ($item->hasIndexPoint($id[1])) {
                            $fileName = $item->getHref() . "#" . $id[1];
                            break;
                        }
                    }
                }
            }
            $navPoint = new NavPoint(StringHelper::decodeHtmlEntities($chapterName), $fileName, "chapter" . $this->chapterCount);
            $this->ncx->addNavPoint($navPoint);
            $this->ncx->chapterList[$chapterName] = $navPoint;
        } elseif (!isset($chapterData) && $fileName == "TOC.xhtml") {
            $this->chapterCount++;
            $this->opf->addItemRef("toc");
            $navPoint = new NavPoint(StringHelper::decodeHtmlEntities($chapterName), $fileName, "chapter" . $this->chapterCount);
            $this->ncx->addNavPoint($navPoint);
            $this->ncx->chapterList[$chapterName] = $navPoint;
            $this->tocNavAdded = true;
        }
        return $navPoint;
    }

    public function addChapterWithAudio($chapterName, $fileName, $chapterData, $audioFile, $duration)
    {
        if ($this->isFinalized) {
            return false;
        }
        $navPoint = $this->addChapter($chapterName, $fileName, $chapterData);
        if ($navPoint === false) {
            return false;
        }
        $audioFileName = basename($audioFile);
        $audioId = 'audio_' . $this->chapterCount;
        $audioMime = MimeHelper::getMimeTypeFromExtension(pathinfo($audioFile, PATHINFO_EXTENSION));
        $this->addFile('audio/' . $audioFileName, $audioId, file_get_contents($audioFile), $audioMime);
        $smilId = 'smil_' . $this->chapterCount;
        $smilFileName = 'smil/' . pathinfo($fileName, PATHINFO_FILENAME) . '.smil';
        $smil = new Smil($smilId, $smilFileName, $fileName, '../audio/' . $audioFileName);
        $ids = $this->findIdAttributes($chapterData);
        $targetId = $ids[0] ?? null;
        if ($targetId) {
            $smil->addPar($targetId, '0s', $duration);
        }
        $this->smilFiles[] = $smil;
        $item = $this->opf->getItemById("chapter" . $this->chapterCount);
        if ($item) {
            $item->setMediaOverlay($smilId);
        }
        $this->opf->addItem($smilId, $smilFileName, Smil::MIMETYPE);
        $this->opf->addMetaProperty("media:duration", $duration);
        $this->opf->addMetaProperty("media:duration", $duration)->refines("#" . $smilId);
        return $navPoint;
    }

    public function findIdAttributes($chapterData)
    {
        $xmlDoc = new DOMDocument();
        @$xmlDoc->loadHTML($chapterData);
        $xpath = new DomXpath($xmlDoc);
        $rv = [];
        foreach ($xpath->query('//@id') as $rowNode) {
            $rv[] = $rowNode->nodeValue;
        }
        return $rv;
    }

    public function extractIdAttributes($partName, $chapterData)
    {
        $item = $this->opf->getItemById($partName);
        $ids = $this->findIdAttributes($chapterData);
        foreach ($ids as $id) {
            $item->addIndexPoint($id);
        }
    }

    public function addFileToMETAINF($fileName, $fileData)
    {
        if ($this->isFinalized) {
            return false;
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $safeName = FileHelper::sanitizeZipPath($fileName, true);
        if ($safeName === false) {
            return false;
        }
        $this->zip->addFromString("META-INF/" . $safeName, $fileData);
        return true;
    }

    public function addFile($fileName, $fileId, $fileData, $mimetype)
    {
        if ($this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return false;
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $safeName = FileHelper::sanitizeZipPath($fileName, false);
        if ($safeName === false) {
            return false;
        }
        $this->zip->addFromString($this->bookRoot . $safeName, $fileData);
        $this->fileList[$safeName] = $safeName;
        $this->opf->addItem($fileId, $safeName, $mimetype);
        return true;
    }

    public function addCSSFile($fileName, $fileId, $fileData, $externalReferences = EPub::EXTERNAL_REF_IGNORE, $baseDir = "")
    {
        if ($this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return false;
        }
        $fileName = Path::canonicalize($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);
        if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
            $cssDir = pathinfo($fileName);
            $cssDir = preg_replace('#^[/\.]+#i', "", $cssDir["dirname"] . "/");
            if (!empty($cssDir)) {
                $cssDir = preg_replace('#[^/]+/#i', "../", $cssDir);
            }
            $this->contentProcessor->processCSSExternalReferences($fileData, $externalReferences, $baseDir, $cssDir);
        }
        $this->addFile($fileName, "css_" . $fileId, $fileData, "text/css");
        return true;
    }

    public function resolveMedia($source, &$internalPath, &$internalSrc, &$isSourceExternal, $baseDir = "", $htmlDir = "")
    {
        if ($this->isFinalized) {
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
                    $mediaPath = $this->docRoot . $mediaPath;
                }
            } else {
                $internalPath = $htmlDir . "/" . preg_replace('#^[/\.]+#', '', pathinfo($source, PATHINFO_DIRNAME));
                $mediaPath = $baseDir . "/" . $source;
                if (!file_exists($mediaPath)) {
                    $mediaPath = $this->docRoot . $mediaPath;
                }
            }
        }
        if ($mediaPath !== false) {
            $mime = MimeHelper::getMimeTypeFromExtension(pathinfo($source, PATHINFO_EXTENSION));
            $internalPath = Path::canonicalize("media/" . $internalPath . "/" . $internalSrc);
            if (!array_key_exists($internalPath, $this->fileList) && $this->addLargeFile($internalPath, "m_" . $internalSrc, $mediaPath, $mime)) {
                $this->fileList[$internalPath] = $source;
            }
            if (isset($tmpFile)) {
                unlink($tmpFile);
            }
            return true;
        }
        return false;
    }

    public function addLargeFile($fileName, $fileId, $filePath, $mimetype)
    {
        if ($this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return false;
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $fileName = FileHelper::normalizeFileName($fileName);
        if (!is_file($filePath)) {
            return false;
        }
        if ($this->zip->addFile($filePath, $this->bookRoot . $fileName)) {
            $this->fileList[$fileName] = $fileName;
            $this->opf->addItem($fileId, $fileName, $mimetype);
            return true;
        }
        return false;
    }

    private function initialize()
    {
        if ($this->isInitialized) {
            return;
        }
        if (strlen($this->bookRoot) != 0 && $this->bookRoot != 'OEBPS/') {
            $this->setBookRoot($this->bookRoot);
        }
        $this->isInitialized = true;
        if (!$this->isEPubVersion2()) {
            $this->htmlContentHeader = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n" . "<head>" . "<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n" . "<title></title>\n" . "</head>\n" . "<body>\n";
        }
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<container version=\"1.0\" xmlns=\"urn:oasis:names:tc:opendocument:xmlns:container\">\n\t<rootfiles>\n\t\t<rootfile full-path=\"" . $this->bookRoot . "book.opf\" media-type=\"application/oebps-package+xml\" />\n\t</rootfiles>\n</container>\n";
        $this->zip->addFromString("META-INF/container.xml", $content);
        $this->ncx->setVersion($this->bookVersion);
        $this->opf->setVersion($this->bookVersion);
        $this->opf->addItem("ncx", "book.ncx", Ncx::MIMETYPE);
        $this->ncx->setLanguageCode($this->languageCode);
        $this->ncx->setWritingDirection($this->writingDirection);
    }

    public function setBookRoot($bookRoot)
    {
        if ($this->isInitialized) {
            die("bookRoot can't be set after book initialization (first file added).");
        }
        $bookRoot = trim($bookRoot);
        if (strlen($bookRoot) <= 1 || $bookRoot == '/') {
            $bookRoot = '';
        } else {
            if (!BinStringStatic::endsWith($bookRoot, '/')) {
                $bookRoot .= '/';
            }
        }
        $this->bookRoot = $bookRoot;
    }

    public function isEPubVersion2()
    {
        return $this->bookVersion === EPub::BOOK_VERSION_EPUB2;
    }

    public function subLevel($navTitle = null, $navId = null, $navClass = null, $isNavHidden = false, $writingDirection = null)
    {
        return $this->ncx->subLevel($navTitle ? StringHelper::decodeHtmlEntities($navTitle) : $navTitle, $navId, $navClass, $isNavHidden, $writingDirection);
    }

    public function backLevel()
    {
        $this->ncx->backLevel();
    }

    public function rootLevel()
    {
        $this->ncx->rootLevel();
    }

    public function setCurrentLevel($newLevel)
    {
        $this->ncx->setCurrentLevel($newLevel);
    }

    public function getCurrentLevel()
    {
        return $this->ncx->getCurrentLevel();
    }

    public function addCustomNamespace($nsName, $nsURI)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->opf->addNamespace($nsName, $nsURI);
    }

    public function addCustomPrefix($name, $URI)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->opf->addPrefix($name, $URI);
    }

    public function addCustomMetaValue($value)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->opf->addMetaValue($value);
    }

    public function addCustomMetaProperty($name, $content)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->opf->addMetaProperty($name, $content);
    }

    public function addCustomMetadata($name, $content)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->opf->addMeta($name, $content);
    }

    public function addDublinCoreMetadata($dublinCoreConstant, $value)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->opf->addDCMeta($dublinCoreConstant, StringHelper::decodeHtmlEntities($value));
    }

    public function setCoverImage($fileName, $imageData = null, $mimetype = null)
    {
        if ($this->isFinalized || $this->isCoverImageSet || array_key_exists("CoverPage.xhtml", $this->fileList)) {
            return false;
        }
        if ($imageData == null) {
            if (!file_exists($fileName)) {
                $rp = realpath($this->docRoot . "/" . $fileName);
                if ($rp !== false) {
                    $fileName = $rp;
                }
            }
            $image = ImageHelper::getImage($this, $fileName);
            $imageData = $image['image'];
            $mimetype = $image['mime'];
            $fileName = preg_replace('#\.[^\.]+$#', "." . $image['ext'], $fileName);
        }
        $path = pathinfo($fileName);
        $imgPath = "images/" . $path["basename"];
        if (empty($mimetype) && file_exists($fileName)) {
            [$width, $height, $type, $attr] = getimagesize($fileName);
            $mimetype = image_type_to_mime_type($type);
        }
        if (empty($mimetype)) {
            $ext = strtolower($path['extension']);
            if ($ext == "jpg") {
                $ext = "jpeg";
            }
            $mimetype = "image/" . $ext;
        }
        if ($this->isEPubVersion2()) {
            $coverPage = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" . "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n" . "  \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n" . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\" xml:lang=\"en\">\n" . "\t<head>\n" . "\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>\n" . $this->getViewportMetaLine() . "\t\t<title>Cover Image</title>\n" . "\t\t<link type=\"text/css\" rel=\"stylesheet\" href=\"Styles/CoverPage.css\" />\n" . "\t</head>\n" . "\t<body>\n" . "\t\t<div>\n" . "\t\t\t<img src=\"" . $imgPath . "\" alt=\"Cover image\" style=\"height: 100%\"/>\n" . "\t\t</div>\n" . "\t</body>\n" . "</html>\n";
        } else {
            $coverPage = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n" . "\t<head>\n" . "\t\t<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n" . $this->getViewportMetaLine() . "\t\t<title>Cover Image</title>\n" . "\t\t<link type=\"text/css\" rel=\"stylesheet\" href=\"Styles/CoverPage.css\" />\n" . "\t</head>\n" . "\t<body>\n" . "\t\t<section epub:type=\"cover\">\n\t\t\t<img src=\"" . $imgPath . "\" alt=\"Cover image\" style=\"height: 100%\"/>\n" . "\t\t</section>\n" . "\t</body>\n" . "</html>\n";
        }
        $coverPageCss = "@page, body, div, img {\n" . "\tpadding: 0pt;\n" . "\tmargin:0pt;\n" . "}\n\nbody {\n" . "\ttext-align: center;\n" . "}\n";
        $this->addCSSFile("Styles/CoverPage.css", "CoverPageCss", $coverPageCss);
        $this->addFile($imgPath, "CoverImage", $imageData, $mimetype);
        $this->addReferencePage("CoverPage", "CoverPage.xhtml", $coverPage, "cover");
        $this->isCoverImageSet = true;
        return true;
    }

    public function addReferencePage($pageName, $fileName, $pageData, $reference, $externalReferences = EPub::EXTERNAL_REF_IGNORE, $baseDir = "")
    {
        if ($this->isFinalized) {
            return false;
        }
        $fileName = Path::canonicalize($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);
        if (!empty($pageData) && is_string($pageData)) {
            if ($this->encodeHTML === true) {
                $pageData = StringHelper::encodeHtml($pageData);
            }
            $this->wrapChapter($pageData);
            if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
                $htmlDirInfo = pathinfo($fileName);
                $htmlDir = preg_replace('#^[/\.]+#i', "", $htmlDirInfo["dirname"] . "/");
                $this->processChapterExternalReferences($pageData, $externalReferences, $baseDir, $htmlDir);
            }
            $this->addFile($fileName, "ref_" . $reference, $pageData, "application/xhtml+xml");
            $this->extractIdAttributes("ref_" . $reference, $pageData);
            if ($reference !== Reference::TABLE_OF_CONTENTS || !isset($this->ncx->referencesList[$reference])) {
                $this->opf->addItemRef("ref_" . $reference);
                $this->opf->addReference($reference, $pageName, $fileName);
                $this->ncx->referencesList[$reference] = $fileName;
                $this->ncx->referencesName[$reference] = $pageName;
            }
            return true;
        }
        return true;
    }

    private function wrapChapter($content)
    {
        return $this->htmlContentHeader . "\n" . $content . "\n" . $this->htmlContentFooter;
    }

    public function getChapterCount()
    {
        return $this->chapterCount;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->title = $title;
        return true;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        if ($this->isFinalized || mb_strlen($language) != 2) {
            return false;
        }
        $this->language = $language;
        return true;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier, $identifierType)
    {
        if ($this->isFinalized || ($identifierType !== EPub::IDENTIFIER_URI && $identifierType !== EPub::IDENTIFIER_ISBN && $identifierType !== EPub::IDENTIFIER_UUID)) {
            return false;
        }
        $this->identifier = $identifier;
        $this->identifierType = $identifierType;
        return true;
    }

    public function getIdentifierType()
    {
        return $this->identifierType;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->description = $description;
        return true;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author, $authorSortKey)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->author = $author;
        $this->authorSortKey = $authorSortKey;
        return true;
    }

    public function setPublisher($publisherName, $publisherURL)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->publisherName = $publisherName;
        $this->publisherURL = $publisherURL;
        return true;
    }

    public function getPublisherName()
    {
        return $this->publisherName;
    }

    public function getPublisherURL()
    {
        return $this->publisherURL;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($timestamp)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->date = $timestamp;
        $this->opf->date = $timestamp;
        return true;
    }

    public function getRights()
    {
        return $this->rights;
    }

    public function setRights($rightsText)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->rights = $rightsText;
        return true;
    }

    public function setSubject($subject)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->opf->addDCMeta(DublinCore::SUBJECT, StringHelper::decodeHtmlEntities($subject));
    }

    public function getSourceURL()
    {
        return $this->sourceURL;
    }

    public function setSourceURL($sourceURL)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->sourceURL = $sourceURL;
        return true;
    }

    public function getCoverage()
    {
        return $this->coverage;
    }

    public function setCoverage($coverage)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->coverage = $coverage;
        return true;
    }

    public function getRelation()
    {
        return $this->relation;
    }

    public function setRelation($relation)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->relation = $relation;
    }

    public function getGenerator()
    {
        return $this->generator;
    }

    public function setGenerator($generator)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->generator = $generator;
    }

    public function setShortDateFormat()
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->dateformat = $this->dateformatShort;
        return true;
    }

    public function setReferencesTitle($referencesTitle = "Guide", $referencesId = "", $referencesClass = "references")
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->ncx->referencesTitle = is_string($referencesTitle) ? trim($referencesTitle) : "Guide";
        $this->ncx->referencesId = is_string($referencesId) ? trim($referencesId) : "references";
        $this->ncx->referencesClass = is_string($referencesClass) ? trim($referencesClass) : "references";
        return true;
    }

    public function setisReferencesAddedToToc($isReferencesAddedToToc = true)
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->isReferencesAddedToToc = $isReferencesAddedToToc === true;
        return true;
    }

    public function isFinalized()
    {
        return $this->isFinalized;
    }

    public function buildTOC($cssFileName = null, $tocCSSClass = "toc", $title = "Table of Contents", $addReferences = true, $addToIndex = false, $tocFileName = "TOC.xhtml")
    {
        if ($this->isFinalized) {
            return false;
        }
        $this->buildTOC = true;
        $this->tocTitle = $title;
        $this->tocFileName = FileHelper::normalizeFileName($tocFileName);
        if (!empty($cssFileName)) {
            $this->tocCssFileName = FileHelper::normalizeFileName($cssFileName);
        }
        $this->tocCSSClass = $tocCSSClass;
        $this->tocAddReferences = $addReferences;
        $this->opf->addReference(Reference::TABLE_OF_CONTENTS, $title, $this->tocFileName);
        if (!$this->tocNavAdded) {
            $this->opf->addItemRef("ref_" . Reference::TABLE_OF_CONTENTS, false);
            if ($addToIndex) {
                $navPoint = new NavPoint(StringHelper::decodeHtmlEntities($title), $this->tocFileName, "ref_" . Reference::TABLE_OF_CONTENTS);
                $this->ncx->addNavPoint($navPoint);
            } else {
                $this->ncx->referencesList[Reference::TABLE_OF_CONTENTS] = $this->tocFileName;
                $this->ncx->referencesName[Reference::TABLE_OF_CONTENTS] = $title;
            }
        }
        return true;
    }

    public function saveBook($fileName, $baseDir = '.')
    {
        if (!$this->isFinalized) {
            $this->finalize();
        }
        return $this->zip->saveBook($fileName, $baseDir);
    }

    public function getBook()
    {
        if (!$this->isFinalized) {
            $this->finalize();
        }
        return $this->zip->getBook();
    }

    public function getBookSize()
    {
        if (!$this->isFinalized) {
            $this->finalize();
        }
        return $this->zip->getBookSize();
    }

    public function sendBook($fileName)
    {
        if (!$this->isFinalized) {
            $this->finalize();
        }
        $dir = dirname($fileName);
        $file = basename($fileName);
        return $this->zip->saveBook($file, $dir);
    }

    public function finalize()
    {
        if ($this->isFinalized || $this->chapterCount == 0 || empty($this->title) || empty($this->language)) {
            return false;
        }
        if (empty($this->identifier) || empty($this->identifierType)) {
            $this->setIdentifier(StringHelper::createUUID(4), EPub::IDENTIFIER_UUID);
        }
        if ($this->date == 0) {
            $this->date = time();
        }
        if (empty($this->sourceURL)) {
            $this->sourceURL = URLHelper::getCurrentPageURL();
        }
        if (empty($this->publisherURL)) {
            $this->sourceURL = URLHelper::getCurrentServerURL();
        }
        $this->opf->setIdent("BookId");
        $this->opf->initialize($this->title, $this->language, $this->identifier, $this->identifierType);
        $DCdate = new DublinCore(DublinCore::DATE, gmdate($this->dateformat, $this->date));
        $DCdate->addOpfAttr("event", "publication");
        $this->opf->metadata->addDublinCore($DCdate);
        if (str_starts_with($this->bookVersion, '3.') && $this->bookVersion !== EPub::BOOK_VERSION_EPUB3 && $this->bookVersion !== EPub::BOOK_VERSION_EPUB301) {
            $this->opf->addMetaProperty("dcterms:modified", gmdate("Y-m-d\TH:i:s\Z", $this->date));
        }
        if ($this->accessibilitySummary !== null) {
            $this->opf->addMetaProperty("schema:accessibilitySummary", $this->accessibilitySummary);
        }
        foreach ($this->accessModes as $mode) {
            $this->opf->addMetaProperty("schema:accessMode", $mode);
        }
        foreach ($this->accessibilityFeatures as $feature) {
            $this->opf->addMetaProperty("schema:accessibilityFeature", $feature);
        }
        foreach ($this->accessibilityHazards as $hazard) {
            $this->opf->addMetaProperty("schema:accessibilityHazard", $hazard);
        }
        if ($this->accessibilityConformsTo !== null) {
            $this->opf->addMetaProperty("dcterms:conformsTo", $this->accessibilityConformsTo);
        }
        if (!empty($this->description)) {
            $this->opf->addDCMeta(DublinCore::DESCRIPTION, StringHelper::decodeHtmlEntities($this->description));
        }
        if (!empty($this->publisherName)) {
            $this->opf->addDCMeta(DublinCore::PUBLISHER, StringHelper::decodeHtmlEntities($this->publisherName));
        }
        if (!empty($this->publisherURL)) {
            $this->opf->addDCMeta(DublinCore::RELATION, StringHelper::decodeHtmlEntities($this->publisherURL));
        }
        if (!empty($this->author)) {
            $author = StringHelper::decodeHtmlEntities($this->author);
            $this->opf->addCreator($author, StringHelper::decodeHtmlEntities($this->authorSortKey), MarcCode::AUTHOR);
            $this->ncx->setDocAuthor($author);
        }
        if (!empty($this->rights)) {
            $this->opf->addDCMeta(DublinCore::RIGHTS, StringHelper::decodeHtmlEntities($this->rights));
        }
        if (!empty($this->coverage)) {
            $this->opf->addDCMeta(DublinCore::COVERAGE, StringHelper::decodeHtmlEntities($this->coverage));
        }
        if (!empty($this->sourceURL)) {
            $this->opf->addDCMeta(DublinCore::SOURCE, $this->sourceURL);
        }
        if (!empty($this->relation)) {
            $this->opf->addDCMeta(DublinCore::RELATION, StringHelper::decodeHtmlEntities($this->relation));
        }
        if ($this->isCoverImageSet) {
            $this->opf->addMeta("cover", "CoverImage");
        }
        if (!empty($this->generator)) {
            $gen = StringHelper::decodeHtmlEntities($this->generator);
            $this->opf->addMeta("generator", $gen);
            $this->ncx->addMetaEntry("dtb:generator", $gen);
        }
        if ($this->EPubMark) {
            $this->opf->addMeta("generator", "EPub (Version " . self::VERSION . ") by A. Grandt, http://www.phpclasses.org/package/6115 or https://github.com/Grandt/PHPePub/");
        }
        reset($this->ncx->chapterList);
        $firstChapterName = array_key_first($this->ncx->chapterList);
        $firstChapterNavPoint = $this->ncx->chapterList[$firstChapterName];
        $firstChapterFileName = $firstChapterNavPoint->getContentSrc();
        $this->opf->addReference(Reference::TEXT, StringHelper::decodeHtmlEntities($firstChapterName), $firstChapterFileName);
        $this->ncx->setUid($this->identifier);
        $this->ncx->setDocTitle(StringHelper::decodeHtmlEntities($this->title));
        $this->ncx->referencesOrder = $this->referencesOrder;
        if ($this->isReferencesAddedToToc) {
            $this->ncx->finalizeReferences();
        }
        $this->finalizeTOC();
        if (str_starts_with($this->bookVersion, '3.')) {
            $this->addEPub3TOC("epub3toc.xhtml", $this->buildEPub3TOC());
        }
        foreach ($this->smilFiles as $smil) {
            $this->zip->addFromString($this->bookRoot . $smil->getHref(), $smil->finalize());
        }
        $opfFinal = StringHelper::fixEncoding($this->opf->finalize());
        $ncxFinal = StringHelper::fixEncoding($this->ncx->finalize());
        if (mb_detect_encoding($opfFinal, 'UTF-8', true) === "UTF-8") {
            $this->zip->addFromString($this->bookRoot . "book.opf", $opfFinal);
        } else {
            $this->zip->addFromString($this->bookRoot . "book.opf", mb_convert_encoding($opfFinal, "UTF-8"));
        }
        if (mb_detect_encoding($ncxFinal, 'UTF-8', true) === "UTF-8") {
            $this->zip->addFromString($this->bookRoot . "book.ncx", $ncxFinal);
        } else {
            $this->zip->addFromString($this->bookRoot . "book.ncx", mb_convert_encoding($ncxFinal, "UTF-8"));
        }
        $this->opf = null;
        $this->ncx = null;
        $this->isFinalized = true;
        return true;
    }

    private function finalizeTOC()
    {
        if (!$this->buildTOC) {
            return false;
        }
        if (empty($this->tocTitle)) {
            $this->tocTitle = "Table of Contents";
        }
        $tocCssCls = "";
        if (!empty($this->tocCSSClass)) {
            $tocCssCls = $this->tocCSSClass . " ";
        }
        $tocData = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        if ($this->isEPubVersion2()) {
            $tocData .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n" . "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n" . "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n" . "\t<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
        } else {
            $tocData .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n" . "<head>\n<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n";
        }
        $tocData .= $this->getViewportMetaLine();
        $tocData .= "<style type=\"text/css\">\n" . $tocCssCls . ".level1 {text-indent:  0em;}\n" . $tocCssCls . ".level2 {text-indent:  2em;}\n" . $tocCssCls . ".level3 {text-indent:  4em;}\n" . $tocCssCls . ".level4 {text-indent:  6em;}\n" . $tocCssCls . ".level5 {text-indent:  8em;}\n" . $tocCssCls . ".level6 {text-indent: 10em;}\n" . $tocCssCls . ".level7 {text-indent: 12em;}\n" . $tocCssCls . ".reference {}\n" . "</style>\n";
        if (!empty($this->tocCssFileName)) {
            $tocData .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->tocCssFileName . "\" />\n";
        }
        $tocData .= "<title>" . $this->tocTitle . "</title>\n" . "</head>\n" . "<body>\n" . "<h3>" . $this->tocTitle . "</h3>\n<div";
        if (!empty($this->tocCSSClass)) {
            $tocData .= " class=\"" . $this->tocCSSClass . "\"";
        }
        $tocData .= ">\n";
        foreach ($this->referencesOrder as $item => $descriptive) {
            if ($item === "text") {
                foreach ($this->ncx->chapterList as $chapterName => $navPoint) {
                    $fileName = $navPoint->getContentSrc();
                    $level = $navPoint->getLevel() - 2;
                    $tocData .= "\t<p class='level" . ($level + 1) . "'>" . "<a href=\"" . $fileName . "\">" . $chapterName . "</a></p>\n";
                }
            } else {
                if ($this->tocAddReferences === true) {
                    if (array_key_exists($item, $this->ncx->referencesList)) {
                        $tocData .= "\t<p class='level1 reference'><a href=\"" . $this->ncx->referencesList[$item] . "\">" . $descriptive . "</a></p>\n";
                    } else {
                        if ($item === "toc") {
                            $tocData .= "\t<p class='level1 reference'><a href=\"TOC.xhtml\">" . $this->tocTitle . "</a></p>\n";
                        } else {
                            if ($item === "cover" && $this->isCoverImageSet) {
                                $tocData .= "\t<p class='level1 reference'><a href=\"CoverPage.xhtml\">" . $descriptive . "</a></p>\n";
                            }
                        }
                    }
                }
            }
        }
        $tocData .= "</div>\n</body>\n</html>\n";
        $this->addReferencePage($this->tocTitle, $this->tocFileName, $tocData, Reference::TABLE_OF_CONTENTS);
        return true;
    }

    public function addEPub3TOC($fileName, $tocData)
    {
        if ($this->isEPubVersion2() || $this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return false;
        }
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $safeName = FileHelper::sanitizeZipPath($fileName, false);
        if ($safeName === false) {
            return false;
        }
        $this->zip->addFromString($this->bookRoot . $safeName, $tocData);
        $this->fileList[$safeName] = $safeName;
        $this->opf->addItem("toc", $safeName, "application/xhtml+xml", "nav");
        return true;
    }

    public function buildEPub3TOC($cssFileName = null, $title = "Table of Contents")
    {
        $this->ncx->referencesOrder = $this->referencesOrder;
        $this->ncx->setDocTitle(StringHelper::decodeHtmlEntities($this->title));
        return $this->ncx->finalizeEPub3($title, $cssFileName);
    }

    public function setViewport($width = null, $height = null)
    {
        if ($width == null) {
            unset($this->viewport);
            return;
        }
        if (is_string($width) && array_key_exists($width, $this->viewportMap)) {
            $vp = $this->viewportMap[$width];
            $width = $vp['width'];
            $height = $vp['height'];
        }
        $this->viewport = ['width' => $width, 'height' => $height];
    }

    public function getViewportMetaLine()
    {
        if (empty($this->viewport)) {
            return "";
        }
        return "\t\t<meta name=\"viewport\" content=\"width=" . $this->viewport['width'] . ", height=" . $this->viewport['height'] . "\"/>\n";
    }

    public function setAccessibilitySummary($summary)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->accessibilitySummary = $summary;
    }

    public function addAccessMode($mode)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->accessModes[] = $mode;
    }

    public function addAccessibilityFeature($feature)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->accessibilityFeatures[] = $feature;
    }

    public function addAccessibilityHazard($hazard)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->accessibilityHazards[] = $hazard;
    }

    public function setAccessibilityConformsTo($standard)
    {
        if ($this->isFinalized) {
            return;
        }
        $this->accessibilityConformsTo = $standard;
    }

    public function getBookVersion(): string
    {
        return $this->bookVersion;
    }
}
