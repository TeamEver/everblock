<?php

namespace Everblock\Tools\Shortcode\Handler;

use Everblock;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;

final class CallbackShortcodeHandler implements ShortcodeHandlerInterface
{
    public function __construct(
        private readonly string $needle,
        private readonly object $service,
        private readonly string $method,
        private readonly array $argumentMap = []
    ) {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, $this->needle);
    }

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        $arguments = [];

        foreach ($this->argumentMap as $argument) {
            switch ($argument) {
                case 'context':
                    $arguments[] = $context->getPrestashopContext();

                    break;
                case 'module':
                    $arguments[] = $module;

                    break;
                case 'renderContext':
                    $arguments[] = $context;

                    break;
            }
        }

        $callback = [$this->service, $this->method];

        if (!is_callable($callback)) {
            return $content;
        }

        $result = $callback($content, ...$arguments);

        return is_string($result) ? $result : (string) $result;
    }
}
