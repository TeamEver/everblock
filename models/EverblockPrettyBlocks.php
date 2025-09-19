<?php

/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockRegistry;
use Everblock\PrettyBlocks\BlockRegistryFactory;

class EverblockPrettyBlocks extends ObjectModel
{
    private const MEDIA_PATH = '$/img/cms/prettyblocks/';

    public function registerBlockToZone($zone_name, $code, $id_lang, $id_shop)
    {
        return PrettyBlocksModel::registerBlockToZone($zone_name, $code, $id_lang, $id_shop);
    }

    public static function getEverPrettyBlocks($context)
    {
        $cacheId = 'EverblockPrettyBlocks_getEverPrettyBlocks_'
            . (int) $context->language->id
            . '_'
            . (int) $context->shop->id;

        if (!EverblockCache::isCacheStored($cacheId)) {
            /** @var Everblock $module */
            $module = Module::getInstanceByName('everblock');
            $variables = self::buildContextVariables($module, $context);

            $definitionContext = new BlockDefinitionContext(
                $module,
                $context,
                $variables
            );

            $registry = self::resolveBlockRegistry($module);
            $blocks = $registry->getBlocks($definitionContext);
            $blocks = self::applyFileUploadPath($blocks);
            EverblockCache::cacheStore($cacheId, $blocks);

            return $blocks;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    private static function buildContextVariables(Everblock $module, Context $context): array
    {
        $templateBase = 'module:' . $module->name . '/views/templates/hook/prettyblocks/';

        $templates = [
            'defaultTemplate' => $templateBase . 'prettyblock_' . $module->name . '.tpl',
            'modalTemplate' => $templateBase . 'prettyblock_modal.tpl',
            'alertTemplate' => $templateBase . 'prettyblock_alert.tpl',
            'buttonTemplate' => $templateBase . 'prettyblock_button.tpl',
            'gmapTemplate' => $templateBase . 'prettyblock_gmap.tpl',
            'shortcodeTemplate' => $templateBase . 'prettyblock_shortcode.tpl',
            'iframeTemplate' => $templateBase . 'prettyblock_iframe.tpl',
            'scrollVideoTemplate' => $templateBase . 'prettyblock_scroll_video.tpl',
            'loginTemplate' => $templateBase . 'prettyblock_login.tpl',
            'contactTemplate' => $templateBase . 'prettyblock_contact.tpl',
            'shoppingCartTemplate' => $templateBase . 'prettyblock_shopping_cart.tpl',
            'accordeonTemplate' => $templateBase . 'prettyblock_accordeon.tpl',
            'textAndImageTemplate' => $templateBase . 'prettyblock_text_and_image.tpl',
            'layoutTemplate' => $templateBase . 'prettyblock_layout.tpl',
            'featuredCategoryTemplate' => $templateBase . 'prettyblock_category_highlight.tpl',
            'imgSliderTemplate' => $templateBase . 'prettyblock_img_slider.tpl',
            'tabTemplate' => $templateBase . 'prettyblock_tab.tpl',
            'categoryTabsTemplate' => $templateBase . 'prettyblock_category_tabs.tpl',
            'dividerTemplate' => $templateBase . 'prettyblock_divider.tpl',
            'spacerTemplate' => $templateBase . 'prettyblock_spacer.tpl',
            'galleryTemplate' => $templateBase . 'prettyblock_gallery.tpl',
            'masonryGalleryTemplate' => $templateBase . 'prettyblock_masonry_gallery.tpl',
            'videoGalleryTemplate' => $templateBase . 'prettyblock_video_gallery.tpl',
            'videoProductsTemplate' => $templateBase . 'prettyblock_video_products.tpl',
            'testimonialTemplate' => $templateBase . 'prettyblock_testimonial.tpl',
            'testimonialSliderTemplate' => $templateBase . 'prettyblock_testimonial_slider.tpl',
            'imgTemplate' => $templateBase . 'prettyblock_img.tpl',
            'rowTemplate' => $templateBase . 'row.tpl',
            'reassuranceTemplate' => $templateBase . 'prettyblock_reassurance.tpl',
            'ctaTemplate' => $templateBase . 'prettyblock_cta.tpl',
            'sharerTemplate' => $templateBase . 'prettyblock_sharer.tpl',
            'linkListTemplate' => $templateBase . 'prettyblock_link_list.tpl',
            'downloadsTemplate' => $templateBase . 'prettyblock_downloads.tpl',
            'socialLinksTemplate' => $templateBase . 'prettyblock_social_links.tpl',
            'brandListTemplate' => $templateBase . 'prettyblock_brands.tpl',
            'productHighlightTemplate' => $templateBase . 'prettyblock_product_highlight.tpl',
            'productSelectorTemplate' => $templateBase . 'prettyblock_product_selector.tpl',
            'guidedSelectorTemplate' => $templateBase . 'prettyblock_guided_selector.tpl',
            'flashDealsTemplate' => $templateBase . 'prettyblock_flash_deals.tpl',
            'categoryProductsTemplate' => $templateBase . 'prettyblock_category_products.tpl',
            'countersTemplate' => $templateBase . 'prettyblock_counters.tpl',
            'countdownTemplate' => $templateBase . 'prettyblock_countdown.tpl',
            'cardTemplate' => $templateBase . 'prettyblock_card.tpl',
            'coverTemplate' => $templateBase . 'prettyblock_cover.tpl',
            'headingTemplate' => $templateBase . 'prettyblock_heading.tpl',
            'categoryPriceTemplate' => $templateBase . 'prettyblock_category_price.tpl',
            'tocTemplate' => $templateBase . 'prettyblock_toc.tpl',
            'imageMapTemplate' => $templateBase . 'prettyblock_image_map.tpl',
            'everblockTemplate' => $templateBase . 'prettyblock_everblock.tpl',
            'lookbookTemplate' => $templateBase . 'prettyblock_lookbook.tpl',
            'pricingTableTemplate' => $templateBase . 'prettyblock_pricing_table.tpl',
            'podcastsTemplate' => $templateBase . 'prettyblock_podcasts.tpl',
            'exitIntentTemplate' => $templateBase . 'prettyblock_exit_intent.tpl',
            'specialEventTemplate' => $templateBase . 'prettyblock_special_event.tpl',
            'wheelTemplate' => $templateBase . 'prettyblock_wheel_of_fortune.tpl',
            'mysteryBoxesTemplate' => $templateBase . 'prettyblock_mystery_boxes.tpl',
            'slotMachineTemplate' => $templateBase . 'prettyblock_slot_machine.tpl',
            'scratchTemplate' => $templateBase . 'prettyblock_scratch_card.tpl',
        ];

        $slotMachineDefaultStartDate = date('Y-m-d 00:00:00');
        $slotMachineDefaultEndDate = date('Y-m-d 23:59:59', strtotime('+30 days'));
        $slotMachineDefaultWinningCombinations = json_encode(
            [
                [
                    'pattern' => ['cherry', 'cherry', 'cherry'],
                    'label' => [
                        (string) $context->language->id => $module->l('Cherry jackpot'),
                    ],
                    'message' => [
                        (string) $context->language->id => $module->l('Congratulations! Enjoy 15% off your next order.'),
                    ],
                    'isWinning' => true,
                    'coupon_type' => 'percent',
                    'discount' => 15,
                    'coupon_validity' => 30,
                ],
                [
                    'pattern' => ['lemon', 'lemon', 'lemon'],
                    'label' => [
                        (string) $context->language->id => $module->l('Citrus prize'),
                    ],
                    'message' => [
                        (string) $context->language->id => $module->l('You win a 10 reward voucher to brighten your day.'),
                    ],
                    'isWinning' => true,
                    'coupon_type' => 'amount',
                    'discount' => 10,
                    'minimum_purchase' => 50,
                    'coupon_validity' => 15,
                ],
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        if (false === $slotMachineDefaultWinningCombinations) {
            $slotMachineDefaultWinningCombinations = '[]';
        }

        $defaultLogo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $module->name . '/logo.png';

        $allShortcodes = EverblockShortcode::getAllShortcodes(
            (int) $context->language->id,
            (int) $context->shop->id
        );
        $everblocks = EverBlockClass::getAllBlocks(
            (int) $context->language->id,
            (int) $context->shop->id
        );

        $everblockChoices = [];
        foreach ($everblocks as $eblock) {
            $everblockChoices[$eblock['id_everblock']] = $eblock['id_everblock'] . ' - ' . $eblock['name'];
        }

        $prettyBlocksShortcodes = [];
        foreach ($allShortcodes as $shortcode) {
            $prettyBlocksShortcodes[$shortcode->shortcode] = $shortcode->shortcode;
        }

        return array_merge(
            $templates,
            [
                'defaultLogo' => $defaultLogo,
                'everblockChoices' => $everblockChoices,
                'prettyBlocksShortcodes' => $prettyBlocksShortcodes,
                'slotMachineDefaultStartDate' => $slotMachineDefaultStartDate,
                'slotMachineDefaultEndDate' => $slotMachineDefaultEndDate,
                'slotMachineDefaultWinningCombinations' => $slotMachineDefaultWinningCombinations,
            ]
        );
    }

    private static function resolveBlockRegistry(Everblock $module): BlockRegistry
    {
        if (method_exists($module, 'getContainer')) {
            $container = $module->getContainer();

            if (is_object($container)
                && method_exists($container, 'has')
                && method_exists($container, 'get')
                && $container->has('everblock.prettyblocks.block_registry')
            ) {
                $registry = $container->get('everblock.prettyblocks.block_registry');

                if ($registry instanceof BlockRegistry) {
                    return $registry;
                }
            }
        }

        return BlockRegistryFactory::createDefaultRegistry();
    }

    private static function applyFileUploadPath(array $blocks): array
    {
        foreach ($blocks as &$block) {
            if (isset($block['config']['fields'])) {
                $block['config']['fields'] = self::setPathRecursive($block['config']['fields']);
            }
            if (isset($block['repeater']['groups'])) {
                $block['repeater']['groups'] = self::setPathRecursive($block['repeater']['groups']);
            }
        }

        return $blocks;
    }

    private static function setPathRecursive(array $fields): array
    {
        foreach ($fields as &$field) {
            if (is_array($field)) {
                if (($field['type'] ?? null) === 'fileupload') {
                    $field['path'] = self::MEDIA_PATH;
                } else {
                    $field = self::setPathRecursive($field);
                }
            }
        }

        return $fields;
    }
}
