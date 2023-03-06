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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/models/EverblockClass.php');

class Everblock extends Module
{
    private $html;

    public function __construct()
    {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '3.5.1';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
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
        if (_PS_VERSION_ < '1.6.1.6') {
            if ($this->isDisplayHookName(lcfirst(str_replace('hook', '', $method)))
              && strpos($method, 'display') !== false
            ) {
                return $this->everHook($method, $args);
            }
        } else {
            if (Hook::isDisplayHookName(lcfirst(str_replace('hook', '', $method)))) {
                return $this->everHook($method, $args);
            }
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
        if ($tabClass == 'AdminEverBlock' && $this->isSeven) {
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
        $this->registerHook('header');
        $this->registerHook('actionAdminControllerSetMedia');
    }

    public function getContent()
    {
        $this->checkHooks();
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
        $this->html .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    public function hookDisplayBackOfficeHeader()
    {
        return $this->hookActionAdminControllerSetMedia();
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path.'views/css/ever.css');
        $currentConfigure = Tools::getValue('configure');
    }

    public function everHook($method, $args)
    {
        if (_PS_VERSION_ < '1.6.1.6') {
            if (!$this->isDisplayHookName(lcfirst(str_replace('hook', '', $method)))
              || !strpos($method, 'display') !== false
            ) {
                return;
            }
        } else {
            if (!Hook::isDisplayHookName(lcfirst(str_replace('hook', '', $method)))) {
                return;
            }
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
                'id_everblock' => $block['id_everblock'],
                'content' => $block['content'],
            ];
        }
        $this->smarty->assign([
            'everhook' => trim($method),
            'everblock' => $currentBlock,
            'args' => $args,
        ]);
        return $this->display(__FILE__, 'everblock.tpl');
    }

    public function hookHeader()
    {
        $this->context->controller->addCss(
            _PS_MODULE_DIR_ . 'everblock/views/css/everblock.css',
            'all'
        );
        $this->context->controller->addJs(
            _PS_MODULE_DIR_ . 'everblock/views/js/everblock.js',
            'all'
        );
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
        if ($id_entity) {
            $shortcodes = array_merge($entityShortcodes, $defaultShortcodes);
        } else {
            $shortcodes = $defaultShortcodes;
        }
        foreach ($shortcodes as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }

    private function isDisplayHookName($hook_name)
    {
        if ($hook_name === 'header' || $hook_name === 'displayheader') {
            // this hook is to add resources to the <head> section of the page
            // so it doesn't display anything by itself
            return false;
        }
        return strpos($hook_name, 'display') === 0;
    }

    public function checkLatestEverModuleVersion($module, $version)
    {
        $upgrade_link = 'https://upgrade.team-ever.com/upgrade.php?module='
        . $module
        . '&version='
        . $version;
        $handle = curl_init($upgrade_link);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            curl_close($handle);
            return false;
        }
        $response = curl_close($handle);
        $module_version = Tools::file_get_contents(
            $upgrade_link
        );
        if ($module_version && $module_version > $version) {
            return true;
        }
        return false;
    }
}
