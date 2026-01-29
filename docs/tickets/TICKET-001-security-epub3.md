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

**No incluye**:
- Re-architecture completo del proyecto ni refactor mayor (se gestionará en releases posteriores).

## Contrato funcional

### Entradas
- Payloads: paths y URLs pasados a los adaptadores (ej. `chapters[..]['path']`).
- Variables de entorno: `BUILD_DIR`, `EPUB_FILE` (para validación manual).

### Salidas
- Ninguna UI; logs y artefactos (archivos .epub) en `build` o `tests/build`.

### Reglas de negocio
- Todos los paths externos deben resolverse y canonicalizarse y rechazarse si apuntan fuera de la carpeta build.
- Las llamadas externas a `epubcheck` o `java` deben ejecutarse con timeouts y sin confiar en variables sin validar.

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

1. Ejecutar `composer audit` y listar vulnerabilidades. Priorizar CVEs críticos.
2. Actualizar dependencias seguras (patches) y documentar cambios en `CHANGELOG.md`.
3. Implementar validaciones en `FileHelper::sanitizeZipPath` y en `EpubAdapter::generate` para validar buildDir.
4. Añadir tests unitarios que prueben path traversal y aseguren que no se crean ficheros fuera del build.
5. Integrar `composer audit` en CI y marcar como bloqueo en PRs con vulnerabilidades críticas.

## Criterios de aceptación (checklist verificable)

- [ ] `composer audit` no reporta vulnerabilidades críticas en la rama de release.
- [ ] Tests Unit/Integration que cubren path traversal pasan en CI.
- [ ] Logs de ejecución de procesos externos contienen tiempo límite aplicado.

## Pruebas

- Happy path:
  - Generar un EPUB con inputs válidos y comprobar que archivo .epub se crea en `tests/build`.
- Edge case:
  - Input con `../../etc/passwd` o `file:///etc/passwd` debe ser rechazado y no crear archivo.

## Checklist de entrega

- [ ] Docs actualizada
- [ ] Tests en verde
- [ ] `composer audit` limpio de CVE críticos

## Referencias
- `docs/plan/release-1-plan.md`
- `docs/tickets/TICKET-002-epub3-validation.md`
