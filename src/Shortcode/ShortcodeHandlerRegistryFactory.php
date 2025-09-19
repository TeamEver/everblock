<?php

namespace Everblock\Shortcode;

use Everblock\Shortcode\Handler\AddToCartShortcodeHandler;
use Everblock\Shortcode\Handler\CartShortcodeHandler;
use Everblock\Shortcode\Handler\CatalogShortcodeHandler;
use Everblock\Shortcode\Handler\CmsShortcodeHandler;
use Everblock\Shortcode\Handler\EverblockReferenceShortcodeHandler;
use Everblock\Shortcode\Handler\FormShortcodeHandler;
use Everblock\Shortcode\Handler\GeneralContentShortcodeHandler;
use Everblock\Shortcode\Handler\HookShortcodeHandler;
use Everblock\Shortcode\Handler\MapShortcodeHandler;
use Everblock\Shortcode\Handler\MediaShortcodeHandler;
use Everblock\Shortcode\Handler\ProductListingShortcodeHandler;
use Everblock\Shortcode\Handler\ProductShortcodeHandler;
use Everblock\Shortcode\Handler\RelatedProductShortcodeHandler;
use Everblock\Shortcode\Handler\ShopShortcodeHandler;
use Everblock\Shortcode\Handler\StoreShortcodeHandler;
use Everblock\Shortcode\Handler\SubcategoryShortcodeHandler;
use Everblock\Shortcode\Handler\UtilityShortcodeHandler;
use Everblock\Shortcode\Handler\WidgetShortcodeHandler;

class ShortcodeHandlerRegistryFactory
{
    public static function createDefaultRegistry(): ShortcodeHandlerRegistry
    {
        $registry = new ShortcodeHandlerRegistry([
            new GeneralContentShortcodeHandler(),
            new ProductShortcodeHandler(),
            new CatalogShortcodeHandler(),
            new MapShortcodeHandler(),
            new HookShortcodeHandler(),
            new UtilityShortcodeHandler(),
            new EverblockReferenceShortcodeHandler(),
            new SubcategoryShortcodeHandler(),
            new StoreShortcodeHandler(),
            new MediaShortcodeHandler(),
            new ProductListingShortcodeHandler(),
            new CartShortcodeHandler(),
            new ShopShortcodeHandler(),
            new FormShortcodeHandler(),
            new RelatedProductShortcodeHandler(),
            new WidgetShortcodeHandler(),
            new AddToCartShortcodeHandler(),
            new CmsShortcodeHandler(),
        ]);

        return $registry;
    }
}
