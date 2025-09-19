<?php

namespace Everblock\PrettyBlocks;

class BlockRegistry
{
    /** @var BlockProviderInterface[] */
    private $providers = [];

    /**
     * @param iterable<BlockProviderInterface> $providers
     */
    public function __construct(iterable $providers = [])
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    public function register(BlockProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $blocks = [];
        foreach ($this->providers as $provider) {
            $blocks = array_merge($blocks, $provider->getBlocks($definitionContext));
        }

        return $blocks;
    }
}
