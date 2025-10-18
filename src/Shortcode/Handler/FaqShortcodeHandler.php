<?php

namespace Everblock\Tools\Shortcode\Handler;

use Everblock;
use Everblock\Tools\Service\EverBlockFaqProvider;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;
use Twig\Environment;

final class FaqShortcodeHandler implements ShortcodeHandlerInterface
{
    public function __construct(
        private readonly EverBlockFaqProvider $faqProvider,
        private readonly Environment $twig
    )
    {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, '[everfaq');
    }

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        return (string) preg_replace_callback(
            '/\\[everfaq tag="([^"]+)"\\]/',
            function (array $matches) use ($context) {
                $tagName = $matches[1];

                $faqs = $this->faqProvider->getFaqByTagName(
                    $context->getShopId(),
                    $context->getLanguageId(),
                    $tagName
                );

                if ($faqs === []) {
                    return '';
                }

                return $this->twig->render('@Everblock/shortcode/faq.html.twig', [
                    'faqs' => $faqs,
                ]);
            },
            $content
        );
    }
}
