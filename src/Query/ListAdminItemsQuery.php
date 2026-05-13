<?php

declare(strict_types=1);

namespace Everblock\Tools\Query;

final class ListAdminItemsQuery
{
    public function __construct(
        public string $section,
        public int $shopId,
        public int $langId
    ) {
    }
}
