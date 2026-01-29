Plan Release 1 - Soporte inicial y seguridad

Objetivos principales
- Asegurar que la generación de EPUB funcione correctamente y cumpla con EPUB 3.0 (prioridad) y EPUB 2.0.1.
- Corregir problemas de seguridad críticos y dependencias.
- Establecer CI que ejecute las pruebas (Unit + Integration) usando epubcheck en runner (Ubuntu) con Java o paquete `epubcheck`.

Alcance
- Implementación y pruebas para: EPUB 3.0, EPUB 2.0.1, AZW3 (soporte de lectura/generación básica), eliminar KFX (descartado).
- Idiomas iniciales: es, en, pt, fr.

Entregables
- Tests automatizados (Unit + Integration) que generen un EPUB desde HTML y validen con epubcheck.
- Pipeline CI en GitHub Actions para PHP 8.2..8.5 que instale epubcheck (apt o jar) y ejecute `composer ci`.
- Documentación: docs/plan, docs/report, docs/tickets.

Prioridades
1. Seguridad y EPUB 3.0
2. Internacionalización (idiomas indicados)
3. Soporte adicional de formatos (AZW3)

Fechas estimadas
- Fase 1 (2 semanas): correcciones críticas, CI básico y validación EPUB 3.0
- Fase 2 (2 semanas): pruebas multi-idioma, mejoras de estabilidad
- Fase 3 (4 semanas): soporte AZW3 y empaquetado adicional

Riesgos
- Dependencia en herramientas externas (epubcheck, java, calibre) — CI debe tener reproducible imagen con epubcheck.
- Código legacy con deuda técnica (muchos avisos phpstan y reglas phpcs relajadas).

Notas
- Se ha generado un `phpstan-baseline.neon` para silenciar problemas preexistentes; la deuda técnica debe abordarse en releases posteriores.
