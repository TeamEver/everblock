<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class MapShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[storelocator]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::generateGoogleMap($content, $context, $module);
            },
            '[evermap]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getEverMapShortcode($content, $context, $module);
            },
        ];
    }
}
