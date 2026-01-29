# Ticket: release_TICKET-004_EPUB-3.0.1-Support

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

The project currently supports EPUB 2.0.1 and EPUB 3.0. The roadmap indicates that Release 2 must add support for EPUB 3.0.1. This version is a minor update to 3.0 but requires the `version` attribute in the OPF `package` element to be "3.0.1" (or remain "3.0" if exclusive features are not used, but we explicitly want to support the declaration).

## Objective (measurable)

- Allow configuring book generation for version "3.0.1".
- The generated OPF file must have `<package version="3.0.1" ...>`.
- The generated EPUB must pass `epubcheck` without errors.

## Scope

**Includes**:
- Add constant `BOOK_VERSION_EPUB301` in `src/Core/EPub.php`.
- Allow passing this version to the `EPub` or `EpubAdapter` constructor.
- Ensure `Opf` renders the version attribute correctly.

**Does not include**:
- Major structural changes (3.0.1 is very similar to 3.0).

## Functional Contract

### Inputs
- `new EPub(EPub::BOOK_VERSION_EPUB301, ...)`
- Or `EpubAdapter::generate(['version' => '3.0.1', ...])`

### Outputs
- Valid `.epub` file according to 3.0.1 standard.

### Business Rules
- `epubcheck` validation must be successful.

## Data

- New constant.

## Implementation Plan

1. Edit `src/Core/EPub.php`: add constant `BOOK_VERSION_EPUB301 = '3.0.1'`.
2. Verify logic in `src/Core/Structure/Opf.php` (or where the `<package>` tag is generated) to use the injected version.
3. Create unit/integration test that generates an EPUB 3.0.1 and validates the attribute in the OPF.

## Acceptance Criteria (verifiable checklist)

- [x] `EPub` can be instantiated with version 3.0.1.
- [x] The generated OPF contains `version="3.0.1"`.
- [x] `epubcheck` validates the generated file.

## Tests

- Generate a simple EPUB with version 3.0.1 and verify content of `OEBPS/content.opf` (or similar).
