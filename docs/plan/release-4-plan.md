# Release 4 Plan: AZW3 (Kindle) Export

## Objective
Enable the ability to export books in AZW3 (Kindle Format 8) format. Since native AZW3 generation is complex and proprietary, the initial strategy will be based on converting a previously generated EPUB using external tools (such as Calibre `ebook-convert` or `kindlegen` if available), encapsulated in a clean interface.

## Scope
- **AZW3 Adapter**: Implement `Azw3Adapter` that extends or uses `EpubAdapter` to generate an intermediate file and then convert it.
- **Tool Detection**: The system must detect if `ebook-convert` (Calibre) or `kindlegen` are available on the system.
- **Error Handling**: Handle cases where no conversion tools are installed.

## Implementation Strategy
1.  **`Azw3Adapter`**:
    - Step 1: Generate temporary EPUB using `EpubAdapter`.
    - Step 2: Invoke conversion command.
    - Step 3: Move/Rename result to destination folder.
2.  **Conversion Abstraction**: Create a `Converter` class or similar in `Helpers` that abstracts the CLI call.

## Tickets

### TICKET-010: Azw3Adapter Implementation
- **Objective**: Create the `Azw3Adapter` class implementing `FormatAdapterInterface`.
- **Tasks**:
    - Implement `generate` method.
    - Logic to search for `ebook-convert` or `kindlegen`.
    - Execute conversion.

### TICKET-011: AZW3 Integration Tests
- **Objective**: Validate that an `.azw3` file is generated if tools are present.
- **Tasks**:
    - Create `Azw3IntegrationTest`.
    - If no tools in CI, the test should be skipped (markTestSkipped) or use a mock if unit.
    - (Optional) Try to install `calibre` in CI or use a docker image that has it, but for basic R4 skip if missing is enough.

### TICKET-012: Native Generation Research (Spike)
- **Objective**: Investigate viability of generating AZW3 natively in PHP without external dependencies for future releases.
- **Deliverable**: Document `docs/report/azw3-native-viability.md`.

## Success Criteria
- An `.azw3` file can be generated from PHP code by invoking the library, assuming configured environment.
- Clear exception if no conversion tools.
