# Ticket: release_TICKET-008_EPUB-2.0.1-Compatibility

> Rule: this ticket must be executable **without interpretation**. If a field cannot be answered, the ticket must be marked as **BLOCKED** and explicitly state what is missing.

## Status

- [x] Ready
- [ ] BLOCKED (explain why)

## Context

Although EPUB 2.0.1 is legacy, many older readers require it. Currently, the adapter attempts to generate valid XHTML, but if HTML5 content is injected (e.g., `<section>`, `<nav>`, `epub:type` attributes), 2.0.1 validation will fail because the XHTML 1.1 DTD does not support them.

## Objective (measurable)

- Ensure that when version 2.0.1 is requested, HTML content is cleaned of HTML5/EPUB3 exclusive tags/attributes.

## Scope

**Includes**:
- Modify `EpubAdapter::convertToXhtml`: if the target version is 2.0.1, use a stricter cleaning process (e.g., convert `<section>` to `<div>`, remove `epub:*` attributes).

**Does not include**:
- Complex CSS3 to CSS2 conversion.

## Functional Contract

### Inputs
- `EpubAdapter::generate(['version' => '2.0', 'content' => '<section epub:type="chapter">...</section>'])`

### Outputs
- Generated XHTML: `<div>...</div>` (without epub attributes).

## Implementation Plan

1. In `EpubAdapter`, pass the target version to `convertToXhtml`.
2. If it is 2.0, apply transformation:
    - Strip `epub:` attributes.
    - Rename HTML5 tags (`article`, `section`, `nav`, `header`, `footer`) to `div` or `span`.
3. Verify with `epubcheck`.

## Acceptance Criteria (verifiable checklist)

- [x] Input with HTML5 tags generates valid EPUB 2.0.1.
- [x] `epubcheck` reports no DTD errors.

## Tests

- Unit test with HTML5 -> XHTML 1.1 snippet.
