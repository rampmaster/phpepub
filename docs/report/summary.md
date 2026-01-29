# Executive Summary

Objective: Transform `phpepub` into a robust library for generating EPUBs compatible with modern standards (EPUB 3.x) and generating derivative formats (AZW3) for Kindle.

Initial Scope (R1):
- Security hardening (ZIP handling, metadata sanitization).
- Implementation of packaging and TOC for EPUB 3.0.
- CI Pipeline validating generation with `epubcheck` and running tests on PHP 8.2–8.5.

Deliverables:
- Documentation in `docs/`.
- Tickets in `docs/tickets/`.
- Format adapter templates in `src/Core/Format/`.
- Test templates in `tests/` and fixtures in `tests/fixtures/`.
