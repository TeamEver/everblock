<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\Command;

/**
 * Command used to duplicate an existing block from the grid.
 */
class DuplicateEverBlockCommand
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
