<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core\Format;

interface FormatAdapterInterface
{
    /**
     * Genera un archivo de salida a partir de los datos de entrada.
     * Devuelve la ruta al archivo generado.
     *
     * @param array $input Estructura mínima con claves como 'title', 'language', 'chapters', 'css', 'metadata'
     * @return string Ruta absoluta/relativa al archivo generado
     */
    public function generate(array $input): string;

    /**
     * Valida el archivo generado (por ejemplo con epubcheck u otra herramienta).
     *
     * @param string $path Ruta al archivo a validar
     * @return bool True si válido, false en caso contrario
     */
    public function validate(string $path): bool;
}
