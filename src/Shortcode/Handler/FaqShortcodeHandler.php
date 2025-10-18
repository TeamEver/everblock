<?php

namespace Everblock\Tools\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;
use Everblock\Tools\Service\EverBlockFaqProvider;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;

final class FaqShortcodeHandler implements ShortcodeHandlerInterface
{
    public function __construct(private readonly EverBlockFaqProvider $faqProvider)
    {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, '[everfaq');
    }

    public function render(string $content, Context $context, Everblock $module): string
    {
        $templatePath = EverblockTools::getTemplatePath('hook/faq.tpl', $module);

        return (string) preg_replace_callback(
            '/\\[everfaq tag="([^"]+)"\\]/',
            function (array $matches) use ($context, $templatePath) {
                $tagName = $matches[1];

                $faqs = $this->faqProvider->getFaqByTagName(
                    (int) $context->shop->id,
                    (int) $context->language->id,
                    $tagName
                );

                $context->smarty->assign('everFaqs', $faqs);

                return $context->smarty->fetch($templatePath);
            },
            $content
        );
    }
}
