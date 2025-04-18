<?php
/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Everblock\Tools\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
