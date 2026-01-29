# Ticket: release_TICKET-005_EPUB-3.1-Support

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [x] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

EPUB 3.1 introduce cambios más significativos respecto a 3.0/3.0.1. Requiere cambios en metadatos (ej. `dcterms:modified`) y estructura. El objetivo es permitir generar archivos conformes a EPUB 3.1.

## Objetivo (medible)

- Permitir configurar la generación para versión "3.1".
- Generar OPF con `version="3.1"`.
- Cumplir requisitos específicos de 3.1 verificados por `epubcheck`.

## Alcance

**Incluye**:
- Añadir constante `BOOK_VERSION_EPUB31` en `src/Core/EPub.php`.
- Ajustar generación de metadatos en `Opf.php` si la versión es >= 3.1 (ej. asegurar `dcterms:modified`).
- Revisar si NCX se debe omitir o mantener (en 3.1 es opcional, pero Nav Document es obligatorio).

**No incluye**:
- Soporte completo de todas las nuevas features de 3.1, solo lo necesario para validación estructural básica.

## Contrato funcional

### Entradas
- `new EPub(EPub::BOOK_VERSION_EPUB31, ...)`

### Salidas
- Archivo `.epub` válido 3.1.

### Reglas de negocio
- Si versión es 3.1, asegurar presencia de `dcterms:modified` en metadatos.

## Plan de implementación

1. Añadir constante `BOOK_VERSION_EPUB31 = '3.1'`.
2. En `Opf.php`, al generar metadatos, si versión es 3.1, asegurar formato correcto de fechas y atributos requeridos.
3. Validar comportamiento con `epubcheck`.

## Criterios de aceptación (checklist verificable)

- [x] Se puede instanciar `EPub` con versión 3.1.
- [x] El OPF tiene `version="3.1"`.
- [x] `epubcheck` pasa sin errores fatales.

## Pruebas

- Test de integración generando EPUB 3.1.
