<?php

namespace Everblock\Shortcode;

use Context;
use Everblock;

interface ShortcodeHandlerInterface
{
    public function supports(string $content): bool;

    public function handle(string $content, Context $context, Everblock $module): string;
}
