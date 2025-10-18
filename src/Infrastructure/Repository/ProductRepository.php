<?php

namespace Everblock\Tools\Infrastructure\Repository;

use Everblock\Tools\Dto\Product\ProductIdCollection;
use Everblock\Tools\Dto\Product\ProductView;
use Everblock\Tools\Dto\Product\ProductViewCollection;
use Everblock\Tools\Shortcode\ShortcodeRenderingContext;
use Everblock\Tools\Service\EverblockCache;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductPresenterFactory;
use Product;
use ProductAssembler;
use Validate;
use Exception;

final class ProductRepository
{
    public function presentProducts(ProductIdCollection $productIds, ShortcodeRenderingContext $context): ProductViewCollection
    {
        $ids = $productIds->toArray();
        $resultHash = md5(json_encode($ids));
        $cacheId = 'everblock_product_presentations_'
            . $context->getShopId() . '_'
            . $context->getLanguageId() . '_'
            . $resultHash;

        if (EverblockCache::isCacheStored($cacheId)) {
            $cached = EverblockCache::cacheRetrieve($cacheId);
            if (is_array($cached)) {
                return new ProductViewCollection(array_map(static fn (array $product): ProductView => new ProductView($product), $cached));
            }
        }

        $presentations = [];

        if ($ids !== []) {
            $psContext = $context->getPrestashopContext();
            $assembler = new ProductAssembler($psContext);
            $presenterFactory = new ProductPresenterFactory($psContext);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presentationSettings->showPrices = true;

            if (class_exists(\PrestaShop\PrestaShop\Core\Product\ProductListingPresenter::class)) {
                $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                    new ImageRetriever($psContext->link),
                    $psContext->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $psContext->getTranslator()
                );
            } elseif (class_exists(\PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter::class)) {
                $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter(
                    new ImageRetriever($psContext->link),
                    $psContext->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $psContext->getTranslator()
                );
            } else {
                throw new Exception('No suitable product presenter class found for this PrestaShop version.');
            }

            foreach ($ids as $productId) {
                $productId = (int) $productId;
                if ($productId <= 0) {
                    continue;
                }

                $psProduct = new Product($productId);
                if (!Validate::isLoadedObject($psProduct) || !(bool) $psProduct->active) {
                    continue;
                }

                $rawProduct = [
                    'id_product' => $productId,
                    'id_lang' => $context->getLanguageId(),
                    'id_shop' => $context->getShopId(),
                ];

                $assembled = $assembler->assembleProduct($rawProduct);

                if (Product::checkAccessStatic($productId, $context->getCustomerId())) {
                    $presentations[] = $presenter->present(
                        $presentationSettings,
                        $assembled,
                        $psContext->language
                    );
                }
            }
        }

        EverblockCache::cacheStore($cacheId, $presentations);

        return new ProductViewCollection(array_map(static fn (array $product): ProductView => new ProductView($product), $presentations));
    }
}
