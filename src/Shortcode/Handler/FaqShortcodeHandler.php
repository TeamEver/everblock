<?php

namespace Everblock\Tools\Shortcode\Handler;

use Everblock;
use EverblockTools;
use Everblock\Tools\Service\EverBlockFaqProvider;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;

final class FaqShortcodeHandler implements ShortcodeHandlerInterface
{
    public function __construct(private readonly EverBlockFaqProvider $faqProvider)
    {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, '[everfaq');
    }

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        $templatePath = EverblockTools::getTemplatePath('hook/faq.tpl', $module);

        return (string) preg_replace_callback(
            '/\\[everfaq tag="([^"]+)"\\]/',
            function (array $matches) use ($context, $templatePath) {
                $tagName = $matches[1];

                $faqs = $this->faqProvider->getFaqByTagName(
                    $context->getShopId(),
                    $context->getLanguageId(),
                    $tagName
                );

                $context->getSmarty()->assign('everFaqs', $faqs);

                return $context->getSmarty()->fetch($templatePath);
            },
            $content
        );
    }
}
