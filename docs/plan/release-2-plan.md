# Release 2 Plan: EPUB 3.0.1 and 3.1 Support

## Objective
Expand EPUB generation capabilities to support EPUB 3.0.1 and EPUB 3.1 specifications. This involves adjustments to the package structure (OPF), metadata, and navigation, ensuring compatibility and correct validation with `epubcheck`.

## Scope
- **EPUB 3.0.1 Support**: Minor adjustments to metadata and allowed attributes.
- **EPUB 3.1 Support**: Changes to navigation structure (NCX no longer mandatory if Nav Document exists), metadata changes (dc:identifier, dcterms:modified).
- **Validation**: Update integration tests to specifically validate these versions.

## Implementation Strategy
1.  **Refactoring `EPub` and `Opf`**: Allow granular configuration of the version (3.0, 3.0.1, 3.1).
2.  **Metadata Adaptation**: Implement conditional logic for required/obsolete metadata based on version.
3.  **Navigation**: Ensure that for 3.1, the HTML5 Nav Document is prioritized and NCX is optional/legacy.

## Tickets

### TICKET-004: EPUB 3.0.1 Support
- **Objective**: Allow generating EPUBs declared as version 3.0.1.
- **Tasks**:
    - Update version constant in `EPub`.
    - Adjust `package` version attribute in OPF.
    - Validate with `epubcheck`.

### TICKET-005: EPUB 3.1 Support
- **Objective**: Allow generating EPUBs declared as version 3.1.
- **Tasks**:
    - Update version constant.
    - Implement structural changes for 3.1 (e.g., `dcterms:modified` mandatory, `opf:role` deprecated in favor of `refines`).
    - Validate with `epubcheck`.

### TICKET-006: Integration Tests R2
- **Objective**: Ensure new versions pass CI validation.
- **Tasks**:
    - Extend `EpubCheckIntegrationTest` to cover 3.0.1 and 3.1.
    - Verify no regressions in 3.0 and 2.0.1.

## Risks
- Subtle differences in `epubcheck` validation for 3.0.1 vs 3.1 may require fine-tuning in `Opf.php`.

## Success Criteria
- `epubcheck` correctly validates files generated as 3.0.1 and 3.1.
- CI passes for all supported versions.
