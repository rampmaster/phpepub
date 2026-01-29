# Release 1 Plan - Initial Support and Security

## Main Objectives
- Ensure EPUB generation works correctly and complies with EPUB 3.0 (priority) and EPUB 2.0.1.
- Fix critical security issues and dependencies.
- Establish CI that runs tests (Unit + Integration) using epubcheck on runner (Ubuntu) with Java or `epubcheck` package.

## Scope
- Implementation and tests for: EPUB 3.0, EPUB 2.0.1, AZW3 (basic reading/generation support), remove KFX (discarded).
- Initial languages: es, en, pt, fr.

## Deliverables
- Automated tests (Unit + Integration) that generate an EPUB from HTML and validate with epubcheck.
- CI pipeline in GitHub Actions for PHP 8.2..8.5 that installs epubcheck (apt or jar) and runs `composer ci`.
- Documentation: docs/plan, docs/report, docs/tickets.

## Priorities
1. Security and EPUB 3.0
2. Internationalization (indicated languages)
3. Additional format support (AZW3)

## Estimated Dates
- Phase 1 (2 weeks): critical fixes, basic CI, and EPUB 3.0 validation
- Phase 2 (2 weeks): multi-language testing, stability improvements
- Phase 3 (4 weeks): AZW3 support and additional packaging

## Risks
- Dependency on external tools (epubcheck, java, calibre) — CI must have a reproducible image with epubcheck.
- Legacy code with technical debt (many phpstan warnings and relaxed phpcs rules).

## Notes
- A `phpstan-baseline.neon` has been generated to silence pre-existing issues; technical debt should be addressed in subsequent releases.
