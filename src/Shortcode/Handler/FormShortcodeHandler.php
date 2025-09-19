<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class FormShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[newsletter_form]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getNewsletterFormShortcode($content, $context, $module);
            },
            '[nativecontact]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getNativeContactShortcode($content, $context, $module);
            },
            '[evercontactform_open]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getFormShortcode($content, $context, $module);
            },
            '[everorderform_open]' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getOrderFormShortcode($content, $context, $module);
            },
        ];
    }
}
