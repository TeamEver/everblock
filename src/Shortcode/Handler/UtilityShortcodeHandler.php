<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class UtilityShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[llorem]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::generateLoremIpsum($content, $context);
            },
        ];
    }
}
