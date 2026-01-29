# Ticket: release_TICKET-004_EPUB-3.0.1-Support

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [x] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

El proyecto actualmente soporta EPUB 2.0.1 y EPUB 3.0. El roadmap indica que el Release 2 debe añadir soporte para EPUB 3.0.1. Esta versión es una actualización menor de 3.0 pero requiere que el atributo `version` en el elemento `package` del OPF sea "3.0.1" (o mantenerse en "3.0" si no se usan features exclusivas, pero explícitamente queremos soportar la declaración).

## Objetivo (medible)

- Permitir configurar la generación del libro para la versión "3.0.1".
- El archivo OPF generado debe tener `<package version="3.0.1" ...>`.
- El EPUB generado debe pasar `epubcheck` sin errores.

## Alcance

**Incluye**:
- Añadir constante `BOOK_VERSION_EPUB301` en `src/Core/EPub.php`.
- Permitir pasar esta versión al constructor de `EPub` o `EpubAdapter`.
- Asegurar que `Opf` renderiza el atributo de versión correctamente.

**No incluye**:
- Cambios estructurales mayores (3.0.1 es muy similar a 3.0).

## Contrato funcional

### Entradas
- `new EPub(EPub::BOOK_VERSION_EPUB301, ...)`
- O `EpubAdapter::generate(['version' => '3.0.1', ...])`

### Salidas
- Archivo `.epub` válido según estándar 3.0.1.

### Reglas de negocio
- Validación `epubcheck` debe ser exitosa.

## Datos

- Constante nueva.

## Plan de implementación

1. Editar `src/Core/EPub.php`: añadir constante `BOOK_VERSION_EPUB301 = '3.0.1'`.
2. Verificar lógica en `src/Core/Structure/Opf.php` (o donde se genere el tag `<package>`) para usar la versión inyectada.
3. Crear test unitario/integración que genere un EPUB 3.0.1 y valide el atributo en el OPF.

## Criterios de aceptación (checklist verificable)

- [x] Se puede instanciar `EPub` con versión 3.0.1.
- [x] El OPF generado contiene `version="3.0.1"`.
- [x] `epubcheck` valida el archivo generado.

## Pruebas

- Generar un EPUB simple con versión 3.0.1 y verificar contenido de `OEBPS/content.opf` (o similar).
