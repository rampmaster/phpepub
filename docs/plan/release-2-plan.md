# Plan de Release 2: Soporte EPUB 3.0.1 y 3.1

## Objetivo
Ampliar la capacidad de generación de EPUB para soportar las especificaciones EPUB 3.0.1 y EPUB 3.1. Esto implica ajustes en la estructura del paquete (OPF), metadatos y navegación, asegurando compatibilidad y validación correcta con `epubcheck`.

## Alcance
- **Soporte EPUB 3.0.1**: Ajustes menores en metadatos y atributos permitidos.
- **Soporte EPUB 3.1**: Cambios en la estructura de navegación (eliminación de NCX como obligatorio si existe Nav Document), cambios en metadatos (dc:identifier, dcterms:modified).
- **Validación**: Actualizar tests de integración para validar específicamente estas versiones.

## Estrategia de Implementación
1.  **Refactorización de `EPub` y `Opf`**: Permitir configuración granular de la versión (3.0, 3.0.1, 3.1).
2.  **Adaptación de Metadatos**: Implementar lógica condicional para metadatos requeridos/obsoletos según versión.
3.  **Navegación**: Asegurar que para 3.1 se priorice el Nav Document HTML5 y el NCX sea opcional/legacy.

## Tickets

### TICKET-004: Soporte EPUB 3.0.1
- **Objetivo**: Permitir generar EPUBs declarados como versión 3.0.1.
- **Tareas**:
    - Actualizar constante de versiones en `EPub`.
    - Ajustar `package` version attribute en OPF.
    - Validar con `epubcheck`.

### TICKET-005: Soporte EPUB 3.1
- **Objetivo**: Permitir generar EPUBs declarados como versión 3.1.
- **Tareas**:
    - Actualizar constante de versiones.
    - Implementar cambios estructurales de 3.1 (ej. metadatos `dcterms:modified` obligatorio, `opf:role` deprecated en favor de `refines`).
    - Validar con `epubcheck`.

### TICKET-006: Tests de Integración R2
- **Objetivo**: Asegurar que las nuevas versiones pasan la validación CI.
- **Tareas**:
    - Extender `EpubCheckIntegrationTest` para cubrir 3.0.1 y 3.1.
    - Verificar que no hay regresiones en 3.0 y 2.0.1.

## Riesgos
- Diferencias sutiles en validación de `epubcheck` para 3.0.1 vs 3.1 pueden requerir ajustes finos en `Opf.php`.

## Criterios de Éxito
- `epubcheck` valida correctamente archivos generados como 3.0.1 y 3.1.
- CI pasa en verde para todas las versiones soportadas.
