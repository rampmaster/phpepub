# Resumen ejecutivo

Objetivo: convertir `phpepub` en una librería robusta para generar ePubs compatibles con estándares modernos (EPUB 3.x) y generar formatos derivados (AZW3) para Kindle.

Alcance inicial (R1):
- Fortalecimiento de seguridad (manejo de ZIP, sanitización de metadatos).
- Implementación de packaging y TOC para EPUB 3.0.
- Pipeline CI que valide generación con `epubcheck` y ejecute tests en PHP 8.2–8.5.

Entregables:
- Documentación en `docs/`.
- Tickets en `docs/tickets/release-1/`.
- Plantillas de adaptadores de formato en `src/Core/Format/`.
- Plantillas de tests en `tests/` y fixtures en `tests/fixtures/`.
