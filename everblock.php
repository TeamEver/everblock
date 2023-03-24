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
        $this->version = '4.5.1';
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
        // Install SQL
        $sql = [];
        include dirname(__FILE__).'/sql/install.php';

        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }
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
            && $this->registerHook('header')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->installModuleTab('AdminEverBlockParent', 'IMPROVE', $this->l('Ever Block'))
            && $this->installModuleTab('AdminEverBlock', 'AdminEverBlockParent', $this->l('HTML Blocks management'))
            && $this->installModuleTab('AdminEverBlockHook', 'AdminEverBlockParent', $this->l('Hooks management')));
    }

    public function uninstall()
    {
        // Uninstall SQL
        $sql = [];
        include dirname(__FILE__).'/sql/uninstall.php';
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }
        return (parent::uninstall()
            && $this->uninstallModuleTab('AdminEverBlockParent')
            && $this->uninstallModuleTab('AdminEverBlock')
            && $this->uninstallModuleTab('AdminEverBlockHook'));
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

    protected function checkHooks()
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
        // Vérifier si l'onglet "Hook management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlock');
        if (!$id_tab) {
            // L'onglet n'existe pas, créer un nouvel onglet
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlock';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            // Les noms des onglets doivent être traduits dans toutes les langues du site
            $tab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $this->l('HTML blocks management');
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
            $tab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $this->l('Hook management');
            $tab->add();
        }
        $this->registerHook('actionOutputHTMLBefore');
        $this->registerHook('header');
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
                    'createHook' => [
                        'name' => 'submitCreateHook',
                        'type' => 'submit',
                        'class' => 'btn btn-info pull-right',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Create hook'),
                    ],
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
        $custom_js = Tools::file_get_contents(
            _PS_MODULE_DIR_.'/' . $this->name . '/views/js/custom' . $idShop . '.js'
        );
        return [
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
            'EVERPSCSS' => $custom_css,
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
        $custom_css = _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom' . $idShop . '.css';
        $custom_js = _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom' . $idShop . '.js';
        // Compressed
        $compressedCss = _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $compressedJs = _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom-compressed' . $idShop . '.js';
        $cssCode = Tools::getValue('EVERPSCSS');
        $jsCode = Tools::getValue('EVERPSJS');
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
        if (Tools::getValue('id_everblock') || Tools::getValue('configure') == $this->name) {
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
            foreach ($shortcodes as $key => $value) {
                $txt = preg_replace(
                    '/(?<!\w|[&\'"])' . preg_quote($key, '/') . '(?!\w|;)/',
                    $value,
                    $txt
                );
            }
            $params['html'] = $txt;
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'Ever Block : unable to rewrite HTML page'
            );
        }
        return $params['html'];
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
        $cacheId = $this->getCacheId($this->name . '-id_hook-' . $id_hook . '-controller-' . Tools::getValue('controller') . '-device-' . $this->context->getDevice());
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
                // Check device
                if ((int) $block['device'] > 0 && (int) $this->context->getDevice() != (int) $block['device']) {
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
                    && !$this->context->controller instanceof CategoryController
                ) {
                    continue;
                }
                if ((bool) $block['only_category'] === true
                    && Tools::getValue('controller') != 'category'
                ) {
                    continue;
                }
                $continue = false;

                if ((bool) $block['only_category'] === true) {
                    $categories = json_decode($block['categories'], true);
                    $categoryId = (int) Tools::getValue('id_category');
                    $isCategorySelected = !empty($categoryId) && !empty($categories) && in_array($categoryId, $categories);

                    if (Tools::getValue('id_product')) {
                        $product = new Product((int) Tools::getValue('id_product'));
                        $isProductSelected = (bool) Validate::isLoadedObject($product);
                    } else {
                        $isProductSelected = false;
                    }

                    if (!$isCategorySelected) {
                        $continue = true;
                    }
                    if (!$isProductSelected) {
                        $continue = true;
                    }
                }

                if ((bool) $continue === true || empty($block['id_hook'])) {
                    continue;
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

    public function hookHeader()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $this->context->controller->addCss(
            _PS_MODULE_DIR_ . $this->name . '/views/css/everblock.css',
            'all'
        );
        $this->context->controller->addJs(
            _PS_MODULE_DIR_ . $this->name . '/views/js/everblock.js',
            'all'
        );
        $custom_css = _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $custom_js = _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom-compressed' . $idShop . '.js';
        if (file_exists($custom_css)) {
            $this->context->controller->addCSS($this->_path . '/views/css/custom-compressed' . $idShop . '.css');
        }
        if (file_exists($custom_js)) {
            $this->context->controller->addJS($this->_path . '/views/js/custom-compressed' . $idShop . '.js');
        }
        // Get current hook name based on method name, first letter to lowercase
        $cacheId = $this->getCacheId($this->name . '-custom-header-' . date('Ymd'));
        if (!$this->isCached('everblockheader.tpl', $cacheId)) {
            $everblock = EverblockClass::getHeaderBlocks(
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $currentBlock = [];
            foreach ($everblock as $block) {
                $continue = false;
                if ((int) $block['device'] > 0 && (int) $this->context->getDevice() != (int) $block['device']) {
                    $continue = true;
                }
                if ((bool) $block['only_home'] === true
                    && !$this->context->controller instanceof IndexController
                ) {
                    $continue = true;
                }
                if ((bool) $block['only_category'] === true
                    && !$this->context->controller instanceof IndexController
                ) {
                    $continue = true;
                }
                if (!$continue && (bool) $block['only_category'] === true) {
                    $categories = json_decode($block['categories'], true);
                    $id_category = (int) Tools::getValue('id_category');
                    $id_product = (int) Tools::getValue('id_product');
                    if ($id_category && !in_array($id_category, $categories)) {
                        $continue = true;
                    }
                    if ($id_product) {
                        $product = new Product($id_product);
                        if (!in_array((int) $product->id_category_default, $categories)) {
                            $continue = true;
                        }
                    }
                }
                if (empty($block['custom_code'])) {
                    continue;
                }
                if ((bool) $continue === false) {
                    if (Context::getContext()->controller->controller_type == 'admin'
                        || Context::getContext()->controller->controller_type == 'moduleadmin'
                    ) {
                        $id_entity = Context::getContext()->employee->id;
                    } else {
                        $id_entity = Context::getContext()->customer->id;
                    }
                    $currentBlock[] = [
                        'block' => $block,
                    ];
                }
            }
            $this->smarty->assign([
                'everhook' => 'header',
                'everblock' => $currentBlock,
            ]);
        }
        return $this->display(__FILE__, 'everblockheader.tpl', $cacheId);
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

    protected function getProductShortcodes($message)
    {
        $productShortcodes = [];
        preg_match_all('/(\[product\s+\d+\])/i', $message, $matches);

        foreach ($matches[0] as $match) {
            $productId = (int) substr($match, 9, -1);
            $product = new Product(
                $productId,
                false,
                Context::getContext()->language->id,
                Context::getContext()->shop->id
            );
            if (Validate::isLoadedObject($product)) {
                $everPresentProducts = $this->everPresentProducts([$product->id]);
                $this->context->smarty->assign('everPresentProducts', $everPresentProducts);
                $productShortcodes[$match] = $this->context->smarty->fetch($this->getTemplatePath('ever_presented_products.tpl'));
            }
        }

        return $productShortcodes;
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
        $blocks = [];
        // Get all blocks
        $allBlocks = EverblockClass::getAllBlocks(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );

        $template = 'module:' . $this->name . '/views/templates/hook/everblock.tpl';
        // Add each block to the array
        foreach ($allBlocks as $block) {
            // Add block to the array
            $blocks[] =  [
                'name' => $this->displayName,
                'description' => $block['title'],
                'code' => $block['content'],
                'tab' => 'general',
                'icon' => 'DocumentTextIcon',
                'need_reload' => true,
                'templates' => [
                    'default' => $template,
                ],
                'config' => [
                    'fields' => [
                        'text' => [
                            'type' => 'editor',
                            'label' => 'Text HTML',
                            'default' => $block['content'],
                        ],
                    ],
                ],
            ];
        }
        die(var_dump($blocks));
        return $blocks;
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

    protected function checkAndFixDatabase()
    {
        $db = Db::getInstance();
        // Ajoute les colonnes manquantes à la table ps_everblock
        $columnsToAdd = [
            'only_home' => 'int(10) unsigned DEFAULT NULL',
            'id_hook' => 'int(10) unsigned NOT NULL',
            'only_home' => 'int(10) unsigned DEFAULT NULL',
            'only_category' => 'int(10) unsigned DEFAULT NULL',
            'device' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
            'position' => 'int(10) unsigned DEFAULT 0',
            'categories' => 'text DEFAULT NULL',
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
                $paragraph .= $sentence.' ';
            }
            $paragraph .= '</p>';
            $paragraphs[] = $paragraph;
        }
        return implode("\n\n", $paragraphs);
    }

    protected function compileScss($inputFile, $outputFile)
    {
        $compiler = new Compiler();
        $compiler->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);
        $css = $compiler->compile(Tools::file_get_contents($inputFile));
        file_put_contents($outputFile, $css);
    }
}
