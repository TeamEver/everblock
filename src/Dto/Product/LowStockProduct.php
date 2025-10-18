<?php

namespace Everblock\Tools\Dto\Product;

final class LowStockProduct
{
    public function __construct(
        private readonly int $productId,
        private readonly ?int $productAttributeId,
        private readonly int $quantity,
        private readonly ?int $soldQuantity
    ) {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductAttributeId(): ?int
    {
        return $this->productAttributeId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getSoldQuantity(): ?int
    {
        return $this->soldQuantity;
    }
}
