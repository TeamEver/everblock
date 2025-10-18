<?php

namespace Everblock\Tools\Dto\Product;

final class ProductTagFilters
{
    public const MATCH_ANY = 'any';
    public const MATCH_ALL = 'all';

    /**
     * @param string[] $tagNames
     * @param int[] $tagIds
     * @param string[] $visibilities
     */
    public function __construct(
        public readonly int $shopId,
        public readonly int $languageId,
        public readonly array $tagNames,
        public readonly array $tagIds,
        public readonly string $match,
        public readonly int $offset,
        public readonly int $limit,
        public readonly string $orderBy,
        public readonly string $orderDirection,
        public readonly array $visibilities,
    ) {
    }
}
