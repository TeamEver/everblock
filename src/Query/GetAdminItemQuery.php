<?php

declare(strict_types=1);

namespace Everblock\Tools\Query;

final class GetAdminItemQuery
{
    public function __construct(
        public readonly string $section,
        public readonly ?int $id,
        public readonly int $shopId,
        public readonly int $langId
    ) {
    }
}
