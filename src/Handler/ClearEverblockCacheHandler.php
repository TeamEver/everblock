<?php

declare(strict_types=1);

namespace Everblock\Tools\Handler;

use Everblock\Tools\Command\ClearEverblockCacheCommand;
use Everblock\Tools\Service\EverblockCache;

final class ClearEverblockCacheHandler
{
    public function __invoke(ClearEverblockCacheCommand $command): void
    {
        $this->handle($command);
    }

    public function handle(ClearEverblockCacheCommand $command): void
    {
        EverblockCache::clearAllModuleCache();
    }
}
