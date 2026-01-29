# Ticket: release_TICKET-007_EPUB-3.2-Support

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [x] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

EPUB 3.2 es la especificación actual recomendada. Es altamente compatible con 3.0.1 pero relaja algunas restricciones de 3.1. El objetivo es permitir generar archivos conformes a EPUB 3.2.

## Objetivo (medible)

- Permitir configurar la generación para versión "3.2".
- Generar OPF con `version="3.2"`.
- Pasar validación `epubcheck`.

## Alcance

**Incluye**:
- Añadir constante `BOOK_VERSION_EPUB32` en `src/Core/EPub.php`.
- Ajustar lógica en `Opf.php` y `EPub.php` para tratar 3.2 (similar a 3.0.1).

**No incluye**:
- Soporte de nuevas features avanzadas de CSS/fuentes, solo estructura básica.

## Contrato funcional

### Entradas
- `new EPub(EPub::BOOK_VERSION_EPUB32, ...)`

### Salidas
- Archivo `.epub` válido 3.2.

## Plan de implementación

1. Añadir constante `BOOK_VERSION_EPUB32 = '3.2'`.
2. Actualizar condiciones en `Opf.php` para incluir 3.2 en la lógica de prefijos y metadatos.
3. Validar.

## Criterios de aceptación (checklist verificable)

- [x] Instancia con versión 3.2.
- [x] OPF `version="3.2"`.
- [x] `epubcheck` OK.

## Pruebas

- Test de integración.
