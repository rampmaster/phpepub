# Ticket: release_TICKET-016_Examples-Update

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

Current examples may be outdated or not reflect best practices (use of `EpubAdapter`).

## Objective (measurable)

- Provide clear and executable examples.

## Scope

**Includes**:
- `docs/examples/01-basic.php`: Simple EPUB 3.2 generation.
- `docs/examples/02-advanced.php`: Metadata, cover image, CSS.
- `docs/examples/03-azw3.php`: Conversion to Kindle.

## Functional Contract

### Inputs
- PHP code.

### Outputs
- `.php` files in `docs/examples`.

## Implementation Plan

1. Write scripts using the current API.
2. Verify they execute without errors.

## Acceptance Criteria (verifiable checklist)

- [x] Scripts exist and work.

## Tests

- Manual execution: `php docs/examples/01-basic.php`.
