# R1-SEC-02: Sanitizar metadatos

Descripción

Sanitizar y validar todos los metadatos que se incluyen en el OPF y NCX (por ejemplo: `dc:identifier`, `title`, `author`) para evitar inyección de entidades o atributos que rompan el XML/XHTML.

Criterios de aceptación
- `dc:identifier` acepta URIs o ISBNs con validación mínima.
- Títulos y autores son escapados/saneados adecuadamente antes de incluirlos en los XML.
- Tests unitarios que inyectan caracteres especiales y comprueban salida válida UTF-8 y XML bien formado.

Estimación: 1 día
