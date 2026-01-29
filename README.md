# PHPePub

[![CI](https://github.com/rampmaster/phpepub/actions/workflows/ci.yml/badge.svg)](https://github.com/rampmaster/phpepub/actions/workflows/ci.yml)
[![License](https://img.shields.io/badge/License-LGPL%202.1-blue.svg)](https://opensource.org/licenses/LGPL-2.1)

**PHPePub** is a PHP library that allows you to generate ePub electronic books on the fly. It supports most of the **EPUB 2.0.1** specification and includes robust support for **EPUB 3.0**, **3.0.1**, **3.1**, and **3.2**, enabling the creation of modern, accessible e-books.

This project is a modernized fork of the original [grandt/phpepub](https://github.com/Grandt/PHPePub) library, updated for PHP 8.2+ and enhanced with new features like accessibility metadata, media overlays, and AZW3 export capabilities.

## Features

*   **Multi-version Support**: Generate valid EPUB 2.0.1, 3.0, 3.0.1, 3.1, and 3.2 files.
*   **Accessibility (A11y)**: Built-in methods to add Schema.org accessibility metadata (essential for European Accessibility Act compliance).
*   **Media Overlays**: Support for SMIL to create "Read Aloud" books with synchronized audio.
*   **Kindle Support**: Export to AZW3 (Kindle Format 8) using an external converter (Calibre).
*   **Modern PHP**: Fully compatible with PHP 8.2 and above.
*   **CI/CD Ready**: Includes GitHub Actions workflows for automated testing and validation with `epubcheck`.

## Installation

Install the library via Composer:

```bash
composer require rampmaster/phpepub
```

## Basic Usage

Here is a simple example of how to generate an EPUB 3.2 book:

```php
<?php

require 'vendor/autoload.php';

use Rampmaster\EPub\Core\EPub;

// Create a new EPUB 3.2 book
$book = new EPub(EPub::BOOK_VERSION_EPUB32);

// Set mandatory metadata
$book->setTitle("My First eBook");
$book->setLanguage("en");
$book->setIdentifier("http://example.com/books/1", EPub::IDENTIFIER_URI);

// Add an author
$book->setAuthor("John Doe", "Doe, John");

// Add a chapter
$content = "<h1>Chapter 1</h1><p>Welcome to my book generated with PHPePub.</p>";
$book->addChapter("Chapter 1", "chapter1.xhtml", $content);

// Finalize and save
$book->finalize();
$book->saveBook("my_book", ".");

echo "Book saved as my_book.epub\n";
```

## Advanced Features

### Accessibility Metadata

Make your books accessible by adding metadata:

```php
$book->setAccessibilitySummary("This book contains structural navigation and alternative text for images.");
$book->addAccessMode("textual");
$book->addAccessMode("visual");
$book->addAccessibilityFeature("structuralNavigation");
$book->setAccessibilityConformsTo("http://www.idpf.org/epub/a11y/accessibility-20170105.html#wcag-aa");
```

### Media Overlays (Read Aloud)

Add synchronized audio to a chapter:

```php
$book->addChapterWithAudio(
    "Chapter 1",
    "chap1.xhtml",
    $htmlContent,
    "path/to/audio.mp3",
    "00:05:30" // Duration
);
```

### Export to AZW3 (Kindle)

Requires `ebook-convert` (from Calibre) to be installed on the system.

```php
use Rampmaster\EPub\Core\Format\Azw3Adapter;

$adapter = new Azw3Adapter();
$azw3Path = $adapter->generate([
    'title' => 'My Kindle Book',
    'chapters' => [/* ... */]
]);
```

## Credits

This library is based on the original work by **A. Grandt** ([grandt/phpepub](https://github.com/Grandt/PHPePub)). We are grateful for his contribution to the PHP community, which served as the foundation for this project.

## License

This project is licensed under the **GNU LGPL 2.1**. See the [LICENSE](LICENSE) file for details.
