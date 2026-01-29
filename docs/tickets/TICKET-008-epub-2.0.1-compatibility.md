# Ticket: release_TICKET-008_EPUB-2.0.1-Compatibility

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [x] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

Aunque EPUB 2.0.1 es legacy, muchos lectores antiguos lo requieren. Actualmente el adaptador intenta generar XHTML válido, pero si se inyecta contenido HTML5 (ej. `<section>`, `<nav>`, atributos `epub:type`), la validación 2.0.1 fallará porque el DTD de XHTML 1.1 no los soporta.

## Objetivo (medible)

- Asegurar que cuando se solicita versión 2.0.1, el contenido HTML se limpia de tags/atributos exclusivos de HTML5/EPUB3.

## Alcance

**Incluye**:
- Modificar `EpubAdapter::convertToXhtml`: si la versión destino es 2.0.1, usar un proceso de limpieza más estricto (ej. convertir `<section>` a `<div>`, eliminar `epub:*` attributes).

**No incluye**:
- Conversión compleja de CSS3 a CSS2.

## Contrato funcional

### Entradas
- `EpubAdapter::generate(['version' => '2.0', 'content' => '<section epub:type="chapter">...</section>'])`

### Salidas
- XHTML generado: `<div>...</div>` (sin atributos epub).

## Plan de implementación

1. En `EpubAdapter`, pasar la versión objetivo a `convertToXhtml`.
2. Si es 2.0, aplicar transformación:
    - Strip `epub:` attributes.
    - Rename HTML5 tags (`article`, `section`, `nav`, `header`, `footer`) a `div` o `span`.
3. Verificar con `epubcheck`.

## Criterios de aceptación (checklist verificable)

- [x] Input con tags HTML5 genera EPUB 2.0.1 válido.
- [x] `epubcheck` no reporta errores de DTD.

## Pruebas

- Test unitario con snippet HTML5 -> XHTML 1.1.
