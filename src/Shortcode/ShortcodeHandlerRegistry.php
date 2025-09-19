<?php

namespace Everblock\Shortcode;

use Context;
use Everblock;

class ShortcodeHandlerRegistry
{
    /** @var ShortcodeHandlerInterface[] */
    private $handlers = [];

    /**
     * @param iterable<ShortcodeHandlerInterface> $handlers
     */
    public function __construct(iterable $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->register($handler);
        }
    }

    public function register(ShortcodeHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function process(string $content, Context $context, Everblock $module): string
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($content)) {
                $content = $handler->handle($content, $context, $module);
            }
        }

        return $content;
    }
}
