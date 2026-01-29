<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core\Structure\OPF;

use Rampmaster\EPub\Core\EPub;
use Rampmaster\EPub\Core\StaticData;

/**
 * ePub OPF Metadata structures
 *
 * @author    A. Grandt <php@grandt.com>
 * @copyright 2014- A. Grandt
 * @license   GNU LGPL 2.1
 */
class Metadata
{
    private $dc = [];

    private $meta = [];

    private $metaProperties = [];

    public $namespaces = [];

    /**
     * Class constructor.
     */
    public function __construct()
    {
    }

    /**
     * Class destructor
     *
     * @return void
     */
    public function __destruct()
    {
        unset($this->dc, $this->meta);
    }

    /**
     *
     * Enter description here ...
     *
     * @param MetaValue $dc
     */
    public function addDublinCore($dc)
    {
        if ($dc != null && is_object($dc) && $dc instanceof MetaValue) {
            $this->dc[] = $dc;
        }
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $name
     * @param string $content
     */
    public function addMeta($name, $content)
    {
        $name = is_string($name) ? trim($name) : null;
        if ($name === null) {
            return;
        }
        $content = is_string($content) ? trim($content) : null;
        if ($content === null) {
            return;
        }
        $this->meta[] = [
            $name => $content,
        ];
    }

    /**
     *
     * Enter description here ...
     *
     * @param string $name
     * @param string $content
     */
    public function addMetaProperty($name, $content)
    {
        $name = is_string($name) ? trim($name) : null;
        if ($name === null) {
            return;
        }
        $content = is_string($content) ? trim($content) : null;
        if ($content === null) {
            return;
        }
        // Evitar duplicados exactos (mismo nombre Y mismo contenido)
        foreach ($this->metaProperties as $existing) {
            $existingName = array_key_first($existing);
            $existingContent = $existing[$existingName] ?? null;
            if ($existingName === $name && $existingContent === $content) {
                // Ya existe exactamente la misma propiedad con el mismo valor
                return;
            }
        }

        $this->metaProperties[] = [
            $name => $content,
        ];
    }

    /**
     * @param string $nsName
     * @param string $nsURI
     */
    public function addNamespace($nsName, $nsURI)
    {
        if (!array_key_exists($nsName, $this->namespaces)) {
            $this->namespaces[$nsName] = $nsURI;
        }
    }

    /**
     *
     * @param string $bookVersion
     * @param int    $date
     *
     * @return string
     */
    public function finalize($bookVersion = EPub::BOOK_VERSION_EPUB2, $date = null)
    {
        if ($bookVersion === EPub::BOOK_VERSION_EPUB2) {
            $this->addNamespace("opf", StaticData::$namespaces["opf"]);
        } else {
            if (!isset($date)) {
                $date = time();
            }
            $this->addNamespace("dcterms", StaticData::$namespaces["dcterms"]);
            $this->addMetaProperty("dcterms:modified", gmdate('Y-m-d\TH:i:s\Z', $date));
        }

        if (sizeof($this->dc) > 0) {
            $this->addNamespace("dc", StaticData::$namespaces["dc"]);
        }

        $metadata = "\t<metadata>\n";

        foreach ($this->dc as $dc) {
            /** @var MetaValue $dc */
            $metadata .= $dc->finalize($bookVersion);
        }

        foreach ($this->metaProperties as $data) {
            $name = array_key_first($data);
            $content = $data[$name];

            $metadata .= "\t\t<meta property=\"" . $name . "\">" . $content . "</meta>\n";
        }

        foreach ($this->meta as $data) {
            $name = array_key_first($data);
            $content = $data[$name];
            $metadata .= "\t\t<meta name=\"" . $name . "\" content=\"" . $content . "\" />\n";
        }

        return $metadata . "\t</metadata>\n";
    }
}
