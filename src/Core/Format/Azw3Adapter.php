<?php
namespace Rampmaster\EPub\Core\Format;

/**
 * Adaptador AZW3 (stub).
 *
 * Nota: La estrategia recomendada es generar primero un EPUB válido y luego
 * invocar un convertidor (por ejemplo Calibre `ebook-convert`) para producir AZW3.
 * Este adaptador deja puntos de extensión para invocar binarios externos o
 * servicios de conversión. Se debe investigar la posibilidad de generar AZW3
 * sin dependencias externas (ticket T-AZW3-INVESTIGATE.md).
 */
class Azw3Adapter implements FormatAdapterInterface {
    public function generate(array $input): string {
        // TODO: Generar AZW3 invocando conversor externo.
        throw new \RuntimeException('Azw3Adapter::generate() no implementado.');
    }

    public function validate(string $path): bool {
        // Validaciones simples: existencia y tamaño > 0. Para validaciones profundas,
        // se necesitarían herramientas específicas.
        return is_file($path) && filesize($path) > 0;
    }
}
