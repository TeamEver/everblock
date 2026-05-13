<?php

declare(strict_types=1);

namespace Everblock\Tools\Command;

final class SaveAdminItemCommand
{
    public function __construct(
        public string $section,
        public ?int $id,
        public int $shopId,
        public array $data,
        public array $languages
    ) {
    }
}
