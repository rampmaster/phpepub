# Feasibility Report: Native AZW3 (KF8) Generation in PHP

## Executive Summary
Native generation of AZW3 (Kindle Format 8) files in PHP is technically possible but requires significant implementation effort. AZW3 is a proprietary format based on a PalmDB container that encapsulates HTML5 and CSS3 content, similar to EPUB 3 but with a different packaging structure and specific compression.

**Recommendation**: **NO-GO** for immediate implementation in the library core. It is recommended to maintain the dependency on external tools (Calibre/KindleGen) for the current version and evaluate a native implementation only if the demand for CLI-less environments justifies the investment of ~2-3 weeks of dedicated development.

## Technical Analysis

### 1. Format Specification
AZW3 (KF8) is not an open standard, although it has been successfully reverse-engineered.
- **Container**: Palm Database (PDB/MOBI).
- **Structure**:
    - Standard PDB Header.
    - PalmDOC Header.
    - MOBI Header (old version).
    - KF8 Header (new structure).
    - Compressed data records (PalmDOC or HUFF/CDIC).
    - Indexes and metadata.

### 2. Implementation Complexity
The complexity lies in:
- **Binary Packaging**: PHP is not the ideal language for intensive bit manipulation and complex binary structures, although it is capable (`pack`/`unpack`).
- **Compression**: Specific compression algorithms need to be implemented (PalmDOC is simple, but HUFF/CDIC is complex).
- **MOBI/KF8 Duality**: A valid AZW3 file often contains both the old MOBI version and the KF8 version for backward compatibility, duplicating content and generation logic.

### 3. Existing Libraries
No robust, maintained, open-source PHP libraries were found that generate AZW3 natively.
- There are old Python scripts (`kindle-unpack`, `mobi-python`) that could be ported.
- There are libraries in Go and Java, but porting them to PHP is a major task.

### 4. Effort Estimation
- **Deep research and prototyping**: 1 week.
- **PDB/MOBI writing implementation**: 1 week.
- **KF8 structure and compression implementation**: 1-2 weeks.
- **Testing and debugging**: 1 week.
- **Total estimated**: 4-5 weeks for a senior developer.

## Alternatives
1.  **Maintain `ebook-convert`**: Stable, robust, but requires installing Calibre (heavy, system dependencies).
2.  **`kindlegen` Binary**: Official from Amazon, but discontinued and restrictive license (not redistributable).
3.  **Microservice**: Delegate conversion to a separate Docker container that has Calibre, keeping the PHP application lightweight.

## Conclusion
Given the current scope of the `phpepub` project, implementing a native AZW3 writer would divert too many resources. The external adapter strategy is the most pragmatic. If total independence is required, it is suggested to seek specific sponsorship for this feature or contribute to a separate "PHP MOBI/AZW3 Writer" project that this library can consume.
