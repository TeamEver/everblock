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
            $dividerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_divider.tpl';
            $galleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_gallery.tpl';
            $masonryGalleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_masonry_gallery.tpl';
            $testimonialTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial.tpl';
            $testimonialSliderTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial_slider.tpl';
            $imgTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_img.tpl';
            $rowTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/row.tpl';
            $reassuranceTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_reassurance.tpl';
            $ctaTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_cta.tpl';
            $sharerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_sharer.tpl';
            $linkListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_link_list.tpl';
            $productHighlightTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_product_highlight.tpl';
            $progressbarTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_progressbar.tpl';
            $cardTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_card.tpl';
            $defaultLogo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $module->name . '/logo.png';
            $blocks = [];
            $allShortcodes = EverblockShortcode::getAllShortcodes(
                (int) $context->language->id,
                (int) $context->shop->id
            );
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
                'repeater' => [
                    'name' => 'Reassurances',
                    'nameFrom' => 'title',
                    'groups' => [
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                                'youtube' =>'youtube',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                            'label' => 'Featured category image',
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                                '1' =>'No',
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
                            'default' => $module->l( 'My alt attribute')
                        ],
                        'url' => [
                            'type' => 'text',
                            'label' =>  $module->l('URL'),
                            'default' =>  $module->l('#')
                        ],
                        'banner' => [
                            'type' => 'fileupload',
                            'label' => 'Images',
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                                'primary' =>'primary',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                                'primary' =>'primary',
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
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
                            'default' => $module->l('Read more'),
                        ],
                        'button_link' => [
                            'type' => 'text',
                            'label' => $module->l('Button link'),
                            'default' => '#',
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Image',
                            'path' => '$/modules/' . $module->name . '/views/img/prettyblocks/',
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
            EverblockCache::cacheStore($cacheId, $blocks);
            return $blocks;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }
}
