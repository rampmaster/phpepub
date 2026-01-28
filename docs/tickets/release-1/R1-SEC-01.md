# R1-SEC-01: Harden ZIP handling (Prevenir path traversal)

Descripción

Validar y endurecer todo el manejo de archivos ZIP para evitar path traversal y la inclusión de rutas no permitidas cuando se empaqueta o se extrae contenido.

Criterios de aceptación
- La librería rechaza o sanea nombres de archivo que intenten escapar del directorio OEBPS (por ejemplo, `../../etc/passwd`).
- Existen tests unitarios que prueban entradas maliciosas y confirman la protección.
- Código revisado y documentado en `docs/`.

QA
- Ejecutar pruebas unitarias que intenten agregar archivos con `../` en su nombre y confirmar que son rechazados o renombrados.

Estimación: 1–2 días
