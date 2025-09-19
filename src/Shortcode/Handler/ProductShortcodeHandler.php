<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class ProductShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[product' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getProductShortcodes($content, $context, $module);
            },
            '[product_image' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getProductImageShortcodes($content, $context, $module);
            },
            '[productfeature' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getFeatureProductShortcodes($content, $context, $module);
            },
            '[productfeaturevalue' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getFeatureValueProductShortcodes($content, $context, $module);
            },
        ];
    }
}
