# Ticket: release_TICKET-002_EPUB3-Validation

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [ ] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

El proyecto debe generar EPUBs válidos para EPUB 3.0 y EPUB 2.0.1 y validar automáticamente los artefactos con `epubcheck`. Durante la integración se detectaron casos donde el contenido HTML (DOCTYPE HTML5, falta de namespace XHTML) provocaba errores de validación.

## Objetivo (medible)

- Garantizar que la generación de capítulos produce documentos XHTML bien formados que pasen `epubcheck`.
- Añadir tests de integración que generen y validen artefactos para EPUB 3.0 y EPUB 2.0.1.

## Alcance

**Incluye**:
- Revisar y ajustar `EpubAdapter::convertToXhtml()` para convertir HTML5 o fragmentos a XHTML válido.
- Asegurar que cada capítulo tenga `<title>` en `<head>` y `xml:lang`/`lang`.
- Añadir/ajustar tests de integración `tests/Integration/EpubCheckIntegrationTest.php`.
- Configurar CI para ejecutar epubcheck y fallar en errores fatales.

**No incluye**:
- Reconstrucción de toda la lógica OPF/NCX; se harán parches y tests.

## Contrato funcional

### Entradas
- Configuración de generación: `title`, `language`, `chapters[]` (path o content).

### Salidas
- Archivo .epub generado en `build` o `tests/build`.
- Salida de `epubcheck` (logs) como parte del job.

### Reglas de negocio
- Si `epubcheck` devuelve error fatal, el pipeline de CI debe fallar para esa job.

### Errores esperados / validaciones
- Casos donde HTML no es bien formado y requiere corrección automática por el adaptador.

## Datos

- Entidades: artifacts EPUB, capítulos (XHTML content).
- No hay migraciones.

## Permisos

- No aplica.

## UX/UI

- No aplica.

## Plan de implementación

1. Confirmar tests actuales y fixtures que reproducen el fallo (tests/Unit fixtures).
2. Ajustar `convertToXhtml()` (ya implementado con DOMDocument) para asegurar título y lang.
3. Extender `EpubAdapter::validate()` para localizar OPF via `META-INF/container.xml`.
4. Ejecutar tests de integración en CI y validar comportamiento.

## Criterios de aceptación (checklist verificable)

- [ ] Tests Integration pasan en la matrix PHP (8.2..8.5) sin errores fatales de epubcheck.
- [ ] Todos los capítulos generados contienen `<title>` y atributos `lang`.

## Pruebas

- Happy path:
  - Generar EPUB desde fixture HTML y comprobar que `epubcheck` no reporta errores.
- Edge case:
  - HTML fragment con doctype HTML5 debe convertirse a XHTML válido.

## Checklist de entrega

- [ ] Docs actualizada
- [ ] Tests en verde
- [ ] Workflow CI configurado para ejecutar epubcheck

## Referencias
- `src/Core/Format/EpubAdapter.php`
- `tests/Integration/EpubCheckIntegrationTest.php`
