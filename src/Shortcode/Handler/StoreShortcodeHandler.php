<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class StoreShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[everstore' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getStoreShortcode($content, $context, $module);
            },
        ];
    }
}
