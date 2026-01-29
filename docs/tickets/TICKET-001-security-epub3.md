# Ticket: release_TICKET-001_Security-Hardening

> Regla: este ticket debe ser ejecutable **sin interpretación**. Si un campo no se puede responder, el ticket debe marcarse como **BLOQUEADO** y explicitar qué falta.

## Estado

- [ ] Ready
- [ ] BLOQUEADO (explicar por qué)

## Contexto

Durante la preparación del Release 1 se detectaron riesgos y dependencias con potenciales vulnerabilidades; además el flujo de generación del EPUB invoca herramientas externas (java, epubcheck) y procesa entradas externas (paths, URLs, HTML). Es necesario aplicar hardening y validaciones para minimizar la superficie de ataque.

## Objetivo (medible)

- Eliminar o mitigar vulnerabilidades críticas identificadas por `composer audit` o `roave/security-advisories`.
- Asegurar que la generación de EPUB no permita path traversal ni escritura fuera de `tests/build`/`build`.
- Añadir pruebas automáticas que cubran 5 casos maliciosos (inputs) y que no permitan escritura fuera de build.

## Alcance

**Incluye**:
- Actualizar dependencias inseguras (patch o minor updates compatibles).
- Añadir validaciones y sanitización en `FileHelper::sanitizeZipPath`, `FileHelper::getFileContents` y puntos de entrada de `EpubAdapter`.
- Limitar y controlar la ejecución de procesos externos mediante `Process` y timeouts.
- Añadir tests Unit que simulen inputs maliciosos y tests de integración que verifiquen que no se crean ficheros fuera del build.
- Integrar `composer audit` en CI (acción B) y crear una regla en el workflow para fallar el job en caso de CVE críticos.
- Implementar tests de fuzzing automatizados (acción C) que generen entradas maliciosas y verifiquen que el sistema las rechaza o las maneja sin crear archivos fuera del build.

**No incluye**:
- Re-architecture completo del proyecto ni refactor mayor (se gestionará en releases posteriores).

## Contrato funcional

### Entradas
- Payloads: paths y URLs pasados a los adaptadores (ej. `chapters[..]['path']`).
- Variables de entorno: `BUILD_DIR`, `EPUB_FILE` (para validación manual).
- Opcional: `SECURITY_STRICT=true` para activar reglas adicionales en CI (opcional).

### Salidas
- Ninguna UI; logs y artefactos (archivos .epub) en `build` o `tests/build`.

### Reglas de negocio
- Todos los paths externos deben resolverse y canonicalizarse y rechazarse si apuntan fuera de la carpeta build.
- Las llamadas externas a `epubcheck` o `java` deben ejecutarse con timeouts y sin confiar en variables sin validar.
- En CI, si `composer audit` detecta vulnerabilidades con severidad `CRITICAL` o `HIGH`, el job marcará FAIL (configurable por variable de entorno).

### Errores esperados / validaciones
- `composer install`/`composer audit` puede detectar paquetes vulnerables y requerir bloqueo hasta actualizar.

## Datos

- Entidades involucradas: ficheros EPUB generados, entradas `chapters` (path/content).
- Cambios Doctrine: No aplica.
- Campos nuevos/modificados: Ninguno (sólo validaciones y checks).

## Permisos

- No aplica roles en este ticket; las validaciones corren en el servidor/CI.

## UX/UI

- No aplica.

## Plan de implementación

