<?php

namespace Everblock\PrestaShopBundle\Service;

use Tools;

class EverBlockCacheRefresher
{
    /**
     * @var callable
     */
    private $clearer;

    public function __construct(?callable $clearer = null)
    {
        $this->clearer = $clearer ?: [Tools::class, 'clearAllCache'];
    }

    public function clear(): void
    {
        \call_user_func($this->clearer);
    }
}
