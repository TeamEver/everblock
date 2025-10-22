<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\CommandHandler;

use Everblock\PrestaShopBundle\Domain\EverBlock\Command\ExportEverBlockSqlCommand;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\CannotExportEverBlockSqlException;
use Everblock\PrestaShopBundle\Service\EverBlockSqlExporter;

class ExportEverBlockSqlHandler
{
    /**
     * @var EverBlockSqlExporter
     */
    private $exporter;

    public function __construct(EverBlockSqlExporter $exporter)
    {
        $this->exporter = $exporter;
    }

    public function handle(ExportEverBlockSqlCommand $command): string
    {
        $sql = $this->exporter->export($command->getEverBlockId());

        if ('' === trim($sql)) {
            throw new CannotExportEverBlockSqlException('Unable to export SQL for this block.');
        }

        return $sql;
    }
}
