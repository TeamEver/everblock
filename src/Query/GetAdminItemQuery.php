<?php

declare(strict_types=1);

namespace Everblock\Tools\Query;

final class GetAdminItemQuery
{
    public function __construct(
        public string $section,
        public ?int $id,
        public int $shopId,
        public int $langId
    ) {
    }
}
