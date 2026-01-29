# Ticket: release_TICKET-001_Security-Hardening

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

During the preparation of Release 1, risks and dependencies with potential vulnerabilities were detected; furthermore, the EPUB generation flow invokes external tools (java, epubcheck) and processes external inputs (paths, URLs, HTML). Hardening and validations are necessary to minimize the attack surface.

## Objective (measurable)

- Eliminate or mitigate critical vulnerabilities identified by `composer audit` or `roave/security-advisories`.
- Ensure that EPUB generation does not allow path traversal or writing outside of `tests/build`/`build`.
- Add automated tests covering 5 malicious cases (inputs) and ensuring no writing outside of build.

## Scope

**Includes**:
- Update insecure dependencies (compatible patch or minor updates).
- Add validations and sanitization in `FileHelper::sanitizeZipPath`, `FileHelper::getFileContents`, and `EpubAdapter` entry points.
- Limit and control the execution of external processes using `Process` and timeouts.
- Add Unit tests simulating malicious inputs and Integration tests verifying that no files are created outside the build.
- Integrate `composer audit` in CI (action B) and create a rule in the workflow to fail the job in case of critical CVEs.
- Implement automated fuzzing tests (action C) that generate malicious inputs and verify that the system rejects them or handles them without creating files outside the build.

**Does not include**:
- Complete project re-architecture or major refactor (will be managed in subsequent releases).

## Functional Contract

### Inputs
- Payloads: paths and URLs passed to adapters (e.g., `chapters[..]['path']`).
- Environment variables: `BUILD_DIR`, `EPUB_FILE` (for manual validation).
- Optional: `SECURITY_STRICT=true` to activate additional rules in CI (optional).

### Outputs
- No UI; logs and artifacts (.epub files) in `build` or `tests/build`.

### Business Rules
- All external paths must be resolved, canonicalized, and rejected if they point outside the build folder.
- External calls to `epubcheck` or `java` must be executed with timeouts and without trusting unvalidated variables.
- In CI, if `composer audit` detects vulnerabilities with `CRITICAL` or `HIGH` severity, the job will mark FAIL (configurable by environment variable).

### Expected Errors / Validations
- `composer install`/`composer audit` may detect vulnerable packages and require blocking until updated.

## Data

- Involved entities: generated EPUB files, `chapters` entries (path/content).
- Doctrine changes: Not applicable.
- New/modified fields: None (only validations and checks).

## Permissions

- Roles do not apply in this ticket; validations run on the server/CI.

## UX/UI

- Not applicable.

## Implementation Plan

1. Run `composer audit` locally and list vulnerabilities. Prioritize critical CVEs. (action B prep)
2. Add a `composer audit` step in the CI workflow (`.github/workflows/ci.yml`) that fails the job if critical vulnerabilities exist or if `SECURITY_STRICT=true`.
3. Update secure dependencies (patches) and document changes in `CHANGELOG.md`.
4. Implement validations in `FileHelper::sanitizeZipPath`, `FileHelper::getFileContents`, and `EpubAdapter::generate` (already started). Guarantee `isSafeBuildDir` and `isPathInside`.
5. Implement unit and integration tests for path traversal (initial tests already added).
6. Implement a fuzzing suite (action C): create tests that generate automated malicious inputs (e.g., paths with .., file:// URIs, URLs with payloads, HTML with scripts, oversized attributes) and verify that no files are written outside the build nor insecure commands executed.
7. Run the security suite in CI: `composer audit`, phpcs/phpstan (with baseline), Unit/Integration tests, and fuzzing tests under `tests/Security`.
8. Review and close vulnerabilities; if they cannot be patched, document mitigations in `docs/report`.

## Acceptance Criteria (verifiable checklist)

- [x] `composer audit` runs in CI and does not let `CRITICAL`/`HIGH` CVEs pass without review.
- [x] Unit/Integration tests covering path traversal pass in CI.
- [x] Fuzzing suite (`tests/Security/Fuzzing*`) detects and blocks 5 defined attack vectors.
- [x] No files are created outside of `tests/build` during test execution (see logs/artifacts).

## Tests

- Happy path:
  - Generate an EPUB with valid inputs and verify that the .epub file is created in `tests/build`.
- Edge case:
  - Input with `../../etc/passwd` or `file:///etc/passwd` must be rejected and not create a file.
- Fuzzing cases (minimum examples to implement):
  1. Paths with repeated `../` (path traversal).
  2. `file://` URIs pointing to system files.
  3. Remote URLs with redirects to local file paths.
  4. HTML with `onerror`/`onload` attributes and `<script>` tags in chapters (must be allowed as safe content or cleaned according to policy).
  5. Fabrication of very long filenames or with control characters.

## Delivery Checklist

- [x] Docs updated
- [x] Tests green (`Unit`, `Integration`, `Security`)
- [x] `composer audit` clean of critical CVEs or mitigations documented

## References
- `docs/plan/release-1-plan.md`
- `docs/tickets/TICKET-002-epub3-validation.md`
