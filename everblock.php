<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once('vendor/autoload.php');
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockClass.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockShortcode.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockTools.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockTabsClass.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockFaq.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockPrettyBlocks.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockCache.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockGpt.php';

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;
use ScssPhp\ScssPhp\Compiler;
use Everblock\Tools\Service\ImportFile;

class Everblock extends Module
{
    private $html;
    private $postErrors = [];
    private $postSuccess = [];

    public function __construct()
    {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '5.5.8';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Ever Block');
        $this->description = $this->l('Add HTML block everywhere !');
        $this->confirmUninstall = $this->l('Do yo really want to uninstall this module ?');
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
    }

    public function __call($method, $args)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = [
            'front',
            'modulefront',
        ];
        $context = Context::getContext();
        if (!in_array($context->controller->controller_type, $controllerTypes)) {
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
        Configuration::updateValue('EVERPS_TAB_NB', 5);
        // Install SQL
        $sql = [];
        include dirname(__FILE__) . '/sql/install.php';
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
            && $this->installModuleTab('AdminEverBlockParent', 'IMPROVE', $this->l('Ever Block'))
            && $this->installModuleTab('AdminEverBlock', 'AdminEverBlockParent', $this->l('HTML Blocks'))
            && $this->installModuleTab('AdminEverBlockHook', 'AdminEverBlockParent', $this->l('Hooks'))
            && $this->installModuleTab('AdminEverBlockShortcode', 'AdminEverBlockParent', $this->l('Shortcodes')))
            && $this->installModuleTab('AdminEverBlockFaq', 'AdminEverBlockParent', $this->l('FAQ'));
    }

    public function uninstall()
    {
        // Uninstall SQL
        $sql = [];
        include dirname(__FILE__) . '/sql/uninstall.php';
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
            && $this->uninstallModuleTab('AdminEverBlockShortcode')
            && $this->uninstallModuleTab('AdminEverBlockFaq'));
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
        $tab = new Tab((int) Tab::getIdFromClassName($tabClass));
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
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockParent';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('IMPROVE');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('Ever Block');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "AdminEverBlock" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlock');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlock';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('HTML blocks management');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "Hook management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockHook');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockHook';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('Hook management');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "Shortcodes management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockShortcode');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockShortcode';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('Shortcodes management');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "FAQ management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockFaq');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockFaq';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('FAQ');
            }
            $tab->add();
        }
        $this->unregisterHook('actionDispatcherBefore');
        $this->registerHook('actionGetAdminOrderButtons');
        $this->registerHook('displayAdminCustomers');
        $this->registerHook('actionCustomerLogoutBefore');
        $this->registerHook('displayAdminProductsExtra');
        $this->registerHook('actionObjectProductUpdateAfter');
        $this->registerHook('actionObjectProductDeleteAfter');
        $this->registerHook('displayProductExtraContent');
        $this->registerHook('actionOutputHTMLBefore');
        $this->registerHook('displayHeader');
        $this->registerHook('actionAdminControllerSetMedia');
        $this->registerHook('actionObjectEverBlockClassUpdateAfter');
        $this->registerHook('actionObjectEverBlockClassDeleteAfter');
        $this->registerHook('actionObjectEverblockFaqUpdateAfter');
        $this->registerHook('actionObjectEverblockFaqDeleteAfter');
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) EverblockTools::moduleDirectoryExists('prettyblocks') === true
        ) {
            $this->registerHook('actionRegisterBlock');
        } else {
            $this->unregisterHook('actionRegisterBlock');
        }
    }

    public function getContent()
    {
        $this->createUpgradeFile();
        $this->secureModuleFolder();
        EverblockTools::checkAndFixDatabase();
        $this->checkHooks();
        $this->html = '';
        if (((bool) Tools::isSubmit('submit' . $this->name . 'Module')) == true) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }

        if ((bool) Tools::isSubmit('submitUploadTabsFile') === true) {
            $this->uploadTabsFile();
        }
        if ((bool) Tools::isSubmit('submitEmptyCache') === true) {
            $this->emptyAllCache();
        }
        if ((bool) Tools::isSubmit('submitEmptyLogs') === true) {
            $purged = EverblockTools::purgeNativePrestashopLogsTable();
            if ((bool) $purged === true) {
                $this->postSuccess[] = $this->l('Log tables emptied');
            } else {
                $this->postErrors[] = $this->l('Log tables NOT emptied');
            }
        }
        if ((bool) Tools::isSubmit('submitDropUnusedLangs') === true) {
            $dropped = EverblockTools::dropUnusedLangs();
            if (is_array($dropped)
                && isset($dropped['postErrors'])
                && count($dropped['postErrors']) > 0
            ) {
                foreach ($dropped['postErrors'] as $postErrors) {
                    $this->postErrors[] = $postErrors;
                }
            }
            if (is_array($dropped)
                && isset($dropped['querySuccess'])
                && count($dropped['querySuccess']) > 0
            ) {
                foreach ($dropped['querySuccess'] as $querySuccess) {
                    $this->postSuccess[] = $querySuccess;
                }
            }
        }
        if ((bool) Tools::isSubmit('submitBackupBlocks') === true) {
            $backuped = EverblockTools::exportModuleTablesSQL();
            if ((bool) $backuped === true) {
                $this->postSuccess[] = $this->l('Backup done');
            } else {
                $this->postErrors[] = $this->l('Backup failed');
            }
        }
        if ((bool) Tools::isSubmit('submitRestoreBackup') === true) {
            $restored = EverblockTools::restoreModuleTablesFromBackup();
            if ((bool) $restored === true) {
                $this->postSuccess[] = $this->l('Restore done');
            } else {
                $this->postErrors[] = $this->l('Restore failed');
            }
        }
        if ((bool) Tools::isSubmit('submitCreateProduct') === true) {
            $created = EverblockTools::generateProducts(
                (int) $this->context->shop->id
            );
            if ((bool) $created === true) {
                $this->postSuccess[] = $this->l('Products creation done');
            } else {
                $this->postErrors[] = $this->l('Products creation failed');
            }
        }
        if ((bool) Tools::isSubmit('submitMigrateUrls') === true
            && Tools::getValue('EVERPS_OLD_URL')
            && Tools::getValue('EVERPS_NEW_URL')
        ) {
            $migration = EverblockTools::migrateUrls(
                Tools::getValue('EVERPS_OLD_URL'),
                Tools::getValue('EVERPS_NEW_URL'),
                (int) $this->context->shop->id
            );
            if (is_array($migration)
                && isset($migration['postErrors'])
                && count($migration['postErrors']) > 0
            ) {
                foreach ($migration['postErrors'] as $postErrors) {
                    $this->postErrors[] = $postErrors;
                }
            }
            if (is_array($migration)
                && isset($migration['querySuccess'])
                && count($migration['querySuccess']) > 0
            ) {
                foreach ($migration['querySuccess'] as $querySuccess) {
                    $this->postSuccess[] = $querySuccess;
                }
            }
        }
        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }
        $blockAdminLink = 'index.php?controller=AdminEverBlock&token=';
        $blockAdminLink .= Tools::getAdminTokenLite('AdminEverBlock');
        $faqAdminLink = 'index.php?controller=AdminEverBlockFaq&token=';
        $faqAdminLink .= Tools::getAdminTokenLite('AdminEverBlockFaq');
        $hookAdminLink = 'index.php?controller=AdminEverBlockHook&token=';
        $hookAdminLink .= Tools::getAdminTokenLite('AdminEverBlockHook');
        $shortcodeAdminLink = 'index.php?controller=AdminEverBlockShortcode&token=';
        $shortcodeAdminLink .= Tools::getAdminTokenLite('AdminEverBlockShortcode');
        $this->context->smarty->assign([
            $this->name . '_dir' => $this->_path,
            'block_admin_link' => $blockAdminLink,
            'faq_admin_link' => $faqAdminLink,
            'hook_admin_link' => $hookAdminLink,
            'shortcode_admin_link' => $shortcodeAdminLink,
        ]);
        $this->html .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/header.tpl'
        );
        if ($this->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                $this->local_path . 'views/templates/admin/upgrade.tpl'
            );
        }
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/configure.tpl'
        );
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
        return $helper->generateForm($this->getConfigForm());
    }

    protected function getConfigForm()
    {
        $formFields = [];
        $formFields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Tools'),
                    'icon' => 'icon-smile',
                ],
                'buttons' => [
                    'emptyCache' => [
                        'name' => 'submitEmptyCache',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Empty cache'),
                    ],
                    'emptyLogs' => [
                        'name' => 'submitEmptyLogs',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Empty logs'),
                    ],
                    'dropUnusedLangs' => [
                        'name' => 'submitDropUnusedLangs',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Drop unused langs'),
                    ],
                    'backupBlocks' => [
                        'name' => 'submitBackupBlocks',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Backup all blocks'),
                    ],
                    'restoreBackup' => [
                        'name' => 'submitRestoreBackup',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Restore backup'),
                    ],
                ],
            ],
        ];
        $formFields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Chat GPT API key'),
                        'desc' => $this->l('Add here your Chat GPT API key, it will be used for blocks generator'),
                        'hint' => $this->l('Without API key, blocks generator won\'t work'),
                        'name' => 'EVERGPT_API_KEY',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Use Google Map for store locator instead of OSM'),
                        'desc' => $this->l('Will use Google Map API for store locator (CMS page only)'),
                        'hint' => $this->l('Else Open Street Map will be used'),
                        'name' => 'EVERBLOCK_USE_GMAP',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Google Map API key (CMS page only)'),
                        'desc' => $this->l('Add here your Google Map API key'),
                        'hint' => $this->l('Without API key, auto complete wont work'),
                        'name' => 'EVERBLOCK_GMAP_KEY',
                    ],
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
                        'label' => $this->l('Enable front-office script for obfuscation ?'),
                        'desc' => $this->l('Will load JS file to manage obfuscated links'),
                        'hint' => $this->l('Leave it to "No" if you already have a script that manages obfuscated links'),
                        'name' => 'EVERBLOCK_USE_OBF',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
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
                        'label' => $this->l('Number of fictitious products to create during product generation'),
                        'desc' => $this->l('Will generate this number of dummy products when generating'),
                        'hint' => $this->l('Default will be 5'),
                        'name' => 'EVERPS_DUMMY_NBR',
                        'lang' => false,
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
                    [
                        'type' => 'text',
                        'label' => $this->l('Migration : Old URL'),
                        'desc' => $this->l('If an old URL and a new one are entered, the module will be able to change the old URL to the new one in the database.'),
                        'hint' => $this->l('Leave empty for no use'),
                        'name' => 'EVERPS_OLD_URL',
                        'lang' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Migration : New URL'),
                        'desc' => $this->l('If an old URL and a new one are entered, the module will be able to change the old URL to the new one in the database.'),
                        'hint' => $this->l('Leave empty for no use'),
                        'name' => 'EVERPS_NEW_URL',
                        'lang' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Number of tabs for the product page'),
                        'desc' => $this->l('Specify here the number of tabs you want to have on the product page'),
                        'hint' => $this->l('The default value is 1 and cannot be less than 1'),
                        'name' => 'EVERPS_TAB_NB',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Title for global catalog tab'),
                        'desc' => $this->l('This text will be your global catalog tab title'),
                        'hint' => $this->l('Leaving empty will hide tab'),
                        'name' => 'EVER_TAB_TITLE',
                        'lang' => true,
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Text shown on global catalog tab'),
                        'desc' => $this->l('This text will be show on all products'),
                        'hint' => $this->l('Leaving empty will hide tab'),
                        'name' => 'EVER_TAB_CONTENT',
                        'lang' => true,
                        'required' => true,
                        'autoload_rte' => true,
                    ],
                ],
                'buttons' => [
                    'migrateUrls' => [
                        'name' => 'submitMigrateUrls',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Migrate URLS'),
                    ],
                    'createProducts' => [
                        'name' => 'submitCreateProduct',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Create fake products'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
        $formFields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('File management'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'file',
                        'label' => $this->l('Upload Excel tabs file'),
                        'desc' => $this->l('Will upload Excel tabs file and import datas into this module'),
                        'hint' => $this->l('You can then import this file in order to set up your tabs in bulk on the product sheets'),
                        'name' => 'TABS_FILE',
                        'display_image' => false,
                        'required' => false,
                    ],
                ],
                'buttons' => [
                    'import' => [
                        'name' => 'submitUploadTabsFile',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-download',
                        'title' => $this->l('Upload file'),
                    ],
                ],
            ],
        ];
        return $formFields;
    }

    protected function getConfigFormValues()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $custom_css = Tools::file_get_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom' . $idShop . '.css'
        );
        $custom_sass = Tools::file_get_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom' . $idShop . '.scss'
        );
        $custom_js = Tools::file_get_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom' . $idShop . '.js'
        );
        return [
            'EVERGPT_API_KEY' => Configuration::get('EVERGPT_API_KEY'),
            'EVERBLOCK_USE_GMAP' => Configuration::get('EVERBLOCK_USE_GMAP'),
            'EVERBLOCK_GMAP_KEY' => Configuration::get('EVERBLOCK_GMAP_KEY'),
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
            'EVERBLOCK_USE_OBF' => Configuration::get('EVERBLOCK_USE_OBF'),
            'EVERPSCSS' => $custom_css,
            'EVERPSSASS' => $custom_sass,
            'EVERPSJS' => $custom_js,
            'EVERPSCSS_LINKS' => Configuration::get('EVERPSCSS_LINKS'),
            'EVERPSJS_LINKS' => Configuration::get('EVERPSJS_LINKS'),
            'EVERPS_DUMMY_NBR' => Configuration::get('EVERPS_DUMMY_NBR'),
            'EVERPSCSS_P_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_P_LLOREM_NUMBER'),
            'EVERPSCSS_S_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_S_LLOREM_NUMBER'),
            'EVERBLOCK_TINYMCE' => Configuration::get('EVERBLOCK_TINYMCE'),
            'EVERPS_OLD_URL' => '',
            'EVERPS_NEW_URL' => '',
            'EVER_TAB_CONTENT' => $this->getConfigInMultipleLangs('EVER_TAB_CONTENT'),
            'EVER_TAB_TITLE' => $this->getConfigInMultipleLangs('EVER_TAB_TITLE'),
            'EVERPS_TAB_NB' => Configuration::get('EVERPS_TAB_NB'),
            'TABS_FILE' => '',
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
            if (!Tools::getValue('EVERPS_TAB_NB')
                || !Validate::isInt(Tools::getValue('EVERPS_TAB_NB'))
                || (int) Tools::getValue('EVERPS_TAB_NB') < 1
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Number of tabs" is not valid'
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
        $idShop = (int) Context::getContext()->shop->id;
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
        if (!empty($custom_css)) {
            $handle_css = fopen(
                $custom_css,
                'w+'
            );
            fclose($handle_css);
        }
        if (!empty($compressedCss)) {
            $handle_css = fopen(
                $compressedCss,
                'w+'
            );
            fclose($handle_css);
        }
        // Create JS file if need
        if (!empty($custom_js)) {
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
        }
        $tabTitle = [];
        $tabContent = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tabTitle[$lang['id_lang']] = (Tools::getValue(
                'EVER_TAB_TITLE_' . $lang['id_lang']
            ))
            ? Tools::getValue(
                'EVER_TAB_TITLE_' . $lang['id_lang']
            ) : '';
            $tabContent[$lang['id_lang']] = (Tools::getValue(
                'EVER_TAB_CONTENT_' . $lang['id_lang']
            ))
            ? Tools::getValue(
                'EVER_TAB_CONTENT_' . $lang['id_lang']
            ) : '';
        }
        Configuration::updateValue(
            'EVER_TAB_CATS',
            json_encode(Tools::getValue('EVER_TAB_CATS')),
            true
        );
        Configuration::updateValue(
            'EVER_TAB_TITLE',
            $tabTitle,
            true
        );
        Configuration::updateValue(
            'EVER_TAB_CONTENT',
            $tabContent,
            true
        );
        Configuration::updateValue(
            'EVERPSCSS_CACHE',
            Tools::getValue('EVERPSCSS_CACHE')
        );
        Configuration::updateValue(
            'EVERBLOCK_USE_OBF',
            Tools::getValue('EVERBLOCK_USE_OBF')
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
            'EVERGPT_API_KEY',
            Tools::getValue('EVERGPT_API_KEY')
        );
        Configuration::updateValue(
            'EVERBLOCK_USE_GMAP',
            Tools::getValue('EVERBLOCK_USE_GMAP')
        );
        Configuration::updateValue(
            'EVERBLOCK_GMAP_KEY',
            Tools::getValue('EVERBLOCK_GMAP_KEY')
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
            'EVERPS_DUMMY_NBR',
            Tools::getValue('EVERPS_DUMMY_NBR')
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
        Configuration::updateValue(
            'EVERPS_TAB_NB',
            Tools::getValue('EVERPS_TAB_NB')
        );
        if ((bool) Tools::getValue('EVERPSCSS_CACHE') === true) {
            $this->emptyAllCache();
        }
        if (Tools::getValue('EVERBLOCK_GMAP_KEY')) {
            $stores = Store::getStores((int) Context::getContext()->language->id);
            if (!empty($stores)) {
                $markers = [];
                foreach ($stores as $store) {
                    $marker = [
                        'lat' => $store['latitude'],
                        'lng' => $store['longitude'],
                        'title' => $store['name'],
                    ];
                    $markers[] = $marker;
                }
                $gmapScript = EverblockTools::generateGoogleMapScript($markers);
                if ($gmapScript) {
                    $filename = 'store-locator-' . $idShop . '.js';
                    $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
                    file_put_contents($filePath, $gmapScript);
                }
            }
        } else {
            $filename = 'store-locator-' . $idShop . '.js';
            $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    protected function emptyAllCache()
    {
        EverblockCache::cleanThemeCache();
        Tools::clearAllCache();
        $this->postSuccess[] = $this->l('Cache has been cleared');
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path . 'views/css/ever.css');
        if (Tools::getValue('id_' . $this->name)
            || Tools::getIsset('add' . $this->name)
            || Tools::getValue('configure') == $this->name
            || Tools::getValue('id_' . $this->name . '_faq')
            || Tools::getIsset('add' . $this->name . '_faq')
        ) {
            $this->context->controller->addCSS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/codemirror.min.css',
                'all'
            );
            $this->context->controller->addCSS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/theme/dracula.min.css',
                'all'
            );
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
            // @Todo : move to EverblockShortcodes
            $txt = EverblockTools::renderShortcodes($txt, $context, $this);
            $params['html'] = $txt;
            return $params['html'];
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'Ever Block hookActionOutputHTMLBefore : ' . $e->getMessage()
            );
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
            return $params['html'];
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (!$params['id_product']) {
            return;
        }

        $productId = (int) $params['id_product'];
        $tabsNumber = max((int) Configuration::get('EVERPS_TAB_NB'), 1);
        
        $everpstabs = EverblockTabsClass::getByIdProductInAdmin($productId, $this->context->shop->id);

        $tabsData = [];
        for ($i = 1; $i <= $tabsNumber; $i++) {
            foreach ($everpstabs as $everpstab) {
                if ($everpstab->id_tab == $i) {
                    $tabsData[$i] = $everpstab;
                    break;
                }
            }

            if (!array_key_exists($i, $tabsData)) {
                $tabsData[$i] = null;
            }
        }

        $everAjaxUrl = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'token' => Tools::getAdminTokenLite('AdminModules')]);

        $this->smarty->assign([
            'tabsData' => $tabsData,
            'default_language' => $this->context->employee->id_lang,
            'ever_languages' => Language::getLanguages(false),
            'ever_ajax_url' => $everAjaxUrl,
            'ever_product_id' => $productId,
            'tabsRange' => range(1, $tabsNumber),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/productTab.tpl');
    }

    public function hookActionObjectEverBlockClassDeleteAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockClass_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
    }

    public function hookActionObjectEverBlockClassUpdateAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockClass_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
    }

    public function hookActionObjectEverblockFaqDeleteAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockShortcode_getFaqByTagName_';
        EverblockCache::cacheDropByPattern($cachePattern);
    }

    public function hookActionObjectEverblockFaqUpdateAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockShortcode_getFaqByTagName_';
        EverblockCache::cacheDropByPattern($cachePattern);
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = ['admin', 'moduleadmin'];
        $context = Context::getContext();
        if (!in_array($context->controller->controller_type, $controllerTypes)) {
            return;
        }
        try {
            $tabsNumber = (int) Configuration::get('EVERPS_TAB_NB');
            if ($tabsNumber < 1) {
                $tabsNumber = 1;
                Configuration::updateValue('EVERPS_TAB_NB', 1);
            }
            $tabsRange = range(1, $tabsNumber);
            foreach ($tabsRange as $tab) {
                $everpstabs = EverblockTabsClass::getByIdProductIdTab(
                    (int) $params['object']->id,
                    (int) $context->shop->id,
                    (int) $tab
                );
                foreach (Language::getLanguages(true) as $language) {
                    if (Tools::getValue((int) $tab . '_everblock_title_' . $language['id_lang'])
                        && !Validate::isCleanHtml(Tools::getValue((int) $tab . '_everblock_title_' . $language['id_lang']))
                    ) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Title is not valid'),
                            ]
                        ));
                    } else {
                        $everpstabs->title[$language['id_lang']] = Tools::getValue((int) $tab . '_everblock_title_' . $language['id_lang']);
                    }
                    if (Tools::getValue((int) $tab . '_everblock_content_' . $language['id_lang'])
                        && !Validate::isCleanHtml(Tools::getValue((int) $tab . '_everblock_content_' . $language['id_lang']))
                    ) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Content is not valid'),
                            ]
                        ));
                    } else {
                        $everpstabs->content[$language['id_lang']] = Tools::getValue(
                            (int) $tab . '_everblock_content_' . $language['id_lang']
                        );
                    }
                }
                $everpstabs->id_tab = (int) $tab;
                $everpstabs->id_product = (int) $params['object']->id;
                $everpstabs->id_shop = (int) $context->shop->id;
                $everpstabs->save();
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $everpstabs = EverblockTabsClass::getByIdProductInAdmin(
            (int) Tools::getValue('id_product'),
            (int) $this->context->shop->id
        );
        foreach ($everpstabs as $everpstab) {
            if (Validate::isLoadedObject($everpstab)) {
                $everpstab->delete();
            }
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        $context = Context::getContext();
        $tab = [];
        $product = new Product(
            (int) $params['product']->id,
            false,
            (int) $context->language->id,
            (int) $context->shop->id
        );
        // Specific product tab
        $everpstabs = EverblockTabsClass::getByIdProduct(
            (int) $product->id,
            (int) $context->shop->id,
            (int) $context->language->id
        );
        foreach ($everpstabs as $everpstab) {
            if (Validate::isLoadedObject($everpstab)) {
                $title = $everpstab->title;
                $content = $everpstab->content;
                if (!empty($title) || !empty($content)) {
                    $tab[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                        ->setTitle($title)
                        ->setContent($content);
                }
            }
        }
        // Global tab
        $titleLangs = $this->getConfigInMultipleLangs('EVER_TAB_TITLE');
        $title = $titleLangs[
            (int) $context->language->id
        ];
        $contentLangs = $this->getConfigInMultipleLangs('EVER_TAB_CONTENT');
        $content = $contentLangs[
            (int) $context->language->id
        ];
        if (!empty($title) && !empty($content)) {
            $tab[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                ->setTitle($title)
                ->setContent($content);
        }
        return $tab;
    }

    public function hookDisplayAdminCustomers($params)
    {
        if (isset($params['id_customer']) && $params['id_customer']) {
            $customerId = (int) $params['id_customer'];
        } else {
            $order = new Order((int) $params['id_order']);
            $customerId = (int) $order->id_customer;
        }
        $customer = new Customer(
            $customerId
        );
        $link = new Link();
        if (Validate::isLoadedObject($customer)) {
            $everToken = Tools::encrypt($this->name . '/everlogin');
            $this->context->smarty->assign(array(
                'login_customer' => $customer,
                'lastname' => $customer->lastname,
                'firstname' => $customer->firstname,
                'base_uri' => __PS_BASE_URI__,
                'login_link' => $link->getModuleLink(
                    $this->name,
                    'everlogin',
                    [
                        'id_ever_customer' => $customer->id,
                        'evertoken' => $everToken,
                        'ever_id_cart' => Cart::lastNoneOrderedCart($customer->id),
                    ]
                )
            ));
        }
        $this->context->smarty->assign([
            $this->name . '_dir' => $this->_path . 'views/img/',
            'evertoken' => $everToken,
            'base_uri' => __PS_BASE_URI__,
        ]);
        return $this->display(__FILE__, 'views/templates/admin/customerConnect.tpl');
    }

    /**
     * Add buttons to main buttons bar
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        $order = new Order(
            (int) $params['id_order']
        );
        if (Validate::isLoadedObject($order)) {
            $everToken = Tools::encrypt($this->name . '/everlogin');
            $link = new Link();
            $connectLink = $link->getModuleLink(
                $this->name,
                'everlogin',
                [
                    'id_ever_customer' => $order->id_customer,
                    'evertoken' => $everToken,
                    'ever_id_cart' => Cart::lastNoneOrderedCart($order->id_customer),
                ]
            );
            /** @var \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection $bar */
            $bar = $params['actions_bar_buttons_collection'];
            $bar->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-info', ['href' => $connectLink, 'target' => '_blank'], $this->l('Connect to customer account')
                )
            );
        }
    }

    public function hookActionCustomerLogoutBefore($params)
    {
        if ($this->context->cookie->__isset('everlogin')) {
            $this->context->cookie->__unset('everlogin');
        }
    }

    public function everHook($method, $args)
    {
        $context = Context::getContext();
        // Drop cache if needed
        EverblockClass::cleanBlocksCacheOnDate(
            $context->language->id,
            $context->shop->id
        );
        $id_hook = (int) Hook::getIdByName(lcfirst(str_replace('hook', '', $method)));
        $hookName = lcfirst(str_replace('hook', '', $method));
        $idObj = 0;
        if (Tools::getValue('id_product')) {
            $idObj = (int) Tools::getValue('id_product');
        }
        if (Tools::getValue('id_category')) {
            $idObj = (int) Tools::getValue('id_category');
        }
        if (Tools::getValue('id_manufacturer')) {
            $idObj = (int) Tools::getValue('id_manufacturer');
        }
        if (Tools::getValue('id_supplier')) {
            $idObj = (int) Tools::getValue('id_supplier');
        }
        if (Tools::getValue('id_cms')) {
            $idObj = (int) Tools::getValue('id_cms');
        }
        $cacheId = $this->name
        . '-id_hook-'
        . (int) $id_hook
        . '-controller-'
        . trim(Tools::getValue('controller'))
        . '-hookName-'
        . trim($hookName)
        . '-idObj-'
        . (int) $idObj
        . '-idLang-'
        . (int) $context->language->id
        . '-idShop-'
        . (int) $context->shop->id
        . '-idCurrency-'
        . (int) $context->currency->id
        . '-device-'
        . (int) $context->getDevice();
        if (!EverblockCache::isCacheStored(str_replace('|', '-', $cacheId))) {
            if ($context->customer->id) {
                $id_entity = (int) $context->customer->id;
            } else {
                $id_entity = false;
            }
            $everblock = EverblockClass::getBlocks(
                (int) $id_hook,
                (int) $context->language->id,
                (int) $context->shop->id
            );
            $currentBlock = [];
            foreach ($everblock as $block) {
                // Check device
                if ((int) $block['device'] > 0
                    && (int) $context->getDevice() != (int) $block['device']
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
                if ((bool) $block['only_category'] === true
                    && Tools::getValue('controller') === 'category'
                ) {
                    $categories = json_decode($block['categories']);
                    if (!in_array((int) Tools::getValue('id_category'), $categories)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only manufacturer pages
                if ((bool) $block['only_manufacturer'] === true
                    && Tools::getValue('controller') === 'manufacturer'
                ) {
                    $manufacturers = json_decode($block['manufacturers']);
                    if (!in_array((int) Tools::getValue('id_manufacturer'), $manufacturers)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only supplier pages
                if ((bool) $block['only_supplier'] === true
                    && Tools::getValue('controller') === 'supplier'
                ) {
                    $suppliers = json_decode($block['suppliers']);
                    if (!in_array((int) Tools::getValue('id_supplier'), $suppliers)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only CMS category pages
                if ((bool) $block['only_cms_category'] === true
                    && Tools::getValue('controller') === 'cms'
                    && Tools::getValue('id_cms_category')
                ) {
                    $cms_categories = json_decode($block['cms_categories']);
                    if (!in_array((int) Tools::getValue('id_cms_category'), $cms_categories)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only products pages with specific category
                if (Tools::getValue('id_product')
                    && Tools::getValue('controller') === 'product'
                    && (bool) $block['only_category'] === true
                    && (bool) $block['only_category_product'] === true
                ) {
                    $product = new Product(
                        (int) Tools::getValue('id_product')
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
                // Date start and date end management
                $now = new DateTime();
                $now = $now->format('Y-m-d H:i:s');
                if (!empty($block['date_start'])
                    && $block['date_start'] !== '0000-00-00 00:00:00'
                    && $block['date_start'] > $now
                ) {
                    continue;
                }
                if (!empty($block['date_end'])
                    && $block['date_end'] !== '0000-00-00 00:00:00'
                    && $block['date_end'] < $now
                ) {
                    continue;
                }
                $customerGroups = Customer::getGroupsStatic(
                    (int) $context->customer->id
                );
                $allowedGroups = json_decode($block['groups'], true);
                if (isset($customerGroups)
                    && !empty($allowedGroups)
                    && !array_intersect($allowedGroups, $customerGroups)
                ) {
                    continue;
                }
                if ((bool) $block['obfuscate_link'] === true) {
                    $block['content'] = EverblockTools::obfuscateText(
                        $block['content']
                    );
                }
                if ((bool) $block['lazyload'] === true) {
                    $block['content'] = EverblockTools::addLazyLoadToImages(
                        $block['content']
                    );
                }
                $currentBlock[] = ['block' => $block];
            }
            Hook::exec(
                'actionRenderBlockBefore',
                [
                    'everhook' => trim($method),
                    $this->name => &$currentBlock,
                    'args' => $args,
                ]
            );
            if ((bool) Module::isInstalled('prettyblocks') === true
                && (bool) Module::isEnabled('prettyblocks') === true
                && (bool) EverblockTools::moduleDirectoryExists('prettyblocks') === true
            ) {
                $context->smarty->assign([
                    'prettyblocks_installed' => true,
                ]);
            }
            $context->smarty->assign([
                'everhook' => trim($method),
                $this->name => $currentBlock,
                'args' => $args,
            ]);
            $tpl = $this->display(__FILE__, $this->name . '.tpl');
            EverblockCache::cacheStore(
                str_replace('|', '-', $cacheId),
                $tpl
            );
        }
        return EverblockCache::cacheRetrieve(
            str_replace('|', '-', $cacheId)
        );
    }

    public function hookDisplayHeader()
    {
        $idShop = (int) $this->context->shop->id;
        // Register your CSS file
        $this->context->controller->registerStylesheet(
            'module-' . $this->name . '-css',
            'modules/' . $this->name . '/views/css/' . $this->name . '.css',
            ['media' => 'all', 'priority' => 200, 'version' => $this->version]
        );
        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-js',
            'modules/' . $this->name . '/views/js/' . $this->name . '.js',
            ['position' => 'bottom', 'priority' => 200, 'version' => $this->version]
        );
        if ((bool) EverblockCache::getModuleConfiguration('EVERBLOCK_USE_OBF') === true) {
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-obf-js',
                'modules/' . $this->name . '/views/js/' . $this->name . '-obfuscation.js',
                ['position' => 'bottom', 'priority' => 200, 'version' => $this->version]
            );
        }
        $compressedCss = _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $compressedJs = _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom-compressed' . $idShop . '.js';
        if (file_exists($compressedCss) && filesize($compressedCss) > 0) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-custom-compressed-css',
                'modules/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200, 'version' => $this->version]
            );
        }
        if (file_exists($compressedJs) && filesize($compressedJs) > 0) {
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-compressed-js',
                'modules/' . $this->name . '/views/js/custom-compressed' . $idShop . '.js',
                ['position' => 'bottom', 'priority' => 200, 'version' => $this->version]
            );
        }
        $externalJs = EverblockCache::getModuleConfiguration('EVERPSJS_LINKS');
        $jsLinksArray = [];
        if ($externalJs) {
            $jsLinksArray = explode("\n", $externalJs);
            foreach ($jsLinksArray as $key => $value) {
                $this->context->controller->registerJavascript(
                    'module-' . $this->name . '-custom-' . (int) $key . '-js',
                    $value,
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 200, 'version' => $this->version]
                );
            }
        }
        $externalCss = EverblockCache::getModuleConfiguration('EVERPSCSS_LINKS');
        $cssLinksArray = [];
        if ($externalCss) {
            $cssLinksArray = explode("\n", $externalCss);
            foreach ($cssLinksArray as $key => $value) {
                $this->context->controller->registerStylesheet(
                    'module-' . $this->name . '-custom-' . (int) $key . '-js',
                    $value,
                    ['server' => 'remote', 'media' => 'all', 'priority' => 200, 'version' => $this->version]
                );
            }
        }
        // Do not show GMAP api KEY on Everblock cache
        $apiKey = Configuration::get('EVERBLOCK_GMAP_KEY');
        if ($apiKey && Tools::getValue('controller') == 'cms') {
            $filename = 'store-locator-' . $idShop . '.js';
            $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
            if (file_exists($filePath) && filesize($filePath) > 0) {
                $this->context->controller->registerJavascript(
                    'module-' . $this->name . '-custom-gmap-js',
                    'https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&libraries=places,geometry',
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 300, 'version' => $this->version, 'attributes' => 'defer']
                );
                $this->context->controller->registerJavascript(
                    'module-' . $this->name . '-shop-gmap-js',
                    'modules/' . $this->name . '/views/js/' . $filename,
                    ['server' => 'local', 'position' => 'bottom', 'priority' => 400, 'version' => $this->version, 'attributes' => 'defer']
                );
            }
        }
        Media::addJsDef([
            'evercontact_link' => $this->context->link->getModuleLink(
                $this->name,
                'contact',
                [
                    'token' => Tools::encrypt($this->name . '/token'),
                ]
            ),
            'evermodal_link' => $this->context->link->getModuleLink(
                $this->name,
                'modal',
                [
                    'token' => Tools::encrypt($this->name . '/token'),
                ]
            ),
        ]);
    }

    public function hookActionRegisterBlock($params)
    {
        return EverblockPrettyBlocks::getEverPrettyBlocks($this->context);
    }

    public function checkLatestEverModuleVersion(): bool
    {
        $upgrade_link = 'https://upgrade.team-ever.com/upgrade.php?module=' . $this->name . '&version=' . $this->version;
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
            if ($module_version && $module_version > $this->version) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Unable to check latest ' . $this->displayName . ' version');
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
            return false;
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
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function getConfigInMultipleLangs($key, $idShopGroup = null, $idShop = null): array
    {
        $resultsArray = [];
        foreach (Language::getIDs() as $idLang) {
            $resultsArray[$idLang] = Configuration::get($key, $idLang, $idShopGroup, $idShop);
        }
        return $resultsArray;
    }

    protected function createUpgradeFile(): bool
    {
        $currentVersion = $this->version;
        $updateDir = _PS_MODULE_DIR_ . $this->name . '/upgrade/';
        $licenceHeader = EverblockTools::getPhpLicenceHeader();
        $upgradeFunction = EverblockTools::getUpgradeMethod($this->version);
        $newFilename = 'upgrade-' . str_replace('.', '_', $currentVersion) . '.php';
        $content = $licenceHeader . PHP_EOL . PHP_EOL . $upgradeFunction . PHP_EOL;
        if (file_put_contents($updateDir . $newFilename, $content) !== false) {
            return true;
        } else {
            return false;
        }
    }

    protected function secureModuleFolder()
    {
        $moduleName = $this->name;
        $moduleAuthor = $this->author;
        $indexContent = <<<PHP
    <?php
    /**
     * Project : {$moduleName}
     * @author {$moduleAuthor}
     * @copyright {$moduleAuthor}
     * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
     * @link https://team-ever.com
     */
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    header('Location: ../');
    exit;
    PHP;
        // Utiliser le chemin du module actuel comme point de départ
        $moduleDir = $this->getLocalPath();
        // Fonction récursive pour ajouter le fichier index.php
        $this->addIndexFileRecursively($moduleDir, $indexContent);
    }

    /**
     * Ajoute le fichier index.php récursivement dans tous les répertoires et sous-répertoires
     * 
     * @param string $dir Le répertoire de départ
     * @param string $indexContent Le contenu à ajouter dans le fichier index.php
     */
    protected function addIndexFileRecursively($dir, $indexContent)
    {
        // Vérifier si le fichier index.php existe, sinon le créer
        $indexPath = $dir . '/index.php';
        if (!file_exists($indexPath)) {
            file_put_contents($indexPath, $indexContent);
        }
        // Parcourir le répertoire pour trouver des sous-répertoires et appliquer la fonction récursivement
        $files = new DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isDir()) {
                $this->addIndexFileRecursively(
                    $file->getPathname(),
                    $indexContent
                );
            }
        }
    }

    protected function uploadTabsFile()
    {
        /* upload the file */
        if (isset($_FILES['TABS_FILE'])
            && isset($_FILES['TABS_FILE']['tmp_name'])
            && !empty($_FILES['TABS_FILE']['tmp_name'])
        ) {
            $filename = $_FILES['TABS_FILE']['name'];
            $exploded_filename = explode('.', $filename);
            $ext = end($exploded_filename);
            if (Tools::strtolower($ext) != 'xlsx') {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['TABS_FILE']['tmp_name'], $tmp_name)
            ) {
                return false;
            }

            copy($tmp_name, _PS_MODULE_DIR_ . $this->name . '/input/tabs.xlsx');
            $this->processTabsFile();
            $this->html .= $this->displayConfirmation($this->l('File has been imported'));
        }
    }

    protected function processTabsFile()
    {
        $tabsFile = _PS_MODULE_DIR_ . $this->name . '/input/tabs.xlsx';
        if (!file_exists($tabsFile)) {
            return;
        }
        $file = new ImportFile($tabsFile);
        $lines = $file->getLines();
        $headers = $file->getHeaders();
        foreach ($lines as $line) {
            $this->updateProductTabs($line);
        }
        unlink($tabsFile);
    }

    protected function updateProductTabs($line)
    {
        if (!isset($line['id_product'])
            || empty($line['id_product'])
        ) {
            $this->postErrors[] = $this->l('Missing id_product column');
            return;
        }
        $product = new Product(
            (int) $line['id_product']
        );
        if (!Validate::isLoadedObject($product)) {
            $this->postErrors[] = $this->l('Product not valid');
            return;
        }
        if (isset($line['id_shop'])
            && !empty($line['id_shop'])
        ) {
            $id_shop = $line['id_shop'];
        } else {
            $id_shop = (int) $this->context->shop->id;
        }
        if (!isset($line['id_tab'])
            || empty($line['id_tab'])
        ) {
            $this->postErrors[] = $this->l('Missing id_tab column');
            return;
        }
        try {
            $tab = EverblockTabsClass::getByIdProductIdTab(
                (int) $line['id_product'],
                (int) $id_shop,
                (int) $line['id_tab']
            );
            $tab->id_tab = (int) $line['id_tab'];
            $tab->id_product = (int) $line['id_product'];
            $tab->id_shop = (int) $id_shop;
            foreach (Language::getLanguages(false, $id_shop) as $lang) {
                $titleKey = 'title_' . $lang['iso_code'];
                $contentKey = 'content_' . $lang['iso_code'];

                // Vérifier et assigner le titre s'il existe et n'est pas vide
                if (isset($line[$titleKey]) && !empty($line[$titleKey])) {
                    $tab->title[(int) $lang['id_lang']] = $line[$titleKey];
                }

                // Vérifier et assigner le contenu s'il existe et n'est pas vide
                if (isset($line[$contentKey]) && !empty($line[$contentKey])) {
                    $tab->content[(int) $lang['id_lang']] = $line[$contentKey];
                }
            }
            $tab->save();
            Tools::clearAllCache();
            EverblockCache::cleanThemeCache();
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }
}
