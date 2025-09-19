<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class HookShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '{hook h=' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::replaceHook($content);
            },
        ];
    }
}
