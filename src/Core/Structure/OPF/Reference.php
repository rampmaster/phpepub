<?php
namespace Rampmaster\EPub\Core\Structure\OPF;

/**
 * Reference constants
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2014- A. Grandt
 * @license   GNU LGPL 2.1
 */
class Reference {
    /* REFERENCE types are derived from the "Chicago Manual of Style"
     */

    /** Acknowledgements page */
    public const ACKNOWLEDGEMENTS = "acknowledgements";

    /** Bibliography page */
    public const BIBLIOGRAPHY = "bibliography";

    /** Colophon page */
    public const COLOPHON = "colophon";

    /** Copyright page */
    public const COPYRIGHT_PAGE = "copyright-page";

    /** Dedication */
    public const DEDICATION = "dedication";

    /** Epigraph */
    public const EPIGRAPH = "epigraph";

    /** Foreword */
    public const FOREWORD = "foreword";

    /** Glossary page */
    public const GLOSSARY = "glossary";

    /** back-of-book style index */
    public const INDEX = "index";

    /** List of illustrations */
    public const LIST_OF_ILLUSTRATIONS = "loi";

    /** List of tables */
    public const LIST_OF_TABLES = "lot";

    /** Notes page */
    public const NOTES = "notes";

    /** Preface page */
    public const PREFACE = "preface";

    /** Table of contents */
    public const TABLE_OF_CONTENTS = "toc";

    /** Page with possibly title, author, publisher, and other metadata */
    public const TITLE_PAGE = "titlepage";

    /** First page of the book, ie. first page of the first chapter */
    public const TEXT = "text";

    // ******************
    // ePub3 constants
    // ******************

    // Document partitions
    /** The publications cover(s), jacket information, etc. This is officially in ePub3, but works for ePub 2 as well */
    public const COVER = "cover";

    /** Preliminary material to the content body, such as tables of contents, dedications, etc. */
    public const FRONTMATTER = "frontmatter";

    /** The main (body) content of a document. */
    public const BODYMATTER = "bodymatter";

    /** Ancillary material occurring after the document body, such as indices, appendices, etc. */
    public const BACKMATTER = "backmatter";

    private $type = null;

    private $title = null;

    private $href = null;

    /**
     * Class constructor.
     *
     * @param string $type
     * @param string $title
     * @param string $href
     */
    public function __construct($type, $title, $href) {
        $this->setType($type);
        $this->setTitle($title);
        $this->setHref($href);
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $type
     */
    public function setType($type) {
        $this->type = is_string($type) ? trim($type) : null;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = is_string($title) ? trim($title) : null;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $href
     */
    public function setHref($href) {
        $this->href = is_string($href) ? trim($href) : null;
    }

    /**
     * Class destructor
     *
     * @return void
     */
    public function __destruct() {
        unset($this->type, $this->title, $this->href);
    }

    /**
     *
     * Enter description here ...
     *
     * @return string
     */
    public function finalize() {
        return "\t\t<reference type=\"" . $this->type . "\" title=\"" . $this->title . "\" href=\"" . $this->href . "\" />\n";
    }
}
