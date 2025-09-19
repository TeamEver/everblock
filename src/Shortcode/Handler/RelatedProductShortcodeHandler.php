<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class RelatedProductShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[random_product' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getRandomProductsShortcode($content, $context, $module);
            },
            '[accessories' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getAccessoriesShortcode($content, $context, $module);
            },
            '[linkedproducts' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getLinkedProductsShortcode($content, $context, $module);
            },
            '[crosselling' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getCrossSellingShortcode($content, $context, $module);
            },
        ];
    }
}
