# Plan de Release 3: Soporte EPUB 3.2 y Compatibilidad EPUB 2.0.1

## Objetivo
Completar el soporte de la familia EPUB 3.x añadiendo la versión 3.2 y asegurar una compatibilidad robusta con EPUB 2.0.1 (legacy). EPUB 3.2 es la versión recomendada actual por el W3C y simplifica algunos requisitos respecto a 3.0/3.1.

## Alcance
- **Soporte EPUB 3.2**:
    - Actualizar constantes y atributos de versión.
    - Eliminar requisitos estrictos de 3.1 que fueron relajados en 3.2 (ej. `dcterms:modified` ya no es estrictamente obligatorio si se usa `dcterms:modified` de otra forma, pero mantenerlo es buena práctica; revisar especificación).
    - Permitir fuentes remotas y scripts (con advertencias).
- **Compatibilidad EPUB 2.0.1**:
    - Revisar y reforzar la generación de NCX (obligatorio en 2.0.1).
    - Asegurar que elementos HTML5 no se filtren en modo 2.0.1 (o se degraden correctamente).
- **Validación**: Actualizar tests de integración.

## Estrategia de Implementación
1.  **Actualización de `EPub`**: Añadir constante para 3.2.
2.  **Ajuste de `Opf`**: Lógica para versión 3.2.
3.  **Revisión de `Ncx`**: Asegurar compatibilidad total con 2.0.1 (ya soportado, pero verificar edge cases).
4.  **Tests**: Ampliar matriz de pruebas.

## Tickets

### TICKET-007: Soporte EPUB 3.2
- **Objetivo**: Generar EPUBs válidos declarados como versión 3.2.
- **Tareas**:
    - Añadir constante `BOOK_VERSION_EPUB32`.
    - Ajustar generación de OPF (`package version="3.2"`).
    - Validar con `epubcheck`.

### TICKET-008: Refuerzo Compatibilidad EPUB 2.0.1
- **Objetivo**: Asegurar que la generación en modo 2.0.1 es estricta y no incluye elementos de 3.x que rompan validación.
- **Tareas**:
    - Revisar `EpubAdapter::convertToXhtml` para degradar HTML5 a XHTML 1.1 si la versión es 2.0.1.
    - Verificar que `epub:type` y otros atributos de 3.x no se inyecten en 2.0.1.

### TICKET-009: Tests de Integración R3
- **Objetivo**: Tests completos para 3.2 y regresión de 2.0.1.
- **Tareas**:
    - Actualizar `EpubCheckIntegrationTest` con provider para 3.2.
    - Añadir test específico para 2.0.1 con contenido HTML5 que debe ser limpiado.

## Criterios de Éxito
- `epubcheck` valida 3.2 sin errores.
- `epubcheck` valida 2.0.1 generado desde input moderno sin errores.
