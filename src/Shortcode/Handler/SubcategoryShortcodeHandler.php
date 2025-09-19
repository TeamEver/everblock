<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class SubcategoryShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[subcategories' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getSubcategoriesShortcode($content, $context, $module);
            },
        ];
    }
}
