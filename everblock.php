<?php
/**
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2023 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once('vendor/autoload.php');
require_once(dirname(__FILE__).'/models/EverblockClass.php');
require_once(dirname(__FILE__).'/models/EverblockShortcode.php');
require_once(dirname(__FILE__).'/models/EverblockTools.php');

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use ScssPhp\ScssPhp\Compiler;

class Everblock extends Module
{
    private $html;
    private $postErrors = [];
    private $postSuccess = [];

    public function __construct()
    {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '4.10.2';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Ever Block');
        $this->description = $this->l('Add HTML block everywhere !');
        $this->confirmUninstall = $this->l('Do yo really want to uninstall this module ?');
    }

    public function __call($method, $args)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = [
            'admin',
            'moduleadmin',
            'front',
            'modulefront',
        ];
        
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        if (Hook::isDisplayHookName(lcfirst(str_replace('hook', '', $method)))) {
            return $this->everHook($method, $args);
        }
    }

    public function install()
    {
        Configuration::updateValue('EVERPSCSS_P_LLOREM_NUMBER', 5);
        Configuration::updateValue('EVERPSCSS_S_LLOREM_NUMBER', 5);
        // Install SQL
        $sql = [];
        include dirname(__FILE__).'/sql/install.php';
        // Hook actionGetEverBlockBefore
        if (!Hook::getIdByName('actionGetEverBlockBefore')) {
            $hook = new Hook();
            $hook->name = 'actionGetEverBlockBefore';
            $hook->title = 'Before block is rendered';
            $hook->description = 'This hook triggers before block is rendered';
            $hook->save();
        }
        // Hook actionEverBlockChangeShortcodeBefore
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeBefore')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeBefore';
            $hook->title = 'Before block shortcodes are rendered';
            $hook->description = 'This hook triggers before every block shortcode is rendered';
            $hook->save();
        }
        // Hook actionEverBlockChangeShortcodeBefore
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeAfter')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeAfter';
            $hook->title = 'After block shortcodes are rendered';
            $hook->description = 'This hook triggers after every block shortcode is rendered';
            $hook->save();
        }
        return (parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionRegisterBlock')
            && $this->registerHook('beforeRenderingEverblockProduct')
            && $this->installModuleTab('AdminEverBlockParent', 'IMPROVE', $this->l('Ever Block'))
            && $this->installModuleTab('AdminEverBlock', 'AdminEverBlockParent', $this->l('HTML Blocks'))
            && $this->installModuleTab('AdminEverBlockHook', 'AdminEverBlockParent', $this->l('Hooks'))
            && $this->installModuleTab('AdminEverBlockShortcode', 'AdminEverBlockParent', $this->l('Shortcodes')));
    }

    public function uninstall()
    {
        // Uninstall SQL
        $sql = [];
        include dirname(__FILE__).'/sql/uninstall.php';
        Configuration::deleteByName('EVERPSCSS_CACHE');
        Configuration::deleteByName('EVERPSCSS_LINKS');
        Configuration::deleteByName('EVERPSJS_LINKS');
        Configuration::deleteByName('EVERPSCSS_P_LLOREM_NUMBER');
        Configuration::deleteByName('EVERPSCSS_S_LLOREM_NUMBER');
        Configuration::deleteByName('EVERBLOCK_TINYMCE');
        return (parent::uninstall()
            && $this->uninstallModuleTab('AdminEverBlockParent')
            && $this->uninstallModuleTab('AdminEverBlock')
            && $this->uninstallModuleTab('AdminEverBlockHook')
            && $this->uninstallModuleTab('AdminEverBlockShortcode'));
    }

    protected function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int) Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminEverBlockParent') {
            $tab->icon = 'icon-team-ever';
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = $tabName;
        }

        return $tab->add();
    }

    protected function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int)Tab::getIdFromClassName($tabClass));

        return $tab->delete();
    }

    public function checkHooks()
    {
        // Hook actionGetEverBlockBefore
        if (!Hook::getIdByName('actionGetEverBlockBefore')) {
            $hook = new Hook();
            $hook->name = 'actionGetEverBlockBefore';
            $hook->title = 'Before block is rendered';
            $hook->description = 'This hook triggers before block is rendered';
            $hook->save();
        }
        // Hook actionEverBlockChangeShortcodeBefore
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeBefore')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeBefore';
            $hook->title = 'Before block shortcodes are rendered';
            $hook->description = 'This hook triggers before every block shortcode is rendered';
            $hook->save();
        }
        // Hook actionEverBlockChangeShortcodeBefore
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeAfter')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeAfter';
            $hook->title = 'After block shortcodes are rendered';
            $hook->description = 'This hook triggers after every block shortcode is rendered';
            $hook->save();
        }

        // Vérifier si l'onglet "AdminEverBlockParent" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockParent');
        if (!$id_tab) {
            // L'onglet n'existe pas, créer un nouvel onglet
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockParent';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('IMPROVE');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            // Les noms des onglets doivent être traduits dans toutes les langues du site
            $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Ever Block');
            $tab->add();
        }
        // Vérifier si l'onglet "AdminEverBlock" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlock');
        if (!$id_tab) {
            // L'onglet n'existe pas, créer un nouvel onglet
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlock';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            // Les noms des onglets doivent être traduits dans toutes les langues du site
            $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('HTML blocks management');
            $tab->add();
        }
        // Vérifier si l'onglet "Hook management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockHook');
        if (!$id_tab) {
            // L'onglet n'existe pas, créer un nouvel onglet
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockHook';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            // Les noms des onglets doivent être traduits dans toutes les langues du site
            $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Hook management');
            $tab->add();
        }
        // Vérifier si l'onglet "Hook management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockShortcode');
        if (!$id_tab) {
            // L'onglet n'existe pas, créer un nouvel onglet
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockShortcode';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            // Les noms des onglets doivent être traduits dans toutes les langues du site
            $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Shortcodes management');
            $tab->add();
        }
        $this->registerHook('actionOutputHTMLBefore');
        $this->registerHook('displayHeader');
        $this->registerHook('actionAdminControllerSetMedia');
        $this->registerHook('actionRegisterBlock');
    }

    public function getContent()
    {
        $this->checkAndFixDatabase();
        $this->checkHooks();
        $this->html = '';
        if (((bool)Tools::isSubmit('submit' . $this->name . 'Module')) == true) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }

        if ((bool)Tools::isSubmit('submitEmptyCache') === true) {
            $this->emptyAllCache();
        }
        if (count($this->postErrors)) {
            // Pour chaque erreur trouvée
            foreach ($this->postErrors as $error) {
                // On les affiche
                $this->html .= $this->displayError($error);
            }
        }

        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }
        $block_admin_link  = 'index.php?controller=AdminEverBlock&token=';
        $block_admin_link .= Tools::getAdminTokenLite('AdminEverBlock');
        $this->context->smarty->assign([
            'everblock_dir' => $this->_path,
            'block_admin_link' => $block_admin_link,
        ]);

        $this->html .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/header.tpl'
        );
        if ($this->checkLatestEverModuleVersion($this->name, $this->version)) {
            $this->html .= $this->context->smarty->fetch(
                $this->local_path . 'views/templates/admin/upgrade.tpl'
            );
        }
        $this->html .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/configure.tpl'
        );
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/footer.tpl'
        );

        return $this->html;
    }
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name . 'Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Empty cache on saving ?'),
                        'desc' => $this->l('Set yes to empty cache on saving'),
                        'hint' => $this->l('Else cache will not be emptied'),
                        'name' => 'EVERPSCSS_CACHE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Extends TinyMCE on blocks management ?'),
                        'desc' => $this->l('Set yes to extends TinyMCE on blocs management'),
                        'hint' => $this->l('Else TinyMCE will be default'),
                        'name' => 'EVERBLOCK_TINYMCE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom CSS'),
                        'desc' => $this->l('Add here your custom CSS rules'),
                        'hint' => $this->l('Webdesigners here can manage CSS rules'),
                        'name' => 'EVERPSCSS',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom SASS'),
                        'desc' => $this->l('Add here your custom SASS rules that will be added after CSS rules'),
                        'hint' => $this->l('Webdesigners here can manage SASS rules'),
                        'name' => 'EVERPSSASS',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom Javascript'),
                        'desc' => $this->l('Add here your custom Javascript rules'),
                        'hint' => $this->l('Webdesigners here can manage Javascript rules'),
                        'name' => 'EVERPSJS',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom CSS links'),
                        'desc' => $this->l('Add here your custom CSS links, one per line'),
                        'hint' => $this->l('Add one link per line, must be CSS'),
                        'name' => 'EVERPSCSS_LINKS',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom JS links'),
                        'desc' => $this->l('Add here your custom JS links, one per line'),
                        'hint' => $this->l('Add one link per line, must be JS'),
                        'name' => 'EVERPSJS_LINKS',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Default number of paragraphs when [llorem] shortcode is detected'),
                        'desc' => $this->l('Will generate this number of paragraphs when the [llorem] shortcode is detected'),
                        'hint' => $this->l('Leaving this value blank will generate five paragraphs'),
                        'name' => 'EVERPSCSS_P_LLOREM_NUMBER',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Default number of sentences per paragraphs when [llorem] shortcode is detected'),
                        'desc' => $this->l('Will generate this number of sentences per paragraphs when the [llorem] shortcode is detected'),
                        'hint' => $this->l('Leaving this value blank will generate five sentences per paragraphs'),
                        'name' => 'EVERPSCSS_S_LLOREM_NUMBER',
                    ],
                ],
                'buttons' => [
                    'emptyCache' => [
                        'name' => 'submitEmptyCache',
                        'type' => 'submit',
                        'class' => 'btn btn-info pull-right',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Empty cache'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    protected function getConfigFormValues()
    {
        $idShop = Context::getContext()->shop->id;
        $custom_css = Tools::file_get_contents(
            _PS_MODULE_DIR_.'/' . $this->name . '/views/css/custom' . $idShop . '.css'
        );
        $custom_sass = Tools::file_get_contents(
            _PS_MODULE_DIR_.'/' . $this->name . '/views/css/custom' . $idShop . '.scss'
        );
        $custom_js = Tools::file_get_contents(
            _PS_MODULE_DIR_.'/' . $this->name . '/views/js/custom' . $idShop . '.js'
        );
        return [
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
            'EVERPSCSS' => $custom_css,
            'EVERPSSASS' => $custom_sass,
            'EVERPSJS' => $custom_js,
            'EVERPSCSS_LINKS' => Configuration::get('EVERPSCSS_LINKS'),
            'EVERPSJS_LINKS' => Configuration::get('EVERPSJS_LINKS'),
            'EVERPSCSS_P_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_P_LLOREM_NUMBER'),
            'EVERPSCSS_S_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_S_LLOREM_NUMBER'),
            'EVERBLOCK_TINYMCE' => Configuration::get('EVERBLOCK_TINYMCE'),
        ];
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submit' . $this->name . 'Module')) {
            if (Tools::getValue('EVERPSCSS_P_LLOREM_NUMBER')
                && !Validate::isInt(Tools::getValue('EVERPSCSS_P_LLOREM_NUMBER'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Llorem paragraph number" is not valid'
                );
            }
            if (Tools::getValue('EVERPSCSS_S_LLOREM_NUMBER')
                && !Validate::isInt(Tools::getValue('EVERPSCSS_S_LLOREM_NUMBER'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Llorem sentences per paragraphs number" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOCK_TINYMCE')
                && !Validate::isBool(Tools::getValue('EVERBLOCK_TINYMCE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Extends TinyMCE" is not valid'
                );
            }
        }
    }

    protected function postProcess()
    {
        $idShop = Context::getContext()->shop->id;
        $custom_css = _PS_MODULE_DIR_ . $this->name . '/views/css/custom' . $idShop . '.css';
        $custom_js = _PS_MODULE_DIR_ . $this->name . '/views/js/custom' . $idShop . '.js';
        // Compressed
        $compressedCss = _PS_MODULE_DIR_ . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $compressedJs = _PS_MODULE_DIR_ . $this->name . '/views/js/custom-compressed' . $idShop . '.js';
        $cssCode = Tools::getValue('EVERPSCSS');
        $scssCode = Tools::getValue('EVERPSSASS');
        $jsCode = Tools::getValue('EVERPSJS');
        // Compile SASS code
        $compiledCss = $this->compileSass(
            $scssCode
        );
        $cssCode .= $compiledCss;
        // Compress CSS code
        $compressedCssCode = $this->compressCSSCode(
            $cssCode
        );
        // Compress JS code
        $compressedJsCode = $this->compressJsCode($jsCode);
        // Create CSS file if need
        $handle_css = fopen(
            $custom_css,
            'w+'
        );
        fclose($handle_css);
        $handle_css = fopen(
            $compressedCss,
            'w+'
        );
        fclose($handle_css);
        // Create JS file if need
        $handle_js = fopen(
            $custom_js,
            'w+'
        );
        fclose($handle_js);
        $handle_js = fopen(
            $compressedJs,
            'w+'
        );
        fclose($handle_js);

        Configuration::updateValue(
            'EVERPSCSS_CACHE',
            Tools::getValue('EVERPSCSS_CACHE')
        );
        file_put_contents(
            $custom_css,
            $cssCode
        );
        file_put_contents(
            $custom_js,
            $jsCode
        );
        file_put_contents(
            $compressedCss,
            $compressedCssCode
        );
        file_put_contents(
            $compressedJs,
            $compressedJsCode
        );
        Configuration::updateValue(
            'EVERPSCSS_LINKS',
            Tools::getValue('EVERPSCSS_LINKS')
        );
        Configuration::updateValue(
            'EVERPSJS_LINKS',
            Tools::getValue('EVERPSJS_LINKS')
        );
        Configuration::updateValue(
            'EVERPSCSS_P_LLOREM_NUMBER',
            Tools::getValue('EVERPSCSS_P_LLOREM_NUMBER')
        );
        Configuration::updateValue(
            'EVERPSCSS_S_LLOREM_NUMBER',
            Tools::getValue('EVERPSCSS_S_LLOREM_NUMBER')
        );
        Configuration::updateValue(
            'EVERBLOCK_TINYMCE',
            Tools::getValue('EVERBLOCK_TINYMCE')
        );
        if ((bool) Configuration::get('EVERPSCSS_CACHE') === true) {
            $this->emptyAllCache();
        }
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    protected function emptyAllCache()
    {
        Tools::clearAllCache();
        $this->postSuccess[] = $this->l('Cache has been cleared');
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path . 'views/css/ever.css');
        if (Tools::getValue('id_everblock')
            || Tools::getIsset('addeverblock')
            || Tools::getValue('configure') == $this->name
        ) {
            /* Chargement des fichiers CSS */
            $this->context->controller->addCSS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/codemirror.min.css',
                'all'
            );

            $this->context->controller->addCSS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/theme/dracula.min.css',
                'all'
            );

            /* Chargement des fichiers JavaScript */
            $this->context->controller->addJS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/codemirror.min.js',
                'all'
            );

            $this->context->controller->addJS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/mode/javascript/javascript.min.js',
                'all'
            );
            if (Tools::getValue('configure') == $this->name) {
                $this->context->controller->addJs($this->_path . 'views/js/admin.js');
            }
            if ((bool) Configuration::get('EVERBLOCK_TINYMCE') === true) {
                $this->context->controller->addJs($this->_path . 'views/js/adminTinyMce.js');
            }
        }
    }

    public function hookActionOutputHTMLBefore($params)
    {
        $cacheId = 'EverBlockClass::getAllBlocks_'
        . (int) $this->context->language->id
        . '_'
        . (int) $this->context->shop->id;
        if (!Cache::isStored($cacheId)) {
            $txt = $params['html'];
            try {
                $context = Context::getContext();
                $contactLink = $context->link->getPageLink('contact');
                if (Context::getContext()->customer->isLogged()) {
                    $myAccountLink = $context->link->getPageLink('my-account');
                } else {
                    $myAccountLink = $context->link->getPageLink('authentication');
                }

                $cartLink = $context->link->getPageLink('cart', null, null, ['action' => 'show']);
                if (!defined(_PS_PARENT_THEME_URI_) || empty(_PS_PARENT_THEME_URI_)) {
                    $theme_uri = Tools::getShopDomainSsl(true) . _PS_THEME_URI_;
                } else {
                    $theme_uri = Tools::getShopDomainSsl(true) . _PS_PARENT_THEME_URI_;
                }
                $shopName = Configuration::get('PS_SHOP_NAME');
                $defaultShortcodes = [
                    '[shop_url]' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__,
                    '[shop_name]'=> $shopName,
                    '[start_cart_link]' => '<a href="'
                    . $cartLink
                    . '" target="_blank" rel="nofollow" title="' . $shopName . '">',
                    '[end_cart_link]' => '</a>',
                    '[start_shop_link]' => '<a href="'
                    . Tools::getShopDomainSsl(true) . __PS_BASE_URI__
                    . '">',
                    '[start_contact_link]' => '<a href="' . $contactLink . '" target="_blank" title="' . $shopName . '">',
                    '[end_shop_link]' => '</a>',
                    '[end_contact_link]' => '</a>',
                    '[contact_link]'=> $contactLink,
                    '[my_account_link]' => $myAccountLink,
                    '[llorem]' => $this->generateLoremIpsum(),
                    '[theme_uri]' => $theme_uri,
                ];
                $shortcodes = array_merge($defaultShortcodes, $this->getEntityShortcodes(Context::getContext()->customer->id));
                $shortcodes = array_merge($shortcodes, $this->getProductShortcodes($txt));
                $shortcodes = array_merge($shortcodes, $this->getCategoryShortcodes($txt));
                $shortcodes = array_merge($shortcodes, $this->getManufacturerShortcodes($txt));
                $shortcodes = array_merge($shortcodes, $this->getBrandsShortcode($txt));
                $shortcodes = array_merge($shortcodes, $this->getEverShortcodes($txt));
                foreach ($shortcodes as $key => $value) {
                    $txt = preg_replace(
                        '/(?<!\w|[&\'"])' . preg_quote($key, '/') . '(?!\w|;)/',
                        $value,
                        $txt
                    );
                }
                $params['html'] = $txt;
                Cache::store($cacheId, $txt);
                return $params['html'];
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'Ever Block : unable to rewrite HTML page'
                );
                return $params['html'];
            }
        }

        return Cache::retrieve($cacheId);

    }

    public function everHook($method, $args)
    {
        if (!Hook::isDisplayHookName(lcfirst(str_replace('hook', '', $method)))) {
            return;
        }
        $controllerTypes = [
            'admin',
            'moduleadmin',
            'front',
            'modulefront',
        ];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        // Get current hook name based on method name, first letter to lowercase
        $id_hook = Hook::getIdByName(lcfirst(str_replace('hook', '', $method)));
        $hookName = lcfirst(str_replace('hook', '', $method));
        $idObj = 0;
        if (Tools::getValue('id_product')) {
            $idObj = Tools::getValue('id_product');
        }
        if (Tools::getValue('id_category')) {
            $idObj = Tools::getValue('id_category');
        }
        // Let's cache
        $cacheId = $this->getCacheId($this->name . '-id_hook-' . $id_hook . '-controller-' . Tools::getValue('controller') . '-hookName' . $hookName . '-idObj-' . $idObj . '-device-' . $this->context->getDevice());
        if (!$this->isCached('everblock.tpl', $cacheId)) {
            if (Context::getContext()->controller->controller_type === 'front') {
                if (Context::getContext()->customer->id) {
                    $id_entity = (int)Context::getContext()->customer->id;
                } else {
                    $id_entity = false;
                }
            } else {
                if (Context::getContext()->controller->controller_type === 'admin'
                    || Context::getContext()->controller->controller_type === 'moduleadmin'
                ) {
                    $id_entity = (int) Context::getContext()->employee->id;
                } else {
                    $id_entity = false;
                }
            }
            $everblock = EverblockClass::getBlocks(
                (int) $id_hook,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $currentBlock = [];
            foreach ($everblock as $block) {
                // Check hook
                $currentHook = new Hook(
                    $block['id_hook']
                );
                // Check device
                if ((int) $block['device'] > 0
                    && (int) $this->context->getDevice() != (int) $block['device']
                ) {
                    continue;
                }
                // Is block only for homepage ?
                if ((bool) $block['only_home'] === true
                    && !$this->context->controller instanceof IndexController
                ) {
                    continue;
                }
                if ((bool) $block['only_home'] === true
                    && Tools::getValue('controller') != 'index'
                ) {
                    continue;
                }
                // Only category management
                if ((bool) $block['only_category'] === true
                    && Tools::getValue('controller') != 'category'
                ) {
                    continue;
                }
                // Only manufacturer management
                if ((bool) $block['only_manufacturer'] === true
                    && Tools::getValue('controller') != 'manufacturer'
                ) {
                    continue;
                }
                // Only supplier management
                if ((bool) $block['only_supplier'] === true
                    && Tools::getValue('controller') != 'supplier'
                ) {
                    continue;
                }
                // Only CMS category management
                if ((bool) $block['only_cms_category'] === true
                    && !Tools::getValue('id_cms_category')
                ) {
                    continue;
                }

                $continue = false;
                // Only category pages
                if ((bool)$block['only_category'] === true && Tools::getValue('controller') === 'category') {
                    $categories = json_decode($block['categories']);
                    if (!in_array((int)Tools::getValue('id_category'), $categories)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only manufacturer pages
                if ((bool)$block['only_manufacturer'] === true && Tools::getValue('controller') === 'manufacturer') {
                    $manufacturers = json_decode($block['manufacturers']);
                    if (!in_array((int)Tools::getValue('id_manufacturer'), $manufacturers)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only supplier pages
                if ((bool)$block['only_supplier'] === true && Tools::getValue('controller') === 'supplier') {
                    $suppliers = json_decode($block['suppliers']);
                    if (!in_array((int)Tools::getValue('id_supplier'), $suppliers)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only CMS category pages
                if ((bool)$block['only_cms_category'] === true
                    && Tools::getValue('controller') === 'cms'
                    && Tools::getValue('id_cms_category')
                ) {
                    $cms_categories = json_decode($block['cms_categories']);
                    if (!in_array((int)Tools::getValue('id_cms_category'), $cms_categories)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only products pages with specific category
                if (Tools::getValue('id_product')
                    && Tools::getValue('controller') === 'product'
                    && (bool)$block['only_category'] === true
                    && (bool)$block['only_category_product'] === true
                ) {
                    $product = new Product(
                        (int) Tools::getValue('id_product')
                    );
                    $currentHook = new Hook(
                        $block['id_hook']
                    );
                    $categories = json_decode($block['categories']);
                    $productCategories = $product->getCategories();
                    if ($categories && is_array($categories)) {
                        $allowedCategory = array_intersect($categories, $productCategories);
                        $continue = empty($allowedCategory);
                    }
                }
                if ((bool) $continue === true) {
                    continue;
                }
                if ((bool) $block['obfuscate_link'] === true) {
                    $block['content'] = $this->obfuscateText(
                        $block['content']
                    );
                }
                if ((bool) $block['lazyload'] === true) {
                    $block['content'] = $this->addLazyLoadToImages(
                        $block['content']
                    );
                }
                $currentBlock[] = ['block' => $block];
            }
            $this->smarty->assign([
                'everhook' => trim($method),
                'everblock' => $currentBlock,
                'args' => $args,
            ]);
        }
        return $this->display(__FILE__, 'everblock.tpl', $cacheId);
    }

    public function hookDisplayHeader()
    {
        $idShop = (int) Context::getContext()->shop->id;
        // Register your CSS file
        $this->context->controller->registerStylesheet(
            'module-everblock-css',
            'modules/' . $this->name . '/views/css/everblock.css',
            ['media' => 'all', 'priority' => 200, 'version' => $this->version]
        );
        $this->context->controller->registerJavascript(
            'module-everblock-js',
            'modules/' . $this->name . '/views/js/everblock.js',
            ['position' => 'bottom', 'priority' => 200, 'version' => $this->version]
        );
        $compressedCss = _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $compressedJs = _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom-compressed' . $idShop . '.js';
        if (file_exists($compressedCss)) {
            $this->context->controller->registerStylesheet(
                'module-everblock-custom-compressed-css',
                'modules/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200, 'version' => $this->version]
            );
        }
        if (file_exists($compressedJs)) {
            $this->context->controller->registerJavascript(
                'module-everblock-compressed-js',
                'modules/' . $this->name . '/views/js/custom-compressed' . $idShop . '.js',
                ['position' => 'bottom', 'priority' => 200, 'version' => $this->version]
            );
        }
    }

    protected function getEntityShortcodes($id_entity)
    {
        $entityShortcodes = [];
        if ($id_entity) {
            if (Context::getContext()->controller->controller_type == 'admin'
                || Context::getContext()->controller->controller_type == 'moduleadmin'
            ) {
                $entity = new Employee((int) $id_entity);
                $entityShortcodes = [
                    '[entity_lastname]' => $entity->lastname,
                    '[entity_firstname]' => $entity->firstname,
                    '[entity_company]' => '', // info unavailable on employee object
                    '[entity_siret]' => '', // info unavailable on employee object
                    '[entity_ape]' => '', // info unavailable on employee object
                    '[entity_birthday]' => '', // info unavailable on employee object
                    '[entity_website]' => '', // info unavailable on employee object
                    '[entity_gender]' => '', // info unavailable on employee object
                ];
            }
            if (Context::getContext()->controller->controller_type == 'front'
                || Context::getContext()->controller->controller_type == 'modulefront'
            ) {
                $entity = new Customer((int) $id_entity);
                $gender = new Gender((int) $entity->id_gender, (int) $entity->id_lang);
                $entityShortcodes = [
                    '[entity_lastname]' => $entity->lastname,
                    '[entity_firstname]' => $entity->firstname,
                    '[entity_company]' => $entity->company,
                    '[entity_siret]' => $entity->siret,
                    '[entity_ape]' => $entity->ape,
                    '[entity_birthday]' => $entity->birthday,
                    '[entity_website]' => $entity->website,
                    '[entity_gender]' => $gender->name,
                ];
            }
        }

        return $entityShortcodes;
    }

    protected function getBrandsShortcode($message)
    {
        $brandsShortcodes = [];
        preg_match_all('/\[brands\s+nb="(\d+)"\]/i', $message, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $brandCount = (int) $match[1];
            
            $brands = $this->getBrandsData($brandCount);
            
            if (!empty($brands)) {
                $this->context->smarty->assign('brands', $brands);
                $brandsShortcodes[$match[0]] = $this->context->smarty->fetch($this->getTemplatePath('ever_brand.tpl'));
            }
        }
        return $brandsShortcodes;
    }

    protected function getBrandsData($limit)
    {
        $cacheId = $this->name . '::getBrandsData_'
        . (int) $this->context->language->id
        . '_'
        . (int) $limit;
        if (!Cache::isStored($cacheId)) {
            $brands = Manufacturer::getLiteManufacturersList(
                (int) $this->context->language->id
            );

            $limitedBrands = [];

            // Limite du nombre de marques en fonction du paramètre $limit
            if (!empty($brands)) {
                $brands = array_slice($brands, 0, $limit);

                foreach ($brands as $brand) {
                    $name = $brand['name'];
                    $logo = $this->context->link->getManufacturerImageLink(
                        (int) $brand['id']
                    );
                    $url = $brand['link'];

                    $limitedBrands[] = [
                        'id' => $brand['id'],
                        'name' => $name,
                        'logo' => $logo,
                        'url' => $url,
                    ];
                }
            }
            Cache::store($cacheId, $limitedBrands);
            return $limitedBrands;
        }

        return Cache::retrieve($cacheId);
    }

    protected function getProductShortcodes($message)
    {
        $message = strip_tags($message);
        $productShortcodes = [];
        preg_match_all('/(\[product\s+[0-9,\s]+\])/i', $message, $matches);

        foreach ($matches[0] as $match) {
            $productIds = preg_replace('/\[product\s+|\s+\]/i', '', $match);
            $productIdsArray = explode(',', $productIds);
            
            $everPresentProducts = $this->everPresentProducts($productIdsArray); // Utilisez la méthode une fois ici
            
            if (!empty($everPresentProducts)) {
                $this->context->smarty->assign('everPresentProducts', $everPresentProducts);
                $productShortcodes[$match] = $this->context->smarty->fetch($this->getTemplatePath('ever_presented_products.tpl'));
            }
        }

        return $productShortcodes;
    }

    protected function getCategoryShortcodes($message)
    {
        $categoryShortcodes = [];
        preg_match_all('/\[category\s+id="(\d+)"\s+nb="(\d+)"\]/i', $message, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $categoryId = (int) $match[1];
            $productCount = (int) $match[2];

            // Récupérez ici les produits de la catégorie $categoryId
            $categoryProducts = $this->getProductsByCategoryId($categoryId, $productCount);
            if (!empty($categoryProducts)) {
                $productIds = [];
                foreach ($categoryProducts as $categoryProduct) {
                    $productIds[] = $categoryProduct['id_product'];
                }
                $everPresentProducts = $this->everPresentProducts($productIds);
                $this->context->smarty->assign('everPresentProducts', $everPresentProducts);
                $categoryShortcodes[$match[0]] = $this->context->smarty->fetch($this->getTemplatePath('ever_presented_products.tpl'));
            }
        }
        return $categoryShortcodes;
    }

    protected function getProductsByCategoryId($categoryId, $limit)
    {
        $cacheId = $this->name . '::getProductsByCategoryId_'
        . (int) $categoryId
        . '_'
        . (int) $limit;
        if (!Cache::isStored($cacheId)) {
            $category = new Category($categoryId);
            $return = [];
            if (Validate::isLoadedObject($category)) {
                $products = $category->getProducts(Context::getContext()->language->id, 1, $limit, 'id_product', 'ASC');
                $return = $products;
            }

            Cache::store($cacheId, $return);
            return $return;
        }

        return Cache::retrieve($cacheId);
    }

    protected function getManufacturerShortcodes($message)
    {
        $manufacturerShortcodes = [];
        preg_match_all('/\[manufacturer\s+id="(\d+)"\s+nb="(\d+)"\]/i', $message, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $manufacturerId = (int) $match[1];
            $productCount = (int) $match[2];
            // Récupérez ici les produits du fabricant $manufacturerId
            $manufacturerProducts = $this->getProductsByManufacturerId($manufacturerId, $productCount);
            if (!empty($manufacturerProducts)) {
                $productIds = [];
                foreach ($manufacturerProducts as $manufacturerProduct) {
                    $productIds[] = $manufacturerProduct['id_product'];
                }
                $everPresentProducts = $this->everPresentProducts($productIds);
                $this->context->smarty->assign('everPresentProducts', $everPresentProducts);
                $manufacturerShortcodes[$match[0]] = $this->context->smarty->fetch($this->getTemplatePath('ever_presented_products.tpl'));
            }
        }
        return $manufacturerShortcodes;
    }

    protected function getProductsByManufacturerId($manufacturerId, $limit)
    {
        $cacheId = $this->name . '::getProductsByManufacturerId_'
        . (int) $manufacturerId
        . '_'
        . (int) $limit;
        if (!Cache::isStored($cacheId)) {
            $manufacturer = new Manufacturer($manufacturerId);
            $return = [];
            if (Validate::isLoadedObject($manufacturer)) {
                $products = Manufacturer::getProducts(
                    $manufacturer->id,
                    Context::getContext()->language->id,
                    1,
                    $limit,
                    'id_product',
                    'ASC'
                );
                $return = $products;
            }

            Cache::store($cacheId, $return);
            return $return;
        }

        return Cache::retrieve($cacheId);
    }

    protected function getEverShortcodes($message)
    {
        $customShortcodes = EverblockShortcode::getAllShortcodes(
            Context::getContext()->shop->id,
            Context::getContext()->language->id
        );
        $returnedShortcodes = [];
        foreach ($customShortcodes as $sc) {
            $returnedShortcodes[$sc->shortcode] = $sc->content;
        }
        return $returnedShortcodes;
    }

    protected function everPresentProducts($result)
    {
        $products = [];
        if (!empty($result)) {
            $showPrice = true;
            $assembler = new ProductAssembler(Context::getContext());
            $presenterFactory = new ProductPresenterFactory(Context::getContext());
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    Context::getContext()->link
                ),
                Context::getContext()->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                Context::getContext()->getTranslator()
            );
            $presentationSettings->showPrices = $showPrice;
            foreach ($result as $productId) {
                $rawProduct = [
                    'id_product' => $productId,
                    'id_lang' => Context::getContext()->language->id,
                    'id_shop' => Context::getContext()->shop->id,
                ];
                $pproduct = $assembler->assembleProduct($rawProduct);
                if (Product::checkAccessStatic((int) $productId, false)) {
                    $products[] = $presenter->present(
                        $presentationSettings,
                        $pproduct,
                        Context::getContext()->language
                    );
                }
            }
        }
        return $products;
    }

    public function hookActionRegisterBlock($params)
    {
        $cacheId = $this->name . '::hookActionRegisterBlock_'
        . (int) $this->context->language->id
        . '_'
        . (int) $this->context->shop->id;
        if (!Cache::isStored($cacheId)) {
            $defaultTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_everblock.tpl';
            $modalTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_modal.tpl';
            $alertTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_alert.tpl';
            $buttonTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_button.tpl';
            $gmapTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_gmap.tpl';
            $shortcodeTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_shortcode.tpl';
            $iframeTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_iframe.tpl';
            $loginTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_login.tpl';
            $contactTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_contact.tpl';
            $hookTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_hook.tpl';
            $categoryTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_category.tpl';
            $productTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_product.tpl';
            $manufacturerTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_manufacturer.tpl';
            $shoppingCartTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_shopping_cart.tpl';
            $accordeonTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_accordeon.tpl';
            $textAndImageTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_text_and_image.tpl';
            $layoutTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_layout.tpl';
            $menuTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_menu.tpl';
            $supplierProductListTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_supplier_product_list.tpl';
            $manufacturerProductListTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_manufacturer_product_list.tpl';
            $productSliderListTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_product_slider.tpl';
            $imgSliderTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_img_slider.tpl';
            $tabTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_tab.tpl';
            $dividerTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_divider.tpl';
            $galleryTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_gallery.tpl';
            $testimonialTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_testimonial.tpl';
            $parallaxTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_parallax.tpl';
            $overlayTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_overlay.tpl';
            $tartifletteTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_tartiflette.tpl';
            $defaultLogo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/logo.png';
            $blocks = [];
            $allShortcodes = EverblockShortcode::getAllShortcodes(
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $allHooks = Hook::getHooks(false, true);
            $prettyBlocksHooks = [];
            foreach ($allHooks as $hook) {
                $prettyBlocksHooks[$hook['id_hook']] = $hook['name'];
            }
            $prettyBlocksShortcodes = [];
            foreach ($allShortcodes as $shortcode) {
                $prettyBlocksShortcodes[$shortcode->id] = $shortcode->shortcode;
            }

            $blocks[] =  [
                'name' => $this->displayName . ' Shortcodes',
                'description' => $this->l('Ever block shortcodes'),
                'code' => 'everblock_shortcode',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $shortcodeTemplate,
                ],
                'config' => [
                    'fields' => [
                        'shortcode' => [
                            'type' => 'select', // type of field
                            'label' => 'Choose a value', // label to display
                            'default' => '', // default value (String)
                            'choices' => $prettyBlocksShortcodes
                        ],
                    ],
                ],
            ];

            $blocks[] =  [
                'name' => $this->displayName,
                'description' => $this->description,
                'code' => 'everblock',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $defaultTemplate,
                ],
                'config' => [
                    'fields' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'Block name (as a reminder)',
                            'default' => '',
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Block content',
                            'default' => '',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];

            $blocks[] =  [
                'name' => $this->l('Modal'),
                'description' => $this->l('Add custom modal'),
                'code' => 'everblock_modal',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $modalTemplate,
                ],
                'config' => [
                    'fields' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'Modal title',
                            'default' => '',
                        ],
                        'open_name' => [
                            'type' => 'text',
                            'label' => 'Open modal button text',
                            'default' => $this->l('Open'),
                        ],
                        'close_name' => [
                            'type' => 'text',
                            'label' => 'Close modal button text',
                            'default' => $this->l('Close'),
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Modal content',
                            'default' => '[llorem]',
                        ],
                        'auto_trigger_modal' => [
                            'type' => 'radio_group', // type of field
                            'label' => $this->l('Auto trigger modal'), // label to display
                            'default' => 'No', // default value (String)
                            'choices' => [
                                '1' =>'No',
                                '2' => 'Auto',
                            ]
                        ],
                        'modal_title_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '#000000',
                            'label' => $this->l('Modal title color')
                        ],
                        'open_modal_button_bg_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '#000000',
                            'label' => $this->l('Open modal button background color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];

            $blocks[] =  [
                'name' => $this->l('Video iframe'),
                'description' => $this->l('Add video iframe using embed link'),
                'code' => 'everblock_iframe',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $iframeTemplate,
                ],
                'config' => [
                    'fields' => [
                        'iframe_link' => [
                            'type' => 'text',
                            'label' => 'Iframe embed code (like https://www.youtube.com/embed/jfKfPfyJRdk)',
                            'default' => 'https://www.youtube.com/embed/jfKfPfyJRdk',
                        ],
                        'iframe_source' => [
                            'type' => 'radio_group', // type of field
                            'label' => $this->l('Iframe source'), // label to display
                            'default' => 'No', // default value (String)
                            'choices' => [
                                '1' =>'youtube',
                                '2' => 'vimeo',
                                '3' => 'dailymotion',
                                '4' => 'vidyard',
                            ]
                        ],
                        'height' => [
                            'type' => 'text',
                            'label' => $this->l('Iframe height (like 250px)'),
                            'default' => '500px',
                        ],
                        'width' => [
                            'type' => 'text',
                            'label' => $this->l('Iframe width (like 250px or 50%)'),
                            'default' => '100%',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Login form'),
                'description' => $this->l('Add login form'),
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
                            'default' => $this->l('Login'),
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Native contact form'),
                'description' => $this->l('Add login form (default contact module must be installed)'),
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
                            'default' => $this->l('Login'),
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Hook'),
                'description' => $this->l('Add hook on zone'),
                'code' => 'everblock_hook',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $hookTemplate,
                ],
                'config' => [
                    'fields' => [
                        'hook_name' => [
                            'type' => 'select', // type of field
                            'label' => 'Choose a value', // label to display
                            'default' => '', // default value (String)
                            'choices' => $prettyBlocksHooks
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Category text'),
                'description' => $this->l('Add text on specific category page'),
                'code' => 'everblock_category',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $categoryTemplate,
                ],
                'config' => [
                    'fields' => [
                        'category' => [
                            'type' => 'selector',
                            'label' => 'Please select a category',
                            'collection' => 'Category',
                            'default' => 'default value',
                            'selector' => '{id} - {name}'
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Category content',
                            'default' => '[llorem]',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Manufacturer text'),
                'description' => $this->l('Add text on specific manufacturer page'),
                'code' => 'everblock_manufacturer',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $manufacturerTemplate,
                ],
                'config' => [
                    'fields' => [
                        'manufacturer' => [
                            'type' => 'selector',
                            'label' => 'Please select a manufacturer',
                            'collection' => 'Manufacturer',
                            'default' => 'default value',
                            'selector' => '{id} - {name}'
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Manufacturer content',
                            'default' => '[llorem]',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Shopping cart'),
                'description' => $this->l('Add dropdown shopping cart'),
                'code' => 'everblock_shopping_cart',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $shoppingCartTemplate,
                ],
                'config' => [
                    'fields' => [
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Google Map'),
                'description' => $this->l('Add Google Map'),
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
                            'label' => 'Google Map sharing iframe code',
                            'default' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d46041.78150083645!2d0.64046735!3d43.843156!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12abd4104fb41073%3A0xbee8d3fd7affda90!2s32500%20Fleurance!5e0!3m2!1sfr!2sfr!4v1691570983796!5m2!1sfr!2sfr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                        ],
                        'height' => [
                            'type' => 'text',
                            'label' => $this->l('Iframe height (like 250px)'),
                            'default' => '500px',
                        ],
                        'width' => [
                            'type' => 'text',
                            'label' => $this->l('Iframe width (like 250px or 50%)'),
                            'default' => '100%',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Alert message'),
                'description' => $this->l('Add alert message'),
                'code' => 'everblock_alert',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $alertTemplate,
                ],
                'config' => [
                    'fields' => [
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Alert message content',
                            'default' => '[llorem]',
                        ],
                        'alert_type' => [
                            'type' => 'radio_group', // type of field
                            'label' => $this->l('Alert type'), // label to display
                            'default' => 'primary', // default value (String)
                            'choices' => [
                                '1' =>'primary',
                                '2' => 'secondary',
                                '3' => 'success',
                                '4' => 'danger',
                                '5' => 'warning',
                                '6' => 'info',
                                '7' => 'light',
                                '8' => 'dark',
                            ]
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Button'),
                'description' => $this->l('Add simple button'),
                'code' => 'everblock_button',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $buttonTemplate,
                ],
                'config' => [
                    'fields' => [
                        'button_type' => [
                            'type' => 'radio_group', // type of field
                            'label' => $this->l('Button type'), // label to display
                            'default' => 'primary', // default value (String)
                            'choices' => [
                                '1' =>'primary',
                                '2' => 'secondary',
                                '3' => 'success',
                                '4' => 'danger',
                                '5' => 'warning',
                                '6' => 'info',
                                '7' => 'light',
                                '8' => 'dark',
                            ]
                        ],
                        'button_size' => [
                            'type' => 'radio_group', // type of field
                            'label' => $this->l('Button type'), // label to display
                            'default' => 'btn-normal', // default value (String)
                            'choices' => [
                                '1' =>'normal',
                                '2' => 'sm',
                                '3' => 'lg',
                                '4' => 'block',
                            ]
                        ],
                        'button_content' => [
                            'type' => 'text',
                            'label' => $this->l('Button text'),
                            'default' => '',
                        ],
                        'button_link' => [
                            'type' => 'text',
                            'label' => $this->l('Button link'),
                            'default' => '',
                        ],
                        'obfuscate' => [
                            'type' => 'checkbox', // type of field
                            'label' => $this->l('Obfuscate link'), // label to display
                            'default' => '0', // default value (String)
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Accordeons'),
                'description' => 'Add horizontal accordeon',
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
                            'label' => $this->l('Accordeon title color')
                        ],
                        'title_bg_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '#000000',
                            'label' => $this->l('Accordeon background color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Product'),
                'description' => $this->l('Add specific product'),
                'code' => 'everblock_product',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $productTemplate,
                ],
                'config' => [
                    'fields' => [
                        'product' => [
                            'type' => 'selector',
                            'label' => $this->l('Please select a product'),
                            'collection' => 'Product',
                            'default' => 'default value',
                            'selector' => '{id} - {name}'
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Text and image'),
                'description' => 'Add image & text layout',
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
                            'label' => 'Layout title',
                            'default' => '',
                        ],
                        'order' => [
                            'type' => 'select',
                            'label' => 'Layout order', 
                            'default' => '1',
                            'choices' => [
                                '1' => 'First image, then text',
                                '2' => 'First text, then image',
                            ]
                        ],
                        'image' => [
                            'type' => 'fileupload',
                            'label' => 'Layout image',
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => 'https://via.placeholder.com/1100x213',
                            ],
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
                            'type' => 'checkbox', // type of field
                            'label' => $this->l('Obfuscate link'), // label to display
                            'default' => '0', // default value (String)
                        ],
                        'target_blank' => [
                            'type' => 'checkbox', // type of field
                            'label' => $this->l('Open in new tab (only if not obfuscated)'), // label to display
                            'default' => '0', // default value (String)
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Layout'),
                'description' => 'Add layout',
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
                            'type' => 'checkbox', // type of field
                            'label' => $this->l('Obfuscate link'), // label to display
                            'default' => '0', // default value (String)
                        ],
                        'target_blank' => [
                            'type' => 'checkbox', // type of field
                            'label' => $this->l('Open in new tab (only if not obfuscated)'), // label to display
                            'default' => '0', // default value (String)
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Supplier product list'),
                'description' => $this->l('Show supplier product list'),
                'code' => 'everblock_supplier_product_list',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $supplierProductListTemplate,
                ],
                'config' => [
                    'fields' => [
                        'supplier' => [
                            'type' => 'selector',
                            'label' => 'Please select a supplier',
                            'collection' => 'Supplier',
                            'default' => 'default value',
                            'selector' => '{id} - {name}'
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Manufacturer product list'),
                'description' => $this->l('Show manufacturer product list'),
                'code' => 'everblock_manufacturer_product_list',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $manufacturerProductListTemplate,
                ],
                'config' => [
                    'fields' => [
                        'manufacturer' => [
                            'type' => 'selector',
                            'label' => 'Please select a manufacturer',
                            'collection' => 'Manufacturer',
                            'default' => 'default value',
                            'selector' => '{id} - {name}'
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Products slider'),
                'description' => $this->l('Show products slider'),
                'code' => 'everblock_product_slider',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $productSliderListTemplate,
                ],
                'repeater' => [
                    'name' => 'Product',
                    'nameFrom' => 'name',
                    'groups' => [
                        'product' => [
                            'type' => 'selector',
                            'label' => 'Please select a product',
                            'collection' => 'Product',
                            'default' => 'default value',
                            'selector' => '{id} - {name}'
                        ],
                    ],
                ],
                'config' => [
                    'fields' => [
                        'slide_duration' => [
                            'type' => 'text',
                            'label' => $this->l('Slide duration (in milliseconds)'),
                            'default' => '2500',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Images slider'),
                'description' => $this->l('Show images slider (images must have same size)'),
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
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => 'https://via.placeholder.com/1100x213',
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
                            'type' => 'checkbox', // type of field
                            'label' => $this->l('Obfuscate link'), // label to display
                            'default' => '0', // default value (String)
                        ],
                        'target_blank' => [
                            'type' => 'checkbox', // type of field
                            'label' => $this->l('Open in new tab (only if not obfuscated)'), // label to display
                            'default' => '0', // default value (String)
                        ],
                    ],
                ],
                'config' => [
                    'fields' => [
                        'slide_duration' => [
                            'type' => 'text',
                            'label' => $this->l('Slide duration (in milliseconds)'),
                            'default' => '2500',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Tabs'),
                'description' => $this->l('Show custom tabs'),
                'code' => 'everblock_tab',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $tabTemplate,
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
                    ],
                ],
                'config' => [
                    'fields' => [
                        'slide_duration' => [
                            'type' => 'text',
                            'label' => $this->l('Slide duration (in milliseconds)'),
                            'default' => '2500',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Divider'),
                'description' => $this->l('Show divider'),
                'code' => 'everblock_divider',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $dividerTemplate,
                ],
                'config' => [
                    'fields' => [
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Gallery'),
                'description' => $this->l('Show image gallery (images must have same size)'),
                'code' => 'everblock_gallery',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $galleryTemplate,
                ],
                'repeater' => [
                    'name' => 'Tab',
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
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => 'https://via.placeholder.com/150',
                            ],
                        ],
                    ],
                ],
                'config' => [
                    'fields' => [
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Testimonials'),
                'description' => $this->l('Show custom testimonials'),
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
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => 'https://via.placeholder.com/60',
                            ],
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Tab content',
                            'default' => '[llorem]',
                        ],
                    ],
                ],
                'config' => [
                    'fields' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'Block title',
                            'default' => $this->l('Testimonials'),
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Tartiflette'),
                'description' => $this->l('Show a tartiflette'),
                'code' => 'everblock_tartiflette',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $tartifletteTemplate,
                ],
                'config' => [
                    'fields' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'In tartiflette we trust',
                            'default' => 'Tartiflette',
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                        'bootstrap_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom Bootstrap class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            Cache::store($cacheId, $blocks);
            return $blocks;
        }

        return Cache::retrieve($cacheId);
    }

    public function hookBeforeRenderingEverblockProduct($params)
    {
        $settings = $params['block']['settings'];
        $block = $params['block'];
        if ($settings) {
            if (isset($settings['product']['id'])) {
                $product = new Product(
                    (int) $settings['product']['id'],
                    false,
                    Context::getContext()->language->id,
                    Context::getContext()->shop->id
                );
                if (Validate::isLoadedObject($product)) {
                    $everPrettyPresentProduct = $this->everPresentProducts([$product->id]);
                    return ['presented' => $everPrettyPresentProduct[0]];
                }
            }
        }
        return $settings;
    }

    public function hookBeforeRenderingEverblockSupplierProductList($params)
    {
        $settings = $params['block']['settings'];
        $block = $params['block'];
        if ($settings) {
            if (isset($settings['supplier']['id'])) {
                $supplier = new Supplier(
                    (int) $settings['supplier']['id'],
                    Context::getContext()->language->id
                );
                if (Validate::isLoadedObject($supplier)) {
                    $products = EverblockTools::getProductIdsBySupplier(
                        $supplier->id
                    );
                    $everPrettyPresentProducts = $this->everPresentProducts($products);
                    return ['presenteds' => $everPrettyPresentProducts];
                }
            }
        }
        return $settings;
    }

    public function hookBeforeRenderingEverblockProductSlider($params)
    {
        $settings = $params['block']['settings'];
        $block = $params['block'];
        if ($settings) {
            if (isset($block['states'])) {
                $productIds = [];
                foreach ($block['states'] as $state) {
                    if (isset($state['product']['id'])) {
                        $productIds[] = $state['product']['id'];
                    }
                }
                $everPrettyPresentProducts = $this->everPresentProducts($productIds);
                return ['presenteds' => $everPrettyPresentProducts];
            }
        }
        return $settings;
    }

    public function hookBeforeRenderingEverblockManufacturerProductList($params)
    {
        $settings = $params['block']['settings'];
        $block = $params['block'];
        if ($settings) {
            if (isset($settings['manufacturer']['id'])) {
                $manufacturer = new Manufacturer(
                    (int) $settings['manufacturer']['id'],
                    Context::getContext()->language->id
                );
                if (Validate::isLoadedObject($manufacturer)) {
                    $products = EverblockTools::getProductIdsByManufacturer(
                        $manufacturer->id
                    );
                    $everPrettyPresentProducts = $this->everPresentProducts($products);
                    return ['presenteds' => $everPrettyPresentProducts];
                }
            }
        }
        return $settings;
    }

    public function hookBeforeRenderingEverblockGmap($params)
    {
        $settings = $params['block']['settings'];
        $block = $params['block'];
        if ($settings) {
            if (isset($settings['iframe'])) {
                $iframe = $settings['iframe'];
                
                if (isset($settings['width'])) {
                    $width = $settings['width'];
                    $iframe = preg_replace('/width="([^"]+)"/', 'width="' . $width . '"', $iframe);
                }
                
                if (isset($settings['height'])) {
                    $height = $settings['height'];
                    $iframe = preg_replace('/height="([^"]+)"/', 'height="' . $height . '"', $iframe);
                }
                
                return ['iframe' => $iframe];
            }
        }
        
        return $settings;
    }

    public function checkLatestEverModuleVersion($module, $version)
    {
        $upgrade_link = 'https://upgrade.team-ever.com/upgrade.php?module=' . $module . '&version=' . $version;
        try {
            $handle = curl_init($upgrade_link);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                curl_close($handle);
                return false;
            }
            curl_close($handle);
            $module_version = $response;
            if ($module_version && $module_version > $version) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Unable to check latest Ever Block version');
            return false;
        }
    }

    public function checkAndFixDatabase()
    {
        $db = Db::getInstance();
        // Ajoute les colonnes manquantes à la table ps_everblock
        $columnsToAdd = [
            'only_home' => 'int(10) unsigned DEFAULT NULL',
            'id_hook' => 'int(10) unsigned NOT NULL',
            'only_home' => 'int(10) unsigned DEFAULT NULL',
            'only_category' => 'int(10) unsigned DEFAULT NULL',
            'only_category_product' => 'int(10) unsigned DEFAULT NULL',
            'only_manufacturer' => 'int(10) unsigned DEFAULT NULL',
            'only_supplier' => 'int(10) unsigned DEFAULT NULL',
            'only_cms_category' => 'int(10) unsigned DEFAULT NULL',
            'obfuscate_link' => 'int(10) unsigned DEFAULT NULL',
            'add_container' => 'int(10) unsigned DEFAULT NULL',
            'lazyload' => 'int(10) unsigned DEFAULT NULL',
            'device' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
            'position' => 'int(10) unsigned DEFAULT 0',
            'categories' => 'text DEFAULT NULL',
            'manufacturers' => 'text DEFAULT NULL',
            'suppliers' => 'text DEFAULT NULL',
            'cms_categories' => 'text DEFAULT NULL',
            'groups' => 'text DEFAULT NULL',
            'background' => 'varchar(255) DEFAULT NULL',
            'css_class' => 'varchar(255) DEFAULT NULL',
            'bootstrap_class' => 'varchar(255) DEFAULT NULL',
            'date_start' => 'DATETIME DEFAULT NULL',
            'date_end' => 'DATETIME DEFAULT NULL',
            'active' => 'int(10) unsigned NOT NULL',
        ];

        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }

        // Ajoute les colonnes manquantes à la table ps_everblock_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'content' => 'text DEFAULT NULL',
            'custom_code' => 'text DEFAULT NULL',
        ];

        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }

        // Ajoute les colonnes manquantes à la table everblock_shortcode
        $columnsToAdd = [
            'shortcode' => 'text DEFAULT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL',
        ];

        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_shortcode` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_shortcode` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }

        // Ajoute les colonnes manquantes à la table everblock_shortcode_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'text DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];

        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_shortcode_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_shortcode_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }
    }

    protected function compressCSSCode($css)
    {
        // Supprime les commentaires
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Supprime les espaces inutiles
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        // Remplace les séparateurs de déclarations par des points-virgules
        $css = str_replace(';}', '}', $css);
        // Supprime les espaces inutiles entre les propriétés et les valeurs
        $css = preg_replace('/[\s]*:[\s]*(.*?)[\s]*;/', ':$1;', $css);
        return $css;
    }

    protected function compressJsCode($code)
    {
        // Supprimer les commentaires
        $code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code);

        // Supprimer les espaces inutiles
        $code = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $code);

        return $code;
    }

    protected function generateLoremIpsum()
    {
        $lloremParagraphNum = (int) Configuration::get('EVERPSCSS_P_LLOREM_NUMBER');
        if ($lloremParagraphNum <= 0) {
            $lloremParagraphNum = 5;
        }
        $lloremSentencesNum = (int) Configuration::get('EVERPSCSS_S_LLOREM_NUMBER');
        if ($lloremSentencesNum <= 0) {
            $lloremSentencesNum = 5;
        }
        $paragraphs = [];
        $sentences = [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
            'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
        ];
        for ($i = 0; $i < $lloremParagraphNum; $i++) {
            $paragraph = '<p>';
            for ($j = 0; $j < $lloremSentencesNum; $j++) {
                $sentence = $sentences[array_rand($sentences)];
                $paragraph .= $sentence . ' ';
            }
            $paragraph .= '</p>';
            $paragraphs[] = $paragraph;
        }
        return implode("\n\n", $paragraphs);
    }

    protected function compileSass($sassCode)
    {
        // Create a new instance of the ScssPhp Compiler
        $compiler = new \ScssPhp\ScssPhp\Compiler();

        try {
            // Compile the SASS code
            $compiledCss = $compiler->compile($sassCode);
            $this->postSuccess[] = $this->l('SASS compilation successful!');
            return $compiledCss;

        } catch (\Exception $e) {
            PrestaShopLogger::addLog('SASS Compilation Error: ' . $e->getMessage());
        }
    }

    protected function obfuscateText($text)
    {
        // Rechercher toutes les balises <a href> dans le texte
        $pattern = '/<a\s+(?:[^>]*)href="([^"]*)"([^>]*)>/i';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        // Parcourir les correspondances et remplacer les balises <a> par des balises <span>
        foreach ($matches as $match) {
            $linkUrl = $match[1];
            $linkAttributes = $match[2];
            $encodedLink = base64_encode($linkUrl);
            
            // Obtenir les classes existantes de la balise <a>
            preg_match('/class="([^"]*)"/i', $match[0], $classMatches);
            $existingClasses = !empty($classMatches[1]) ? $classMatches[1] : '';

            // Ajouter la classe 'obflink' aux classes existantes
            $classesWithObflink = $existingClasses . ' obflink';

            // Construire la nouvelle balise <span> avec les classes existantes et les attributs de lien
            $newTag = '<span class="' . $classesWithObflink . '" data-obflink="' . $encodedLink . '"' . $linkAttributes . '>';
            $text = str_replace($match[0], $newTag, $text);
        }

        return $text;
    }
    protected function addLazyLoadToImages($text)
    {
        // Rechercher toutes les balises <img> dans le texte
        $pattern = '/<img\s+(?:[^>]*)src="([^"]*)"([^>]*)>/i';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        // Parcourir les correspondances et ajouter la classe 'lazyload' et l'attribut 'loading="lazy"'
        foreach ($matches as $match) {
            $imageUrl = $match[1];
            $imageAttributes = $match[2];
            
            // Vérifier si la balise <img> contient déjà la classe 'lazyload'
            if (stripos($imageAttributes, 'class=') === false || stripos($imageAttributes, 'lazyload') === false) {
                // Ajouter la classe 'lazyload' aux classes existantes
                $imageAttributesWithLazyLoad = 'class="lazyload ' . $imageAttributes . '"';
            } else {
                $imageAttributesWithLazyLoad = $imageAttributes;
            }

            // Construire la nouvelle balise <img> avec la classe 'lazyload' et l'attribut 'loading="lazy"'
            $newTag = '<img src="' . $imageUrl . '" ' . $imageAttributesWithLazyLoad . ' loading="lazy">';
            $text = str_replace($match[0], $newTag, $text);
        }

        return $text;
    }
}
