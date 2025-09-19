<?php

namespace Everblock\Shortcode\Handler;

use Context;
use Everblock;
use EverblockTools;

class ProductListingShortcodeHandler extends AbstractShortcodeHandler
{
    protected function getShortcodeProcessors(): array
    {
        return [
            '[best-sales' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getBestSalesShortcode($content, $context, $module);
            },
            '[categorybestsales' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getCategoryBestSalesShortcode($content, $context, $module);
            },
            '[brandbestsales' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getBrandBestSalesShortcode($content, $context, $module);
            },
            '[featurebestsales' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getFeatureBestSalesShortcode($content, $context, $module);
            },
            '[featurevaluebestsales' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getFeatureValueBestSalesShortcode($content, $context, $module);
            },
            '[last-products' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getLastProductsShortcode($content, $context, $module);
            },
            '[recently_viewed' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getRecentlyViewedShortcode($content, $context, $module);
            },
            '[promo-products' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getPromoProductsShortcode($content, $context, $module);
            },
            '[products_by_tag' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getProductsByTagShortcode($content, $context, $module);
            },
            '[low_stock' => function (string $content, Context $context, Everblock $module): string {
                return EverblockTools::getLowStockShortcode($content, $context, $module);
            },
        ];
    }
}
