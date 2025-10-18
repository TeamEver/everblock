<?php

namespace Everblock\Tools\Shortcode;

use Context;
use Everblock;

interface ShortcodeHandlerInterface
{
    public function supports(string $content): bool;

    public function render(string $content, Context $context, Everblock $module): string;
}
