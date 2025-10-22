<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\Command;

/**
 * Command responsible for toggling a block status from the grid.
 */
class ToggleEverBlockStatusCommand
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
