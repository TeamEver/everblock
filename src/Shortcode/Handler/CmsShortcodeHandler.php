<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class CmsShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[cms' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getCmsShortcode($content, $context);
            },
        ];
    }
}
