<?php
/**
 * Project : everblock
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @see https://www.team-ever.com
 */

namespace Everblock\Tools\Service;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ImportFile
{
    protected array $headers;
    protected array $lines;

    public function __construct($file_name)
    {
        $this->lines = [];

        $reader = new Xlsx();
        $spreadsheet = $reader->load($file_name);

        $lines = $spreadsheet->getSheet(0)->toArray();
        foreach ($lines as $k => $line) {
            if ($k == 0) {
                $this->setHeaders($line);
                continue;
            }

            if (!$this->getHeaders()) {
                continue;
            }

            $line = $this->convertLine($line);

            $this->addLine($line);
        }
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @param array $line
     */
    public function addLine(array $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * @param $line
     * @return array
     */
    protected function convertLine($line): array
    {
        $result = [];
        foreach ($this->getHeaders() as $k => $header) {
            if (isset($result[$header])) {
                continue;
            }
            $result[$header] = $line[$k];
        }
        return $result;
    }
}
