<?php
namespace Rampmaster\EPub\Core\Structure\OPF;

use src\Core\EPub;

/**
 * ePub OPF Manifest structure
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2014- A. Grandt
 * @license   GNU LGPL 2.1
 */
class Manifest {
    private $items = [];

    /**
     * Class constructor.
     */
    public function __construct() {
    }

    /**
     * Class destructor
     *
     * @return void
     */
    public function __destruct() {
        unset($this->items);
    }

    /**
     *
     * Enter description here ...
     *
     * @param Item $item
     */
    public function addItem($item) {
        if ($item != null && is_object($item) && $item instanceof Item) {
            $this->items[] = $item;
        }
    }

    /**
     *
     * @param string $bookVersion
     *
     * @return string
     */
    public function finalize($bookVersion = EPub::BOOK_VERSION_EPUB2) {
        $manifest = "\n\t<manifest>\n";
        foreach ($this->items as $item) {
            /** @var $item Item */
            $manifest .= $item->finalize($bookVersion);
        }

        return $manifest . "\t</manifest>\n";
    }

    /**
     * @return array
     */
    public function getItems() {
        return $this->items;
    }
}
