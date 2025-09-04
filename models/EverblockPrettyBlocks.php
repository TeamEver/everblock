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
        $module = Module::getInstanceByName('everblock');
        if (!EverblockCache::isCacheStored($cacheId)) {
            $defaultTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_' . $module->name . '.tpl';
            $modalTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_modal.tpl';
            $alertTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_alert.tpl';
            $buttonTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_button.tpl';
            $gmapTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_gmap.tpl';
            $shortcodeTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_shortcode.tpl';
            $iframeTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_iframe.tpl';
            $scrollVideoTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_scroll_video.tpl';
            $loginTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_login.tpl';
            $contactTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_contact.tpl';
            $shoppingCartTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_shopping_cart.tpl';
            $accordeonTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_accordeon.tpl';
            $textAndImageTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_text_and_image.tpl';
            $layoutTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_layout.tpl';
            $featuredCategoryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_highlight.tpl';
            $imgSliderTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_img_slider.tpl';
            $tabTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_tab.tpl';
            $categoryTabsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_tabs.tpl';
            $dividerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_divider.tpl';
            $spacerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_spacer.tpl';
            $galleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_gallery.tpl';
            $videoGalleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_video_gallery.tpl';
            $masonryGalleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_masonry_gallery.tpl';
            $testimonialTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial.tpl';
            $testimonialSliderTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial_slider.tpl';
            $imgTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_img.tpl';
            $rowTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/row.tpl';
            $reassuranceTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_reassurance.tpl';
            $ctaTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_cta.tpl';
            $sharerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_sharer.tpl';
            $linkListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_link_list.tpl';
            $socialLinksTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_social_links.tpl';
            $brandListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_brands.tpl';
            $productHighlightTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_product_highlight.tpl';
            $productSelectorTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_product_selector.tpl';
            $flashDealsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_flash_deals.tpl';
            $categoryProductsTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_products.tpl';
            $progressbarTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_progressbar.tpl';
            $cardTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_card.tpl';
            $coverTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_cover.tpl';
            $headingTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_heading.tpl';
            $categoryPriceTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_price.tpl';
            $tocTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_toc.tpl';
            $imageMapTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_image_map.tpl';
            $everblockTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_everblock.tpl';
            $lookbookTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_lookbook.tpl';
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
                    'fields' => [
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ]
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
                    'fields' => [
                        'title' => [
                            'type' => 'text',
                            'label' => 'Block title',
                            'default' => $module->l('Login'),
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'title' => [
                            'type' => 'text',
                            'label' => 'Block title',
                            'default' => $module->l('Login'),
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ]
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
                    'fields' => [
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
                    ],
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
                    'fields' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ]
                ],
                'repeater' => [
                    'name' => 'Tab',
                    'nameFrom' => 'name',
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                'settings' => [
                    'default' => [
                        'display_inline' => [
                            'type' => 'switch',
                            'label' => $module->l('Display reassurances side by side'),
                            'default' => false,
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Reassurances',
                    'nameFrom' => 'title',
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                            'default' => 'Le n°1 dans la protection et la santé du sportif',
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Right column description'),
                            'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec placerat, risus quis lobortis aliquam...',
                        ],
                        'text_highlight_1' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight word 1'),
                            'default' => 'protection',
                        ],
                        'text_highlight_2' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight word 2'),
                            'default' => 'santé',
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'desktop_columns' => [
                            'type' => 'select',
                            'label' => $module->l('Desktop columns'),
                            'default' => '2',
                            'choices' => [
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                                '6' => '6',
                            ],
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Menu',
                    'nameFrom' => 'name',
                    'groups' => [
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
                                'top' => $module->l('Top'),
                                'bottom' => $module->l('Bottom'),
                                'left' => $module->l('Left'),
                                'right' => $module->l('Right'),
                            ],
                            'default' => 'center',
                        ],
                        'title_position_mobile' => [
                            'type' => 'select',
                            'label' => $module->l('Title position (mobile)'),
                            'choices' => [
                                'center' => $module->l('Center'),
                                'top' => $module->l('Top'),
                                'bottom' => $module->l('Bottom'),
                                'left' => $module->l('Left'),
                                'right' => $module->l('Right'),
                            ],
                            'default' => 'center',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                'repeater' => [
                    'name' => $module->l('Image title'),
                    'nameFrom' => 'name',
                    'groups' => [
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
                        'text_highlight_1' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight text 1'),
                            'default' => 'protection',
                        ],
                        'text_highlight_2' => [
                            'type' => 'editor',
                            'label' => $module->l('Highlight text 2'),
                            'default' => 'santé',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'Row title',
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Video gallery'),
                'description' => $module->l('Display video gallery'),
                'code' => 'everblock_video_gallery',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $videoGalleryTemplate,
                ],
                'config' => [
                    'fields' => [
                        'use_carousel' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Display as carousel'),
                            'default' => '0',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Video',
                    'nameFrom' => 'title',
                    'groups' => [
                        'thumbnail' => [
                            'type' => 'fileupload',
                            'label' => $module->l('Thumbnail image'),
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
                            'label' => $module->l('Video title'),
                            'default' => '',
                        ],
                        'description' => [
                            'type' => 'editor',
                            'label' => $module->l('Video description'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'iframe' => [
                            'type' => 'text',
                            'label' => $module->l('Map iframe link'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                'repeater' => [
                    'name' => 'Image',
                    'nameFrom' => 'name',
                    'groups' => [
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Layout image',
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Block title'),
                            'default' => $module->l('Links'),
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Link',
                    'nameFrom' => 'name',
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'icon_color' => [
                            'type' => 'color',
                            'label' => $module->l('Icon color'),
                            'default' => '',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Social link',
                    'nameFrom' => 'url',
                    'groups' => [
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
                    ],
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
                    'fields' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Brand',
                    'nameFrom' => 'brand',
                    'groups' => [
                        'brand' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a brand'),
                            'collection' => 'Manufacturer',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Category',
                    'nameFrom' => 'name',
                    'groups' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'Tab title',
                            'default' => Configuration::get('PS_SHOP_NAME'),
                        ],
                        'id_category' => [
                            'type' => 'text',
                            'label' => $module->l('Category ID'),
                            'default' => '',
                        ],
                        'nb_products' => [
                            'type' => 'text',
                            'label' => $module->l('Number of products to display (0 for default)'),
                            'default' => 0,
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] = [
                'name' => $module->l('Progress bars'),
                'description' => $module->l('Display progress bars'),
                'code' => 'everblock_progressbar',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $progressbarTemplate,
                ],
                'repeater' => [
                    'name' => 'Bar',
                    'nameFrom' => 'text',
                    'groups' => [
                        'text' => [
                            'type' => 'text',
                            'label' => $module->l('Bar text'),
                            'default' => $module->l('Progress'),
                        ],
                        'value' => [
                            'type' => 'text',
                            'label' => $module->l('Progress value (0-100)'),
                            'default' => '50',
                        ],
                        'style' => [
                            'type' => 'select',
                            'label' => $module->l('Bar style'),
                            'default' => 'bg-success',
                            'choices' => [
                                'bg-primary' => 'bg-primary',
                                'bg-secondary' => 'bg-secondary',
                                'bg-success' => 'bg-success',
                                'bg-danger' => 'bg-danger',
                                'bg-warning' => 'bg-warning',
                                'bg-info' => 'bg-info',
                                'bg-light' => 'bg-light',
                                'bg-dark' => 'bg-dark',
                            ],
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                'repeater' => [
                    'name' => 'Card',
                    'nameFrom' => 'title',
                    'groups' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Cover',
                    'nameFrom' => 'title',
                    'groups' => [
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
                            ],
                            'default' => 'center',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'title' => [
                            'type' => 'text',
                            'label' => $module->l('Summary title'),
                            'default' => $module->l('Summary'),
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Section',
                    'nameFrom' => 'title',
                    'groups' => [
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
                    ],
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
                    'fields' => [
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Marker',
                    'nameFrom' => 'label',
                    'groups' => [
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
                    ],
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
                    'fields' => [
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Product',
                    'nameFrom' => 'product',
                    'groups' => [
                        'product' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a product'),
                            'collection' => 'Product',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                    ],
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
                    'fields' => [
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
                'repeater' => [
                    'name' => 'Deal',
                    'nameFrom' => 'product',
                    'groups' => [
                        'product' => [
                            'type' => 'selector',
                            'label' => $module->l('Choose a product'),
                            'collection' => 'Product',
                            'selector' => '{id} - {name}',
                            'default' => '',
                        ],
                    ],
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
                    'groups' => [
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
                        'slider' => [
                            'type' => 'checkbox',
                            'label' => $module->l('Enable slider'),
                            'default' => 0,
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
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
                'repeater' => [
                    'name' => 'Look',
                    'nameFrom' => 'title',
                    'groups' => [
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
                        'product_ids' => [
                            'type' => 'text',
                            'label' => $module->l('Associated product IDs (comma-separated)'),
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
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks = self::applyFileUploadPath($blocks);
            EverblockCache::cacheStore($cacheId, $blocks);
            return $blocks;
        }
        return EverblockCache::cacheRetrieve($cacheId);
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
