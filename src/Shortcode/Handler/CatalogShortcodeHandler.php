<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class CatalogShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[category' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getCategoryShortcodes($content, $context, $module);
            },
            '[manufacturer' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getManufacturerShortcodes($content, $context, $module);
            },
            '[brands' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getBrandsShortcode($content, $context, $module);
            },
        ];
    }
}
