<?php

namespace Everblock\Tools\Shortcode;

use Customer;
use Everblock;
use EverblockTools;
use Everblock\Tools\Service\EverBlockFaqProvider;
use Everblock\Tools\Service\EverBlockShortcodeProvider;
use Everblock\Tools\Service\EverblockPrettyBlocks;
use Gender;
use Hook;
use Traversable;

final class ShortcodeRenderer
{
    /** @var iterable<int, ShortcodeHandlerInterface> */
    private iterable $handlers;

    /**
     * @param iterable<int, ShortcodeHandlerInterface> $handlers
     */
    public function __construct(
        iterable $handlers,
        private readonly EverBlockShortcodeProvider $shortcodeProvider,
        private readonly EverBlockFaqProvider $faqProvider,
        private readonly EverblockPrettyBlocks $prettyBlocks
    ) {
        $this->handlers = $handlers instanceof Traversable ? $handlers : (array) $handlers;

        EverblockTools::setShortcodeProvider($this->shortcodeProvider);
        EverblockPrettyBlocks::setShortcodeProvider($this->shortcodeProvider);
    }

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        Hook::exec('displayBeforeRenderingShortcodes', ['html' => &$content]);

        $content = $this->renderEverShortcodes($content, $context);
        $content = $this->renderRegisteredHandlers($content, $context, $module);

        if ($this->shouldRenderCustomerShortcodes($context)) {
            $content = $this->renderCustomerShortcodes($content, $context);
            $content = $this->obfuscateTextByClass($content);
        }

        $content = $this->renderSmartyVariables($content, $context);

        Hook::exec('displayAfterRenderingShortcodes', ['html' => &$content]);

        return $content;
    }

    public function getFaqProvider(): EverBlockFaqProvider
    {
        return $this->faqProvider;
    }

    public function getPrettyBlocksService(): EverblockPrettyBlocks
    {
        return $this->prettyBlocks;
    }

    public function renderEverShortcodes(string $content, ShortcodeRenderingContext $context): string
    {
        $customShortcodes = $this->shortcodeProvider->getAllShortcodes(
            $context->getShopId(),
            $context->getLanguageId()
        );

        foreach ($customShortcodes as $shortcode) {
            $content = str_replace($shortcode->shortcode, $shortcode->content, $content);
        }

        return $content;
    }

    public function renderRegisteredHandlers(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        foreach ($this->handlers as $handler) {
            if (!$handler instanceof ShortcodeHandlerInterface) {
                continue;
            }

            if (!$handler->supports($content)) {
                continue;
            }

            $content = $handler->render($content, $context, $module);
        }

        return $content;
    }

    public function renderCustomerShortcodes(string $content, ShortcodeRenderingContext $context): string
    {
        $customer = new Customer($context->getCustomerId());
        $gender = new Gender((int) $customer->id_gender, $context->getLanguageId());

        $replacements = [
            '[entity_lastname]' => $customer->lastname,
            '[entity_firstname]' => $customer->firstname,
            '[entity_company]' => $customer->company,
            '[entity_siret]' => $customer->siret,
            '[entity_ape]' => $customer->ape,
            '[entity_birthday]' => $customer->birthday,
            '[entity_website]' => $customer->website,
            '[entity_gender]' => $gender->name,
        ];

        foreach ($replacements as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    public function renderSmartyVariables(string $content, ShortcodeRenderingContext $context): string
    {
        if (!$this->shouldRenderCustomerShortcodes($context)) {
            return $content;
        }

        $templateVars = [
            'customer' => $context->getTemplateVarCustomer(),
            'currency' => $context->getTemplateVarCurrency(),
            'shop' => $context->getTemplateVarShop(),
            'urls' => $context->getTemplateVarUrls(),
            'configuration' => $context->getTemplateVarConfiguration(),
            'breadcrumb' => $context->getBreadcrumb(),
        ];

        foreach ($templateVars as $key => $value) {
            $search = '$' . $key;

            if (is_array($value)) {
                $content = $this->renderSmartyArray($content, $search, $value);

                continue;
            }

            if (is_string($value)) {
                $content = str_replace($search, $value, $content);
            }
        }

        return $content;
    }

    private function renderSmartyArray(string $content, string $search, array $values): string
    {
        foreach ($values as $key => $value) {
            $elementSearch = $search . '.' . $key;

            if (is_array($value)) {
                $content = $this->renderSmartyArray($content, $elementSearch, $value);

                continue;
            }

            $content = str_replace($elementSearch, (string) $value, $content);
        }

        return $content;
    }

    private function shouldRenderCustomerShortcodes(ShortcodeRenderingContext $context): bool
    {
        return in_array($context->getControllerType(), ['front', 'modulefront'], true);
    }

    private function obfuscateTextByClass(string $text): string
    {
        $pattern = '/<a\\s+(.*?)>/i';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $wholeTag = $match[0];
            $attributesPart = $match[1];

            if (!preg_match('/\\bclass="[^"]*\\bobfme\\b[^"]*"/', $wholeTag)
                && !preg_match("/\\bclass='[^']*\\bobfme\\b[^']*'/", $wholeTag)
            ) {
                continue;
            }

            preg_match('/href="([^"]*)"/i', $wholeTag, $urlMatch);
            $linkUrl = $urlMatch[1] ?? '';
            $encodedLink = base64_encode($linkUrl);

            $newClassAttribute = preg_replace_callback(
                '/\\bclass=("|\')([^"\']*)("|\')/i',
                static function ($classMatch) {
                    return 'class=' . $classMatch[1] . $classMatch[2] . ' obflink' . $classMatch[3];
                },
                $attributesPart
            );

            $newAttributesPart = preg_replace(
                '/href="([^"]*)"/i',
                'data-obflink="' . $encodedLink . '"',
                $newClassAttribute ?? $attributesPart
            );

            $newTag = '<span ' . $newAttributesPart . '>';
            $text = str_replace($wholeTag, $newTag, $text);
        }

        return $text;
    }
}
