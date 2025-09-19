<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class ShopShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[shop_logo]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getShopLogoShortcode($content, $context);
            },
        ];
    }
}
