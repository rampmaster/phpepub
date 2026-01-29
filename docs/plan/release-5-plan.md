# Release 5 Plan: Accessibility, Documentation, and Examples

## Objective
This release focuses on improving the quality of the final product through accessibility (A11y) features and professionalizing the project presentation with updated English documentation and functional examples aligned with the modern API.

## Scope
- **Accessibility (A11y)**: Add explicit support for accessibility metadata (Schema.org) and basic validation.
- **Media Overlays (SMIL)**: Initial support for books with synchronized audio (Read Aloud).
- **Documentation**: Complete translation to English and update README with credits and installation guides.
- **Examples**: Update the `docs/examples` folder to reflect the usage of `EpubAdapter` and new features.

## Implementation Strategy
1.  **Core**: Extend `EPub` and `Opf` to handle accessibility metadata and SMIL structure.
2.  **Docs**: Rewrite technical documentation and README.
3.  **Examples**: Create executable PHP scripts demonstrating real use cases (simple EPUB3, EPUB3 with audio, AZW3 export).

## Tickets

### TICKET-013: Accessibility Metadata Support
- **Objective**: Allow easy injection of accessibility metadata required by the EPUB Accessibility 1.1 standard.
- **Tasks**:
    - Methods in `EPub`: `setAccessibilitySummary`, `addAccessMode`, `setAccessibilityFeature`, `setAccessibilityHazard`.
    - Correct generation in OPF (`meta property="schema:..."`).

### TICKET-014: Media Overlays (SMIL) Implementation
- **Objective**: Allow creating EPUBs with "Media Overlays" (text synchronized with audio).
- **Tasks**:
    - Create `Smil` class in `src/Core/Structure`.
    - Allow associating an audio file and timings to a chapter.
    - Generate `.smil` files and update OPF (`media-overlay` attribute).

### TICKET-015: Documentation and Credits Update
- **Objective**: Internationalize the project and credit the original author.
- **Tasks**:
    - Translate key documentation to English.
    - Update `README.md`:
        - CI badges.
        - Installation instructions (`composer require`).
        - "Credits" section thanking `grandt/phpepub`.
        - Migration/basic usage guide.

### TICKET-016: Examples Update
- **Objective**: Provide "copy-paste" examples that work with the current version.
- **Tasks**:
    - Clean `docs/examples` of legacy code.
    - Create `example-basic.php` (EPUB 3.2).
    - Create `example-advanced.php` (with images, A11y metadata).
    - Create `example-azw3.php` (conversion).

## Success Criteria
- Clear and professional documentation in English.
- Examples executable without errors.
- Ability to generate an EPUB that passes "Ace by DAISY" validation (or at least has the required metadata).
