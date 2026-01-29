<?php

require __DIR__ . '/../../vendor/autoload.php';

use Rampmaster\EPub\Core\EPub;

// 1. Create a new EPUB 3.2 book
// We use EPUB 3.2 as it is the current recommended version.
$book = new EPub(EPub::BOOK_VERSION_EPUB32);

// 2. Set mandatory metadata
$book->setTitle("My First eBook");
$book->setLanguage("en"); // RFC 3066 Language code
// The identifier should be unique. For this example, we use a URI.
$book->setIdentifier("http://example.com/books/basic-example", EPub::IDENTIFIER_URI);

// 3. Add an author
// The second argument is the sort key (Lastname, Firstname)
$book->setAuthor("John Doe", "Doe, John");

// 4. Add content (Chapters)
// Chapter 1: Simple HTML content
$content1 = "
<h1>Chapter 1: Introduction</h1>
<p>Welcome to my first book generated with PHPePub.</p>
<p>This is a simple paragraph.</p>
";
$book->addChapter("Chapter 1", "chapter1.xhtml", $content1);

// Chapter 2: More content
$content2 = "
<h1>Chapter 2: The Journey</h1>
<p>This is the second chapter.</p>
";
$book->addChapter("Chapter 2", "chapter2.xhtml", $content2);

// 5. Finalize the book structure
// This generates the OPF, NCX, and other internal files.
$book->finalize();

// 6. Save the book
// The first argument is the filename (without extension), the second is the directory.
$outputDir = __DIR__;
$filename = "01-basic";
$book->saveBook($filename, $outputDir);

echo "Book saved as {$outputDir}/{$filename}.epub\n";
