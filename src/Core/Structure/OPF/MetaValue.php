<?php
namespace Rampmaster\EPub\Core\Structure\OPF;

use src\Core\EPub;

/**
 * ePub OPF Metadata value structures
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2014- A. Grandt
 * @license   GNU LGPL 2.1
 */
class MetaValue {
    private $tagName = null;

    private $tagValue = null;

    private $attr = [];

    private $opfAttr = [];

    /**
     * Class constructor.
     *
     * @param string $name the name includes the namespace. ie. "dc:contributor"
     * @param string $value
     */
    public function __construct($name, $value) {
        $this->setValue($name, $value);
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $name
     * @param string $value
     */
    public function setValue($name, $value) {
        $this->tagName = is_string($name) ? trim($name) : null;
        if (isset($this->tagName)) {
            $this->tagValue = isset($value) ? (string)$value : null;
        }
        if (!isset($this->tagValue)) {
            $this->tagName = null;
        }
    }

    /**
     * Class destructor
     *
     * @return void
     */
    public function __destruct() {
        unset($this->tagName, $this->tagValue, $this->attr, $this->opfAttr);
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $attrName
     * @param string $attrValue
     */
    public function addAttr($attrName, $attrValue) {
        $attrName = is_string($attrName) ? trim($attrName) : null;
        if (isset($attrName)) {
            $attrValue = is_string($attrValue) ? trim($attrValue) : null;
        }
        if (isset($attrValue)) {
            $this->attr[$attrName] = $attrValue;
        }
    }

    /**
     * Add opf specified attributes.
     *
     * Note: Only available in ePub2 books.
     *
     * @param string $opfAttrName
     * @param string $opfAttrValue
     */
    public function addOpfAttr($opfAttrName, $opfAttrValue) {
        $opfAttrName = is_string($opfAttrName) ? trim($opfAttrName) : null;
        if (isset($opfAttrName)) {
            $opfAttrValue = is_string($opfAttrValue) ? trim($opfAttrValue) : null;
        }
        if (isset($opfAttrValue)) {
            $this->opfAttr[$opfAttrName] = $opfAttrValue;
        }
    }

    /**
     *
     * @param string $bookVersion
     *
     * @return string
     */
    public function finalize($bookVersion = EPub::BOOK_VERSION_EPUB2) {
        $dc = "\t\t<" . $this->tagName;

        if (sizeof($this->attr) > 0) {
            while ([$name, $content] = each($this->attr)) {
                $dc .= " " . $name . "=\"" . $content . "\"";
            }
        }

        if ($bookVersion === EPub::BOOK_VERSION_EPUB2 && sizeof($this->opfAttr) > 0) {
            while ([$name, $content] = each($this->opfAttr)) {
                $dc .= " opf:" . $name . "=\"" . $content . "\"";
            }
        }

        return $dc . ">" . $this->tagValue . "</" . $this->tagName . ">\n";
    }
}
