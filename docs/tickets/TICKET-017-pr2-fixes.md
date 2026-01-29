# Ticket: release_TICKET-017_PR2-Fixes

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (Need PR #2 comments content)

## Context

Feedback has been received on Pull Request #2 (https://github.com/rampmaster/phpepub/pull/2). This feedback needs to be addressed to merge the changes.

## Objective (measurable)

- Resolve all conversation threads in PR #2.
- Apply code fixes.

## Scope

**Includes**:
- **`src/Core/EPub.php`**: Simplify EPUB 3 version check using `str_starts_with($this->bookVersion, '3.')`.
- **`docs/tickets/TICKET-001-security-epub3.md`**: Remove conversational artifacts at the end of the file.
- **`tools/run-composer-audit.php`**: Remove `2>/dev/null` redirection to expose errors in CI.
- **`src/Core/Format/EpubAdapter.php`**: Improve `epub:` attribute removal using `$attr->prefix` and `removeAttributeNode`.

**Does not include**:
- New features not related to the PR.

## Functional Contract

### Inputs
- PR comments provided.

### Outputs
- Code changes in specified files.

## Implementation Plan

1.  **`src/Core/EPub.php`**: Replace long condition with `!str_starts_with($this->bookVersion, '3.')`.
2.  **`docs/tickets/TICKET-001-security-epub3.md`**: Delete lines 149-155.
3.  **`tools/run-composer-audit.php`**: Change command to `$cmd = 'composer audit --format=json';`.
4.  **`src/Core/Format/EpubAdapter.php`**: Refactor `cleanHtml5ForEpub2` to use `prefix` check and `removeAttributeNode`.

## Acceptance Criteria (verifiable checklist)

- [x] `EPub.php` uses `str_starts_with` for version check.
- [x] `TICKET-001` doc is clean.
- [x] `run-composer-audit.php` shows stderr.
- [x] `EpubAdapter.php` uses robust attribute removal.

## Tests

- Existing tests must pass.
