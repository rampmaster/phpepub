# Ticket: release_TICKET-007_EPUB-3.2-Support

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

EPUB 3.2 is the current recommended specification. It is highly compatible with 3.0.1 but relaxes some restrictions of 3.1. The objective is to allow generating files compliant with EPUB 3.2.

## Objective (measurable)

- Allow configuring generation for version "3.2".
- Generate OPF with `version="3.2"`.
- Pass `epubcheck` validation.

## Scope

**Includes**:
- Add constant `BOOK_VERSION_EPUB32` in `src/Core/EPub.php`.
- Adjust logic in `Opf.php` and `EPub.php` to handle 3.2 (similar to 3.0.1).

**Does not include**:
- Support for new advanced CSS/font features, only basic structure.

## Functional Contract

### Inputs
- `new EPub(EPub::BOOK_VERSION_EPUB32, ...)`

### Outputs
- Valid 3.2 `.epub` file.

## Implementation Plan

1. Add constant `BOOK_VERSION_EPUB32 = '3.2'`.
2. Update conditions in `Opf.php` to include 3.2 in prefix and metadata logic.
3. Validate.

## Acceptance Criteria (verifiable checklist)

- [x] Instance with version 3.2.
- [x] OPF `version="3.2"`.
- [x] `epubcheck` OK.

## Tests

- Integration test.
