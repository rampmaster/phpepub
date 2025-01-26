<?php
namespace Rampmaster\EPub\Core\Structure\OPF;

/**
 * ePub OPF Itemref structure
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2014- A. Grandt
 * @license   GNU LGPL 2.1
 */
class Itemref {
    private $idref = null;

    private $linear = true;

    /**
     * Class constructor.
     *
     * @param      $idref
     * @param bool $linear
     */
    public function __construct($idref, $linear = true) {
        $this->setIdref($idref);
        $this->setLinear($linear);
    }

    /**
     *
     * Enter description here ...
     *
     * @param bool $linear
     */
    public function setLinear($linear = true) {
        $this->linear = $linear === true;
    }

    /**
     * Class destructor
     *
     * @return void
     */
    public function __destruct() {
        unset($this->idref, $this->linear);
    }

    /**
     *
     * Enter description here ...
     *
     * @return string $idref
     */
    public function getIdref() {
        return $this->idref;
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $idref
     */
    public function setIdref($idref) {
        $this->idref = is_string($idref) ? trim($idref) : null;
    }

    /**
     *
     * Enter description here ...
     *
     * @return string
     */
    public function finalize() {
        $itemref = "\t\t<itemref idref=\"" . $this->idref . "\"";

        return $itemref . ($this->linear == false ? ' linear="no"' : '') . " />\n";
    }
}
