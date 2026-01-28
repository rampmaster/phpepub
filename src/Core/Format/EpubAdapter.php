<?php
namespace Rampmaster\EPub\Core\Format;

use Rampmaster\EPub\Core\EPub;
use Rampmaster\EPub\Helpers\FileHelper;

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

        // If epubcheck available, use it
        $epubcheck = null;
        $which = trim(`which epubcheck`);
        if ($which) {
            $epubcheck = $which;
        }

        if ($epubcheck) {
            $cmd = escapeshellcmd($epubcheck) . ' ' . escapeshellarg($path) . ' 2>&1';
            $output = [];
            $ret = 0;
            exec($cmd, $output, $ret);
            // epubcheck returns 0 on success
            return $ret === 0;
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

            return $hasMimetype && $hasOpf && ($hasNcx || $hasEpub3Toc);
        }

        return false;
    }
}
