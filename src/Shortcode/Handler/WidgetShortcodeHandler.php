<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class WidgetShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[widget' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getWidgetShortcode($content);
            },
            '[prettyblocks' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getPrettyblocksShortcodes($content, $context, $module);
            },
        ];
    }
}
