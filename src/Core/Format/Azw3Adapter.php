<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core\Format;

use Rampmaster\EPub\Helpers\FileHelper;
use Symfony\Component\Process\Process;

/**
 * Adaptador AZW3.
 *
 * Genera un EPUB intermedio y utiliza `ebook-convert` (Calibre) para convertirlo a AZW3.
 */
class Azw3Adapter implements FormatAdapterInterface
{
    private ?string $converterBinary;

    public function __construct(?string $converterBinary = null)
    {
        $this->converterBinary = $converterBinary;
    }

    /**
     * Genera un archivo AZW3.
     *
     * @param array $input Parámetros de entrada (mismos que EpubAdapter).
     * @return string Ruta al archivo AZW3 generado.
     * @throws \RuntimeException Si no se encuentra la herramienta de conversión o falla el proceso.
     */
    public function generate(array $input): string
    {
        // 1. Detectar herramienta de conversión
        $binary = $this->getConverterBinary();
        if ($binary === null) {
            throw new \RuntimeException('No se encontró la herramienta de conversión (ebook-convert). Instale Calibre o configure la ruta.');
        }

        // 2. Generar EPUB intermedio
        // Usamos un directorio temporal para el EPUB intermedio si no se especifica buildDir,
        // o usamos el buildDir pero con un nombre temporal.
        $buildDir = $input['buildDir'] ?? sys_get_temp_dir();

        // Asegurar que el directorio existe
        if (!is_dir($buildDir)) {
            @mkdir($buildDir, 0775, true);
        }

        // Modificamos el input para que EpubAdapter genere en el mismo directorio
        $epubInput = $input;
        $epubInput['buildDir'] = $buildDir;

        $epubAdapter = new EpubAdapter();
        $epubPath = $epubAdapter->generate($epubInput);

        // 3. Definir ruta de salida AZW3
        // Si el usuario especificó un nombre en el input, podríamos intentar respetarlo,
        // pero EpubAdapter genera su propio nombre basado en el título.
        // Vamos a derivar el nombre AZW3 del nombre del EPUB generado.
        $azw3Path = preg_replace('/\.epub$/i', '.azw3', $epubPath);
        if ($azw3Path === $epubPath) {
            $azw3Path .= '.azw3';
        }

        // 4. Ejecutar conversión
        $cmd = [$binary, $epubPath, $azw3Path];

        $process = new Process($cmd);
        $process->setTimeout(300); // 5 minutos debería ser suficiente
        $process->run();

        if (!$process->isSuccessful()) {
            // Intentar limpiar el EPUB intermedio antes de lanzar excepción
            if (is_file($epubPath)) {
                @unlink($epubPath);
            }
            throw new \RuntimeException('Error al convertir a AZW3: ' . $process->getErrorOutput());
        }

        // 5. Limpiar EPUB intermedio (opcional, por defecto sí)
        if (!($input['keep_intermediate'] ?? false)) {
            if (is_file($epubPath)) {
                @unlink($epubPath);
            }
        }

        return $azw3Path;
    }

    public function validate(string $path): bool
    {
        // Validaciones simples: existencia y tamaño > 0.
        // AZW3 es un formato binario propietario, validación profunda es difícil sin herramientas.
        return is_file($path) && filesize($path) > 0;
    }

    private function getConverterBinary(): ?string
    {
        if ($this->converterBinary !== null) {
            return $this->converterBinary;
        }

        // Intentar encontrar ebook-convert en el PATH
        $process = new Process(['which', 'ebook-convert']);
        $process->run();
        if ($process->isSuccessful()) {
            return trim($process->getOutput());
        }

        // Fallback comunes
        $commonPaths = [
            '/usr/bin/ebook-convert',
            '/usr/local/bin/ebook-convert',
            '/Applications/calibre.app/Contents/MacOS/ebook-convert', // macOS
            'C:\Program Files\Calibre2\ebook-convert.exe', // Windows standard
        ];

        foreach ($commonPaths as $path) {
            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }
}
