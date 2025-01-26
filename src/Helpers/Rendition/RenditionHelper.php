<?php
namespace Rampmaster\EPub\Helpers\Rendition;

use src\Core\EPub;

/**
 * Helper for Rendition ePub3 extensions.
 *
 *   http://www.idpf.org/epub/fxl/#property-defs
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2015- A. Grandt
 * @license   GNU LGPL 2.1
 */
class RenditionHelper {

    public const RENDITION_PREFIX_NAME = "rendition";
    public const RENDITION_PREFIX_URI = "http://www.idpf.org/vocab/rendition/#";

    public const RENDITION_LAYOUT = "rendition:layout";
    public const RENDITION_ORIENTATION = "rendition:orientation";
    public const RENDITION_SPREAD = "rendition:spread";

    public const LAYOUT_REFLOWABLE = "reflowable";
    public const LAYOUT_PRE_PAGINATED = "pre-paginated";

    public const ORIENTATION_LANDSCAPE = "landscape";
    public const ORIENTATION_PORTRAIT = "portrait";
    public const ORIENTATION_AUTO = "auto";

    public const SPREAD_NONE = "none";
    public const SPREAD_LANDSCAPE = "landscape";
    public const SPREAD_PORTRAIT = "portrait";
    public const SPREAD_BOTH = "both";
    public const SPREAD_AUTO = "auto";

    /**
     * Add iBooks prefix to the ePub book
     *
     * @param EPub $book
     */
    public static function addPrefix($book) {
        if (!$book->isEPubVersion2()) {
            $book->addCustomPrefix(self::RENDITION_PREFIX_NAME, self::RENDITION_PREFIX_URI);
        }
    }

    /**
     * @param EPub   $book
     * @param string $value "reflowable", "pre-paginated"
     */
    public static function setLayout($book, $value) {
        if (!$book->isEPubVersion2() && $value === self::LAYOUT_REFLOWABLE || $value === self::LAYOUT_PRE_PAGINATED) {
            $book->addCustomMetaProperty(self::RENDITION_LAYOUT, $value);
        }
    }

    /**
     * @param EPub   $book
     * @param string $value "landscape", "portrait" or "auto"
     */
    public static function setOrientation($book, $value) {
        if (!$book->isEPubVersion2() && $value === self::ORIENTATION_LANDSCAPE || $value === self::ORIENTATION_PORTRAIT || $value === self::ORIENTATION_AUTO) {
            $book->addCustomMetaProperty(self::RENDITION_ORIENTATION, $value);
        }
    }

    /**
     * @param EPub   $book
     * @param string $value "landscape", "portrait" or "auto"
     */
    public static function setSpread($book, $value) {
        if (!$book->isEPubVersion2() && $value === self::SPREAD_NONE || $value === self::SPREAD_LANDSCAPE || $value === self::SPREAD_PORTRAIT || $value === self::SPREAD_BOTH || $value === self::SPREAD_AUTO) {
            $book->addCustomMetaProperty(self::RENDITION_SPREAD, $value);
        }
    }

    // TODO Implement Rendition settings
}
