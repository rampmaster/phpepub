# Ticket: release_TICKET-009_Integration-Tests-R3

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

Validate Release 3 (EPUB 3.2 and improved 2.0.1 compatibility).

## Objective (measurable)

- Integration tests for EPUB 3.2.
- Integration test for EPUB 2.0.1 with "dirty" content (HTML5).

## Scope

**Includes**:
- Update `EpubCheckIntegrationTest`.

## Functional Contract

### Inputs
- `phpunit`

### Outputs
- Green tests.

## Implementation Plan

1. Add 'EPUB 3.2' to the data provider.
2. Create new test `testEpub2CompatibilityWithHtml5Content`.

## Acceptance Criteria (verifiable checklist)

- [x] CI passes.

## Tests

- Run `composer test`.
