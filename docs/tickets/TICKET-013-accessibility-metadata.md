# Ticket: release_TICKET-013_Accessibility-Metadata

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

To comply with the "European Accessibility Act" and improve quality, EPUBs must include Schema.org metadata describing their accessibility features.

## Objective (measurable)

- Be able to add accessibility metadata to the `EPub` object.
- That these metadata appear correctly in the OPF.

## Scope

**Includes**:
- Methods in `EPub.php`:
    - `addAccessibilityFeature(string $feature)`
    - `addAccessibilityHazard(string $hazard)`
    - `addAccessMode(string $mode)`
    - `setAccessibilitySummary(string $summary)`
    - `setAccessibilityConformsTo(string $standard)`
- Constants for common values (optional, but recommended).

**Does not include**:
- Automatic content validation (e.g., checking if images have alt). Only metadata.

## Functional Contract

### Inputs
- `$book->setAccessibilitySummary("This book contains structural navigation and alternative text for images.");`

### Outputs
- OPF: `<meta property="schema:accessibilitySummary">This book...</meta>`

## Implementation Plan

1. Modify `EPub.php` to store these values.
2. In `finalize()`, inject properties using `addMetaProperty` (ensuring `schema:` prefix).
3. Unit test verifying the generated XML.

## Acceptance Criteria (verifiable checklist)

- [x] Methods implemented.
- [x] OPF contains `schema:` tags.

## Tests

- Unit test.
