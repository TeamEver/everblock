<?php

namespace Everblock\Tools\Dto\Product;

use Countable;
use IteratorAggregate;
use ArrayIterator;

/**
 * @implements IteratorAggregate<int, int>
 */
final class ProductIdCollection implements IteratorAggregate, Countable
{
    /**
     * @param int[] $productIds
     */
    public function __construct(private readonly array $productIds)
    {
    }

    /**
     * @return ArrayIterator<int, int>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->productIds);
    }

    public function count(): int
    {
        return count($this->productIds);
    }

    /**
     * @return int[]
     */
    public function toArray(): array
    {
        return $this->productIds;
    }
}
