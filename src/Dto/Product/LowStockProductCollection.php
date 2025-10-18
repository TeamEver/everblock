<?php

namespace Everblock\Tools\Dto\Product;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, LowStockProduct>
 */
final class LowStockProductCollection implements IteratorAggregate, Countable
{
    /**
     * @param LowStockProduct[] $products
     */
    public function __construct(private readonly array $products)
    {
    }

    /**
     * @return ArrayIterator<int, LowStockProduct>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->products);
    }

    public function count(): int
    {
        return count($this->products);
    }

    /**
     * @return LowStockProduct[]
     */
    public function toArray(): array
    {
        return $this->products;
    }
}
