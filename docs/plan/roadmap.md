# phpepub Project Roadmap

This document summarizes the approved roadmap for adding EPUB format support and validation, AZW3 generation, release structure, and CI strategy.

## Overview
- Sequential priority: Deliver Release 1 (R1) focused on security and robust EPUB 3.0 support, then expand support to EPUB 3.x and EPUB 2.0.1 versions, and finally add AZW3 export.
- KFX discarded for now.
- Minimum language support: Spanish (es), English (en), Portuguese (pt), and French (fr).
- CI Pipeline: Run tests on PHP matrix 8.2–8.5; validate generated EPUBs with `epubcheck`; clean up temporary artifacts upon completion.

## Releases
- Release 1 (R1): Security + EPUB 3.0 (HTML5 nav, compatible OPF, epubcheck in CI)
- Release 2 (R2): EPUB 3.0.1 and 3.1
- Release 3 (R3): EPUB 3.2 and EPUB 2.0.1 compatibility
- Release 4 (R4): AZW3 export (using external tool or invokable binary)
- Release 5 (R5): Accessibility, Documentation, and Examples

## Deliverables per release
- R1: Documentation, tickets, tests, minimal implementation of EpubAdapter for EPUB3, CI pipeline with `epubcheck`.
- R2–R3: Adjustments in `Opf`/`Ncx` and adapters for minor versions; expand test matrix.
- R4: `Azw3Adapter` and conversion tests; research ticket for generation without Calibre.
- R5: Accessibility metadata, Media Overlays, English documentation, and updated examples.

## Notes
- CI will delete generated files in each job (cleanup at the end of the job). For debugging, artifacts can be uploaded with short expiration.
