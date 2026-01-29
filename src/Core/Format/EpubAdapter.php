<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core\Format;

use Rampmaster\EPub\Core\EPub;
use Rampmaster\EPub\Helpers\FileHelper;
use Symfony\Component\Process\Process;

/**
 * Stub de adaptador EPUB. Implementa la interfaz pero delega en la clase EPub existente.
 * Configurable para generar EPUB 2.x o EPUB 3.x según la opción 'version' en $input.
 */
class EpubAdapter implements FormatAdapterInterface
{
    /**
     * Genera un epub mínimo en build/ y devuelve la ruta al archivo generado.
     * Se esperan campos en $input: title, language, author, chapters (array of [name, filename, pathOrContent]), version
     */
    public function generate(array $input): string
    {
        $title = $input['title'] ?? 'Untitled';
        $language = $input['language'] ?? 'en';
        $author = $input['author'] ?? null;
        $version = $input['version'] ?? EPub::BOOK_VERSION_EPUB3;

        // Crear directorio build si no existe
        $buildDir = $input['buildDir'] ?? __DIR__ . '/../../../../build';
        if (!is_dir($buildDir)) {
            @mkdir($buildDir, 0775, true);
        }

        // Validate build directory is inside the repository
        if (!FileHelper::isSafeBuildDir($buildDir)) {
            throw new \RuntimeException('Build directory is not inside repository root: ' . $buildDir);
        }

        $book = new EPub($version, $language);
        $book->setTitle($title);
        if ($author) {
            $book->setAuthor($author, $author);
        }

        // Añadir CSS si existe
        if (!empty($input['css'])) {
            $book->addCSSFile('styles.css', 'css1', $input['css']);
        }

        // Añadir capítulos: aceptamos contenido o rutas a fichero HTML
        $chapters = $input['chapters'] ?? [];
        $i = 1;
        foreach ($chapters as $chapter) {
            $cname = $chapter['name'] ?? ('Chapter ' . $i);
            $cfile = $chapter['file'] ?? ('chapter' . $i . '.xhtml');
            // Reject excessively long file names
            if (strlen($cfile) > 255) {
                throw new \RuntimeException('Chapter file name too long');
            }
            $ccontent = '';
            if (!empty($chapter['path'])) {
                $path = $chapter['path'];
                // Reject file:// URIs
                if (str_starts_with($path, 'file://')) {
                    throw new \RuntimeException('file:// URIs are not allowed for chapter paths');
                }
                // Reject remote URLs — require content or a local repo path
                if (preg_match('#^https?://#i', $path)) {
                    throw new \RuntimeException('Remote URLs are not allowed as chapter paths');
                }
                if (is_file($path)) {
                    // Ensure the source path is inside the repository (prevent path traversal / external files)
                    $repoRoot = dirname(__DIR__, 3);
                    if (!FileHelper::isPathInside($path, $repoRoot)) {
                        throw new \RuntimeException('Chapter path is outside repository: ' . $path);
                    }
                    $ccontent = file_get_contents($path);
                } else {
                    throw new \RuntimeException('Chapter path does not exist or is not a file: ' . $path);
                }
            } elseif (!empty($chapter['content'])) {
                $ccontent = $chapter['content'];
            } else {
                $ccontent = "<h1>$cname</h1><p>Empty chapter</p>";
            }

            // Ensure content is XHTML-like and include chapter title in <title>
            $ccontent = $this->convertToXhtml($ccontent, $language, $cname, $version);

            $book->addChapter($cname, $cfile, $ccontent);
            $i++;
        }

        // Generar TOC
        $book->buildTOC();

        // Guardar
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $title);
        $fileName = $safeTitle . '_' . time() . '.epub';
        $saved = $book->saveBook($fileName, $buildDir);
        if ($saved === false) {
            throw new \RuntimeException('No se pudo guardar el epub');
        }

