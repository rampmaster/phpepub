# Release 6 Plan: Fixes and Optimizations

## Objective
This release focuses on code quality, addressing feedback from code reviews (PR #2), and reducing technical debt by refactoring the core `EPub` class, which has become a "God Class".

## Scope
- **PR #2 Fixes**: Address comments and issues identified in the pull request (details to be provided).
- **Refactoring**: Split `EPub.php` responsibilities into specialized classes (e.g., Zip handling, Content processing).

## Tickets

### TICKET-017: Fixes from PR #2
- **Objective**: Apply fixes and improvements suggested in GitHub Pull Request #2.
- **Status**: BLOCKED (Waiting for PR details).

### TICKET-018: Refactor EPub Class
- **Objective**: Decompose `EPub.php` to improve maintainability and testability.
- **Tasks**:
    - Extract Zip/Archive handling to a `ZipContainer` or `PackageWriter` class.
    - Extract HTML processing logic (if applicable) to a `ContentProcessor`.
    - Ensure backward compatibility where possible or document breaking changes.

## Success Criteria
- All PR #2 comments addressed.
- `EPub` class size reduced and responsibilities delegated.
- All tests pass (Unit, Integration, Security).
