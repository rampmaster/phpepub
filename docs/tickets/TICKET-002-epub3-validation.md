# Ticket: release_TICKET-002_EPUB3-Validation

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

The project must generate valid EPUBs for EPUB 3.0 and EPUB 2.0.1 and automatically validate artifacts with `epubcheck`. During integration, cases were detected where HTML content (HTML5 DOCTYPE, missing XHTML namespace) caused validation errors.

## Objective (measurable)

- Guarantee that chapter generation produces well-formed XHTML documents that pass `epubcheck`.
- Add integration tests that generate and validate artifacts for EPUB 3.0 and EPUB 2.0.1.

## Scope

**Includes**:
- Review and adjust `EpubAdapter::convertToXhtml()` to convert HTML5 or fragments to valid XHTML.
- Ensure each chapter has `<title>` in `<head>` and `xml:lang`/`lang`.
- Add/adjust integration tests `tests/Integration/EpubCheckIntegrationTest.php`.
- Configure CI to run epubcheck and fail on fatal errors.

**Does not include**:
- Reconstruction of all OPF/NCX logic; patches and tests will be made.

## Functional Contract

### Inputs
- Generation configuration: `title`, `language`, `chapters[]` (path or content).

### Outputs
- .epub file generated in `build` or `tests/build`.
- `epubcheck` output (logs) as part of the job.

### Business Rules
- If `epubcheck` returns a fatal error, the CI pipeline must fail for that job.

### Expected Errors / Validations
- Cases where HTML is not well-formed and requires automatic correction by the adapter.

## Data

- Entities: EPUB artifacts, chapters (XHTML content).
- No migrations.

## Permissions

- Not applicable.

## UX/UI

- Not applicable.

## Implementation Plan

1. Confirm current tests and fixtures reproducing the failure (tests/Unit fixtures).
2. Adjust `convertToXhtml()` (already implemented with DOMDocument) to ensure title and lang.
3. Extend `EpubAdapter::validate()` to locate OPF via `META-INF/container.xml`.
4. Run integration tests in CI and validate behavior.

## Acceptance Criteria (verifiable checklist)

- [x] Integration tests pass in PHP matrix (8.2..8.5) without fatal epubcheck errors.
- [x] All generated chapters contain `<title>` and `lang` attributes.

## Tests

- Happy path:
  - Generate EPUB from HTML fixture and check that `epubcheck` reports no errors.
- Edge case:
  - HTML fragment with HTML5 doctype must be converted to valid XHTML.

## Delivery Checklist

- [x] Docs updated
- [x] Tests green
- [x] CI workflow configured to run epubcheck

## References
- `src/Core/Format/EpubAdapter.php`
- `tests/Integration/EpubCheckIntegrationTest.php`
