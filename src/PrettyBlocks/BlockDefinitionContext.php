<?php

namespace Everblock\PrettyBlocks;

use Context;
use Everblock;

class BlockDefinitionContext
{
    /** @var Everblock */
    private $module;

    /** @var Context */
    private $context;

    /** @var array<string, mixed> */
    private $variables;

    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(Everblock $module, Context $context, array $variables)
    {
        $this->module = $module;
        $this->context = $context;
        $this->variables = $variables;
    }

    public function getModule(): Everblock
    {
        return $this->module;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return array<string, mixed>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return mixed|null
     */
    public function getVariable(string $name)
    {
        return $this->variables[$name] ?? null;
    }
}
