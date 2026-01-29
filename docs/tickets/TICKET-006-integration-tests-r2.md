# Ticket: release_TICKET-006_Integration-Tests-R2

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

With the introduction of support for EPUB 3.0.1 and 3.1, it is necessary to update the integration test suite to ensure that these versions are generated and validated correctly in the CI pipeline.

## Objective (measurable)

- Have executable integration tests covering EPUB 3.0.1 and 3.1.
- Integrate these tests into the `composer ci` command.

## Scope

**Includes**:
- Modify `tests/Integration/EpubCheckIntegrationTest.php` to iterate over the new versions.
- Ensure that `EpubAdapter` accepts and correctly processes version parameters.

**Does not include**:
- New functional features, only test coverage.

## Functional Contract

### Inputs
- `phpunit` execution.

### Outputs
- Green test report for all versions.

## Implementation Plan

1. Update `EpubCheckIntegrationTest.php`: add data provider or test methods for 3.0.1 and 3.1.
2. Run tests locally and verify `epubcheck` logs.

## Acceptance Criteria (verifiable checklist)

- [x] `phpunit` executes validation for 3.0.1 and 3.1.
- [x] CI in GitHub Actions passes correctly.

## Tests

- Run `composer test` and verify output.
