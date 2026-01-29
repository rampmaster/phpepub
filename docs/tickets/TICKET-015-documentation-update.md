# Ticket: release_TICKET-015_Documentation-Update

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

The project needs to be internationalized and acknowledge its origin.

## Objective (measurable)

- Documentation in English.
- Updated README.

## Scope

**Includes**:
- Translate `README.md` to English.
- Add "Installation" section: `composer require rampmaster/phpepub`.
- Add "Credits" section: "Based on the original work by A. Grandt (grandt/phpepub)."
- Add badges (GitHub Actions).

**Does not include**:
- Translate old tickets.

## Functional Contract

### Inputs
- Current text.

### Outputs
- Updated `.md` files.

## Implementation Plan

1. Rewrite `README.md`.
2. Move Spanish documentation to `docs/es/` if desired to keep, or replace.

## Acceptance Criteria (verifiable checklist)

- [x] README in English.
- [x] Credits visible.
- [x] Clear installation instructions.

## Tests

- Visual review.
