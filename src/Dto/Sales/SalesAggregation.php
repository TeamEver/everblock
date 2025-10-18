<?php

namespace Everblock\Tools\Dto\Sales;

final class SalesAggregation
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(private readonly string $sql, private readonly array $parameters)
    {
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
