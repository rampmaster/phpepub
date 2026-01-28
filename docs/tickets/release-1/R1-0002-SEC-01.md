# Ticket: R1_SEC_01_HARDEN_ZIP_HANDLING

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [ ] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

El manejo actual de archivos dentro del empaquetado ePub puede aceptar nombres de archivos que contengan rutas relativas o secuencias maliciosas (por ejemplo `../`). Es necesario prevenir path traversal tanto en la adición de archivos al ZIP como al extraer o procesar recursos externos.

## Objetivo (medible)

- Evitar que cualquier entrada de archivo permita escapar del directorio OEBPS dentro del ZIP.
- Añadir tests que prueben nombres de archivo maliciosos y confirmen rechazo o normalización.

## Alcance

**Incluye**:
- Validación y saneamiento en métodos que añaden archivos al ZIP (`addFile`, `addChapter`, `addCSSFile`, etc.).
- Tests unitarios que simulan intentos de path traversal.
- Documentación en `docs/` con la lógica aplicada.

**No incluye**:
- Cambios en la API pública salvo saneamiento en parámetros existentes.

## Contrato funcional

### Entradas

- Strings: nombres de ficheros (p.ej. `../../etc/passwd`, `..\windows\system32`).

### Salidas

- Rechazo explícito (retorno FALSE o excepción) o normalización a un nombre seguro.

### Reglas de negocio

- Cualquier ruta que intente salir del prefijo `OEBPS/` será rechazada o renombrada.
- Los separadores de ruta serán normalizados a '/'.

### Errores esperados / validaciones

- Nombre vacío → error.
- Nombre con secuencia `..` que escape directorio → rechazo.

## Datos

- Entidades involucradas: operaciones ZIP, API de EPub.
- Cambios Doctrine: No aplica.
- Campos nuevos/modificados: No aplica.

## Permisos

- No aplica.

## UX/UI

- No aplica.

## Plan de implementación

1. Identificar todos los puntos donde se añaden nombres de archivo al ZIP (`addFile`, `addChapter`, `addCSSFile`, `addImage`, etc.).
2. Implementar función helper `sanitizeZipPath(string $name): string|false` que normalice y valide.
3. Reemplazar llamadas directas por la función helper; si devuelve false, rechazar la operación.
4. Añadir tests unitarios en `tests/Unit` para casos maliciosos.

## Criterios de aceptación (checklist verificable)

- [ ] Función `sanitizeZipPath` implementada y usada en todos los puntos relevantes.
- [ ] Tests que cubren `../`, `..\\`, rutas absolutas y nombres vacíos.
- [ ] La librería no añade entradas con rutas fuera de `OEBPS/`.

## Pruebas

- Happy path:
  - Añadir `chapter1.xhtml` y confirmar que se añade.
- Edge case:
  - Intentar añadir `../foo.txt` y comprobar rechazo.

## Checklist de entrega

- [ ] Docs actualizada
- [ ] Tests en verde

## Referencias

- `docs/plan/roadmap.md`
- `docs/tickets/T-AZW3-INVESTIGATE.md`