1. Ejecutar `composer audit` local y listar vulnerabilidades. Priorizar CVEs críticos. (acción B prep)
2. Añadir un paso de `composer audit` en el workflow CI (`.github/workflows/ci.yml`) que falle la job si existen vulnerabilidades críticas o si `SECURITY_STRICT=true`.
3. Actualizar dependencias seguras (patches) y documentar cambios en `CHANGELOG.md`.
4. Implementar validaciones en `FileHelper::sanitizeZipPath`, `FileHelper::getFileContents` y en `EpubAdapter::generate` (ya empezado). Garantizar `isSafeBuildDir` y `isPathInside`.
5. Implementar tests unitarios y de integración para path traversal (ya se añadieron tests iniciales).
6. Implementar una suite de fuzzing (acción C): crear tests que generen entradas maliciosas automatizadas (p. ej. rutas con .., URIs file://, URLs con payloads, HTML con scripts, oversized attributes) y verificar que no se escriben ficheros fuera del build ni se ejecutan comandos inseguros.
7. Ejecutar la suite de seguridad en CI: `composer audit`, phpcs/phpstan (con baseline), tests Unit/Integration y fuzzing tests bajo `tests/Security`.
8. Revisar y cerrar vulnerabilidades; si no pueden parchearse, documentar mitigaciones en `docs/report`.

## Criterios de aceptación (checklist verificable)

- [ ] `composer audit` se ejecuta en CI y no deja pasar CVE `CRITICAL`/`HIGH` sin revisión.
- [ ] Tests Unit/Integration que cubren path traversal pasan en CI.
- [ ] Suite de fuzzing (`tests/Security/Fuzzing*`) detecta y bloquea 5 vectores de ataque definidos.
- [ ] No se crean archivos fuera de `tests/build` durante la ejecución de tests (ver logs/artifacts).

## Pruebas

- Happy path:
  - Generar un EPUB con inputs válidos y comprobar que archivo .epub se crea en `tests/build`.
- Edge case:
  - Input con `../../etc/passwd` o `file:///etc/passwd` debe ser rechazado y no crear archivo.
- Fuzzing cases (ejemplos mínimos a implementar):
  1. Rutas con `../` repetidos (path traversal).
  2. `file://` URIs hacia archivos del sistema.
  3. URLs remotas con redirecciones a local file paths.
  4. HTML con atributos `onerror`/`onload` y tags `<script>` en capítulos (deben ser permitidos como contenido seguro o limpiados según política).
  5. Fabricación de nombres de archivos muy largos o con caracteres de control.

## Checklist de entrega

- [ ] Docs actualizada
- [ ] Tests en verde (`Unit`, `Integration`, `Security`)
- [ ] `composer audit` limpio de CVE críticos o mitigaciones documentadas

## Referencias
- `docs/plan/release-1-plan.md`
- `docs/tickets/TICKET-002-epub3-validation.md`

## Evidencia (acciones A y B)

Resumen de la ejecución realizada localmente:

- Comandos ejecutados:
  - `composer dump-autoload -o`
  - `vendor/bin/phpunit --testsuite Unit --debug`
  - `vendor/bin/phpunit --testsuite Integration --debug`
  - `composer audit` (audit nativo / wrapper)

Resultados resumidos:

- Unit tests:
  - Total: 6 tests (suite `Unit`).
  - Resultado: PASS — todos los tests Unit pasaron correctamente.

- Integration tests:
  - Total: 3 tests (suite `Integration`).
  - Resultado: 2 PASS, 1 FAIL.
  - Test que falló: `testEpub301GenerationAndEpubcheckValidation` (EPUB 3.0.1)
    - Mensaje de epubcheck: "ERROR(OPF-001): ... Ha ocurrido un error analizando la versión del EPUB: Version not supported." — indica que la versión declarada en el OPF no es aceptada por la versión de `epubcheck` usada en este entorno.
    - Nota: Los tests para EPUB 3.0 y EPUB 2.0 pasaron correctamente.

- Composer audit:
  - Salida: "No security vulnerability advisories found." (no se detectaron advisory en el entorno local).
  - Observación: Composer advirtió que un script llamado `audit` podría sobreescribir un comando nativo y mostró un aviso, pero la comprobación de seguridad nativa devolvió que no hay advisories.

Archivos/artefactos
- Los epubs generados por los tests de integración fueron temporales para las pruebas; en el run local algunos artefactos se generan en `build/` (por ejemplo `Integration_Test_3_0_*.epub`). En ejecuciones exitosas, los epubs se validan con `epubcheck`.

Conclusión y próximos pasos recomendados

1. Corregir compatibilidad con EPUB 3.0.1 (falla actual de Integration): ajustar la generación del OPF para que la `version` sea compatible con la versión de `epubcheck` instalada en CI (posibles opciones: normalizar `3.0.1` a `3.0` en el OPF, o detectar la versión de epubcheck y adaptar la salida). Esta corrección permitirá que `testEpub301GenerationAndEpubcheckValidation` pase.

2. Integrar `composer audit` en el workflow CI (acción B): añadir el paso `composer audit` (o nuestro wrapper `tools/run-composer-audit.php`) en `.github/workflows/ci.yml` y configurar la variable `AUDIT_THRESHOLD`/`SECURITY_STRICT` para controlar si HIGH/CRITICAL deben bloquear el job.

3. Completar la suite de fuzzing (acción C) y ejecutarla en CI (ya añadida en `tests/Security/` pero conviene revisar los assert y la política sobre HTML con scripts).

Estado de checklist del ticket actualizado temporalmente:
- [x] Ejecutadas pruebas Unit localmente
- [ ] Tests Integration en verde (hay 1 fallo por EPUB 3.0.1)
- [x] Composer audit ejecutado localmente (sin advisories en este run)

Si quieres, procedo ahora a:

- (1) implementar la corrección de OPF (normalizar `3.0.1` a `3.0`) y volver a ejecutar Integration tests para confirmar (recomendado), o
- (2) primero añadir `composer audit` al workflow CI y ejecutar pipeline, o
- (3) revisar en más detalle el fallo con EPUB 3.0.1 (intentar regenerar el epub y extraer su OPF para inspección). 

Indica cuál quieres que haga ahora (recomiendo 1: arreglar OPF para 3.0.1 y re-ejecutar integration tests).
