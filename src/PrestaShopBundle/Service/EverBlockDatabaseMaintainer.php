<?php

namespace Everblock\PrestaShopBundle\Service;

use Everblock\Tools\Service\EverblockTools;

class EverBlockDatabaseMaintainer
{
    /**
     * @var callable
     */
    private $checker;

    public function __construct(?callable $checker = null)
    {
        $this->checker = $checker ?: [EverblockTools::class, 'checkAndFixDatabase'];
    }

    public function ensureSchema(): void
    {
        \call_user_func($this->checker);
    }
}
