# Ticket: release_TICKET-009_Integration-Tests-R3

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [x] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

Validar Release 3 (EPUB 3.2 y compatibilidad 2.0.1 mejorada).

## Objetivo (medible)

- Tests de integración para EPUB 3.2.
- Test de integración para EPUB 2.0.1 con contenido "sucio" (HTML5).

## Alcance

**Incluye**:
- Actualizar `EpubCheckIntegrationTest`.

## Contrato funcional

### Entradas
- `phpunit`

### Salidas
- Green tests.

## Plan de implementación

1. Añadir 'EPUB 3.2' al data provider.
2. Crear nuevo test `testEpub2CompatibilityWithHtml5Content`.

## Criterios de aceptación (checklist verificable)

- [x] CI pasa.

## Pruebas

- Ejecutar `composer test`.
