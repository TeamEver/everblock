<?php

namespace Everblock\Tools\Dto\Product;

final class LowStockFilters
{
    public const GRANULARITY_PRODUCT = 'product';
    public const GRANULARITY_COMBINATION = 'combination';

    /**
     * @param int[] $categoryIds
     * @param int[] $manufacturerIds
     * @param string[] $visibilities
     */
    public function __construct(
        public readonly int $shopId,
        public readonly int $shopGroupId,
        public readonly int $languageId,
        public readonly int $threshold,
        public readonly string $comparisonOperator,
        public readonly int $limit,
        public readonly int $offset,
        public readonly string $orderBy,
        public readonly string $orderDirection,
        public readonly int $days,
        public readonly array $categoryIds,
        public readonly array $manufacturerIds,
        public readonly array $visibilities,
        public readonly bool $availableOnly,
        public readonly string $granularity,
    ) {
    }

    public function orderBySales(): bool
    {
        return $this->orderBy === 'sales';
    }

    public function orderRandomly(): bool
    {
        return $this->orderBy === 'rand';
    }

    public function isCombinationLevel(): bool
    {
        return $this->granularity === self::GRANULARITY_COMBINATION;
    }
}
