<?php

namespace Everblock\Tools\Dto\Product;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, ProductView>
 */
final class ProductViewCollection implements IteratorAggregate, Countable
{
    /**
     * @param ProductView[] $products
     */
    public function __construct(private readonly array $products)
    {
    }

    /**
     * @return ArrayIterator<int, ProductView>
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
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn (ProductView $product): array => $product->toArray(), $this->products);
    }
}
