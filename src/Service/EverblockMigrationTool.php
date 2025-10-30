<?php

namespace Everblock\Tools\Service;

use Everblock\Tools\Entity\Everblock;

class EverblockMigrationTool
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $plans = [];

    public function __construct()
    {
        $this->register(
            'EverBlockClass',
            Everblock::class,
            [
                'id_everblock' => 'id',
                'id_hook' => 'hookId',
                'id_shop' => 'shopId',
                'name' => 'name',
                'position' => 'position',
                'active' => 'active',
            ]
        );
    }

    public function register(string $legacyModel, string $entityClass, array $fieldMap = []): void
    {
        $this->plans[$legacyModel] = [
            'entity' => $entityClass,
            'field_map' => $fieldMap,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPlan(string $legacyModel): ?array
    {
        return $this->plans[$legacyModel] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->plans;
    }
}
