# Ticket: release_TICKET-011_Azw3-Integration-Tests

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

Validate the functionality of `Azw3Adapter`.

## Objective (measurable)

- Integration test that attempts to generate an AZW3.

## Scope

**Includes**:
- `tests/Integration/Azw3IntegrationTest.php`.

## Functional Contract

### Inputs
- `phpunit`.

### Outputs
- Pass or Skip (if no calibre).

## Implementation Plan

1. Create test.
2. In `setUp`, verify `exec('which ebook-convert')`. If fails, `markTestSkipped`.
3. Execute generation and `assertFileExists` of the `.azw3`.

## Acceptance Criteria (verifiable checklist)

- [x] Test created.
- [x] Skips correctly in environments without Calibre (CI by default).

## Tests

- Run locally (if you have calibre) and verify it passes.
