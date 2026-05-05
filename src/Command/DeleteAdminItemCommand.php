<?php

declare(strict_types=1);

namespace Everblock\Tools\Command;

final class DeleteAdminItemCommand
{
    public function __construct(
        public readonly string $section,
        public readonly int $id,
        public readonly int $shopId
    ) {
    }
}
