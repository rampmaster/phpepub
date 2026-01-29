# Ticket: release_TICKET-005_EPUB-3.1-Support

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

EPUB 3.1 introduces more significant changes compared to 3.0/3.0.1. It requires changes in metadata (e.g., `dcterms:modified`) and structure. The objective is to allow generating files compliant with EPUB 3.1.

## Objective (measurable)

- Allow configuring generation for version "3.1".
- Generate OPF with `version="3.1"`.
- Comply with specific 3.1 requirements verified by `epubcheck`.

## Scope

**Includes**:
- Add constant `BOOK_VERSION_EPUB31` in `src/Core/EPub.php`.
- Adjust metadata generation in `Opf.php` if version is >= 3.1 (e.g., ensure `dcterms:modified`).
- Review if NCX should be omitted or kept (in 3.1 it is optional, but Nav Document is mandatory).

**Does not include**:
- Full support of all new 3.1 features, only what is necessary for basic structural validation.

## Functional Contract

### Inputs
- `new EPub(EPub::BOOK_VERSION_EPUB31, ...)`

### Outputs
- Valid 3.1 `.epub` file.

### Business Rules
- If version is 3.1, ensure presence of `dcterms:modified` in metadata.

## Implementation Plan

1. Add constant `BOOK_VERSION_EPUB31 = '3.1'`.
2. In `Opf.php`, when generating metadata, if version is 3.1, ensure correct date format and required attributes.
3. Validate behavior with `epubcheck`.

## Acceptance Criteria (verifiable checklist)

- [x] `EPub` can be instantiated with version 3.1.
- [x] The OPF has `version="3.1"`.
- [x] `epubcheck` passes without fatal errors.

## Tests

- Integration test generating EPUB 3.1.
