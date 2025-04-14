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
    public static function convertSingleCmsToPrettyBlock(int $id_shop, int $id_cms)
    {
        // Récupérer toutes les langues disponibles pour le shop
        $languages = Language::getLanguages(true, $id_shop);

        foreach ($languages as $language) {
            // Sélectionner une seule page CMS pour la langue spécifique
            $cmsPage = Db::getInstance()->getRow('
                SELECT c.id_cms, cl.id_lang, cl.content, cl.meta_title 
                FROM ' . _DB_PREFIX_ . 'cms c
                INNER JOIN ' . _DB_PREFIX_ . 'cms_lang cl ON c.id_cms = cl.id_cms
                INNER JOIN ' . _DB_PREFIX_ . 'cms_shop cs ON c.id_cms = cs.id_cms
                WHERE c.id_cms = ' . (int)$id_cms . ' 
                AND cl.id_lang = ' . (int)$language['id_lang'] . ' 
                AND cs.id_shop = ' . (int)$id_shop
            );

            // Si la page CMS n'existe pas pour cette langue, on passe à la langue suivante
            if (!$cmsPage) {
                continue;
            }

            // Récupérer les informations de la CMS pour cette langue
            $id_lang = (int)$cmsPage['id_lang'];
            $content = $cmsPage['content'];
            $metaTitle = $cmsPage['meta_title'];
            $module = Module::getInstanceByName('everblock');
            $defaultTemplate = 'module:prettyblocks/views/templates/blocks/custom_text/default.tpl';

            // Créer une zone spécifique pour la CMS et la langue
            $zoneName = 'cms|' . $id_cms;

            // Créer un nouveau bloc PrettyBlocks pour cette CMS et langue
            $prettyBlock = new PrettyBlocksModel();
            $prettyBlock->id_shop = $id_shop;
            $prettyBlock->id_lang = $id_lang;
            $prettyBlock->code = 'prettyblocks_custom_text';
            $prettyBlock->name = $metaTitle;
            $prettyBlock->zone_name = $zoneName;
            $prettyBlock->template = $defaultTemplate;

            // Configuration du bloc avec des champs par défaut et contenu pour cette langue
            $prettyBlock->config = json_encode([
                'content' => [
                    'type' => 'editor',
                    'label' => 'Content',
                    'default' => '<p> lorem ipsum </p>',
                    'force_default_value' => true,
                    'value' => $content,
                ],
            ]);

            // Sauvegarder le bloc
            $prettyBlock->add();

            // Facultatif : Une fois le bloc créé, supprimer le contenu de la CMS pour cette langue
            Db::getInstance()->execute('
                UPDATE ' . _DB_PREFIX_ . 'cms_lang
                SET content = ""
                WHERE id_cms = ' . (int)$id_cms . ' AND id_lang = ' . (int)$id_lang
            );
        }
    }

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
            $smartyTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_smarty.tpl';
            $modalTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_modal.tpl';
            $alertTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_alert.tpl';
            $buttonTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_button.tpl';
            $gmapTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_gmap.tpl';
            $shortcodeTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_shortcode.tpl';
            $iframeTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_iframe.tpl';
            $loginTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_login.tpl';
            $contactTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_contact.tpl';
            $hookTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_hook.tpl';
            $productTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_product.tpl';
            $shoppingCartTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_shopping_cart.tpl';
            $accordeonTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_accordeon.tpl';
            $textAndImageTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_text_and_image.tpl';
            $layoutTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_layout.tpl';
            $featuredCategoryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_category_highlight.tpl';
            $menuTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_menu.tpl';
            $supplierProductListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_supplier_product_list.tpl';
            $manufacturerProductListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_manufacturer_product_list.tpl';
            $productSliderListTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_product_slider.tpl';
            $imgSliderTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_img_slider.tpl';
            $tabTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_tab.tpl';
            $dividerTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_divider.tpl';
            $galleryTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_gallery.tpl';
            $testimonialTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial.tpl';
            $parallaxTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_parallax.tpl';
            $overlayTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_overlay.tpl';
            $tartifletteTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_tartiflette.tpl';
            $imgTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/prettyblock_img.tpl';
            $rowTemplate = 'module:' . $module->name . '/views/templates/hook/prettyblocks/row.tpl';
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                        'css_class' => [
                            'type' => 'text',
                            'label' => $module->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'padding_left' => [
                            'type' => 'text',
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
                            'label' => $module->l('Padding left (%)'),
                            'default' => '',
                        ],
                        'padding_right' => [
                            'type' => 'text',
                            'label' => $module->l('Padding right (%)'),
                            'default' => '',
                        ],
                        'padding_top' => [
                            'type' => 'text',
                            'label' => $module->l('Padding top (%)'),
                            'default' => '',
                        ],
                        'padding_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Padding bottom (%)'),
                            'default' => '',
                        ],
                        'margin_left' => [
                            'type' => 'text',
                            'label' => $module->l('Margin left (%)'),
                            'default' => '',
                        ],
                        'margin_right' => [
                            'type' => 'text',
                            'label' => $module->l('Margin right (%)'),
                            'default' => '',
                        ],
                        'margin_top' => [
                            'type' => 'text',
                            'label' => $module->l('Margin top (%)'),
                            'default' => '',
                        ],
                        'margin_bottom' => [
                            'type' => 'text',
                            'label' => $module->l('Margin bottom (%)'),
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
