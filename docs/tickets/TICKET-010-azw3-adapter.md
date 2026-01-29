# Ticket: release_TICKET-010_Azw3-Adapter

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

To support modern Kindle devices, we need to export to AZW3. The most reliable way currently is to convert a valid EPUB.

## Objective (measurable)

- Implement `Rampmaster\EPub\Core\Format\Azw3Adapter`.
- Must implement `FormatAdapterInterface`.
- Must be able to convert a generated EPUB to AZW3 using `ebook-convert` (Calibre).

## Scope

**Includes**:
- `Azw3Adapter` class.
- Logic to detect `ebook-convert` binary.
- Execution of conversion process.

**Does not include**:
- Calibre installation (it is the environment's responsibility).

## Functional Contract

### Inputs
- `generate($input)` with the same parameters as `EpubAdapter`.

### Outputs
- Path to the generated `.azw3` file.

### Business Rules
- If `ebook-convert` is not in the PATH, throw `RuntimeException`.
- The intermediate EPUB file must be deleted (or saved in tmp) after successful conversion, unless configured otherwise.

## Implementation Plan

1. Create `src/Core/Format/Azw3Adapter.php`.
2. In `generate`:
    - Instantiate `EpubAdapter` and generate EPUB in temporary folder.
    - Build command: `ebook-convert source.epub target.azw3`.
    - Execute with `Symfony\Component\Process\Process`.
    - Verify success and return path.

## Acceptance Criteria (verifiable checklist)

- [x] `Azw3Adapter` exists and implements interface.
- [x] Throws exception if no external tool.
- [x] Generates `.azw3` file if tool exists.

## Tests

- Unit test with Process mock or local integration test if Calibre is available.
