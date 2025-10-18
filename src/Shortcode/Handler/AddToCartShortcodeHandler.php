<?php

namespace Everblock\Tools\Shortcode\Handler;

use Db;
use Everblock;
use Everblock\Tools\Shortcode\ShortcodeHandlerInterface;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AddToCartShortcodeHandler implements ShortcodeHandlerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function supports(string $content): bool
    {
        return str_contains($content, '[everaddtocart');
    }

    public function render(string $content, ShortcodeRenderingContext $context, Everblock $module): string
    {
        return (string) preg_replace_callback(
            '/\[everaddtocart\s+ref="([^"]+)"(?:\s+text="([^"]*)")?\]/i',
            function (array $matches) use ($module): string {
                $reference = trim($matches[1]);
                if ($reference === '') {
                    return '';
                }

                $label = isset($matches[2]) && $matches[2] !== ''
                    ? $matches[2]
                    : $this->translator->trans('Add to cart', [], 'Modules.Everblock.Shop');

                $productData = $this->findProductByReference($reference);
                if ($productData === null) {
                    return '';
                }

                $url = $this->router->generate(
                    'everblock_cart_add',
                    [
                        'id_product' => $productData['product_id'],
                        'id_product_attribute' => $productData['product_attribute_id'],
                        'qty' => 1,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_PATH
                );

                return sprintf(
                    '<a href="%s" class="btn btn-primary ">%s</a>',
                    htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );
    }

    /**
     * @return array{product_id: int, product_attribute_id: int}|null
     */
    private function findProductByReference(string $reference): ?array
    {
        $db = Db::getInstance();
        $productId = (int) $db->getValue(
            sprintf(
                "SELECT `id_product` FROM `%sproduct` WHERE `reference` = '%s'",
                _DB_PREFIX_,
                pSQL($reference)
            )
        );

        if ($productId > 0) {
            return [
                'product_id' => $productId,
                'product_attribute_id' => 0,
            ];
        }

        $result = $db->getRow(
            sprintf(
                "SELECT pa.`id_product`, pa.`id_product_attribute` FROM `%sproduct_attribute` pa WHERE pa.`reference` = '%s'",
                _DB_PREFIX_,
                pSQL($reference)
            )
        );

        if ($result === false || !isset($result['id_product'])) {
            return null;
        }

        return [
            'product_id' => (int) $result['id_product'],
            'product_attribute_id' => (int) $result['id_product_attribute'],
        ];
    }
}
