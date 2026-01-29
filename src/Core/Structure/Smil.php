<?php

declare(strict_types=1);

namespace Rampmaster\EPub\Core\Structure;

/**
 * SMIL (Synchronized Multimedia Integration Language) structure for Media Overlays.
 */
class Smil
{
    public const MIMETYPE = 'application/smil+xml';

    private string $id;
    private string $href;
    private string $audioSrc;
    private string $textSrc;
    private array $parList = [];

    public function __construct(string $id, string $href, string $textSrc, string $audioSrc)
    {
        $this->id = $id;
        $this->href = $href;
        $this->textSrc = $textSrc;
        $this->audioSrc = $audioSrc;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * Add a parallel time container (par) to the SMIL.
     *
     * @param string $textId The ID of the element in the XHTML file.
     * @param string $clipBegin Start time (e.g., "0s", "00:00:00.000").
     * @param string $clipEnd End time.
     */
    public function addPar(string $textId, string $clipBegin, string $clipEnd): void
    {
        $this->parList[] = [
            'textId' => $textId,
            'clipBegin' => $clipBegin,
            'clipEnd' => $clipEnd,
        ];
    }

    public function finalize(): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xml .= '<smil xmlns="http://www.w3.org/ns/SMIL" version="3.0" profile="http://www.ipdf.org/epub/30/profile/content/">' . "\n";
        $xml .= "\t<body>\n";

        // We assume the whole SMIL file corresponds to one sequence (seq) of parallel (par) elements.
        // A more complex implementation might support nested seq/par.
        $xml .= "\t\t<seq id=\"seq1\" epub:textref=\"{$this->textSrc}\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n";

        foreach ($this->parList as $index => $par) {
            $parId = "par" . ($index + 1);
            $xml .= "\t\t\t<par id=\"{$parId}\">\n";
            $xml .= "\t\t\t\t<text src=\"{$this->textSrc}#{$par['textId']}\"/>\n";
            $xml .= "\t\t\t\t<audio src=\"{$this->audioSrc}\" clipBegin=\"{$par['clipBegin']}\" clipEnd=\"{$par['clipEnd']}\"/>\n";
            $xml .= "\t\t\t</par>\n";
        }

        $xml .= "\t\t</seq>\n";
        $xml .= "\t</body>\n";
        $xml .= '</smil>';

        return $xml;
    }
}
