# Roadmap del proyecto phpepub

Este documento resume el roadmap aprobado para añadir soporte y validación de formatos EPUB y generación de AZW3, la estructura de releases y la estrategia de CI.

## Visión general
- Prioridad secuencial: entregar Release 1 (R1) centrado en seguridad y soporte robusto para EPUB 3.0, luego ampliar soporte a versiones EPUB 3.x y EPUB 2.0.1, y finalmente añadir export AZW3.
- KFX descartado por ahora.
- Soporte mínimo de idiomas: español (es), inglés (en), portugués (pt) y francés (fr).
- Pipeline CI: ejecutar pruebas en matrix PHP 8.2–8.5; validar EPUB generados con `epubcheck`; eliminar artefactos temporales al finalizar.

## Releases
- Release 1 (R1): Seguridad + EPUB 3.0 (nav HTML5, OPF compatible, epubcheck en CI)
- Release 2 (R2): EPUB 3.0.1 y 3.1
- Release 3 (R3): EPUB 3.2 y compatibilidad con EPUB 2.0.1
- Release 4 (R4): Export AZW3 (usando herramienta externa o binario invocable)

## Entregables por release
- R1: Documentación, tickets, tests, implementación mínima de EpubAdapter para EPUB3, pipeline CI con `epubcheck`.
- R2–R3: Ajustes en `Opf`/`Ncx` y adaptadores para versiones menores; ampliar test matrix.
- R4: Adaptador `Azw3Adapter` y tests de conversión; ticket de investigación para generación sin Calibre.

## Notas
- CI eliminará los archivos generados en cada job (limpieza al final del job). Para debugging se podrán subir artefactos con expiración corta.
