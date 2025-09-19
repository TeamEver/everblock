<?php

namespace Everblock\PrettyBlocks;

use Everblock\PrettyBlocks\Blocks as Blocks;

class BlockRegistryFactory
{
    public static function createDefaultRegistry(): BlockRegistry
    {
        $registry = new BlockRegistry();
        foreach ([
            new Blocks\ShoppingCartBlockProvider(),
            new Blocks\LoginBlockProvider(),
            new Blocks\ContactBlockProvider(),
            new Blocks\DividerBlockProvider(),
            new Blocks\SpacerBlockProvider(),
            new Blocks\HeadingBlockProvider(),
            new Blocks\EverblockBlockProvider(),
            new Blocks\TabBlockProvider(),
            new Blocks\AccordeonBlockProvider(),
            new Blocks\Module1BlockProvider(),
            new Blocks\ReassuranceBlockProvider(),
            new Blocks\CtaBlockProvider(),
            new Blocks\IframeBlockProvider(),
            new Blocks\ScrollVideoBlockProvider(),
            new Blocks\LayoutBlockProvider(),
            new Blocks\CategoryHighlightBlockProvider(),
            new Blocks\CategoryPriceBlockProvider(),
            new Blocks\ModalBlockProvider(),
            new Blocks\ExitIntentBlockProvider(),
            new Blocks\ShortcodeBlockProvider(),
            new Blocks\ImgBlockProvider(),
            new Blocks\RowBlockProvider(),
            new Blocks\TextAndImageBlockProvider(),
            new Blocks\GalleryBlockProvider(),
            new Blocks\MasonryGalleryBlockProvider(),
            new Blocks\VideoGalleryBlockProvider(),
            new Blocks\VideoProductsBlockProvider(),
            new Blocks\AlertBlockProvider(),
            new Blocks\TestimonialBlockProvider(),
            new Blocks\TestimonialSliderBlockProvider(),
            new Blocks\ButtonBlockProvider(),
            new Blocks\GmapBlockProvider(),
            new Blocks\ImgSliderBlockProvider(),
            new Blocks\LinkListBlockProvider(),
            new Blocks\DownloadsBlockProvider(),
            new Blocks\PodcastsBlockProvider(),
            new Blocks\SharerBlockProvider(),
            new Blocks\SocialLinksBlockProvider(),
            new Blocks\BrandsBlockProvider(),
            new Blocks\ProductHighlightBlockProvider(),
            new Blocks\CategoryTabsBlockProvider(),
            new Blocks\CountersBlockProvider(),
            new Blocks\CountdownBlockProvider(),
            new Blocks\CardBlockProvider(),
            new Blocks\CoverBlockProvider(),
            new Blocks\TocBlockProvider(),
            new Blocks\ImageMapBlockProvider(),
            new Blocks\ProductSelectorBlockProvider(),
            new Blocks\GuidedSelectorBlockProvider(),
            new Blocks\SpecialEventBlockProvider(),
            new Blocks\FlashDealsBlockProvider(),
            new Blocks\CategoryProductsBlockProvider(),
            new Blocks\PricingTableBlockProvider(),
            new Blocks\LookbookBlockProvider(),
            new Blocks\WheelOfFortuneBlockProvider(),
            new Blocks\MysteryBoxesBlockProvider(),
            new Blocks\SlotMachineBlockProvider(),
            new Blocks\ScratchCardBlockProvider(),
        ] as $provider) {
            $registry->register($provider);
        }

        return $registry;
    }
}
