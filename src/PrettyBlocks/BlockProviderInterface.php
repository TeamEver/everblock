<?php

namespace Everblock\PrettyBlocks;

interface BlockProviderInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBlocks(BlockDefinitionContext $definitionContext): array;
}
