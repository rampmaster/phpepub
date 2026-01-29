# Ticket: release_TICKET-018_EPub-Refactor

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [ ] Ready
- [ ] BLOCKED (explain why)

## Context

The `src/Core/EPub.php` class has grown too large and handles too many responsibilities (metadata, zip handling, HTML processing, structure management). It is a "God Class" that is hard to maintain and test.

## Objective (measurable)

- Extract Zip handling logic to a dedicated class.
- Extract Content/Chapter processing logic to a dedicated class.
- `EPub` class should act as a facade/coordinator.

## Scope

**Includes**:
- Create `Rampmaster\EPub\Core\Storage\ZipContainer` (wraps `ZipArchive`).
- Create `Rampmaster\EPub\Core\Content\ContentProcessor` (handles `processChapterExternalReferences`, `convertToXhtml`).
- Refactor `EPub` to use these classes.

**Does not include**:
- Changing the public API of `EPub` (methods should remain, delegating to new classes if necessary, or marked deprecated).

## Functional Contract

### Inputs
- Existing `EPub` class.

### Outputs
- Refactored classes.

## Implementation Plan

1. Extract `ZipContainer`: move `zip`, `zipPath`, `addFile`, `finalize` (zip part) logic.
2. Extract `ContentProcessor`: move `processChapter...` methods.
3. Update `EPub` to instantiate and use these classes.

## Acceptance Criteria (verifiable checklist)

- [ ] `EPub.php` is significantly smaller.
- [ ] `ZipContainer` handles all zip operations.
- [ ] Tests pass without modification (refactor is internal).

## Tests

- Run full test suite.
