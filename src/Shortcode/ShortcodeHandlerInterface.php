<?php

namespace Everblock\Tools\Shortcode;

use Everblock;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;

interface ShortcodeHandlerInterface
{
    public function supports(string $content): bool;

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string;
}
