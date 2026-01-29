# Ticket: release_TICKET-014_Media-Overlays

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

Support for "Read Aloud" via SMIL (Synchronized Multimedia Integration Language).

## Objective (measurable)

- Generate valid `.smil` files.
- Link XHTML chapters with their SMIL files in the OPF.

## Scope

**Includes**:
- `Smil` class (or internal structure).
- Method `addChapterWithAudio($title, $file, $content, $audioFile, $duration)`.
- Basic SMIL XML generation (`<par><text src="..."><audio src="..."/></par>`).

**Does not include**:
- Word-by-word synchronization (requires complex analysis). Chapter or paragraph level synchronization is sufficient for v1.

## Functional Contract

### Inputs
- Audio file path, duration.

### Outputs
- `.smil` file in the ZIP.
- Item in manifest with `media-type="application/smil"`.
- Chapter item with `media-overlay="id_del_smil"` attribute.

## Implementation Plan

1. Create data structure for SMIL.
2. Update `addChapter` or create variant to accept audio.
3. In `finalize`, generate SMIL files and update references.

## Acceptance Criteria (verifiable checklist)

- [x] Generated EPUB with audio passes `epubcheck`.
- [x] Compatible readers (e.g., Thorium) recognize the overlay.

## Tests

- Integration test with a dummy audio file.
