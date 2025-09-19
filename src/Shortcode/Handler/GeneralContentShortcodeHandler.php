<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class GeneralContentShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[alert' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getAlertShortcode($content);
            },
            '[everfaq' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getFaqShortcodes($content, $context, $module);
            },
            '[everinstagram]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getInstagramShortcodes($content, $context, $module);
            },
        ];
    }
}
