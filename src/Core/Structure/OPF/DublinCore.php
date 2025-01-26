<?php
namespace Rampmaster\EPub\Core\Structure\OPF;

/**
 * ePub OPF Dublin Core (dc:) Metadata structures
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2014- A. Grandt
 * @license   GNU LGPL 2.1
 */
class DublinCore extends MetaValue {
    public const CONTRIBUTOR = "contributor";
    public const COVERAGE = "coverage";
    public const CREATOR = "creator";
    public const DATE = "date";
    public const DESCRIPTION = "description";
    public const FORMAT = "format";
    public const IDENTIFIER = "identifier";
    public const LANGUAGE = "language";
    public const PUBLISHER = "publisher";
    public const RELATION = "relation";
    public const RIGHTS = "rights";
    public const SOURCE = "source";
    public const SUBJECT = "subject";
    public const TITLE = "title";
    public const TYPE = "type";

    /**
     * Class constructor.
     *
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value) {
        $this->setDc($name, $value);
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $name
     * @param string $value
     */
    public function setDc($name, $value) {
        if (is_string($name)) {
            $this->setValue("dc:" . trim($name), $value);
        }
    }
}
