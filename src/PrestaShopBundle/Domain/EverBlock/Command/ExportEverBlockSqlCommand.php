<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\Command;

/**
 * Command used to export the SQL representation of a block.
 */
class ExportEverBlockSqlCommand
{
    /**
     * @var int
     */
    private $everBlockId;

    public function __construct(int $everBlockId)
    {
        $this->everBlockId = $everBlockId;
    }

    public function getEverBlockId(): int
    {
        return $this->everBlockId;
    }
}