        return rtrim($buildDir, '/') . '/' . $saved;
    }

    /**
     * Valida el epub: usa epubcheck si está en el PATH; si no, valida la presencia de archivos clave dentro del zip.
     */
    public function validate(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        // If epubcheck available, use it (via symfony/process)
        try {
            // First try system binary 'epubcheck'
            $probe = new Process(['epubcheck', '--version']);
            $probe->run();
            if ($probe->isSuccessful()) {
                $proc = new Process(['epubcheck', $path]);
                $proc->setTimeout(60);
                $proc->run();
                return $proc->isSuccessful();
            }
        } catch (\Throwable $e) {
            // ignore and try next option
        }

        // Fallback: try bundled epubcheck JAR if present in repository root at /epubcheck/epubcheck.jar
        $jarPath = __DIR__ . '/../../../epubcheck/epubcheck.jar';
        if (is_file($jarPath)) {
            try {
                $proc = new Process(['java', '-jar', $jarPath, $path]);
                $proc->setTimeout(120);
                $proc->run();
                return $proc->isSuccessful();
            } catch (\Throwable $e) {
                // ignore and fall back to zip checks
            }
        }

        // Fallback: open zip and check for mimetype and OEBPS/book.opf
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            // Prefer to read container.xml to find the OPF location
            $containerPath = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($entry), 'meta-inf/container.xml')) {
                    $containerPath = $entry;
                    break;
                }
            }

            $hasOpf = false;
            $hasNcx = false;
            $hasEpub3Toc = false;
            $hasMimetype = false;

            if ($containerPath !== null) {
                $containerXml = $zip->getFromName($containerPath);
                if ($containerXml !== false) {
                    try {
                        $doc = new \DOMDocument();
                        $doc->loadXML($containerXml);
                        $rootfiles = $doc->getElementsByTagName('rootfile');
                        if ($rootfiles->length > 0) {
                            $fullPath = $rootfiles->item(0)->getAttribute('full-path');
                            if (!empty($fullPath) && $zip->locateName($fullPath, \ZipArchive::FL_NODIR) !== false) {
                                $hasOpf = true;
                                $opfContent = $zip->getFromName($fullPath);
                                if ($opfContent !== false) {
                                    // look for NCX item in manifest (application/x-dtbncx+xml)
                                    if (stripos($opfContent, 'application/x-dtbncx+xml') !== false) {
                                        $hasNcx = true;
                                    }
                                    // look for EPUB3 nav item (properties="nav")
                                    if (stripos($opfContent, 'properties="nav"') !== false || stripos($opfContent, "properties='nav'") !== false) {
                                        $hasEpub3Toc = true;
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // fall back to scanning entries
                    }
                }
            }

            // If we didn't find OPF via container.xml, fallback to scanning entries
            if (!$hasOpf || (!$hasNcx && !$hasEpub3Toc)) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    $lname = strtolower($name);

                    // detect mimetype item
                    if (!$hasMimetype && $lname === 'mimetype') {
                        $hasMimetype = true;
                    }

                    if (!$hasOpf && preg_match('/\.opf$/i', $lname)) {
                        $hasOpf = true;
                    }
                    if (!$hasNcx && preg_match('/\.ncx$/i', $lname)) {
                        $hasNcx = true;
                    }

                    if (!$hasEpub3Toc && preg_match('/\.(xhtml|html?|xml)$/i', $lname)) {
                        $contents = $zip->getFromIndex($i);
                        if ($contents !== false) {
                            if (stripos($contents, '<nav') !== false || stripos($contents, 'epub:type') !== false || stripos($contents, 'properties="nav"') !== false) {
                                $hasEpub3Toc = true;
                            }
                        }
                    }

                    if ($hasOpf && ($hasNcx || $hasEpub3Toc) && $hasMimetype) {
                        break;
                    }
                }

                // close after scanning
                $zip->close();

                // If ZipArchive couldn't find the mimetype entry, check first bytes
                if (!$hasMimetype) {
                    $fp = @fopen($path, 'rb');
                    if ($fp) {
                        $first = fread($fp, 20);
                        fclose($fp);
                        if (strpos($first, 'application/epub+zip') !== false) {
                            $hasMimetype = true;
                        }
                    }
                }

                return $hasMimetype && $hasOpf && ($hasNcx || $hasEpub3Toc);
            }

            // If we got here, we had OPF detected via container.xml and maybe found ncx/nav
            // Also confirm mimetype presence
            $mimetypeIndex = $zip->locateName('mimetype', \ZipArchive::FL_NODIR);
            $hasMimetype = $mimetypeIndex !== false;
            if (!$hasMimetype) {
                $fp = @fopen($path, 'rb');
                if ($fp) {
                    $first = fread($fp, 20);
                    fclose($fp);
                    if (strpos($first, 'application/epub+zip') !== false) {
                        $hasMimetype = true;
                    }
                }
            }

            return $hasMimetype && $hasOpf && ($hasNcx || $hasEpub3Toc);
        }

        return false;
    }

    /**
     * Convert HTML content to valid XHTML for EPUB.
     */
    private function convertToXhtml(string $content, string $language = 'en', string $title = '', string $version = EPub::BOOK_VERSION_EPUB3): string
    {
        // Quick path: if snippet has no HTML root, treat as body fragment
        $isFragment = (stripos($content, '<html') === false);

        // Normalize encoding
        // Wrap fragment for parsing
        $parseHtml = $content;
        if ($isFragment) {
            $parseHtml = '<div>' . $content . '</div>';
        }

        // Use DOMDocument to parse the HTML fragment (tolerant parser)
        $libxmlState = libxml_use_internal_errors(true);
        $doc = new \DOMDocument('1.0', 'utf-8');
        // Suppress warnings from malformed HTML
        @$doc->loadHTML('<?xml encoding="utf-8"?>' . $parseHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Create the XHTML document
        $x = new \DOMDocument('1.0', 'utf-8');
        $html = $x->createElementNS('http://www.w3.org/1999/xhtml', 'html');
        $html->setAttribute('xml:lang', $language);
        $html->setAttribute('lang', $language);
        $x->appendChild($html);

        $head = $x->createElement('head');
        $meta = $x->createElement('meta');
        $meta->setAttribute('charset', 'utf-8');
        $head->appendChild($meta);
        $titleElement = $x->createElement('title', $title);
        $head->appendChild($titleElement);
        $html->appendChild($head);

        $body = $x->createElement('body');
        $html->appendChild($body);

        // Import nodes from parsed doc into body
        if ($isFragment) {
            // parsed wrapper div
            $divs = $doc->getElementsByTagName('div');
            if ($divs->length > 0) {
                $frag = $divs->item(0);
                foreach ($frag->childNodes as $child) {
                    $imported = $x->importNode($child, true);
                    $body->appendChild($imported);
                }
            }
        } else {
            // full document: import body children if present, else import documentElement
            $b = null;
            $bList = $doc->getElementsByTagName('body');
            if ($bList->length > 0) {
                $b = $bList->item(0);
            } elseif ($doc->documentElement) {
                $b = $doc->documentElement;
            }
            if ($b) {
                foreach ($b->childNodes as $child) {
                    $imported = $x->importNode($child, true);
                    $body->appendChild($imported);
                }
            }
        }

        // If version is EPUB 2.0.1, strip HTML5 specific tags and attributes
        if ($version === EPub::BOOK_VERSION_EPUB2) {
            $this->cleanHtml5ForEpub2($x);
        }

        // Restore libxml state
        libxml_clear_errors();
        libxml_use_internal_errors($libxmlState);

        // Return serialized XHTML with XML declaration
        $xml = $x->saveXML();
        return $xml;
    }

    /**
     * Removes HTML5 tags and attributes that are not valid in XHTML 1.1 (EPUB 2.0.1).
     */
    private function cleanHtml5ForEpub2(\DOMDocument $doc): void
    {
        $xpath = new \DOMXPath($doc);

        // 1. Remove 'epub:' attributes (e.g. epub:type)
        // Note: DOMDocument might not handle namespaces perfectly without registration,
        // but we can iterate all elements and attributes.
        foreach ($xpath->query('//*') as $element) {
            if ($element instanceof \DOMElement) {
                // Check attributes
                // We need to collect attributes to remove first to avoid modification during iteration issues
                $attrsToRemove = [];
                foreach ($element->attributes as $attr) {
                    if ($attr->prefix === 'epub') {
                        $attrsToRemove[] = $attr;
                    }
                    // Also remove 'charset' attribute from meta tags if present (except for content-type which is handled differently)
                    // The error message says: attribute "charset" not allowed here
                    if ($attr->nodeName === 'charset') {
                        $attrsToRemove[] = $attr;
                    }
                }
                foreach ($attrsToRemove as $attr) {
                    $element->removeAttributeNode($attr);
                }
            }
        }

        // 2. Rename HTML5 structural elements to div or span
        // List of HTML5 tags to convert to div
        $blockTags = ['section', 'nav', 'article', 'aside', 'header', 'footer', 'main', 'figure', 'figcaption'];
        // List of HTML5 tags to convert to span (inline) - mostly time, mark
        $inlineTags = ['time', 'mark'];

        foreach ($blockTags as $tagName) {
            $nodes = $doc->getElementsByTagName($tagName);
            // Iterate backwards or collect first to avoid live list issues
            $nodesArray = iterator_to_array($nodes);
            foreach ($nodesArray as $node) {
                $this->renameElement($node, 'div');
            }
        }

        foreach ($inlineTags as $tagName) {
            $nodes = $doc->getElementsByTagName($tagName);
            $nodesArray = iterator_to_array($nodes);
            foreach ($nodesArray as $node) {
                $this->renameElement($node, 'span');
            }
        }
    }

    private function renameElement(\DOMElement $element, string $newName): void
    {
        $newElement = $element->ownerDocument->createElement($newName);
        // Copy attributes
        foreach ($element->attributes as $attribute) {
            $newElement->setAttribute($attribute->nodeName, $attribute->nodeValue);
        }
        // Move children
        while ($element->firstChild) {
            $newElement->appendChild($element->firstChild);
        }
        // Replace
        $element->parentNode->replaceChild($newElement, $element);
    }
}
