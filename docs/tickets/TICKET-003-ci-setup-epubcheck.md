# Ticket: release_TICKET-003_CI-Setup-EPUBCheck

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [ ] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

Actualmente las pruebas de integración usan `epubcheck` localmente y hay una intención de ejecutar la matriz de PHP en CI (8.2..8.5). El repo ya incluye tests Unit/Integration y scripts composer (`ci`). Falta un workflow reproducible que instale Java/epubcheck, ejecute `composer ci` y suba artefactos `tests/build`.

Origen: roadmap Release 1 — TICKET-002 y correcciones previas en `EpubAdapter` y comandos de consola.

## Objetivo (medible)

- Proveer un GitHub Actions workflow que ejecute la matriz PHP 8.2, 8.3, 8.4 y 8.5 y valide que:
  - `composer install` funciona y las dependencias dev se instalan.
  - `composer run-script ci` completa con tests Unit e Integration que validen creación y verificación con `epubcheck`.
- Artefactos `tests/build/*.epub` deben subirse como artifacts (cuando los tests fallan o opcionalmente cuando pasan) para inspección.

Métrica de éxito: workflow aprobado y green en al menos 3/4 versiones (8.2..8.5) en un runner Ubuntu reproducible.

## Alcance

**Incluye**:
- Crear `.github/workflows/ci.yml` con matrix PHP 8.2..8.5.
- Instalar Java y `epubcheck` (preferible apt si existe, fallback descargar JAR a `/opt/epubcheck`).
- Ejecutar `composer install --no-interaction --prefer-dist` y `composer run-script ci`.
- Subida de artifacts desde `tests/build`.
- Documentar pasos y variables de entorno necesarias.

**No incluye**:
- Migración completa del proyecto a PSR-12 ni refactor de deuda técnica (ya se gestionó con baseline y reglas temporales).
- Soporte de KFX (descartado) o conversión de AZW3 en CI (queda para release siguiente).

## Contrato funcional

### Entradas
- Repositorio con código (branch de PR) y `composer.json` con scripts.
- Variables opcionales de workflow: `EPUBCHECK_INSTALL_METHOD` (apt|jar), `UPLOAD_ARTIFACTS` (true|false).

### Salidas
- Estado del workflow (success/fail) por versión de PHP.
- Artifacts: `tests/build/*.epub` subidos para inspección.
- Logs de `epubcheck` y PHPUnit guardados en job logs.

### Reglas de negocio
- Si `epubcheck` devuelve errores fatales (epubcheck exit non-zero), el job debe fallar.
- Advertencias de epubcheck pueden ser permitidas (configurable) — por defecto NO hacen fallar.

### Errores esperados / validaciones
- Failing: `composer install` falla por dependencias — mark as infra error.
- Failing: `epubcheck` no encontrado y el método `EPUBCHECK_INSTALL_METHOD` no se aplica — job falla.

## Datos

- Entidades involucradas: artefacto EPUB generado (archivo `.epub`), logs de prueba.
- No hay cambios en base de datos.
- No migrations.

## Permisos

- El workflow será público en el repo; no requiere secretos especiales, salvo si decides almacenar JAR privado (no recomendado).

## UX/UI

- No aplica UI.

## Plan de implementación

1. Crear el workflow `.github/workflows/ci.yml` con matrix PHP: [8.2,8.3,8.4,8.5].
2. En cada job: instalar PHP usando actions/setup-php, instalar Java (`openjdk-17`) y opcionalmente `epubcheck` con `apt-get install epubcheck` o descargar el JAR en `/opt/epubcheck`.
3. Ejecutar `composer install --no-interaction --prefer-dist`.
4. Ejecutar `composer run-script ci` (this runs phpcs, phpstan, phpunit).
5. Si `tests/build` contiene archivos `.epub`, subirlos como artifact (on: always or on: failure).
6. Documentar variables y cómo ejecutar localmente en README.

Fragmento sugerido del workflow (resumen):

```yaml
name: CI
on: [push, pull_request]
jobs:
  build:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: [8.2, 8.3, 8.4, 8.5]
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: dom, zip
      - name: Setup Java
        run: sudo apt-get update && sudo apt-get install -y default-jdk
      - name: Install epubcheck (apt preferred)
        run: |
          if command -v epubcheck >/dev/null 2>&1; then echo 'epubcheck already installed'; else sudo apt-get install -y epubcheck || true; fi
      - name: Composer install
        run: composer install --no-interaction --prefer-dist
      - name: Run CI script
        run: composer run-script ci
      - name: Upload epubs
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: epubs-${{ matrix.php }}
          path: tests/build/*.epub
```

(Nota: si `apt-get install epubcheck` falla en algunos runners, fallback descargar jar desde releases y ejecutar con `java -jar /opt/epubcheck/epubcheck.jar`.)

## Criterios de aceptación (checklist verificable)

- [ ] Workflow `.github/workflows/ci.yml` creado y añadido al repo.
- [ ] Workflow ejecuta y pasa en al menos una ejecución de la matrix (localmente replicable).
- [ ] `composer run-script ci` completa sin errores fatales en el job.
- [ ] Artefactos `tests/build/*.epub` se suben como artifacts.

## Pruebas

- Happy path:
  - Crear PR, workflow corre en ubuntu-latest con PHP 8.4, composer install OK, composer ci OK, artifact subido.
- Edge case:
  - Runner sin apt `epubcheck`: el job debe intentar descargar el JAR y usar `java -jar`.
  - `epubcheck` devuelve non-zero: job falla y artifact subido para inspección.

## Checklist de entrega

- [ ] Docs actualizada (`docs/tickets/TICKET-003-ci-setup-epubcheck.md`)
- [ ] Workflow `.github/workflows/ci.yml` creado y probado en branch
- [ ] Tests en verde en CI (o fallos controlados con artifacts)

## Referencias
- `docs/plan/release-1-plan.md`
- `tests/Integration/EpubCheckIntegrationTest.php`
- `composer.json` scripts: `ci`, `phpcs`, `phpstan`, `test`


---

Si confirmas, creo el archivo de workflow `.github/workflows/ci.yml` en un branch y lo pruebo localmente (simulando ejecuciones relevantes). ¿Procedo a crear el workflow ya y ejecutar una prueba local (en este entorno) para validar?
