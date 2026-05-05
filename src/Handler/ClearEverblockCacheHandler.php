<?php

declare(strict_types=1);

namespace Everblock\Tools\Handler;

use Everblock\Tools\Command\ClearEverblockCacheCommand;

final class ClearEverblockCacheHandler
{
    public function handle(ClearEverblockCacheCommand $command): void
    {
        \Tools::clearAllCache();
    }
}
