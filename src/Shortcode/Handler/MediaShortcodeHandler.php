<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class MediaShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[video' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getVideoShortcode($content);
            },
            '[qcdacf' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getQcdAcfCode($content, $context);
            },
            '[displayQcdSvg' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getQcdSvgCode($content, $context);
            },
            '[everimg' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getEverImgShortcode($content, $context, $module);
            },
            '[wordpress-posts]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getWordpressPostsShortcode($content, $context, $module);
            },
        ];
    }
}
