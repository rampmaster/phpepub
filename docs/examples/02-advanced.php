<?php

require __DIR__ . '/../../vendor/autoload.php';

use Rampmaster\EPub\Core\EPub;

// 1. Create a new EPUB 3.2 book
$book = new EPub(EPub::BOOK_VERSION_EPUB32);

// 2. Set metadata
$book->setTitle("Advanced eBook Example");
$book->setLanguage("en");
$book->setIdentifier("urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6", EPub::IDENTIFIER_UUID);
$book->setAuthor("Jane Smith", "Smith, Jane");
$book->setDescription("An example demonstrating advanced features like CSS, cover images, and accessibility metadata.");
$book->setPublisher("Rampmaster Publishing", "https://rampmaster.dev");
$book->setDate(time());
$book->setRights("Copyright (c) 2023 Jane Smith");

// 3. Accessibility Metadata (A11y)
// Essential for compliance with modern standards
$book->setAccessibilitySummary("This book contains structural navigation and alternative text for images.");
$book->addAccessMode("textual");
$book->addAccessMode("visual");
$book->addAccessibilityFeature("structuralNavigation");
$book->addAccessibilityFeature("alternativeText");
$book->setAccessibilityConformsTo("http://www.idpf.org/epub/a11y/accessibility-20170105.html#wcag-aa");

// 4. Add CSS
$css = "
body { font-family: serif; line-height: 1.5; }
h1 { color: #2c3e50; border-bottom: 1px solid #eee; }
p { margin-bottom: 1em; }
.highlight { background-color: #fff3cd; padding: 0.2em; }
";
$book->addCSSFile("styles.css", "main-css", $css);

// 5. Add Cover Image
// We create a simple placeholder image for the example
$coverImage = imagecreatetruecolor(600, 800);
$bgColor = imagecolorallocate($coverImage, 52, 62, 80); // Dark blue
$textColor = imagecolorallocate($coverImage, 255, 255, 255); // White
imagefill($coverImage, 0, 0, $bgColor);
imagestring($coverImage, 5, 200, 350, "Advanced eBook", $textColor);
imagestring($coverImage, 4, 220, 400, "By Jane Smith", $textColor);

// Save image to a temp file
$coverPath = sys_get_temp_dir() . '/cover.png';
imagepng($coverImage, $coverPath);
imagedestroy($coverImage);

// Set the cover
$book->setCoverImage($coverPath);

// 6. Add Content
$content = "
<h1>Chapter 1: Styling</h1>
<p>This paragraph uses the global CSS styles defined for the book.</p>
<p>This is a <span class=\"highlight\">highlighted text</span> using a CSS class.</p>
";
$book->addChapter("Chapter 1", "chapter1.xhtml", $content);

// 7. Finalize and Save
$book->finalize();
$outputDir = __DIR__;
$filename = "02-advanced";
$book->saveBook($filename, $outputDir);

// Cleanup temp file
@unlink($coverPath);

echo "Book saved as {$outputDir}/{$filename}.epub\n";
