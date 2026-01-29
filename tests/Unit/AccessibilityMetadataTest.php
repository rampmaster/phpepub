<?php

namespace Rampmaster\EPub\Test\Unit;

use PHPUnit\Framework\TestCase;
use Rampmaster\EPub\Core\EPub;

class AccessibilityMetadataTest extends TestCase
{
    public function testAccessibilityMetadataInjection()
    {
        $book = new EPub(EPub::BOOK_VERSION_EPUB3);
        $book->setTitle('Accessibility Test');
        $book->setLanguage('en');
        $book->addChapter('Chapter 1', 'chap1.xhtml', '<h1>Chapter 1</h1><p>Content</p>');

        // Set accessibility metadata
        $book->setAccessibilitySummary('This book contains structural navigation and alternative text for images.');
        $book->addAccessMode('textual');
        $book->addAccessMode('visual');
        $book->addAccessibilityFeature('structuralNavigation');
        $book->addAccessibilityFeature('alternativeText');
        $book->addAccessibilityHazard('noFlashingHazard');
        $book->setAccessibilityConformsTo('http://www.idpf.org/epub/a11y/accessibility-20170105.html#wcag-aa');

        // Finalize to generate OPF
        $book->finalize();

        // Get the book content (zip)
        $zipContent = $book->getBook();

        // Save to temp file to inspect
        $tmpFile = tempnam(sys_get_temp_dir(), 'epub_test_');
        file_put_contents($tmpFile, $zipContent);

        $zip = new \ZipArchive();
        $res = $zip->open($tmpFile);
        $this->assertTrue($res === true, 'Could not open generated EPUB zip');

        // Find OPF file
        $opfContent = $zip->getFromName('OEBPS/book.opf');
        $this->assertNotFalse($opfContent, 'Could not find OEBPS/book.opf');

        // Check for metadata
        $this->assertStringContainsString('<meta property="schema:accessibilitySummary">This book contains structural navigation and alternative text for images.</meta>', $opfContent);
        $this->assertStringContainsString('<meta property="schema:accessMode">textual</meta>', $opfContent);
        $this->assertStringContainsString('<meta property="schema:accessMode">visual</meta>', $opfContent);
        $this->assertStringContainsString('<meta property="schema:accessibilityFeature">structuralNavigation</meta>', $opfContent);
        $this->assertStringContainsString('<meta property="schema:accessibilityFeature">alternativeText</meta>', $opfContent);
        $this->assertStringContainsString('<meta property="schema:accessibilityHazard">noFlashingHazard</meta>', $opfContent);
        $this->assertStringContainsString('<meta property="dcterms:conformsTo">http://www.idpf.org/epub/a11y/accessibility-20170105.html#wcag-aa</meta>', $opfContent);

        $zip->close();
        unlink($tmpFile);
    }
}
