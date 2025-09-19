<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class EverblockReferenceShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[everblock' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getEverBlockShortcode($content, $context);
            },
        ];
    }
}
