# Ticket: release_TICKET-006_Integration-Tests-R2

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [x] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

Con la introducción de soporte para EPUB 3.0.1 y 3.1, es necesario actualizar la suite de pruebas de integración para asegurar que estas versiones se generan y validan correctamente en el pipeline de CI.

## Objetivo (medible)

- Tener tests de integración ejecutables que cubran EPUB 3.0.1 y 3.1.
- Integrar estos tests en el comando `composer ci`.

## Alcance

**Incluye**:
- Modificar `tests/Integration/EpubCheckIntegrationTest.php` para iterar sobre las nuevas versiones.
- Asegurar que `EpubAdapter` acepta y procesa correctamente los parámetros de versión.

**No incluye**:
- Nuevos features funcionales, solo cobertura de tests.

## Contrato funcional

### Entradas
- Ejecución de `phpunit`.

### Salidas
- Reporte de tests en verde para todas las versiones.

## Plan de implementación

1. Actualizar `EpubCheckIntegrationTest.php`: añadir data provider o métodos de test para 3.0.1 y 3.1.
2. Ejecutar tests localmente y verificar logs de `epubcheck`.

## Criterios de aceptación (checklist verificable)

- [x] `phpunit` ejecuta validación para 3.0.1 y 3.1.
- [x] CI en GitHub Actions pasa correctamente.

## Pruebas

- Ejecutar `composer test` y verificar output.
