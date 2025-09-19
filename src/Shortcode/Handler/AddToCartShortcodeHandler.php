<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class AddToCartShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[everaddtocart' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getAddToCartShortcode($content, $context, $module);
            },
        ];
    }
}
