<?php

namespace Everblock\Tools\Shortcode\Handler;

use Context;
use Everblock;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;

final class CallbackShortcodeHandler implements ShortcodeHandlerInterface
{
    /**
     * @param callable(string, mixed ...$args): string $callback
     * @param array<int, string> $argumentMap
     */
    public function __construct(
        private readonly string $needle,
        private $callback,
        private readonly array $argumentMap = []
    ) {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, $this->needle);
    }

    public function render(string $content, Context $context, Everblock $module): string
    {
        $arguments = [];

        foreach ($this->argumentMap as $argument) {
            switch ($argument) {
                case 'context':
                    $arguments[] = $context;

                    break;
                case 'module':
                    $arguments[] = $module;

                    break;
            }
        }

        $result = ($this->callback)($content, ...$arguments);

        return is_string($result) ? $result : (string) $result;
    }
}
