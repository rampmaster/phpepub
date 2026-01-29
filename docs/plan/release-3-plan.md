# Release 3 Plan: EPUB 3.2 Support and EPUB 2.0.1 Compatibility

## Objective
Complete support for the EPUB 3.x family by adding version 3.2 and ensuring robust compatibility with EPUB 2.0.1 (legacy). EPUB 3.2 is the current recommended version by the W3C and simplifies some requirements compared to 3.0/3.1.

## Scope
- **EPUB 3.2 Support**:
    - Update constants and version attributes.
    - Remove strict requirements from 3.1 that were relaxed in 3.2 (e.g., `dcterms:modified` is no longer strictly mandatory if `dcterms:modified` is used in another way, but keeping it is good practice; check specification).
    - Allow remote fonts and scripts (with warnings).
- **EPUB 2.0.1 Compatibility**:
    - Review and reinforce NCX generation (mandatory in 2.0.1).
    - Ensure HTML5 elements do not leak into 2.0.1 mode (or degrade gracefully).
- **Validation**: Update integration tests.

## Implementation Strategy
1.  **Update `EPub`**: Add constant for 3.2.
2.  **Adjust `Opf`**: Logic for version 3.2.
3.  **Review `Ncx`**: Ensure full compatibility with 2.0.1 (already supported, but verify edge cases).
4.  **Tests**: Expand test matrix.

## Tickets

### TICKET-007: EPUB 3.2 Support
- **Objective**: Generate valid EPUBs declared as version 3.2.
- **Tasks**:
    - Add constant `BOOK_VERSION_EPUB32`.
    - Adjust OPF generation (`package version="3.2"`).
    - Validate with `epubcheck`.

### TICKET-008: Reinforce EPUB 2.0.1 Compatibility
- **Objective**: Ensure generation in 2.0.1 mode is strict and does not include 3.x elements that break validation.
- **Tasks**:
    - Review `EpubAdapter::convertToXhtml` to degrade HTML5 to XHTML 1.1 if the version is 2.0.1.
    - Verify that `epub:type` and other 3.x attributes are not injected into 2.0.1.

### TICKET-009: Integration Tests R3
- **Objective**: Complete tests for 3.2 and regression for 2.0.1.
- **Tasks**:
    - Update `EpubCheckIntegrationTest` with provider for 3.2.
    - Add specific test for 2.0.1 with HTML5 content that must be cleaned.

## Success Criteria
- `epubcheck` validates 3.2 without errors.
- `epubcheck` validates 2.0.1 generated from modern input without errors.
