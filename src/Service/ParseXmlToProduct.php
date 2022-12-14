<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use XMLParser;

class ParseXmlToProduct
{
    private int $currentProduct = 0;
    private array $data = [];
    private string $key = '';

    public function __construct()
    {
    }

    public function parse(UploadedFile $file)
    {
        if ($file->getMimeType() !== 'text/xml') {
            throw new \Exception('Wrong file type');
        }

        $parser = xml_parser_create();

        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'startXML', 'endXML');
        xml_set_character_data_handler($parser, 'charXML');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);

        $stream = fopen($file->getPathname(), 'r');

        while (($data = fread($stream, 16 * 1024))) {
            xml_parse($parser, $data);
        }

        xml_parse($parser, '', true);
        xml_parser_free($parser);
        fclose($stream);
    }

    public function startXML(XMLParser $parser, string $name, array $attr): void
    {
        if ($name === 'product' || $name === 'products') {
            // nothing
        } else {
            $this->key = $name;
        }
    }

    public function endXML(XMLParser $parser, string $name): void
    {
        if ($name === 'product') {
            $this->currentProduct++;

            if ($this->currentProduct >= 1000) {
                // TODO: Спарсить список перед сбросом
                $this->currentProduct = 0;
                $this->data = [];
                $this->key = '';
            }
        }
    }

    public function charXML(XMLParser $parser, string $data): void
    {
        $data = trim($data);
        if (!$data) {
            return;
        }

        if (!isset($this->data[$this->currentProduct][$this->key])) {
            $this->data[$this->currentProduct][$this->key] = '';
        }

        $this->data[$this->currentProduct][$this->key] .= $data;
    }
}
