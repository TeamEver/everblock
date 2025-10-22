<?php

namespace Everblock\PrestaShopBundle\Service;

use Everblock\Tools\Service\EverblockTools;

class EverBlockSqlExporter
{
    /**
     * @var callable
     */
    private $exporter;

    public function __construct(?callable $exporter = null)
    {
        $this->exporter = $exporter ?: [EverblockTools::class, 'exportBlockSQL'];
    }

    public function export(int $everBlockId): string
    {
        return (string) \call_user_func($this->exporter, $everBlockId);
    }
}
