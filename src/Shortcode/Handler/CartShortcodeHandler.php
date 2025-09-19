<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class CartShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[evercart]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getCartShortcode($content, $context, $module);
            },
            '[cart_total]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getCartTotalShortcode($content, $context);
            },
            '[cart_quantity]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getCartQuantityShortcode($content, $context);
            },
        ];
    }
}
