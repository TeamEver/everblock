<?php

declare(strict_types=1);

namespace Everblock\Tools\Query;

final class ListAdminItemsQuery
{
    public function __construct(
        public readonly string $section,
        public readonly int $shopId,
        public readonly int $langId
    ) {
    }
}
