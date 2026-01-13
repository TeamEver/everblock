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

namespace Everblock\Tools\Service;

use Configuration;
use Context;
use EverBlockClass;
use Everblock\Tools\Service\EverblockCache;
use EverblockShortcode;
use Everblock\Tools\Service\EverblockTools;
use Group;
use Hook;
use Module;
use PrettyBlocksModel;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockPrettyBlocks
{
    private const MEDIA_PATH = '$/img/cms/prettyblocks/';
    private const BEFORE_RENDERING_HOOKS = [
        'beforeRenderingEverblockEverblock',
        'beforeRenderingEverblockCategoryTabs',
        'beforeRenderingEverblockCategoryPrice',
        'beforeRenderingEverblockProductHighlight',
        'beforeRenderingEverblockProductSelector',
        'beforeRenderingEverblockVideoProducts',
        'beforeRenderingEverblockSpecialEvent',
        'beforeRenderingEverblockFlashDeals',
        'beforeRenderingEverblockBestSales',
        'beforeRenderingEverblockGuidedSelector',
        'beforeRenderingEverblockLookbook',
        'beforeRenderingEverblockCategoryProducts',
    ];

    public function registerBlockToZone($zone_name, $code, $id_lang, $id_shop)
    {
        return PrettyBlocksModel::registerBlockToZone($zone_name, $code, $id_lang, $id_shop);
    }

    public static function ensureBeforeRenderingHooksRegistered(Module $module): void
    {
        if (!Module::isInstalled('prettyblocks')) {
            return;
        }

        foreach (self::BEFORE_RENDERING_HOOKS as $hookName) {
            $module->registerHook($hookName);
        }
    }

    private static function appendSpacingFields(array $groups, Module $module): array
    {
        return array_merge($groups, static::getSpacingFields($module));
    }

    private static function getSpacingFields(Module $module): array
    {
        $labels = [
            'padding_left' => 'Padding left (Please specify the unit of measurement)',
            'padding_right' => 'Padding right (Please specify the unit of measurement)',
            'padding_top' => 'Padding top (Please specify the unit of measurement)',
            'padding_bottom' => 'Padding bottom (Please specify the unit of measurement)',
            'margin_left' => 'Margin left (Please specify the unit of measurement)',
            'margin_right' => 'Margin right (Please specify the unit of measurement)',
            'margin_top' => 'Margin top (Please specify the unit of measurement)',
            'margin_bottom' => 'Margin bottom (Please specify the unit of measurement)',
            'margin_left_mobile' => 'Mobile margin left (Please specify the unit of measurement)',
            'margin_right_mobile' => 'Mobile margin right (Please specify the unit of measurement)',
            'margin_top_mobile' => 'Mobile margin top (Please specify the unit of measurement)',
            'margin_bottom_mobile' => 'Mobile margin bottom (Please specify the unit of measurement)',
        ];

        $fields = [];

        foreach ($labels as $name => $label) {
            $fields[$name] = [
                'type' => 'text',
                'label' => $module->l($label),
                'default' => '',
            ];
        }

        return $fields;
    }

    private static function getColumnChoices(Module $module): array
    {
        return [
            '1' => $module->l('1 column'),
            '2' => $module->l('2 columns'),
            '3' => $module->l('3 columns'),
            '4' => $module->l('4 columns'),
            '5' => $module->l('5 columns'),
            '6' => $module->l('6 columns'),
        ];
    }

    private static function getEverblockPageChoices(Context $context, Module $module): array
    {
        $choices = [
            '' => $module->l('Select a guide'),
        ];
        $pages = \EverblockPage::getPages(
            (int) $context->language->id,
            (int) $context->shop->id,
            false
        );

        foreach ($pages as $page) {
            $pageId = (int) $page->id;
            $pageTitle = $page->title ?: $page->name ?: $pageId;
            $choices[$pageId] = $pageId . ' - ' . $pageTitle;
        }

        return $choices;
    }

    public static function getEverPrettyBlocks($context)
    {
        $cacheId = 'EverblockPrettyBlocks_getEverPrettyBlocks_'
        . (int) $context->language->id
        . '_'
        . (int) $context->shop->id;
        $module = Module::getInstanceByName('everblock');
        if (!EverblockCache::isCacheStored($cacheId)) {
            $defaultTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_' . $module->name . '.tpl';
            $modalTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_modal.tpl';
            $alertTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_alert.tpl';
            $buttonTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_button.tpl';
            $gmapTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_gmap.tpl';
            $customIframeTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_custom_iframe.tpl';
            $shortcodeTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_shortcode.tpl';
            $iframeTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_iframe.tpl';
            $scrollVideoTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_scroll_video.tpl';
            $loginTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_login.tpl';
            $contactTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_contact.tpl';
            $shoppingCartTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_shopping_cart.tpl';
            $accordeonTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_accordeon.tpl';
            $faqTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_faq.tpl';
            $textAndImageTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_text_and_image.tpl';
            $layoutTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_layout.tpl';
            $featuredCategoryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_highlight.tpl';
            $imgSliderTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_img_slider.tpl';
            $tabTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_tab.tpl';
            $categoryTabsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_tabs.tpl';
            $dividerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_divider.tpl';
            $spacerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_spacer.tpl';
            $galleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_gallery.tpl';
            $masonryGalleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_masonry_gallery.tpl';
            $videoGalleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_video_gallery.tpl';
            $videoProductsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_video_products.tpl';
            $testimonialTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial.tpl';
            $testimonialSliderTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial_slider.tpl';
            $imgTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_img.tpl';
            $rowTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/row.tpl';
            $reassuranceTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_reassurance.tpl';
            $ctaTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_cta.tpl';
            $googleReviewsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_google_reviews.tpl';
            $sharerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_sharer.tpl';
            $linkListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_link_list.tpl';
            $downloadsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_downloads.tpl';
            $socialLinksTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_social_links.tpl';
            $brandListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_brands.tpl';
            $productHighlightTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_product_highlight.tpl';
            $productSelectorTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_product_selector.tpl';
            $guidedSelectorTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_guided_selector.tpl';
            $flashDealsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_flash_deals.tpl';
            $bestSalesTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_best_sales.tpl';
            $categoryProductsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_products.tpl';
            $countersTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_counters.tpl';
            $countdownTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_countdown.tpl';
            $cardTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_card.tpl';
            $coverTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_cover.tpl';
            $headingTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_heading.tpl';
            $categoryPriceTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_price.tpl';
            $tocTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_toc.tpl';
            $imageMapTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_image_map.tpl';
            $everblockTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_everblock.tpl';
            $lookbookTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_lookbook.tpl';
            $pricingTableTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_pricing_table.tpl';
            $podcastsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_podcasts.tpl';
            $exitIntentTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_exit_intent.tpl';
            $specialEventTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_special_event.tpl';
            $wheelTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_wheel_of_fortune.tpl';
            $mysteryBoxesTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_mystery_boxes.tpl';
            $slotMachineTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_slot_machine.tpl';
            $pagesGuideTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_pages_guide.tpl';
            $guidesSelectionTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_guides_selection.tpl';
            $latestGuidesTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_latest_guides.tpl';
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
            $blocks = [];
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
            $everblockPageChoices = self::getEverblockPageChoices($context, $module);
            $allHooks = Hook::getHooks(false, true);
            $prettyBlocksHooks = [];
            foreach ($allHooks as $hook) {
                $prettyBlocksHooks[$hook['name']] = $hook['name'];
            }
            $prettyBlocksShortcodes = [];
            foreach ($allShortcodes as $shortcode) {
                $prettyBlocksShortcodes[$shortcode->shortcode] = $shortcode->shortcode;
            }
            $blocks[] = [
                'name' => $module->l('Shopping cart'),
                'description' => $module->l('Add dropdown shopping cart'),
                'code' => 'everblock_shopping_cart',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $shoppingCartTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Login form'),
                'description' => $module->l('Add login form'),
                'code' => 'everblock_login',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $loginTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => 'Block title',
                            'default' => $module->l('Login'),
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Native contact form'),
                'description' => $module->l('Add login form (default contact module must be installed)'),
                'code' => 'everblock_contact',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $contactTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => 'Block title',
                            'default' => $module->l('Login'),
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Divider'),
                'description' => $module->l('Show divider'),
                'code' => 'everblock_divider',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $dividerTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Spacer'),
                'description' => $module->l('Add a vertical space'),
                'code' => 'everblock_spacer',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $spacerTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'space_top' => [
                            'type' => 'text',
                            'label' => $module->l('Space top (rem)'),
                            'default' => '0',
                        ],
                        'space_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Space bottom (rem)'),
                            'default' => '0',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Title'),
                'description' => $module->l('Display a customizable heading'),
                'code' => 'everblock_heading',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $headingTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title text'),
                            'default' => $module->l('My title'),
                        ],
                        'level' => [
                            'type' => 'select',
                            'label' => $module->l('Heading level'),
                            'choices' => [
                                'h1' => 'H1',
                                'h2' => 'H2',
                                'h3' => 'H3',
                                'h4' => 'H4',
                                'h5' => 'H5',
                                'h6' => 'H6',
                            ],
                            'default' => 'h2',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'title_alignment' => [
                            'type' => 'select',
                            'label' => $module->l('Title alignment'),
                            'choices' => [
                                'left' => $module->l('Left'),
                                'center' => $module->l('Center'),
                                'right' => $module->l('Right'),
                            ],
                            'default' => 'left',
                        ],
                        'text_color' => [
                            'type' => 'color',
                            'label' => $module->l('Text color'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Everblocks'),
                'description' => $module->l('Render existing Everblocks'),
                'code' => 'everblock_everblock',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $everblockTemplate,
                ],
                'repeater' => [
                    'name' => 'Everblock',
                    'nameFrom' => 'id_everblock',
                    'groups' => static::appendSpacingFields([
                        'id_everblock' => [
                            'type' => 'select',
                            'label' => $module->l('Select an Everblock'),
                            'choices' => $everblockChoices,
                            'default' => '',
                        ],
                        'background_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block background color'),
                        ],
                        'text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block text color'),
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Tabs'),
                'description' => $module->l('Show custom tabs'),
                'code' => 'everblock_tab',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $tabTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([], $module),
                ],
                'repeater' => [
                    'name' => 'Tab',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Tab title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Tab content',
                            'default' => '[llorem]',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Accordeons'),
                'description' => $module->l('Add horizontal accordeon'),
                'code' => 'everblock_accordeon',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $accordeonTemplate
                ],
                'repeater' => [
                    'name' => 'Accordeon',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Accordeon title',
                            'default' => '',
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Accordeon content',
                            'default' => '',
                        ],
                        'title_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '#000000',
                            'label' => $module->l('Accordeon title color')
                        ],
                        'title_bg_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '#000000',
                            'label' => $module->l('Accordeon background color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('FAQ list'),
                'description' => $module->l('Display a Prettyblock FAQ with badges and links'),
                'code' => 'everblock_faq',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $faqTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Section title'),
                            'default' => $module->l('Frequently asked questions'),
                        ],
                        'subtitle' => [
                            'type' => 'textarea',
                            'label' => $module->l('Section subtitle'),
                            'default' => '',
                        ],
                        'cta_text' => [
                            'type' => 'text',
                            'label' => $module->l('CTA label'),
                            'default' => $module->l('See all FAQs'),
                        ],
                        'cta_link' => [
                            'type' => 'text',
                            'label' => $module->l('CTA link'),
                            'default' => '#',
                        ],
                        'badge_background' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'label' => $module->l('Default badge background'),
                            'default' => '#f3f0ff',
                        ],
                        'badge_text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'label' => $module->l('Default badge text color'),
                            'default' => '#5b35c3',
                        ],
                        'background_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'label' => $module->l('Section background color'),
                            'default' => '#f7f9fc',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'FAQ',
                    'nameFrom' => 'question',
                    'groups' => static::appendSpacingFields([
                        'question' => [
                            'type' => 'text',
                            'label' => $module->l('Question'),
                            'default' => $module->l('What do you want to know?'),
                        ],
                        'answer' => [
                            'type' => 'editor',
                            'label' => $module->l('Answer'),
                            'default' => $module->l('Share the most helpful information about this topic.'),
                        ],
                        'link_label' => [
                            'type' => 'text',
                            'label' => $module->l('Link label'),
                            'default' => $module->l('Learn more'),
                        ],
                        'link_url' => [
                            'type' => 'text',
                            'label' => $module->l('Link URL'),
                            'default' => '#',
                        ],
                        'badge_label' => [
                            'type' => 'text',
                            'label' => $module->l('Badge text'),
                            'default' => '?',
                        ],
                        'badge_background' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'label' => $module->l('Badge background'),
                            'default' => '',
                        ],
                        'badge_text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'label' => $module->l('Badge text color'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->displayName,
                'description' => $module->description,
                'code' => $module->name,
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $defaultTemplate,
                ],
                'repeater' => [
                    'name' => 'Tab',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Block title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Block content',
                            'default' => '[llorem]',
                        ],
                        'background_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block background color')
                        ],
                        'text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block text color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Reassurance block'),
                'description' => $module->l('Add multiple reassurance icons with titles and short texts.'),
                'code' => 'everblock_reassurance',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $reassuranceTemplate,
                ],
                'config' => [
                    'fields' => [
                        'items_per_row' => [
                            'type' => 'select',
                            'label' => $module->l('Reassurances per row'),
                            'choices' => [
                                '1' => $module->l('1 item per row'),
                                '2' => $module->l('2 items per row'),
                                '3' => $module->l('3 items per row'),
                                '4' => $module->l('4 items per row'),
                                '5' => $module->l('5 items per row'),
                                '6' => $module->l('6 items per row'),
                            ],
                            'default' => '3',
                        ],
                        'slider' => [
                            'type' => 'switch',
                            'label' => $module->l('Enable slider'),
                            'default' => false,
                        ],
                        'slider_devices' => [
                            'type' => 'select',
                            'label' => $module->l('Enable slider on'),
                            'choices' => [
                                'desktop' => $module->l('Desktop only'),
                                'mobile' => $module->l('Mobile only'),
                                'both' => $module->l('Desktop and mobile'),
                            ],
                            'default' => 'both',
                        ],
                        'slider_items_desktop' => [
                            'type' => 'select',
                            'label' => $module->l('Items per slide (desktop)'),
                            'choices' => [
                                '1' => $module->l('1 item'),
                                '2' => $module->l('2 items'),
                                '3' => $module->l('3 items'),
                                '4' => $module->l('4 items'),
                                '5' => $module->l('5 items'),
                                '6' => $module->l('6 items'),
                            ],
                            'default' => '3',
                        ],
                        'slider_items_mobile' => [
                            'type' => 'select',
                            'label' => $module->l('Items per slide (mobile)'),
                            'choices' => [
                                '1' => $module->l('1 item'),
                                '2' => $module->l('2 items'),
                                '3' => $module->l('3 items'),
                            ],
                            'default' => '1',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Reassurances',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Upload image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'icon' => [
                            'type' => 'select',
                            'label' => $module->l('Select an icon'),
                            'choices' => EverblockTools::getAvailableSvgIcons(),
                            'default' => 'payment.svg',
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => $module->l('Free delivery'),
                        ],
                        'text' => [
                            'type' => 'editor',
                            'label' => $module->l('Short text'),
                            'default' => $module->l('On all orders over 50€'),
                        ],
                        'background_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block background color')
                        ],
                        'text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block text color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Call to Action'),
                'description' => $module->l('Display a title, some content and a call-to-action button.'),
                'code' => 'everblock_cta',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $ctaTemplate,
                ],
                'repeater' => [
                    'name' => 'CTA',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'editor',
                            'label' => 'Title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Block content',
                            'default' => '[llorem]',
                        ],
                        'cta_link' => [
                            'type' => 'text',
                            'label' => $module->l('CTA Link'),
                            'default' => '#',
                        ],
                        'cta_text' => [
                            'type' => 'text',
                            'label' => $module->l('CTA Button Text'),
                            'default' => $module->l('Discover now'),
                        ],
                        'title' => [
                            'type' => 'editor',
                            'label' => $module->l('Right column title'),
                            'default' => '',
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Right column description'),
                            'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec placerat, risus quis lobortis aliquam...',
                        ],
                        'text_highlight_1' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight word 1'),
                            'default' => '',
                        ],
                        'text_highlight_2' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight word 2'),
                            'default' => '',
                        ],
                        'background_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Background image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'parallax' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Parallax mode'),
                            'default' => false,
                        ],
                        'background_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block background color')
                        ],
                        'text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block text color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Video iframe'),
                'description' => $module->l('Add video iframe using embed link'),
                'code' => 'everblock_iframe',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $iframeTemplate,
                ],
                'repeater' => [
                    'name' => 'Video',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'iframe_link' => [
                            'type' => 'text',
                            'label' => $module->l('Iframe embed code (like https://www.youtube.com/embed/jfKfPfyJRdk)'),
                            'default' => 'https://www.youtube.com/embed/jfKfPfyJRdk',
                        ],
                        'iframe_source' => [
                            'type' => 'radio_group',
                            'label' => $module->l('Iframe source'),
                            'default' => 'youtube',
                            'choices' => [
                                 'youtube' => 'youtube',
                                'vimeo' => 'vimeo',
                                'dailymotion' => 'dailymotion',
                                'vidyard' => 'vidyard',
                            ]
                        ],
                        'height' => [
                            'type' => 'text',
                            'label' => $module->l('Iframe height (like 250px)'),
                            'default' => '500px',
                        ],
                        'width' => [
                            'type' => 'text',
                            'label' => $module->l('Iframe width (like 250px or 50%)'),
                            'default' => '100%',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Scroll video'),
                'description' => $module->l('Autoplay video when block is visible'),
                'code' => 'everblock_scroll_video',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $scrollVideoTemplate,
                ],
                'repeater' => [
                    'name' => 'Video',
                    'nameFrom' => 'video_url',
                    'groups' => static::appendSpacingFields([
                        'video_url' => [
                            'type' => 'text',
                            'label' => $module->l('Video URL'),
                            'default' => '',
                        ],
                        'thumbnail' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Thumbnail image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'width' => [
                            'type' => 'text',
                            'label' => $module->l('Video width (e.g., 100% or 400px)'),
                            'default' => '100%',
                        ],
                        'height' => [
                            'type' => 'text',
                            'label' => $module->l('Video height (e.g., 360px)'),
                            'default' => '360px',
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => '',
                        ],
                        'content' => [
                            'type' => 'textarea',
                            'label' => $module->l('Text'),
                            'default' => '',
                        ],
                        'button_label' => [
                            'type' => 'text',
                            'label' => $module->l('Button label'),
                            'default' => '',
                        ],
                        'button_url' => [
                            'type' => 'text',
                            'label' => $module->l('Button URL'),
                            'default' => '',
                        ],
                        'overlay_color' => [
                            'type' => 'text',
                            'label' => $module->l('Overlay color'),
                            'default' => '#000000',
                        ],
                        'overlay_opacity' => [
                            'type' => 'text',
                            'label' => $module->l('Overlay opacity (0-1)'),
                            'default' => '0.5',
                        ],
                        'loop' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Infinite loop'),
                            'default' => false,
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Layout'),
                'description' => $module->l('Add layout'),
                'code' => 'everblock_layout',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $layoutTemplate
                ],
                'repeater' => [
                    'name' => 'Menu',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Layout title',
                            'default' => '',
                        ],
                        'order' => [
                            'type' => 'select',
                            'label' => 'Layout width', 
                            'default' => 'col-12',
                            'choices' => [
                                'col-12' => '100%',
                                'col-12 col-md-6' => '50%',
                                'col-12 col-md-4' => '33,33%',
                                'col-12 col-md-3' => '25%',
                                'col-12 col-md-2' => '16,67%',
                            ]
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Layout image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'image_as_background' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Use image as background'),
                            'default' => false,
                        ],
                        'image_width' => [
                            'type' => 'text',
                            'label' => 'Image width (e.g., 100px or 50%)',
                            'default' => '100%',
                        ],
                        'image_height' => [
                            'type' => 'text',
                            'label' => 'Image height (e.g., 100px)',
                            'default' => 'auto',
                        ],
                        'parallax' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Parallax mode'),
                            'default' => false,
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Layout content',
                            'default' => '[llorem]',
                        ],
                        'link' => [
                            'type' => 'text',
                            'label' => 'Layout link',
                            'default' => '',
                        ],
                        'obfuscate' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Obfuscate link'),
                            'default' => '0',
                        ],
                        'target_blank' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Open in new tab (only if not obfuscated)'),
                            'default' => '0',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Featured category'),
                'description' => $module->l('Add featured category'),
                'code' => 'everblock_category_highlight',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $featuredCategoryTemplate
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'desktop_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Desktop columns'),
                            'default' => '2',
                            'choices' => [
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                                '5' => '5',
                                '6' => '6',
                            ],
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Menu',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Featured category title',
                            'default' => '',
                        ],
                        'category' => [
                            'type' => 'selector',
                            'label' => $module->l('Featured category'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'default' => \HelperBuilder::getRandomCategory((int) $context->language->id, (int) $context->shop->id),
                            'force_default_value' => true,
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Featured category image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'desktop_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Desktop image (optional)'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'mobile_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Mobile image (optional)'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'image_width' => [
                            'type' => 'text',
                            'label' => 'Image width (e.g., 100px or 50%)',
                            'default' => '100%',
                        ],
                        'image_height' => [
                            'type' => 'text',
                            'label' => 'Image height (e.g., 100px)',
                            'default' => 'auto',
                        ],
                        'link' => [
                            'type' => 'text',
                            'label' => 'Layout link',
                            'default' => '',
                        ],
                        'obfuscate' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Obfuscate link'),
                            'default' => '0',
                        ],
                        'target_blank' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Open in new tab (only if not obfuscated)'),
                            'default' => '0',
                        ],
                        'title_position_desktop' => [
                            'type' => 'select',
                            'label' => $module->l('Title position (desktop)'),
                            'choices' => [
                                'center' => $module->l('Center'),
                                'top' => $module->l('Center top'),
                                'bottom' => $module->l('Center bottom'),
                                'left' => $module->l('Center left'),
                                'right' => $module->l('Center right'),
                                'top-left' => $module->l('Top left'),
                                'top-right' => $module->l('Top right'),
                                'bottom-left' => $module->l('Bottom left'),
                                'bottom-right' => $module->l('Bottom right'),
                            ],
                            'default' => 'center',
                        ],
                        'title_position_mobile' => [
                            'type' => 'select',
                            'label' => $module->l('Title position (mobile)'),
                            'choices' => [
                                'center' => $module->l('Center'),
                                'top' => $module->l('Center top'),
                                'bottom' => $module->l('Center bottom'),
                                'left' => $module->l('Center left'),
                                'right' => $module->l('Center right'),
                                'top-left' => $module->l('Top left'),
                                'top-right' => $module->l('Top right'),
                                'bottom-left' => $module->l('Bottom left'),
                                'bottom-right' => $module->l('Bottom right'),
                            ],
                            'default' => 'center',
                        ],
                        'title_font_size_desktop' => [
                            'type' => 'text',
                            'label' => $module->l('Title font size (desktop)'),
                            'default' => '',
                        ],
                        'title_font_size_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Title font size (mobile)'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'margin_left_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Mobile margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Mobile margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Mobile margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Mobile margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Category price list'),
                'description' => $module->l('Display categories with starting price'),
                'code' => 'everblock_category_price',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $categoryPriceTemplate,
                ],
                'repeater' => [
                    'name' => 'Category',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'category' => [
                            'type' => 'selector',
                            'label' => $module->l('Category'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'default' => \HelperBuilder::getRandomCategory((int) $context->language->id, (int) $context->shop->id),
                            'force_default_value' => true,
                        ],
                        'name' => [
                            'type' => 'text',
                            'label' => $module->l('Custom title'),
                            'default' => '',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Custom image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Modal'),
                'description' => $module->l('Add custom modal'),
                'code' => 'everblock_modal',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $modalTemplate,
                ],
                'repeater' => [
                    'name' => $module->l('Modal title'),
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Modal title',
                            'default' => '',
                        ],
                        'open_name' => [
                            'type' => 'text',
                            'label' => 'Open modal button text',
                            'default' => $module->l('Open'),
                        ],
                        'close_name' => [
                            'type' => 'text',
                            'label' => 'Close modal button text',
                            'default' => $module->l('Close'),
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Modal content',
                            'default' => '[llorem]',
                        ],
                        'auto_trigger_modal' => [
                            'type' => 'radio_group',
                            'label' => $module->l('Auto trigger modal'),
                            'default' => 'No',
                            'choices' => [
                                '1' => 'No',
                                '2' => 'Auto',
                            ]
                        ],
                        'modal_title_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Modal title color')
                        ],
                        'open_modal_button_bg_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Open modal button background color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Exit intent offer'),
                'description' => $module->l('Display popup when leaving the page'),
                'code' => 'everblock_exit_intent',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $exitIntentTemplate,
                ],
                'repeater' => [
                    'name' => $module->l('Offer'),
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => '',
                        ],
                        'message' => [
                            'type' => 'textarea',
                            'label' => $module->l('Message'),
                            'default' => '',
                        ],
                        'cta_label' => [
                            'type' => 'text',
                            'label' => $module->l('CTA label'),
                            'default' => '',
                        ],
                        'cta_url' => [
                            'type' => 'text',
                            'label' => $module->l('CTA URL'),
                            'default' => '#',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->displayName . ' Shortcodes',
                'description' => $module->l('Ever block shortcodes'),
                'code' => 'everblock_shortcode',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $shortcodeTemplate,
                ],
                'repeater' => [
                    'name' => $module->l('Shortcode title'),
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Shortcode title',
                            'default' => '',
                        ],
                        'shortcode' => [
                            'type' => 'select',
                            'label' => 'Choose a value',
                            'default' => '',
                            'choices' => $prettyBlocksShortcodes
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Simple image'),
                'description' => $module->l('Add simple image'),
                'code' => 'everblock_img',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $imgTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'slider_autoplay' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable auto scroll'),
                            'default' => 0,
                        ],
                        'slider_infinite' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable infinite loop'),
                            'default' => 0,
                        ],
                        'slider_autoplay_delay' => [
                            'type' => 'text',
                            'label' => $module->l('Auto scroll delay (ms)'),
                            'default' => 5000,
                        ],
                        'slider_items' => [
                            'type' => 'text',
                            'label' => $module->l('Number of images in slider'),
                            'default' => 3,
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => $module->l('Image title'),
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Image title',
                            'default' => '',
                        ],
                        'image_width' => [
                            'type' => 'text',
                            'label' => $module->l('Image width (e.g., 100px or 50%)'),
                            'default' => '100%',
                        ],
                        'image_height' => [
                            'type' => 'text',
                            'label' => $module->l('Image height (e.g., 100px)'),
                            'default' => 'auto',
                        ],
                        'alt' => [
                            'type' => 'text',
                            'label' =>  $module->l('alt attribute'),
                            'default' => $module->l('My alt attribute')
                        ],
                        'url' => [
                            'type' => 'text',
                            'label' =>  $module->l('URL'),
                            'default' =>  $module->l('#')
                        ],
                        'banner' => [
                            'type' => 'fileupload',
                            'label' => 'Images',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'banner_mobile' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Mobile image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'start_date' => [
                            'type' => 'text',
                            'label' => $module->l('Start date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'end_date' => [
                            'type' => 'text',
                            'label' => $module->l('End date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'text_highlight_1' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight text 1'),
                            'default' => '',
                        ],
                        'text_highlight_2' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight text 2'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Row'),
                'description' => $module->l('Add row'),
                'code' => 'everblock_row',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $rowTemplate
                ],
                'repeater' => [
                    'name' => 'Menu',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Row title',
                            'default' => '',
                        ],
                        'columns_mobile' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on mobile'),
                            'default' => '1',
                            'choices' => static::getColumnChoices($module),
                        ],
                        'columns_tablet' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on tablet'),
                            'default' => '2',
                            'choices' => static::getColumnChoices($module),
                        ],
                        'columns_desktop' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on desktop'),
                            'default' => '3',
                            'choices' => static::getColumnChoices($module),
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Text and image'),
                'description' => $module->l('Add image and text layout'),
                'code' => 'everblock_text_and_image',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $textAndImageTemplate
                ],
                'repeater' => [
                    'name' => 'Menu',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Block title',
                            'default' => '',
                        ],
                        'order' => [
                            'type' => 'select',
                            'label' => 'Block order', 
                            'default' => '1',
                            'choices' => [
                                '1' => 'First image, then text',
                                '2' => 'First text, then image',
                            ]
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Layout image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'image_mobile' => [
                            'type' => 'fileupload',
                            'label' => 'Mobile layout image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'image_width' => [
                            'type' => 'text',
                            'label' => 'Image width (e.g., 100px or 50%)',
                            'default' => '100%',
                        ],
                        'image_height' => [
                            'type' => 'text',
                            'label' => 'Image height (e.g., 100px)',
                            'default' => 'auto',
                        ],
                        'parallax' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Parallax mode'),
                            'default' => false,
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Layout content',
                            'default' => '[llorem]',
                        ],
                        'link' => [
                            'type' => 'text',
                            'label' => 'Layout link',
                            'default' => '',
                        ],
                        'obfuscate' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Obfuscate link'),
                            'default' => '0',
                        ],
                        'target_blank' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Open in new tab (only if not obfuscated)'),
                            'default' => '0',
                        ],
                        'text_position_desktop' => [
                            'type' => 'select',
                            'label' => $module->l('Text position (desktop)'),
                            'choices' => [
                                'start' => $module->l('Top'),
                                'center' => $module->l('Center'),
                                'end' => $module->l('Bottom'),
                            ],
                            'default' => 'center',
                        ],
                        'text_position_mobile' => [
                            'type' => 'select',
                            'label' => $module->l('Text position (mobile)'),
                            'choices' => [
                                'start' => $module->l('Top'),
                                'center' => $module->l('Center'),
                                'end' => $module->l('Bottom'),
                            ],
                            'default' => 'center',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Gallery'),
                'description' => $module->l('Show image gallery (images must have same size)'),
                'code' => 'everblock_gallery',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $galleryTemplate,
                ],
                'repeater' => [
                    'name' => 'Image name',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Image title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Masonry gallery'),
                'description' => $module->l('Show masonry style image gallery'),
                'code' => 'everblock_masonry_gallery',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $masonryGalleryTemplate,
                ],
                'repeater' => [
                    'name' => 'Image name',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Image title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'image_width' => [
                            'type' => 'text',
                            'label' => $module->l('Image width (e.g., 100px or 50%)'),
                            'default' => 'auto',
                        ],
                        'image_height' => [
                            'type' => 'text',
                            'label' => $module->l('Image height (e.g., 100px)'),
                            'default' => 'auto',
                        ],
                        'alt' => [
                            'type' => 'text',
                            'label' =>  $module->l('alt attribute'),
                            'default' => $module->l('My alt attribute')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Video gallery'),
                'description' => $module->l('Display a gallery of videos'),
                'code' => 'everblock_video_gallery',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $videoGalleryTemplate,
                ],
                'repeater' => [
                    'name' => 'Video',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'thumbnail' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Thumbnail'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'video_url' => [
                            'type' => 'text',
                            'label' => $module->l('Video URL'),
                            'default' => '',
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => '',
                        ],
                        'description' => [
                            'type' => 'textarea',
                            'label' => $module->l('Description'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Product video gallery'),
                'description' => $module->l('Display videos with related products'),
                'code' => 'everblock_video_products',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $videoProductsTemplate,
                ],
                'repeater' => [
                    'name' => 'Video',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'thumbnail' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Thumbnail'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'video_url' => [
                            'type' => 'text',
                            'label' => $module->l('Video URL'),
                            'default' => '',
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => '',
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Description'),
                            'default' => '',
                        ],
                        'product_ids' => [
                            'type' => 'text',
                            'label' => $module->l('Product IDs'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Alert message'),
                'description' => $module->l('Add alert message'),
                'code' => 'everblock_alert',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $alertTemplate,
                ],
                'repeater' => [
                    'name' => 'Message name',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Message title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Alert message content',
                            'default' => '[llorem]',
                        ],
                        'alert_type' => [
                            'type' => 'radio_group',
                            'label' => $module->l('Alert type'),
                            'default' => 'primary',
                            'choices' => [
                                'primary' => 'primary',
                                'secondary' => 'secondary',
                                'success' => 'success',
                                'danger' => 'danger',
                                'warning' => 'warning',
                                'info' => 'info',
                                'light' => 'light',
                                'dark' => 'dark',
                            ]
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Google reviews'),
                'description' => $module->l('Display your Google Business reviews.'),
                'code' => 'everblock_google_reviews',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $googleReviewsTemplate,
                ],
                'repeater' => [
                    'name' => 'Feed',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => $module->l('What our customers say'),
                        ],
                        'intro' => [
                            'type' => 'editor',
                            'label' => $module->l('Introductory text'),
                            'default' => '',
                        ],
                        'columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '3',
                        ],
                        'api_key_override' => [
                            'type' => 'text',
                            'label' => $module->l('Override API key'),
                            'default' => '',
                        ],
                        'place_id_override' => [
                            'type' => 'text',
                            'label' => $module->l('Override Place ID'),
                            'default' => '',
                        ],
                        'limit_override' => [
                            'type' => 'text',
                            'label' => $module->l('Override review limit'),
                            'default' => (string) ((int) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_LIMIT') ?: 5),
                        ],
                        'min_rating_override' => [
                            'type' => 'text',
                            'label' => $module->l('Override minimum rating'),
                            'default' => (string) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING'),
                        ],
                        'sort_override' => [
                            'type' => 'select',
                            'label' => $module->l('Override sort order'),
                            'default' => (string) (Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SORT') ?: 'most_relevant'),
                            'choices' => [
                                'most_relevant' => $module->l('Most relevant'),
                                'newest' => $module->l('Most recent'),
                            ],
                        ],
                        'show_rating_override' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Show rating summary'),
                            'default' => (bool) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING'),
                        ],
                        'show_avatar_override' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Show reviewer avatars'),
                            'default' => (bool) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR'),
                        ],
                        'show_cta_override' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Show CTA button'),
                            'default' => (bool) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA'),
                        ],
                        'cta_label_override' => [
                            'type' => 'text',
                            'label' => $module->l('CTA label'),
                            'default' => (string) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL'),
                        ],
                        'cta_url_override' => [
                            'type' => 'text',
                            'label' => $module->l('CTA URL'),
                            'default' => (string) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL'),
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Testimonials'),
                'description' => $module->l('Show custom testimonials'),
                'code' => 'everblock_testimonial',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $testimonialTemplate,
                ],
                'repeater' => [
                    'name' => 'Tab',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'testimonial title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Tab content',
                            'default' => '[llorem]',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Testimonials slider'),
                'description' => $module->l('Display testimonials in a carousel'),
                'code' => 'everblock_testimonial_slider',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $testimonialSliderTemplate,
                ],
                'repeater' => [
                    'name' => 'Slide',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'testimonial title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Tab content',
                            'default' => '[llorem]',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Button'),
                'description' => $module->l('Add simple button'),
                'code' => 'everblock_button',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $buttonTemplate,
                ],
                'repeater' => [
                    'name' => 'Tab',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Button title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'button_type' => [
                            'type' => 'radio_group', // type of field
                            'label' => $module->l('Button type'), // label to display
                            'default' => 'primary', // default value (String)
                            'choices' => [
                                'primary' => 'primary',
                                'secondary' => 'secondary',
                                'success' => 'success',
                                'danger' => 'danger',
                                'warning' => 'warning',
                                'info' => 'info',
                                'light' => 'light',
                                'dark' => 'dark',
                            ]
                        ],
                        'button_content' => [
                            'type' => 'text',
                            'label' => $module->l('Button text'),
                            'default' => '',
                        ],
                        'button_link' => [
                            'type' => 'text',
                            'label' => $module->l('Button link'),
                            'default' => '',
                        ],
                        'color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '#fff',
                            'label' => $module->l('Button text color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Google map'),
                'description' => $module->l('Display map in an iframe'),
                'code' => 'everblock_gmap',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $gmapTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'iframe' => [
                            'type' => 'text',
                            'label' => $module->l('Map iframe link'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];

            $blocks[] = [
                'name' => $module->l('Iframe'),
                'description' => $module->l('Display custom iframe content'),
                'code' => 'everblock_custom_iframe',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $customIframeTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'iframe_src' => [
                            'type' => 'text',
                            'label' => $module->l('Iframe source URL'),
                            'default' => '',
                        ],
                        'iframe_width' => [
                            'type' => 'text',
                            'label' => $module->l('Iframe width (like 100% or 600px)'),
                            'default' => '100%',
                        ],
                        'iframe_height' => [
                            'type' => 'text',
                            'label' => $module->l('Iframe height (like 400 or 400px)'),
                            'default' => '400',
                        ],
                        'allow_fullscreen' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Allow fullscreen'),
                            'default' => true,
                        ],
                        'loading_behavior' => [
                            'type' => 'text',
                            'label' => $module->l('Loading attribute (like lazy)'),
                            'default' => 'lazy',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];

            $blocks[] = [
                'name' => $module->l('Images slider'),
                'description' => $module->l('Show images slider (images must have same size)'),
                'code' => 'everblock_img_slider',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $imgSliderTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider_autoplay' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable auto scroll'),
                            'default' => 1,
                        ],
                        'slider_autoplay_delay' => [
                            'type' => 'text',
                            'label' => $module->l('Auto scroll delay (ms)'),
                            'default' => 5000,
                        ],
                        'slider_transition_speed' => [
                            'type' => 'text',
                            'label' => $module->l('Transition speed (ms)'),
                            'default' => 500,
                        ],
                        'slider_pause_on_hover' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Pause on hover'),
                            'default' => 1,
                        ],
                        'slider_show_arrows' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Show navigation arrows'),
                            'default' => 1,
                        ],
                        'slider_show_dots' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Show pagination dots'),
                            'default' => 1,
                        ],
                        'slider_disable_lazyload' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Disable lazy loading'),
                            'default' => 0,
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Image',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Layout image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'image_mobile' => [
                            'type' => 'fileupload',
                            'label' => 'Mobile layout image',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'name' => [
                            'type' => 'text',
                            'label' => 'Image title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'link' => [
                            'type' => 'text',
                            'label' => 'Image link',
                            'default' => '',
                        ],
                        'obfuscate' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Obfuscate link'),
                            'default' => '0',
                        ],
                        'target_blank' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Open in new tab (only if not obfuscated)'),
                            'default' => '0',
                        ],
                        'start_date' => [
                            'type' => 'text',
                            'label' => $module->l('Start date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'end_date' => [
                            'type' => 'text',
                            'label' => $module->l('End date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Links list'),
                'description' => $module->l('Display a list of links'),
                'code' => 'everblock_link_list',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $linkListTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Links'),
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Link',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => $module->l('Link text'),
                            'default' => $module->l('My link'),
                        ],
                        'url' => [
                            'type' => 'text',
                            'label' => $module->l('Link URL'),
                            'default' => '#',
                        ],
                        'target_blank' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Open in new tab'),
                            'default' => '0',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];

            $blocks[] = [
                'name' => $module->l('Downloads list'),
                'description' => $module->l('Display a list of downloadable resources'),
                'code' => 'everblock_downloads',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $downloadsTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Downloads'),
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Download',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => $module->l('My file'),
                        ],
                        'file' => [
                            'type' => 'fileupload',
                            'label' => $module->l('File'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Description'),
                            'default' => '',
                        ],
                        'icon' => [
                            'type' => 'select',
                            'label' => $module->l('Select an icon'),
                            'choices' => EverblockTools::getAvailableSvgIcons(),
                            'default' => 'file.svg',
                        ],
                    ], $module),
                ],
            ];

            $blocks[] = [
                'name' => $module->l('Podcasts'),
                'description' => $module->l('Display podcasts'),
                'code' => 'everblock_podcasts',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $podcastsTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Podcasts'),
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Podcast',
                    'nameFrom' => 'episode_title',
                    'groups' => static::appendSpacingFields([
                        'cover_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Cover image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'episode_title' => [
                            'type' => 'text',
                            'label' => $module->l('Episode title'),
                            'default' => '',
                        ],
                        'audio_url' => [
                            'type' => 'text',
                            'label' => $module->l('Audio URL'),
                            'default' => '',
                        ],
                        'duration' => [
                            'type' => 'text',
                            'label' => $module->l('Duration'),
                            'default' => '',
                        ],
                        'description' => [
                            'type' => 'textarea',
                            'label' => $module->l('Description'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];

            $blocks[] = [
                'name' => $module->l('Page sharer'),
                'description' => $module->l('Display social share buttons'),
                'code' => 'everblock_sharer',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $sharerTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Social links'),
                'description' => $module->l('Display custom links to social networks'),
                'code' => 'everblock_social_links',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $socialLinksTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'icon_color' => [
                            'type' => 'color',
                            'label' => $module->l('Icon color'),
                            'default' => '',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Social link',
                    'nameFrom' => 'url',
                    'groups' => static::appendSpacingFields([
                        'url' => [
                            'type' => 'text',
                            'label' => $module->l('Link URL'),
                            'default' => '#',
                        ],
                        'icon' => [
                            'type' => 'select',
                            'label' => $module->l('Select an icon'),
                            'choices' => EverblockTools::getAvailableSvgIcons(),
                            'default' => 'facebook.svg',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Brands'),
                'description' => $module->l('Display selected brands side by side'),
                'code' => 'everblock_brands',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $brandListTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'brands_per_slide' => [
                            'type' => 'select',
                            'label' => $module->l('Number of brands per slide'),
                            'default' => '4',
                            'choices' => [
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                                '6' => '6',
                            ],
                        ],
                        'show_all_brands_button' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Show "All brands" button'),
                            'default' => 0,
                        ],
                        'all_brands_button_label' => [
                            'type' => 'text',
                            'label' => $module->l('All brands button text'),
                            'default' => $module->l('Voir toutes les marques'),
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Brand',
                    'nameFrom' => 'brand',
                    'groups' => static::appendSpacingFields([
                        'brand' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a brand'),
                            'collection' => 'Manufacturer',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Product highlight'),
                'description' => $module->l('Highlight one product with custom text'),
                'code' => 'everblock_product_highlight',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $productHighlightTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'id_product' => [
                            'type' => 'text',
                            'label' => $module->l('Product ID'),
                            'default' => '',
                        ],
                        'badge_text' => [
                            'type' => 'text',
                            'label' => $module->l('Badge text'),
                            'default' => $module->l('Our current favorite!'),
                        ],
                        'custom_text' => [
                            'type' => 'editor',
                            'label' => $module->l('Custom text'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Category tabs'),
                'description' => $module->l('Display products from categories inside tabs'),
                'code' => 'everblock_category_tabs',
                'tab' => 'general', 
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $categoryTabsTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([], $module),
                ],
                'repeater' => [
                    'name' => 'Category',
                    'nameFrom' => 'name',
                    'groups' => static::appendSpacingFields([
                        'name' => [
                            'type' => 'text',
                            'label' => 'Tab title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'slider_image_1' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Slider image 1'),
                            'accept' => 'image/svg+xml,image/webp,image/png,image/jpeg,image/gif',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'slider_image_2' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Slider image 2'),
                            'accept' => 'image/svg+xml,image/webp,image/png,image/jpeg,image/gif',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'slider_image_3' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Slider image 3'),
                            'accept' => 'image/svg+xml,image/webp,image/png,image/jpeg,image/gif',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'slider_image_4' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Slider image 4'),
                            'accept' => 'image/svg+xml,image/webp,image/png,image/jpeg,image/gif',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'html_before_products' => [
                            'type' => 'editor',
                            'label' => $module->l('HTML content before products'),
                            'default' => '',
                        ],
                        'id_categories' => [
                            'type' => 'selector',
                            'label' => $module->l('Categories'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'multiple' => true,
                            'default' => [],
                        ],
                        'order_by' => [
                            'type' => 'select',
                            'label' => $module->l('Order by'),
                            'choices' => [
                                'id_product' => $module->l('ID'),
                                'date_add' => $module->l('Date added'),
                                'price' => $module->l('Price'),
                            ],
                            'default' => 'id_product',
                        ],
                        'order_way' => [
                            'type' => 'select',
                            'label' => $module->l('Order way'),
                            'choices' => [
                                'ASC' => $module->l('Ascending'),
                                'DESC' => $module->l('Descending'),
                            ],
                            'default' => 'ASC',
                        ],
                        'nb_products' => [
                            'type' => 'text',
                            'label' => $module->l('Number of products to display (0 for default)'),
                            'default' => 0,
                        ],
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'slider_devices' => [
                            'type' => 'select',
                            'label' => $module->l('Enable slider on'),
                            'choices' => [
                                'both' => $module->l('Desktop and mobile'),
                                'desktop' => $module->l('Desktop only'),
                                'mobile' => $module->l('Mobile only'),
                            ],
                            'default' => 'both',
                        ],
                        'products_per_slide_desktop' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on desktop'),
                            'default' => '4',
                        ],
                        'products_per_slide_tablet' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on tablet'),
                            'default' => '2',
                        ],
                        'products_per_slide_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on mobile'),
                            'default' => '1',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Counters'),
                'description' => $module->l('Display animated counters'),
                'code' => 'everblock_counters',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $countersTemplate,
                ],
                'repeater' => [
                    'name' => 'Counter',
                    'nameFrom' => 'label',
                    'groups' => static::appendSpacingFields([
                        'icon' => [
                            'type' => 'select',
                            'label' => $module->l('Select an icon'),
                            'choices' => EverblockTools::getAvailableSvgIcons(),
                            'default' => 'payment.svg',
                        ],
                        'value' => [
                            'type' => 'text',
                            'label' => $module->l('Value'),
                            'default' => '100',
                        ],
                        'label' => [
                            'type' => 'text',
                            'label' => $module->l('Label'),
                            'default' => '',
                        ],
                        'animation_speed' => [
                            'type' => 'text',
                            'label' => $module->l('Animation speed (ms)'),
                            'default' => '2000',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Countdown'),
                'description' => $module->l('Display a countdown timer'),
                'code' => 'everblock_countdown',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $countdownTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'target_date' => [
                            'type' => 'text',
                            'label' => $module->l('Target date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'completion_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message displayed when the countdown ends'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Bootstrap cards'),
                'description' => $module->l('Display content cards'),
                'code' => 'everblock_card',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $cardTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'center_cards' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Center cards in container'),
                            'default' => 0,
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Card',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Card title'),
                            'default' => '',
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => $module->l('Card content'),
                            'default' => '',
                        ],
                        'button_text' => [
                            'type' => 'text',
                            'label' => $module->l('Button text'),
                            'default' => $module->l('En savoir plus'),
                        ],
                        'button_link' => [
                            'type' => 'text',
                            'label' => $module->l('Button link'),
                            'default' => '#',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Image',
                            'accept' => 'image/svg+xml,image/webp,image/png,image/jpeg,image/gif',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'alt' => [
                            'type' => 'text',
                            'label' => $module->l('Image alt text'),
                            'default' => '',
                        ],
                        'image_width' => [
                            'type' => 'text',
                            'label' => $module->l('Image width'),
                            'default' => '',
                        ],
                        'image_height' => [
                            'type' => 'text',
                            'label' => $module->l('Image height'),
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Cover block'),
                'description' => $module->l('Background image with title, text and two buttons'),
                'code' => 'everblock_cover',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $coverTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns per row'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '1',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Cover',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => '',
                        ],
                        'title_tag' => [
                            'type' => 'select',
                            'label' => $module->l('Heading level'),
                            'choices' => [
                                'h1' => 'H1',
                                'h2' => 'H2',
                                'h3' => 'H3',
                                'h4' => 'H4',
                                'h5' => 'H5',
                                'h6' => 'H6',
                            ],
                            'default' => 'h2',
                        ],
                        'title_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'label' => $module->l('Title color'),
                            'default' => '#000000',
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => $module->l('Content'),
                            'default' => '',
                        ],
                        'cover_link' => [
                            'type' => 'text',
                            'label' => $module->l('Full cover link'),
                            'default' => '',
                        ],
                        'background_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Background image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'background_image_mobile' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Mobile background image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'parallax' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable parallax effect'),
                            'default' => false,
                        ],
                        'btn1_text' => [
                            'type' => 'text',
                            'label' => $module->l('Button 1 text'),
                            'default' => '',
                        ],
                        'btn1_link' => [
                            'type' => 'text',
                            'label' => $module->l('Button 1 link'),
                            'default' => '',
                        ],
                        'btn1_type' => [
                            'type' => 'radio_group',
                            'label' => $module->l('Button 1 type'),
                            'default' => 'primary',
                            'choices' => [
                                'primary' => 'primary',
                                'secondary' => 'secondary',
                                'success' => 'success',
                                'danger' => 'danger',
                                'warning' => 'warning',
                                'info' => 'info',
                                'light' => 'light',
                                'dark' => 'dark',
                                'outline-primary' => 'outline-primary',
                                'outline-secondary' => 'outline-secondary',
                                'outline-success' => 'outline-success',
                                'outline-danger' => 'outline-danger',
                                'outline-warning' => 'outline-warning',
                                'outline-info' => 'outline-info',
                                'outline-light' => 'outline-light',
                                'outline-dark' => 'outline-dark',
                            ],
                        ],
                        'btn2_text' => [
                            'type' => 'text',
                            'label' => $module->l('Button 2 text'),
                            'default' => '',
                        ],
                        'btn2_link' => [
                            'type' => 'text',
                            'label' => $module->l('Button 2 link'),
                            'default' => '',
                        ],
                        'btn2_type' => [
                            'type' => 'radio_group',
                            'label' => $module->l('Button 2 type'),
                            'default' => 'primary',
                            'choices' => [
                                'primary' => 'primary',
                                'secondary' => 'secondary',
                                'success' => 'success',
                                'danger' => 'danger',
                                'warning' => 'warning',
                                'info' => 'info',
                                'light' => 'light',
                                'dark' => 'dark',
                                'outline-primary' => 'outline-primary',
                                'outline-secondary' => 'outline-secondary',
                                'outline-success' => 'outline-success',
                                'outline-danger' => 'outline-danger',
                                'outline-warning' => 'outline-warning',
                                'outline-info' => 'outline-info',
                                'outline-light' => 'outline-light',
                                'outline-dark' => 'outline-dark',
                            ],
                        ],
                        'content_position_desktop' => [
                            'type' => 'select',
                            'label' => $module->l('Content position (desktop)'),
                            'choices' => [
                                'center' => $module->l('Center'),
                                'top' => $module->l('Top'),
                                'bottom' => $module->l('Bottom'),
                                'left' => $module->l('Left'),
                                'right' => $module->l('Right'),
                                'top-left' => $module->l('Top left'),
                                'top-right' => $module->l('Top right'),
                                'center-left' => $module->l('Center left'),
                                'center-right' => $module->l('Center right'),
                                'bottom-left' => $module->l('Bottom left'),
                                'bottom-right' => $module->l('Bottom right'),
                            ],
                            'default' => 'center',
                        ],
                        'content_position_mobile' => [
                            'type' => 'select',
                            'label' => $module->l('Content position (mobile)'),
                            'choices' => [
                                'center' => $module->l('Center'),
                                'top' => $module->l('Top'),
                                'bottom' => $module->l('Bottom'),
                                'left' => $module->l('Left'),
                                'right' => $module->l('Right'),
                                'top-left' => $module->l('Top left'),
                                'top-right' => $module->l('Top right'),
                                'center-left' => $module->l('Center left'),
                                'center-right' => $module->l('Center right'),
                                'bottom-left' => $module->l('Bottom left'),
                                'bottom-right' => $module->l('Bottom right'),
                            ],
                            'default' => 'center',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Table of contents'),
                'description' => $module->l('Display a summary with anchored sections'),
                'code' => 'everblock_toc',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $tocTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Summary title'),
                            'default' => $module->l('Summary'),
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Section',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'anchor' => [
                            'type' => 'text',
                            'label' => $module->l('Anchor ID'),
                            'default' => 'section-1',
                        ],
                        'category' => [
                            'type' => 'text',
                            'label' => $module->l('Category'),
                            'default' => '',
                        ],
                        'subcategory' => [
                            'type' => 'text',
                            'label' => $module->l('Sub-category'),
                            'default' => '',
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => $module->l('Section 1'),
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => $module->l('Content'),
                            'default' => '[llorem]',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Image map with markers'),
                'description' => $module->l('Display a clickable map image with markers'),
                'code' => 'everblock_image_map',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $imageMapTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Title'),
                            'default' => '',
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => $module->l('Content'),
                            'default' => '',
                        ],
                        'button_text' => [
                            'type' => 'text',
                            'label' => $module->l('Button text'),
                            'default' => $module->l('Find a store'),
                        ],
                        'button_link' => [
                            'type' => 'text',
                            'label' => $module->l('Button link'),
                            'default' => '#',
                        ],
                        'map_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Map image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Marker',
                    'nameFrom' => 'label',
                    'groups' => static::appendSpacingFields([
                        'label' => [
                            'type' => 'text',
                            'label' => $module->l('Marker label'),
                            'default' => '',
                        ],
                        'top' => [
                            'type' => 'text',
                            'label' => $module->l('Top position (e.g., 50%)'),
                            'default' => '0%',
                        ],
                        'left' => [
                            'type' => 'text',
                            'label' => $module->l('Left position (e.g., 50%)'),
                            'default' => '0%',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Selected products'),
                'description' => $module->l('Display selected products and optionally enable a Bootstrap slider'),
                'code' => 'everblock_product_selector',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $productSelectorTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'products_per_slide_desktop' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on desktop'),
                            'default' => 4,
                        ],
                        'products_per_slide_tablet' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on tablet'),
                            'default' => 2,
                        ],
                        'products_per_slide_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on mobile'),
                            'default' => 1,
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Product',
                    'nameFrom' => 'product',
                    'groups' => static::appendSpacingFields([
                        'product' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a product'),
                            'collection' => 'Product',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Guided product selector'),
                'description' => $module->l('Ask a few questions and redirect to a matching category'),
                'code' => 'everblock_guided_selector',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $guidedSelectorTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'fallback_shortcode' => [
                            'type' => 'editor',
                            'label' => $module->l('Fallback content (shortcodes allowed)'),
                            'default' => '[evercontactform_open][evercontact type="text" label="' . $module->l('Your name') . '"][evercontact type="email" label="' . $module->l('Your email') . '"][evercontact type="textarea" label="' . $module->l('Message') . '"][evercontact type="submit" label="' . $module->l('Send') . '"][evercontactform_close]',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Question',
                    'nameFrom' => 'question',
                    'groups' => static::appendSpacingFields([
                        'question' => [
                            'type' => 'text',
                            'label' => $module->l('Question'),
                            'default' => '',
                        ],
                        'answers' => [
                            'type' => 'textarea',
                            'label' => $module->l('Answers (one per line: "Answer label|Answer link")'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Guide selection'),
                'description' => $module->l('Display selected Everblock guides'),
                'code' => 'everblock_guides_selection',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $guidesSelectionTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => '',
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Introduction'),
                            'default' => '',
                        ],
                        'desktop_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on desktop'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '3',
                        ],
                        'tablet_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on tablet'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '2',
                        ],
                        'mobile_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on mobile'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '1',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Guide',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Custom title (optional)'),
                            'default' => '',
                        ],
                        'guide' => [
                            'type' => 'select',
                            'label' => $module->l('Choose a guide'),
                            'choices' => $everblockPageChoices,
                            'default' => '',
                        ],
                        'cover_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Cover image (optional)'),
                        ],
                        'summary' => [
                            'type' => 'textarea',
                            'label' => $module->l('Excerpt'),
                            'default' => '',
                        ],
                        'cta_text' => [
                            'type' => 'text',
                            'label' => $module->l('CTA label'),
                            'default' => $module->l('Read guide'),
                        ],
                        'target_blank' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Open in a new tab'),
                            'default' => 0,
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Latest guides'),
                'description' => $module->l('Display the most recent Everblock guides'),
                'code' => 'everblock_latest_guides',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $latestGuidesTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => '',
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Introduction'),
                            'default' => '',
                        ],
                        'limit' => [
                            'type' => 'text',
                            'label' => $module->l('Number of guides to display'),
                            'default' => 3,
                        ],
                        'desktop_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on desktop'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '3',
                        ],
                        'tablet_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on tablet'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '2',
                        ],
                        'mobile_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on mobile'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '1',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Guide pages'),
                'description' => $module->l('Display a curated list of CMS pages'),
                'code' => 'everblock_pages_guide',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $pagesGuideTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Our guides'),
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Introduction'),
                            'default' => '',
                        ],
                        'desktop_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on desktop'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '3',
                        ],
                        'tablet_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on tablet'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '2',
                        ],
                        'mobile_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on mobile'),
                            'choices' => static::getColumnChoices($module),
                            'default' => '1',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Page',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Page title (optional)'),
                            'default' => '',
                        ],
                        'page' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a CMS page'),
                            'collection' => 'CmsPage',
                            'selector' => '{id} - {meta_title}',
                            'default' => '',
                        ],
                        'summary' => [
                            'type' => 'textarea',
                            'label' => $module->l('Summary'),
                            'default' => '',
                        ],
                        'cta_text' => [
                            'type' => 'text',
                            'label' => $module->l('CTA label'),
                            'default' => $module->l('Read more'),
                        ],
                        'target_blank' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Open in a new tab'),
                            'default' => 0,
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Special event'),
                'description' => $module->l('Display a special event with countdown and CTA'),
                'code' => 'everblock_special_event',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $specialEventTemplate,
                ],
                'repeater' => [
                    'name' => 'Event',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'editor',
                            'label' => 'Title',
                            'default' => $module->l('Winter sale'),
                        ],
                        'background_image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Background image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'cta_text' => [
                            'type' => 'text',
                            'label' => $module->l('CTA Button Text'),
                            'default' => $module->l('Shop now'),
                        ],
                        'cta_link' => [
                            'type' => 'text',
                            'label' => $module->l('CTA Link'),
                            'default' => '#',
                        ],
                        'target_date' => [
                            'type' => 'text',
                            'label' => $module->l('Target date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'product_ids' => [
                            'type' => 'text',
                            'label' => $module->l('Product IDs'),
                            'default' => '',
                        ],
                        'background_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block background color'),
                        ],
                        'text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block text color'),
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Flash deals'),
                'description' => $module->l('Display temporary deals with a countdown timer'),
                'code' => 'everblock_flash_deals',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $flashDealsTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'sort_by' => [
                            'type' => 'select',
                            'label' => $module->l('Sort products by'),
                            'choices' => [
                                'price' => $module->l('Price'),
                                'date_add' => $module->l('Date added'),
                            ],
                            'default' => 'price',
                        ],
                        'sort_direction' => [
                            'type' => 'select',
                            'label' => $module->l('Sort direction'),
                            'choices' => [
                                'ASC' => $module->l('Ascending'),
                                'DESC' => $module->l('Descending'),
                            ],
                            'default' => 'ASC',
                        ],
                        'product_limit' => [
                            'type' => 'text',
                            'label' => $module->l('Number of products to display'),
                            'default' => 8,
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Best sellers'),
                'description' => $module->l('Display best-selling products with optional sliders'),
                'code' => 'everblock_best_sales',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $bestSalesTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider_desktop' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable desktop slider'),
                            'default' => 1,
                        ],
                        'slider_mobile' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable mobile slider'),
                            'default' => 1,
                        ],
                        'product_limit' => [
                            'type' => 'text',
                            'label' => $module->l('Number of best sellers to display'),
                            'default' => 10,
                        ],
                        'best_sales_category' => [
                            'type' => 'selector',
                            'label' => $module->l('Optional category for best sellers'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                        'items_per_slide_desktop' => [
                            'type' => 'text',
                            'label' => $module->l('Items per slide on desktop'),
                            'default' => 4,
                        ],
                        'items_per_slide_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Items per slide on mobile'),
                            'default' => 1,
                        ],
                        'show_best_sales_button' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Show best sellers button'),
                            'default' => 1,
                        ],
                        'button_url_override' => [
                            'type' => 'text',
                            'label' => $module->l('Override the best sellers button URL'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Category products'),
                'description' => $module->l('Display products from selected categories'),
                'code' => 'everblock_category_products',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $categoryProductsTemplate,
                ],
                'repeater' => [
                    'name' => 'Category',
                    'nameFrom' => 'category',
                    'groups' => static::appendSpacingFields([
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Upload image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'category' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a category'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                        'product_limit' => [
                            'type' => 'text',
                            'label' => $module->l('Number of products to display'),
                            'default' => '4',
                        ],
                        'include_subcategories' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Include products from subcategories'),
                            'default' => 0,
                        ],
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'products_per_slide_desktop' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on desktop'),
                            'default' => '4',
                        ],
                        'products_per_slide_tablet' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on tablet'),
                            'default' => '2',
                        ],
                        'products_per_slide_mobile' => [
                            'type' => 'text',
                            'label' => $module->l('Products per slide on mobile'),
                            'default' => '1',
                        ],
                        'background_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block background color'),
                        ],
                        'text_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $module->l('Block text color'),
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Pricing table'),
                'description' => $module->l('Display pricing plans in grid or carousel'),
                'code' => 'everblock_pricing_table',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $pricingTableTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'plans_per_slide' => [
                            'type' => 'select',
                            'label' => $module->l('Number of plans per slide'),
                            'default' => '3',
                            'choices' => [
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                            ],
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Plan',
                    'nameFrom' => 'title',
                    'groups' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Plan title'),
                            'default' => '',
                        ],
                        'price' => [
                            'type' => 'text',
                            'label' => $module->l('Plan price'),
                            'default' => '',
                        ],
                        'features' => [
                            'type' => 'textarea',
                            'label' => $module->l('Features'),
                            'default' => '',
                        ],
                        'cta_label' => [
                            'type' => 'text',
                            'label' => $module->l('CTA label'),
                            'default' => '',
                        ],
                        'cta_url' => [
                            'type' => 'text',
                            'label' => $module->l('CTA URL'),
                            'default' => '',
                        ],
                        'highlight' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Highlight'),
                            'default' => 0,
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Lookbook'),
                'description' => $module->l('Display looks with associated products'),
                'code' => 'everblock_lookbook',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $lookbookTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Look title'),
                            'default' => '',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Look image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'columns' => [
                            'type' => 'select',
                            'label' => $module->l('Columns on desktop'),
                            'default' => '1',
                            'choices' => [
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                            ],
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Product',
                    'nameFrom' => 'product',
                    'groups' => static::appendSpacingFields([
                        'product' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a product'),
                            'collection' => 'Product',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                        'top' => [
                            'type' => 'text',
                            'label' => $module->l('Top position (e.g., 50%)'),
                            'default' => '0%',
                        ],
                        'left' => [
                            'type' => 'text',
                            'label' => $module->l('Left position (e.g., 50%)'),
                            'default' => '0%',
                        ],
                        'animation_enabled' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable zoom animation'),
                            'default' => 0,
                        ],
                        'marker_color' => [
                            'type' => 'color',
                            'label' => $module->l('Marker color'),
                            'default' => '#f25b76',
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Wheel of fortune'),
                'description' => $module->l('Display a prize wheel'),
                'code' => 'everblock_wheel_of_fortune',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $wheelTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Wheel of fortune'),
                        ],
                        'button_label' => [
                            'type' => 'text',
                            'label' => $module->l('Button label'),
                            'default' => $module->l('Spin'),
                        ],
                        'login_text' => [
                            'type' => 'editor',
                            'label' => $module->l('Text above login form'),
                            'default' => '',
                        ],
                        'top_text' => [
                            'type' => 'editor',
                            'label' => $module->l('Text above the wheel'),
                            'default' => '',
                        ],
                        'bottom_text' => [
                            'type' => 'editor',
                            'label' => $module->l('Text below the wheel'),
                            'default' => '',
                        ],
                        'start_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game start date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'end_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game end date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'pre_start_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message before the game starts'),
                            'default' => '',
                        ],
                        'post_end_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message after the game ends'),
                            'default' => '',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Segment',
                    'nameFrom' => 'label',
                    'groups' => static::appendSpacingFields([
                        'label' => [
                            'type' => 'text',
                            'label' => $module->l('Label'),
                            'default' => '',
                        ],
                        'probability' => [
                            'type' => 'text',
                            'label' => $module->l('Probability'),
                            'default' => '1',
                        ],
                        'color' => [
                            'type' => 'color',
                            'label' => $module->l('Color'),
                            'default' => '#ff0000',
                        ],
                        'text_color' => [
                            'type' => 'color',
                            'label' => $module->l('Text color'),
                            'default' => '#ffffff',
                        ],
                        'discount' => [
                            'type' => 'text',
                            'label' => $module->l('Discount value'),
                            'default' => '10',
                        ],
                        'coupon_name' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon name'),
                            'default' => $module->l('Wheel reward'),
                        ],
                        'coupon_prefix' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon code prefix'),
                            'default' => 'WHEEL',
                        ],
                        'coupon_validity' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon validity in days'),
                            'default' => '30',
                        ],
                        'minimum_purchase' => [
                            'type' => 'text',
                            'label' => $module->l('Minimum purchase amount (tax incl.)'),
                            'default' => '',
                        ],
                        'max_winners' => [
                            'type' => 'text',
                            'label' => $module->l('Maximum winners for this segment (0 for unlimited)'),
                            'default' => '0',
                        ],
                        'coupon_type' => [
                            'type' => 'select',
                            'label' => $module->l('Discount type'),
                            'default' => 'percent',
                            'choices' => [
                                'percent' => $module->l('Percent'),
                                'amount' => $module->l('Amount'),
                            ],
                        ],
                        'id_categories' => [
                            'type' => 'selector',
                            'label' => $module->l('Coupon category restrictions'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'multiple' => true,
                            'default' => [],
                        ],
                        'isWinning' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Winning segment'),
                            'default' => false,
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Mystery boxes'),
                'description' => $module->l('Interactive mystery boxes mini game'),
                'code' => 'everblock_mystery_boxes',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $mysteryBoxesTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Mystery boxes'),
                        ],
                        'instructions' => [
                            'type' => 'editor',
                            'label' => $module->l('Instructions text'),
                            'default' => $module->l('Pick a box to reveal your surprise!'),
                        ],
                        'closed_label' => [
                            'type' => 'text',
                            'label' => $module->l('Closed state label'),
                            'default' => '?',
                        ],
                        'start_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game start date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'end_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game end date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'pre_start_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message before the game starts'),
                            'default' => '',
                        ],
                        'post_end_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message after the game ends'),
                            'default' => '',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Mystery box',
                    'nameFrom' => 'label',
                    'groups' => static::appendSpacingFields([
                        'label' => [
                            'type' => 'text',
                            'label' => $module->l('Label'),
                            'default' => '',
                        ],
                        'message' => [
                            'type' => 'editor',
                            'label' => $module->l('Reveal message'),
                            'default' => '',
                        ],
                        'probability' => [
                            'type' => 'text',
                            'label' => $module->l('Probability'),
                            'default' => '1',
                        ],
                        'color' => [
                            'type' => 'color',
                            'label' => $module->l('Background color'),
                            'default' => '#0d6efd',
                        ],
                        'text_color' => [
                            'type' => 'color',
                            'label' => $module->l('Text color'),
                            'default' => '#ffffff',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Optional image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'discount' => [
                            'type' => 'text',
                            'label' => $module->l('Discount value'),
                            'default' => '10',
                        ],
                        'coupon_name' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon name'),
                            'default' => $module->l('Mystery reward'),
                        ],
                        'coupon_prefix' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon code prefix'),
                            'default' => 'MYSTERY',
                        ],
                        'coupon_validity' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon validity in days'),
                            'default' => '30',
                        ],
                        'minimum_purchase' => [
                            'type' => 'text',
                            'label' => $module->l('Minimum purchase amount (tax incl.)'),
                            'default' => '',
                        ],
                        'max_winners' => [
                            'type' => 'text',
                            'label' => $module->l('Maximum winners for this box (0 for unlimited)'),
                            'default' => '0',
                        ],
                        'coupon_type' => [
                            'type' => 'select',
                            'label' => $module->l('Discount type'),
                            'default' => 'percent',
                            'choices' => [
                                'percent' => $module->l('Percent'),
                                'amount' => $module->l('Amount'),
                            ],
                        ],
                        'id_categories' => [
                            'type' => 'selector',
                            'label' => $module->l('Coupon category restrictions'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'multiple' => true,
                            'default' => [],
                        ],
                        'isWinning' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Winning box'),
                            'default' => false,
                        ],
                    ], $module),
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Slot machine'),
                'description' => $module->l('Interactive slot machine mini game'),
                'code' => 'everblock_slot_machine',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $slotMachineTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Slot machine'),
                        ],
                        'instructions' => [
                            'type' => 'editor',
                            'label' => $module->l('Instructions text'),
                            'default' => $module->l('Press the button to spin the reels and try your luck!'),
                        ],
                        'spin_button_label' => [
                            'type' => 'text',
                            'label' => $module->l('Spin button label'),
                            'default' => $module->l('Spin the reels'),
                        ],
                        'result_title' => [
                            'type' => 'text',
                            'label' => $module->l('Result area title'),
                            'default' => $module->l('Result'),
                        ],
                        'require_login' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Require customers to be logged in to play'),
                            'default' => true,
                        ],
                        'login_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message displayed when login is required'),
                            'default' => $module->l('Log in to spin the slot machine!'),
                        ],
                        'start_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game start date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => $slotMachineDefaultStartDate,
                        ],
                        'end_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game end date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => $slotMachineDefaultEndDate,
                        ],
                        'pre_start_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message before the game starts'),
                            'default' => $module->l('The slot machine opens soon, stay tuned!'),
                        ],
                        'post_end_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message after the game ends'),
                            'default' => $module->l('Thanks for playing! The slot machine is now closed.'),
                        ],
                        'winning_combinations' => [
                            'type' => 'textarea',
                            'label' => $module->l('Winning combinations (JSON array)'),
                            'default' => $slotMachineDefaultWinningCombinations,
                        ],
                        'default_coupon_name' => [
                            'type' => 'text',
                            'label' => $module->l('Default coupon name'),
                            'default' => $module->l('Slot machine reward'),
                        ],
                        'default_coupon_prefix' => [
                            'type' => 'text',
                            'label' => $module->l('Default coupon code prefix'),
                            'default' => 'SLOT',
                        ],
                        'default_coupon_validity' => [
                            'type' => 'text',
                            'label' => $module->l('Default coupon validity in days'),
                            'default' => 30,
                        ],
                        'default_coupon_type' => [
                            'type' => 'select',
                            'label' => $module->l('Default discount type'),
                            'default' => 'percent',
                            'choices' => [
                                'percent' => $module->l('Percent'),
                                'amount' => $module->l('Amount'),
                            ],
                        ],
                        'default_max_winners' => [
                            'type' => 'text',
                            'label' => $module->l('Maximum overall winners (0 for unlimited)'),
                            'default' => 0,
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Symbol',
                    'nameFrom' => 'label',
                    'groups' => static::appendSpacingFields([
                        'symbol_key' => [
                            'type' => 'text',
                            'label' => $module->l('Internal symbol key'),
                            'default' => 'cherry',
                        ],
                        'label' => [
                            'type' => 'text',
                            'label' => $module->l('Display label'),
                            'default' => $module->l('Cherry'),
                        ],
                        'probability' => [
                            'type' => 'text',
                            'label' => $module->l('Probability'),
                            'default' => '3',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Symbol image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'alt_text' => [
                            'type' => 'text',
                            'label' => $module->l('Image alternative text'),
                            'default' => $module->l('Cherry icon'),
                        ],
                        'text' => [
                            'type' => 'editor',
                            'label' => $module->l('Accessible description'),
                            'default' => $module->l('A juicy cherry symbol that hints at the jackpot.'),
                        ],
                    ], $module),
                ],
            ];
            $adventTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_advent_calendar.tpl';
            $blocks[] = [
                'name' => $module->l('Advent calendar'),
                'description' => $module->l('Interactive Advent calendar with 24 festive windows'),
                'code' => 'everblock_advent_calendar',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $adventTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Christmas Advent calendar'),
                        ],
                        'instructions' => [
                            'type' => 'editor',
                            'label' => $module->l('Instructions text'),
                            'default' => $module->l('Scratch or click on today\'s window to reveal your festive surprise!'),
                        ],
                        'start_date' => [
                            'type' => 'text',
                            'label' => $module->l('Calendar start date (YYYY-MM-DD)'),
                            'default' => date('Y') . '-12-01',
                        ],
                        'allow_past_windows' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Allow opening past windows'),
                            'default' => false,
                        ],
                        'restrict_to_current_day' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Allow only the current day to be opened'),
                            'default' => true,
                        ],
                        'locked_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Locked window message (use %date% to insert the day)'),
                            'default' => $module->l('Come back on the matching December day to open this window.'),
                        ],
                        'opened_label' => [
                            'type' => 'text',
                            'label' => $module->l('Opened badge label'),
                            'default' => $module->l('Opened'),
                        ],
                        'snow_enabled' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable snowfall decoration'),
                            'default' => true,
                        ],
                        'calendar_background_color' => [
                            'type' => 'color',
                            'label' => $module->l('Calendar background color'),
                            'default' => '',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Calendar window',
                    'nameFrom' => 'window_title',
                    'groups' => static::appendSpacingFields([
                        'day_number' => [
                            'type' => 'text',
                            'label' => $module->l('Day number (1-24)'),
                            'default' => 1,
                        ],
                        'window_title' => [
                            'type' => 'text',
                            'label' => $module->l('Window title'),
                            'default' => $module->l('Festive surprise'),
                        ],
                        'window_subtitle' => [
                            'type' => 'text',
                            'label' => $module->l('Subtitle'),
                            'default' => '',
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => $module->l('Content to reveal'),
                            'default' => $module->l('Add a personalised message, discount code or surprise.'),
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Optional image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'button_label' => [
                            'type' => 'text',
                            'label' => $module->l('Button label'),
                            'default' => $module->l('Shop now'),
                        ],
                        'button_url' => [
                            'type' => 'text',
                            'label' => $module->l('Button link URL'),
                            'default' => '',
                        ],
                        'promo_code' => [
                            'type' => 'text',
                            'label' => $module->l('Promo code'),
                            'default' => '',
                        ],
                        'background_color' => [
                            'type' => 'color',
                            'label' => $module->l('Window background color'),
                            'default' => '#b3002d',
                        ],
                        'text_color' => [
                            'type' => 'color',
                            'label' => $module->l('Window text color'),
                            'default' => '#ffffff',
                        ],
                    ], $module),
                ],
            ];
            $scratchTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_scratch_card.tpl';
            $blocks[] = [
                'name' => $module->l('Scratch card'),
                'description' => $module->l('Interactive scratch card mini game'),
                'code' => 'everblock_scratch_card',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $scratchTemplate,
                ],
                'config' => [
                    'fields' => static::appendSpacingFields([
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Scratch card'),
                        ],
                        'instructions' => [
                            'type' => 'editor',
                            'label' => $module->l('Instructions text'),
                            'default' => $module->l('Scratch the area to reveal your surprise!'),
                        ],
                        'start_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game start date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'end_date' => [
                            'type' => 'text',
                            'label' => $module->l('Game end date (YYYY-MM-DD HH:MM:SS)'),
                            'default' => '',
                        ],
                        'pre_start_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message before the game starts'),
                            'default' => '',
                        ],
                        'post_end_message' => [
                            'type' => 'editor',
                            'label' => $module->l('Message after the game ends'),
                            'default' => '',
                        ],
                    ], $module),
                ],
                'repeater' => [
                    'name' => 'Scratch case',
                    'nameFrom' => 'label',
                    'groups' => static::appendSpacingFields([
                        'label' => [
                            'type' => 'text',
                            'label' => $module->l('Label'),
                            'default' => '',
                        ],
                        'probability' => [
                            'type' => 'text',
                            'label' => $module->l('Probability'),
                            'default' => '1',
                        ],
                        'color' => [
                            'type' => 'color',
                            'label' => $module->l('Background color'),
                            'default' => '#0d6efd',
                        ],
                        'text_color' => [
                            'type' => 'color',
                            'label' => $module->l('Text color'),
                            'default' => '#ffffff',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Optional image'),
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'discount' => [
                            'type' => 'text',
                            'label' => $module->l('Discount value'),
                            'default' => '10',
                        ],
                        'coupon_name' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon name'),
                            'default' => $module->l('Scratch reward'),
                        ],
                        'coupon_prefix' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon code prefix'),
                            'default' => 'SCRATCH',
                        ],
                        'coupon_validity' => [
                            'type' => 'text',
                            'label' => $module->l('Coupon validity in days'),
                            'default' => '30',
                        ],
                        'minimum_purchase' => [
                            'type' => 'text',
                            'label' => $module->l('Minimum purchase amount (tax incl.)'),
                            'default' => '',
                        ],
                        'max_winners' => [
                            'type' => 'text',
                            'label' => $module->l('Maximum winners for this scratch (0 for unlimited)'),
                            'default' => '0',
                        ],
                        'coupon_type' => [
                            'type' => 'select',
                            'label' => $module->l('Discount type'),
                            'default' => 'percent',
                            'choices' => [
                                'percent' => $module->l('Percent'),
                                'amount' => $module->l('Amount'),
                            ],
                        ],
                        'id_categories' => [
                            'type' => 'selector',
                            'label' => $module->l('Coupon category restrictions'),
                            'collection' => 'Category',
                            'selector' => '{id} - {name}',
                            'multiple' => true,
                            'default' => [],
                        ],
                        'isWinning' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Winning scratch'),
                            'default' => false,
                        ],
                    ], $module),
                ],
            ];
            $blocks = self::addDisplaySettings($blocks, $module, $context);
            $blocks = self::applyFileUploadPath($blocks);
            EverblockCache::cacheStore($cacheId, $blocks);
            return $blocks;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    private static function addDisplaySettings(array $blocks, Module $module, Context $context): array
    {
        foreach ($blocks as &$block) {
            if (!isset($block['config']) || !is_array($block['config'])) {
                $block['config'] = [];
            }

            if (!isset($block['config']['fields']) || !is_array($block['config']['fields'])) {
                $block['config']['fields'] = [];
            }

            $displayField = [
                'display_on' => [
                    'type' => 'select',
                    'label' => $module->l('Display on'),
                    'default' => 'all',
                    'choices' => [
                        'all' => $module->l('Mobile and desktop'),
                        'mobile' => $module->l('Mobile only'),
                        'desktop' => $module->l('Desktop only'),
                        'none' => $module->l('Nowhere'),
                    ],
                ],
            ];

            $customerGroupField = self::getCustomerGroupField($context, $module);

            if (!isset($block['config']['fields']['display_on'])) {
                $block['config']['fields'] = array_merge($displayField, $block['config']['fields']);
            }

            if (!isset($block['config']['fields']['allowed_customer_groups'])) {
                $block['config']['fields'] = array_merge($customerGroupField, $block['config']['fields']);
            }
        }

        return $blocks;
    }

    private static function getCustomerGroupField(Context $context, Module $module): array
    {
        $groups = Group::getGroups((int) $context->language->id);

        $choices = [];
        $default = [];

        foreach ($groups as $group) {
            $groupId = (int) $group['id_group'];
            $choices[$groupId] = $group['name'];
            $default[] = $groupId;
        }

        return [
            'allowed_customer_groups' => [
                'type' => 'multiselect',
                'label' => $module->l('Allowed customer groups'),
                'default' => $default,
                'choices' => $choices,
            ],
        ];
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
