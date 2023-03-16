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

require_once(dirname(__FILE__).'/models/EverblockClass.php');

class Everblock extends Module
{
    private $html;
    private $postErrors = [];
    private $postSuccess = [];

    public function __construct()
    {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '4.2.2';
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
            && $this->installModuleTab('AdminEverBlock', 'IMPROVE', $this->l('Block HTML')));
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
            && $this->uninstallModuleTab('AdminEverBlock'));
    }

    protected function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int) Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminEverBlock') {
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
        $this->registerHook('header');
        $this->registerHook('actionAdminControllerSetMedia');
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

        return $helper->generateForm(array($this->getConfigForm()));
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
        $custom_js = Tools::file_get_contents(
            _PS_MODULE_DIR_.'/' . $this->name . '/views/js/custom' . $idShop . '.js'
        );
        return array(
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
            'EVERPSCSS' => $custom_css,
            'EVERPSJS' => $custom_js,
            'EVERPSCSS_LINKS' => Configuration::get('EVERPSCSS_LINKS'),
            'EVERPSJS_LINKS' => Configuration::get('EVERPSJS_LINKS')
        );
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submit' . $this->name . 'Module')) {
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
        $compressedCssCode = preg_replace(
            [
                '/\s*(\w)\s*{\s*/',
                '/\s*(\S*:)(\s*)([^;]*)(\s|\n)*;(\n|\s)*/',
                '/\n/',
                '/\s*}\s*/',
            ], 
            [
                '$1{ ','$1$3;',"",'} ',
            ],
            $cssCode
        );
        // Compress JS code
        $compressedJsCode = $jsCode;
        $search = [
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s',
            '#[\r\n]+#',   
        ];
        $replace = [
            '>',
            '<',
            '\\1',
            '',
        ];
        $compressedJsCode = preg_replace($search, $replace, $compressedJsCode);
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
        if ((bool) Configuration::get('EVERPSCSS_CACHE') === true) {
            $this->emptyAllCache();
        }
        // Là, comme on a bien enregistré, c'est ici qu'on précise un message de succès
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
        if (Tools::getValue('id_everblock')) {
            $this->context->controller->addJs($this->_path . 'views/js/admin.js');
        }
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
        $controller_name = Tools::getValue('controller');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        // Get current hook name based on method name, first letter to lowercase
        $id_hook = Hook::getIdByName(lcfirst(str_replace('hook', '', $method)));
        $cacheId = $this->getCacheId($this->name . '-id_hook-' . $id_hook . '-' . date('Ymd'));
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
                if ((int) $block['device'] > 0
                    && (int) $this->context->getDevice() != (int) $block['device']
                ) {
                    continue;
                }
                // Is block only for homepage ?
                if ((bool)$block['only_home'] === true
                    && $controller_name != 'index'
                ) {
                    continue;
                }
                // Only category management
                if ((bool)$block['only_category'] === true
                    && $controller_name == 'index'
                ) {
                    continue;
                }
                $continue = false;
                if ((bool)$block['only_category'] === true) {
                    $categories = json_decode($block['categories']);
                    if (Tools::getValue('id_category')
                        && !in_array((int) Tools::getValue('id_category'), $categories)
                    ) {
                        $continue = true;
                    }
                    if (Tools::getValue('id_product')) {
                        $product = new Product(
                            (int) Tools::getValue('id_product')
                        );
                        if (!in_array((int) $product->id_category_default, $categories)) {
                            $continue = true;
                        }
                    }
                }
                if (isset($continue) && (bool)$continue === true) {
                    continue;
                }
                $block['content'] = $this->changeShortcodes(
                    $block['content'],
                    $id_entity
                );
                $currentBlock[] = [
                    'block' => $block,
                ];
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
                if ((bool)$block['only_home'] === true && $controller_name != 'index') {
                    $continue = true;
                }
                if ((bool)$block['only_category'] === true && $controller_name == 'index') {
                    $continue = true;
                }
                if (!$continue && (bool)$block['only_category'] === true) {
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
                if (!$continue) {
                    $block['content'] = $this->changeShortcodes($block['content'], $id_entity);
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

    private function changeShortcodes($message, $id_entity)
    {
        $link = new Link();
        $contactLink = $link->getPageLink('contact');
        if (Context::getContext()->customer->isLogged()) {
            $my_account_link = $link->getPageLink('my-account');
        } else {
            $my_account_link = $link->getPageLink('authentication');
        }
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
        if (!defined(_PS_PARENT_THEME_URI_) || empty(_PS_PARENT_THEME_URI_)) {
            $theme_uri = Tools::getShopDomainSsl(true) . _PS_THEME_URI_;
        } else {
            $theme_uri = Tools::getShopDomainSsl(true) . _PS_PARENT_THEME_URI_;
        }
        $defaultShortcodes = [
            '[shop_url]' => Tools::getShopDomainSsl(true),
            '[shop_name]'=> (string) Configuration::get('PS_SHOP_NAME'),
            '[start_cart_link]' => '<a href="'
            . Tools::getShopDomainSsl(true)
            . '/index.php?controller=cart&action=show" target="_blank">',
            '[end_cart_link]' => '</a>',
            '[start_shop_link]' => '<a href="'
            . Tools::getShopDomainSsl(true)
            . '" target="_blank">',
            '[start_contact_link]' => '<a href="' . $contactLink . '" target="_blank">',
            '[end_shop_link]' => '</a>',
            '[end_contact_link]' => '</a>',
            '[contact_link]'=> $contactLink,
            '[my_account_link]' => $my_account_link,
            '[theme_uri]' => $theme_uri,
            'NULL' => '', // Useful : remove empty strings in case of NULL
            'null' => '', // Useful : remove empty strings in case of null
        ];
        Hook::exec('actionEverBlockChangeShortcodeBefore', [
            'message' => &$message,
            'id_entity' => (int) $id_entity,
            'id_shop' => (int) Context::getContext()->shop->id,
            'id_lang' => (int) Context::getContext()->language->id,
        ]);
        if ($id_entity) {
            $shortcodes = array_merge($entityShortcodes, $defaultShortcodes);
        } else {
            $shortcodes = $defaultShortcodes;
        }
        foreach ($shortcodes as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        Hook::exec('actionEverBlockChangeShortcodeAfter', [
            'message' => &$message,
            'id_entity' => (int) $id_entity,
            'id_shop' => (int) Context::getContext()->shop->id,
            'id_lang' => (int) Context::getContext()->language->id,
        ]);
        return $message;
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
            return false;
        }
    }

    protected function checkAndFixDatabase() {
        // Requêtes pour créer les tables
        $createEverblockTable = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everblock` (
            `id_everblock` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` text NOT NULL,
            `id_hook` int(10) unsigned NOT NULL,
            `only_home` int(10) unsigned DEFAULT NULL,
            `only_category` int(10) unsigned DEFAULT NULL,
            `device` int(10) unsigned NOT NULL DEFAULT 0,
            `id_shop` int(10) unsigned NOT NULL,
            `position` int(10) unsigned DEFAULT 0,
            `categories` text DEFAULT NULL,
            `groups` text DEFAULT NULL,
            `background` varchar(255) DEFAULT NULL,
            `css_class` varchar(255) DEFAULT NULL,
            `date_start` DATETIME DEFAULT NULL,
            `date_end` DATETIME DEFAULT NULL,
            `active` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_everblock`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $createEverblockLangTable = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everblock_lang` (
            `id_everblock` int(10) unsigned NOT NULL,
            `id_lang` int(10) unsigned NOT NULL,
            `content` text DEFAULT NULL,
            `custom_code` text DEFAULT NULL,
            PRIMARY KEY (`id_everblock`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        // Exécute les requêtes pour créer les tables si elles n'existent pas
        Db::getInstance()->execute($createEverblockTable);
        Db::getInstance()->execute($createEverblockLangTable);
        // Vérifie et ajoute les colonnes manquantes pour la table 'everblock'
        $everblockColumns = [
            'id_everblock' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
            'name' => 'text NOT NULL',
            'id_hook' => 'int(10) unsigned NOT NULL',
            'only_home' => 'int(10) unsigned DEFAULT NULL',
            'only_category' => 'int(10) unsigned DEFAULT NULL',
            'device' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'id_shop' => 'int(10) unsigned NOT NULL',
            'position' => 'int(10) unsigned DEFAULT 0',
            'categories' => 'text DEFAULT NULL',
            'groups' => 'text DEFAULT NULL',
            'background' => 'varchar(255) DEFAULT NULL',
            'css_class' => 'varchar(255) DEFAULT NULL',
            'date_start' => 'DATETIME DEFAULT NULL',
            'date_end' => 'DATETIME DEFAULT NULL',
            'active' => 'int(10) unsigned NOT NULL'
        ];

        foreach ($everblockColumns as $column => $definition) {
            $columnExists = Db::getInstance()->getValue('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'everblock` LIKE "' . $column . '"');
            if (!$columnExists) {
                $addColumnQuery = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock` ADD `' . $column . '` ' . $definition;
                Db::getInstance()->execute($addColumnQuery);
            }
        }

        // Vérifie et ajoute les colonnes manquantes pour la table 'everblock_lang'
        $everblockLangColumns = [
            'id_everblock' => 'int(10) unsigned NOT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
            'content' => 'text DEFAULT NULL',
            'custom_code' => 'text DEFAULT NULL'
        ];

        foreach ($everblockLangColumns as $column => $definition) {
            $columnExists = Db::getInstance()->getValue('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'everblock_lang` LIKE "' . $column . '"');
            if (!$columnExists) {
                $addColumnQuery = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_lang` ADD `' . $column . '` ' . $definition;
                Db::getInstance()->execute($addColumnQuery);
            }
        }
    }
}
