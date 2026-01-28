<?php
namespace Rampmaster\EPub\Core\Format;

use Rampmaster\EPub\Core\EPub;
use Rampmaster\EPub\Helpers\FileHelper;
use Symfony\Component\Process\Process;

/**
 * Stub de adaptador EPUB. Implementa la interfaz pero delega en la clase EPub existente.
 * Configurable para generar EPUB 2.x o EPUB 3.x según la opción 'version' en $input.
 */
class EpubAdapter implements FormatAdapterInterface {
    /**
     * Genera un epub mínimo en build/ y devuelve la ruta al archivo generado.
     * Se esperan campos en $input: title, language, author, chapters (array of [name, filename, pathOrContent]), version
     */
    public function generate(array $input): string {
        $title = $input['title'] ?? 'Untitled';
        $language = $input['language'] ?? 'en';
        $author = $input['author'] ?? null;
        $version = $input['version'] ?? EPub::BOOK_VERSION_EPUB3;

        // Crear directorio build si no existe
        $buildDir = $input['buildDir'] ?? __DIR__ . '/../../../../build';
        if (!is_dir($buildDir)) {
            @mkdir($buildDir, 0775, true);
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
            $ccontent = '';
            if (!empty($chapter['path']) && is_file($chapter['path'])) {
                $ccontent = file_get_contents($chapter['path']);
            } elseif (!empty($chapter['content'])) {
                $ccontent = $chapter['content'];
            } else {
                $ccontent = "<h1>$cname</h1><p>Empty chapter</p>";
            }

            // Ensure content is XHTML-like; wrap with basic header/footer if missing
            if (strpos($ccontent, '<html') === false) {
                $ccontent = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head>\n<body>" . $ccontent . "</body>\n</html>";
            }

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
    public function validate(string $path): bool {
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
            $mimetypeIndex = $zip->locateName('mimetype', \ZipArchive::FL_NODIR);
            $hasMimetype = $mimetypeIndex !== false;
            $hasOpf = $zip->locateName('OEBPS/book.opf', \ZipArchive::FL_NODIR) !== false;
            $hasNcx = $zip->locateName('OEBPS/book.ncx', \ZipArchive::FL_NODIR) !== false;
            $hasEpub3Toc = $zip->locateName('OEBPS/epub3toc.xhtml', \ZipArchive::FL_NODIR) !== false;
            $zip->close();

            // If ZipArchive couldn't find the mimetype entry, check the first bytes of the file
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
}
