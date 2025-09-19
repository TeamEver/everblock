<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use Everblock\Shortcode\ShortcodeHandlerInterface;

abstract class AbstractShortcodeHandler implements ShortcodeHandlerInterface
{
    /**
     * @return array<string, callable>
     */
    abstract protected function getShortcodeProcessors(): array;

    public function supports(string $content): bool
    {
        foreach (array_keys($this->getShortcodeProcessors()) as $trigger) {
            if (strpos($content, $trigger) !== false) {
                return true;
            }
        }

        return false;
    }

    public function handle(string $content, Context $context, Everblock $module): string
    {
        foreach ($this->getShortcodeProcessors() as $trigger => $processor) {
            if (strpos($content, $trigger) !== false) {
                $content = $processor($content, $context, $module);
            }
        }

        return $content;
    }
}
