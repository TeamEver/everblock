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

require_once('vendor/autoload.php');
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockClass.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockShortcode.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockTools.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockTabsClass.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockFlagsClass.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockFaq.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockPrettyBlocks.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockCache.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockCheckoutStep.php';

use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use Everblock\Tools\Service\ImportFile;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use ScssPhp\ScssPhp\Compiler;

class Everblock extends Module
{
    private $html;
    private $postErrors = [];
    private $postSuccess = [];
    private $allowedActions = [
        'saveblocks',
        'restoreblocks',
        'removeinlinecsstags',
        'droplogs',
        'refreshtokens',
        'securewithapache',
    ];
    private $bypassedControllers = [
        'hookDisplayInvoiceLegalFreeText',
    ];

    public function __construct()
    {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '7.1.1';
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
        $this->registerHook('beforeRenderingEverblockProductHighlight');
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = [
            'front',
            'modulefront',
        ];
        $context = Context::getContext();
        if (!in_array($context->controller->controller_type, $controllerTypes) && !in_array($method, $this->bypassedControllers)) {
            return;
        }
        if (Hook::isDisplayHookName(lcfirst(str_replace('hook', '', $method)))) {
            return $this->everHook($method, $args);
        }
    }

    public function install()
    {
        Configuration::updateValue('EVERBLOCK_TINYMCE', 1);
        Configuration::updateValue('EVERPSCSS_P_LLOREM_NUMBER', 5);
        Configuration::updateValue('EVERPSCSS_S_LLOREM_NUMBER', 5);
        Configuration::updateValue('EVERPS_TAB_NB', 5);
        Configuration::updateValue('EVERPS_FLAG_NB', 5);
        Configuration::updateValue(
            'EVERPS_FEATURES_AS_FLAGS',
            json_encode([1]),
            true
        );
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
        // Hook displayBeforeRenderingShortcodes
        if (!Hook::getIdByName('displayBeforeRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeRenderingShortcodes';
            $hook->title = 'Before rendering shortcodes';
            $hook->description = 'This hook triggers before shortcodes are rendered';
            $hook->save();
        }
        // Hook displayAfterRenderingShortcodes
        if (!Hook::getIdByName('displayAfterRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayAfterRenderingShortcodes';
            $hook->title = 'After rendering shortcodes';
            $hook->description = 'This hook triggers after shortcodes are rendered';
            $hook->save();
        }
        // Hook displayFakeHook
        if (!Hook::getIdByName('displayFakeHook')) {
            $hook = new Hook();
            $hook->name = 'displayFakeHook';
            $hook->title = 'Fake hook';
            $hook->description = 'Ne pas afficher ce hook en front, il sera utilisé pour du contenu asynchrone';
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
        if (!Hook::getIdByName('displayEverblockExtraOrderStep')) {
            $hook = new Hook();
            $hook->name = 'displayEverblockExtraOrderStep';
            $hook->title = 'Extra order step';
            $hook->description = 'This hook is triggered on extra order step';
            $hook->save();
        }
        if (!Hook::getIdByName('actionGetEverBlockBefore')) {
            $hook = new Hook();
            $hook->name = 'actionGetEverBlockBefore';
            $hook->title = 'Before block is rendered';
            $hook->description = 'This hook triggers before block is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeBefore')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeBefore';
            $hook->title = 'Before block shortcodes are rendered';
            $hook->description = 'This hook triggers before every block shortcode is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeAfter')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeAfter';
            $hook->title = 'After block shortcodes are rendered';
            $hook->description = 'This hook triggers after every block shortcode is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayBeforeRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeRenderingShortcodes';
            $hook->title = 'Before rendering shortcodes';
            $hook->description = 'This hook triggers before shortcodes are rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayAfterRenderingShortcodes';
            $hook->title = 'After rendering shortcodes';
            $hook->description = 'This hook triggers after shortcodes are rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayFakeHook')) {
            $hook = new Hook();
            $hook->name = 'displayFakeHook';
            $hook->title = 'Fake hook';
            $hook->description = 'Ne pas afficher ce hook en front, il sera utilisé pour du contenu asynchrone';
            $hook->save();
        }
        if (!Hook::getIdByName('displayBeforeStoreLocator')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeStoreLocator';
            $hook->title = 'display before Everblock store locator';
            $hook->description = 'This hook triggers before store locator is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterStoreLocator')) {
            $hook = new Hook();
            $hook->name = 'displayAfterStoreLocator';
            $hook->title = 'display after Everblock store locator';
            $hook->description = 'This hook triggers after store locator is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterLocatorStore')) {
            $hook = new Hook();
            $hook->name = 'displayAfterLocatorStore';
            $hook->title = 'display after store content on store locator';
            $hook->description = 'This hook triggers after store content on store locator';
            $hook->save();
        }
        if (!Hook::getIdByName('displayBeforeProductMiniature')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeProductMiniature';
            $hook->title = 'display before product miniature';
            $hook->description = 'This hook triggers before product miniature is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterProductMiniature')) {
            $hook = new Hook();
            $hook->name = 'displayAfterProductMiniature';
            $hook->title = 'display after product miniature';
            $hook->description = 'This hook triggers after product miniature is rendered';
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
        $this->registerHook('actionClearCache');
        $this->registerHook('displayContentWrapperTop');
        $this->registerHook('actionCmsPageFormBuilderModifier');
        $this->registerHook('actionObjectCmsUpdateAfter');
        $this->registerHook('displayMaintenance');
        $this->registerHook('displayPDFInvoice');
        $this->registerHook('displayPDFDeliverySlip');        
        $this->registerHook('displayAdminOrder');
        $this->registerHook('actionCheckoutRender');
        $this->registerHook('displayOrderConfirmation');
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
        $this->registerHook('actionObjectEverBlockFlagsUpdateAfter');
        $this->registerHook('actionObjectEverBlockFlagsDeleteAfter');
        $this->registerHook('displayWrapperBottom');
        $this->registerHook('displayWrapperTop');
        $this->registerHook('actionProductFlagsModifier');
        $this->registerHook('actionEmailAddAfterContent');
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
        if ((bool) Tools::isSubmit('submitUploadBlocksFile') === true) {
            $this->uploadBlocksFile();
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
        if ((bool) Tools::isSubmit('submitSecureModuleFoldersWithApache') === true) {
            $secured = EverblockTools::secureModuleFoldersWithApache();
            if (is_array($secured)
                && isset($secured['postErrors'])
                && count($secured['postErrors']) > 0
            ) {
                foreach ($secured['postErrors'] as $postErrors) {
                    $this->postErrors[] = $postErrors;
                }
            }
            if (is_array($secured)
                && isset($secured['querySuccess'])
                && count($secured['querySuccess']) > 0
            ) {
                foreach ($secured['querySuccess'] as $querySuccess) {
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
        $blockAdminLink = $this->context->link->getAdminLink('AdminEverBlock', true, [], [
            'configure' => $this->name,
            'module_name' => $this->name,
        ]);
        $faqAdminLink = $this->context->link->getAdminLink('AdminEverBlockFaq', true, [], [
            'configure' => $this->name,
            'module_name' => $this->name,
        ]);
        $hookAdminLink = $this->context->link->getAdminLink('AdminEverBlockHook', true, [], [
            'configure' => $this->name,
            'module_name' => $this->name,
        ]);
        $shortcodeAdminLink = $this->context->link->getAdminLink('AdminEverBlockShortcode', true, [], [
            'configure' => $this->name,
            'module_name' => $this->name,
        ]);
        $cronLinks = [];
        $cronToken = $this->encrypt($this->name . '/evercron');
        foreach ($this->allowedActions as $action) {
            $cronLinks[$action] = $this->context->link->getModuleLink(
                $this->name,
                'cron',
                [
                    'action' => $action,
                    'evertoken' => $cronToken,
                ]
            );
        }
        $this->context->smarty->assign([
            'module_name' => $this->displayName,
            $this->name . '_version' => $this->version,
            $this->name . '_dir' => $this->_path,
            'block_admin_link' => $blockAdminLink,
            'faq_admin_link' => $faqAdminLink,
            'hook_admin_link' => $hookAdminLink,
            'shortcode_admin_link' => $shortcodeAdminLink,
            'cron_links' => $cronLinks,
            'modules_list_link' => $this->context->link->getAdminLink('AdminModules'),
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
        $step_position = [
            [
                'id_position' => 1,
                'name' => $this->l('After login'),
            ],
            [
                'id_position' => 2,
                'name' => $this->l('After address form'),
            ],
            [
                'id_position' => 3,
                'name' => $this->l('After shipping form'),
            ],
        ];
        $features = Feature::getFeatures(
            (int) $this->context->language->id
        );
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
                    'secureModuleFoldersWithApache' => [
                        'name' => 'submitSecureModuleFoldersWithApache',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Secure all modules folders using Apache'),
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
                        'lang' => true,
                        'label' => $this->l('New order step title'),
                        'desc' => $this->l('Please specify new order step title'),
                        'hint' => $this->l('If not set, new order step won\'t be shown'),
                        'name' => 'EVEROPTIONS_TITLE',
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('New order step position'),
                        'desc' => $this->l('Please select new order step position'),
                        'hint' => $this->l('Will impact position of the new order step'),
                        'name' => 'EVEROPTIONS_POSITION',
                        'options' => [
                            'query' => $step_position,
                            'id' => 'id_position',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Maintenance password'),
                        'desc' => $this->l('If entered, you will have a password entry form on the maintenance page'),
                        'hint' => $this->l('People with the password will be able to access the store in maintenance mode'),
                        'name' => 'EVERBLOCK_MAINTENANCE_PSSWD',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Instagram access token'),
                        'desc' => $this->l('Add here your Instagram access token'),
                        'hint' => $this->l('Without access token, you wont be able to show Instagram slider'),
                        'name' => 'EVERINSTA_ACCESS_TOKEN',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Instagram profile link'),
                        'desc' => $this->l('Add here your Instagram profile URL'),
                        'hint' => $this->l('This will set a custom link to your Instagram profile'),
                        'name' => 'EVERINSTA_LINK',
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
                        'label' => $this->l('Use module cache system instead of Prestashop native cache ?'),
                        'desc' => $this->l('Set yes to use module cache, this will generate cache files on your server'),
                        'hint' => $this->l('Else Prestashop native cache will be used'),
                        'name' => 'EVERBLOCK_CACHE',
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
                        'label' => $this->l('Enable slick slider scripts ?'),
                        'desc' => $this->l('Set yes to enable slick scripts for carousels'),
                        'hint' => $this->l('Else carousels wont work'),
                        'name' => 'EVERBLOCK_USE_SLICK',
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
                        'type' => 'switch',
                        'label' => $this->l('Disable automatic conversion of images to webp format'),
                        'desc' => $this->l('Will disable automatic conversion of images to webp format in HTML blocks'),
                        'hint' => $this->l('If the setting is changed to no, images will not be converted to webp format'),
                        'name' => 'EVERBLOCK_DISABLE_WEBP',
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
                        'type' => 'textarea',
                        'label' => $this->l('Header scripts'),
                        'desc' => $this->l('Add here your custom header scripts'),
                        'hint' => $this->l('Header scripts like Clarity can be added here'),
                        'name' => 'EVERPS_HEADER_SCRIPTS',
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'multiple' => true,
                        'label' => $this->l('Features as flags'),
                        'desc' => $this->l('Please select features used for flags'),
                        'hint' => $this->l('The selected features will be converted into product flags'),
                        'name' => 'EVERPS_FEATURES_AS_FLAGS[]',
                        'required' => false,
                        'options' => [
                            'query' => $features,
                            'id' => 'id_feature',
                            'name' => 'name',
                        ],
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
                        'label' => $this->l('Number of flags for products'),
                        'desc' => $this->l('Specify here the number of flags you want to have on products'),
                        'hint' => $this->l('The default value is 1 and cannot be less than 1'),
                        'name' => 'EVERPS_FLAG_NB',
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
        $formFields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Import HTML blocks'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'file',
                        'label' => $this->l('Upload Excel blocks file'),
                        'desc' => $this->l('Upload an Excel file to create HTML blocks with all settings'),
                        'hint' => $this->l('This file must contain the same columns as the export command'),
                        'name' => 'BLOCKS_FILE',
                        'display_image' => false,
                        'required' => false,
                    ],
                ],
                'buttons' => [
                    'import' => [
                        'name' => 'submitUploadBlocksFile',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-download',
                        'title' => $this->l('Upload file'),
                    ],
                ],
            ],
        ];
        $bannedFeatures = json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
        if (!is_array($bannedFeatures)) {
            $bannedFeatures = [];
        }
        $bannedFeaturesColors = [];
        foreach ($bannedFeatures as $bannedFeature) {
            $bannedFeaturesColors[] = [
                'feature_id' => (int) $bannedFeature,
                'color' => Configuration::get('EVERPS_FEATURE_COLOR_' . (int) $bannedFeature),
            ];
        }

        // Ajouter les couleurs au formulaire
        $formFields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Features as flags Colors'),
                    'icon' => 'icon-palette',
                ],
                'input' => [],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        // Ajouter un champ par feature bannie
        foreach ($bannedFeaturesColors as $bannedFeatureColor) {
            $featureId = $bannedFeatureColor['feature_id'];
            $feature = new Feature((int) $featureId);
            $idLang = (int) Context::getContext()->language->id;
            $featureName = isset($feature->name[$idLang]) ? $feature->name[$idLang] : $this->l('Unnamed feature');

            // Couleur de fond
            $formFields[count($formFields) - 1]['form']['input'][] = [
                'type' => 'color',
                'label' => $this->l('Background color for Feature: ') . $featureName,
                'name' => 'EVERPS_FEATURE_COLOR_' . $featureId,
                'size' => 20,
                'required' => false,
            ];

            // Couleur du texte
            $formFields[count($formFields) - 1]['form']['input'][] = [
                'type' => 'color',
                'label' => $this->l('Text color for Feature: ') . $featureName,
                'name' => 'EVERPS_FEATURE_TEXTCOLOR_' . $featureId,
                'size' => 20,
                'required' => false,
            ];
        }
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
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/header-scripts-' . $this->context->shop->id . '.js';
        if (file_exists($filePath) && filesize($filePath) > 0) {
            $headerScripts = file_get_contents($filePath);
        } else {
            $headerScripts = '';
        }
        $bannedFeatures = json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
        if (!is_array($bannedFeatures)) {
            $bannedFeatures = [];
        }
        $bannedFeaturesColors = [];
        foreach ($bannedFeatures as $bannedFeature) {
            $featureId = (int) $bannedFeature;
            $bannedFeaturesColors[
                'EVERPS_FEATURE_COLOR_' . $featureId
            ] = Configuration::get('EVERPS_FEATURE_COLOR_' . $featureId);

            $bannedFeaturesColors[
                'EVERPS_FEATURE_TEXTCOLOR_' . $featureId
            ] = Configuration::get('EVERPS_FEATURE_TEXTCOLOR_' . $featureId);
        }
        $configData = [
            'EVEROPTIONS_POSITION' => Configuration::get('EVEROPTIONS_POSITION'),
            'EVEROPTIONS_TITLE' => $this->getConfigInMultipleLangs('EVEROPTIONS_TITLE'),
            'EVERBLOCK_MAINTENANCE_PSSWD' => Configuration::get('EVERBLOCK_MAINTENANCE_PSSWD'),
            'EVERINSTA_ACCESS_TOKEN' => Configuration::get('EVERINSTA_ACCESS_TOKEN'),
            'EVERINSTA_LINK' => Configuration::get('EVERINSTA_LINK'),
            'EVERBLOCK_USE_GMAP' => Configuration::get('EVERBLOCK_USE_GMAP'),
            'EVERBLOCK_GMAP_KEY' => Configuration::get('EVERBLOCK_GMAP_KEY'),
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
            'EVERBLOCK_CACHE' => Configuration::get('EVERBLOCK_CACHE'),
            'EVERBLOCK_USE_OBF' => Configuration::get('EVERBLOCK_USE_OBF'),
            'EVERBLOCK_USE_SLICK' => Configuration::get('EVERBLOCK_USE_SLICK'),
            'EVERPSCSS' => $custom_css,
            'EVERPSSASS' => $custom_sass,
            'EVERPSJS' => $custom_js,
            'EVERPSCSS_LINKS' => Configuration::get('EVERPSCSS_LINKS'),
            'EVERPSJS_LINKS' => Configuration::get('EVERPSJS_LINKS'),
            'EVERPS_HEADER_SCRIPTS' => $headerScripts,
            'EVERPS_FEATURES_AS_FLAGS[]' => json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS')),
            'EVERPS_DUMMY_NBR' => Configuration::get('EVERPS_DUMMY_NBR'),
            'EVERPSCSS_P_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_P_LLOREM_NUMBER'),
            'EVERPSCSS_S_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_S_LLOREM_NUMBER'),
            'EVERBLOCK_TINYMCE' => Configuration::get('EVERBLOCK_TINYMCE'),
            'EVERBLOCK_DISABLE_WEBP' => Configuration::get('EVERBLOCK_DISABLE_WEBP'),
            'EVERPS_OLD_URL' => '',
            'EVERPS_NEW_URL' => '',
            'EVER_TAB_CONTENT' => $this->getConfigInMultipleLangs('EVER_TAB_CONTENT'),
            'EVER_TAB_TITLE' => $this->getConfigInMultipleLangs('EVER_TAB_TITLE'),
            'EVERPS_TAB_NB' => Configuration::get('EVERPS_TAB_NB'),
            'EVERPS_FLAG_NB' => Configuration::get('EVERPS_FLAG_NB'),
            'TABS_FILE' => '',
            'BLOCKS_FILE' => '',
        ];
        $configData = array_merge($configData, $bannedFeaturesColors);
        return $configData;
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
            if (Tools::getValue('EVERPS_FEATURES_AS_FLAGS')
                && !Validate::isArrayWithIds(Tools::getValue('EVERPS_FEATURES_AS_FLAGS'))
            ) {
                $this->postErrors[] = $this->l('Error: selected features are not valid');
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
            'EVERBLOCK_CACHE',
            Tools::getValue('EVERBLOCK_CACHE')
        );
        Configuration::updateValue(
            'EVERBLOCK_USE_OBF',
            Tools::getValue('EVERBLOCK_USE_OBF')
        );
        Configuration::updateValue(
            'EVERBLOCK_USE_SLICK',
            Tools::getValue('EVERBLOCK_USE_SLICK')
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
        Configuration::updateValue(
            'EVEROPTIONS_POSITION',
            Tools::getValue('EVEROPTIONS_POSITION')
        );
        $formTitle = [];
        foreach (Language::getLanguages(false) as $lang) {
            $formTitle[$lang['id_lang']] = (
                Tools::getValue('EVEROPTIONS_TITLE_' . $lang['id_lang'])
            ) ? Tools::getValue(
                'EVEROPTIONS_TITLE_' . $lang['id_lang']
            ) : '';
        }
        $headerScripts = Tools::getValue('EVERPS_HEADER_SCRIPTS');
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/header-scripts-' . $this->context->shop->id . '.js';
        file_put_contents($filePath, $headerScripts);
        $bannedFeatures = Tools::getValue('EVERPS_FEATURES_AS_FLAGS');
        Configuration::updateValue(
            'EVERPS_FEATURES_AS_FLAGS',
            json_encode($bannedFeatures),
            true
        );

        if (!empty($bannedFeatures)) {
            foreach ($bannedFeatures as $bannedFeature) {
                $featureId = (int) $bannedFeature;

                // Couleur de fond
                $bgColorKey = 'EVERPS_FEATURE_COLOR_' . $featureId;
                $bgColorValue = Tools::getValue($bgColorKey);
                Configuration::updateValue($bgColorKey, $bgColorValue);

                // Couleur du texte
                $textColorKey = 'EVERPS_FEATURE_TEXTCOLOR_' . $featureId;
                $textColorValue = Tools::getValue($textColorKey);
                Configuration::updateValue($textColorKey, $textColorValue);
            }
        }
        Configuration::updateValue(
            'EVEROPTIONS_TITLE',
            $formTitle,
            true
        );
        Configuration::updateValue(
            'EVERBLOCK_MAINTENANCE_PSSWD',
            Tools::getValue('EVERBLOCK_MAINTENANCE_PSSWD')
        );        
        Configuration::updateValue(
            'EVERINSTA_ACCESS_TOKEN',
            Tools::getValue('EVERINSTA_ACCESS_TOKEN')
        );
        // Auto refresh Instagram token
        if (Tools::getValue('EVERINSTA_ACCESS_TOKEN')
            && !empty(Tools::getValue('EVERINSTA_ACCESS_TOKEN'))
        ) {
            EverblockTools::refreshInstagramToken();
        }
        Configuration::updateValue(
            'EVERINSTA_LINK',
            Tools::getValue('EVERINSTA_LINK')
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
            'EVERBLOCK_DISABLE_WEBP',
            Tools::getValue('EVERBLOCK_DISABLE_WEBP')
        );        
        Configuration::updateValue(
            'EVERPS_TAB_NB',
            Tools::getValue('EVERPS_TAB_NB')
        );
        Configuration::updateValue(
            'EVERPS_FLAG_NB',
            Tools::getValue('EVERPS_FLAG_NB')
        );        
        if ((bool) Tools::getValue('EVERPSCSS_CACHE') === true) {
            $this->emptyAllCache();
        }
        $stores = Store::getStores((int) Context::getContext()->language->id);
        if (!empty($stores)) {
            if (Tools::getValue('EVERBLOCK_GMAP_KEY')) {
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
            } else {
                $markers = [];
                foreach ($stores as $store) {
                    $marker = [
                        'lat' => $store['latitude'],
                        'lng' => $store['longitude'],
                        'title' => $store['name'],
                    ];
                    $markers[] = $marker;
                }
                $osmScript = EverblockTools::generateOsmScript($markers);
                if ($osmScript) {
                    $filename = 'store-locator-' . $idShop . '.js';
                    $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
                    file_put_contents($filePath, $osmScript);
                }
            }
        }
        $this->generateFeatureFlagsCssFile();
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    protected function generateFeatureFlagsCssFile()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/css/feature-flags-' . $idShop . '.css';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $bannedFeatures = Configuration::get('EVERPS_FEATURES_AS_FLAGS');
        if (!$bannedFeatures) {
            return;
        }

        $bannedFeatures = json_decode($bannedFeatures, true);
        if (!is_array($bannedFeatures) || empty($bannedFeatures)) {
            return;
        }

        $css = "/* Auto-generated feature flags CSS */\n";

        foreach ($bannedFeatures as $featureId) {
            $featureId = (int) $featureId;
            $bgColor = Configuration::get('EVERPS_FEATURE_COLOR_' . $featureId);
            $textColor = Configuration::get('EVERPS_FEATURE_TEXTCOLOR_' . $featureId);

            // Skip if both values are empty
            if (empty($bgColor) && empty($textColor)) {
                continue;
            }

            // Exemple de classe : .feature-flag-12
            $css .= sprintf(
                ".ever_feature_flag_%d {\n%s%s}\n",
                $featureId,
                $bgColor ? "  background-color: {$bgColor}!important;\n" : '',
                $textColor ? "  color: {$textColor}!important;\n" : ''
            );
        }

        // Écriture dans le fichier si on a généré quelque chose
        if (trim($css) !== '') {
            file_put_contents($filePath, $css);
        }
    }

    protected function emptyAllCache()
    {
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
            $this->context->controller->addJs($this->_path . 'views/js/controller.js');
            if (Tools::getValue('configure') == $this->name) {
                $this->context->controller->addJs($this->_path . 'views/js/admin.js');
            }
            if ((bool) Configuration::get('EVERBLOCK_TINYMCE') === true
                && Tools::getValue('configure') != $this->name
            ) {
                $this->context->controller->addJs($this->_path . 'views/js/adminTinyMce.js');
            }
        }
    }


    public function hookActionClearCache($params)
    {
        EverblockTools::warmup(Tools::getShopDomainSsl(true) . __PS_BASE_URI__);
    }

    public function hookActionCmsPageFormBuilderModifier($params)
    {
        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];
        $idCms = (int) $params['id'];

        $stores = Store::getStores((int) Context::getContext()->language->id);
        $choices = [];
        $selectedStoreId = null;

        foreach ($stores as $store) {
            $choices[$store['name']] = (int) $store['id_store'];

            // Vérifie si ce store est lié à cette page CMS
            $cmsLinked = (int) Configuration::get('QCD_ASSOCIATED_CMS_PAGE_ID_STORE_' . $store['id_store']);
            if ($cmsLinked === $idCms) {
                $selectedStoreId = (int) $store['id_store'];
            }
        }
        $formBuilder->add('qcd_associated_store', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
            'label' => $this->l('Associate with store'),
            'required' => false,
            'choices' => $choices,
            'placeholder' => $this->l('Select a store'),
            'data' => $selectedStoreId,
            'attr' => [
                'class' => 'form-select',
            ],
        ]);
    }

    public function hookActionObjectCmsUpdateAfter($params)
    {
        /** @var CMS $cms */
        $cms = $params['object'];
        $cmsPage = Tools::getValue('cms_page');

        if (!empty($cmsPage['qcd_associated_store'])) {
            $id_store = (int) $cmsPage['qcd_associated_store'];
            Configuration::updateValue(
                'QCD_ASSOCIATED_CMS_PAGE_ID_STORE_' . $id_store,
                (int) $cms->id,
                false, // id_lang
                $this->context->shop->id // id_shop
            );
        }
    }

    public function hookActionOutputHTMLBefore($params)
    {
        if (Tools::getValue('evermaintenancepassword')
            && Tools::getValue('evermaintenancepassword') == Configuration::get('EVERBLOCK_MAINTENANCE_PSSWD')
        ) {
            $userIp = Tools::getRemoteAddr();  // Obtenir l'IP de l'utilisateur

            // Récupérer la liste actuelle des IPs autorisées depuis la configuration
            $ips = Configuration::get('PS_MAINTENANCE_IP');

            if ($ips) {
                $ipsArray = explode(',', $ips);  // Transformer la chaîne en tableau
            } else {
                $ipsArray = [];
            }

            // Vérifier si l'IP de l'utilisateur n'est pas déjà dans la liste
            if (!in_array($userIp, $ipsArray)) {
                $ipsArray[] = $userIp;  // Ajouter l'IP de l'utilisateur au tableau
                $newIps = implode(',', $ipsArray);  // Transformer le tableau en chaîne
                Configuration::updateValue('PS_MAINTENANCE_IP', $newIps);  // Mettre à jour la configuration avec la nouvelle liste d'IPs
            }
            Tools::redirect(
                Tools::getHttpHost(true) . __PS_BASE_URI__
            );
        }
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

    public function hookDisplayMaintenance($params)
    {
        if (Configuration::get('EVERBLOCK_MAINTENANCE_PSSWD')) {
            return $this->display(__FILE__, 'views/templates/hook/maintenance.tpl');
        }
    }

    public function hookDisplayWrapperTop()
    {
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) EverblockTools::moduleDirectoryExists('prettyblocks') === true
        ) {
            if (Tools::getValue('id_product')) {
                $idObj = (int) Tools::getValue('id_product');
                $objectName = 'Product';
            }
            if (Tools::getValue('id_category')) {
                $idObj = (int) Tools::getValue('id_category');
                $objectName = 'Category';
            }
            if (Tools::getValue('id_manufacturer')) {
                $idObj = (int) Tools::getValue('id_manufacturer');
                $objectName = 'Manufacturer';
            }
            if (Tools::getValue('id_supplier')) {
                $idObj = (int) Tools::getValue('id_supplier');
                $objectName = 'Supplier';
            }
            if (Tools::getValue('id_cms')) {
                $idObj = (int) Tools::getValue('id_cms');
                $objectName = 'Cms';
            }
            if (isset($idObj) && isset($objectName)) {
                $this->context->smarty->assign(array(
                    'idObj' => $idObj,
                    'objectName' => $objectName,
                    'zone' => 'displayWrapperTop',
                ));
                return $this->display(__FILE__, 'views/templates/hook/prettyblocks.tpl');
            }
        }
    }

    public function hookDisplayWrapperBottom()
    {
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) EverblockTools::moduleDirectoryExists('prettyblocks') === true
        ) {
            if (Tools::getValue('id_product')) {
                $idObj = (int) Tools::getValue('id_product');
                $objectName = 'Product';
            }
            if (Tools::getValue('id_category')) {
                $idObj = (int) Tools::getValue('id_category');
                $objectName = 'Category';
            }
            if (Tools::getValue('id_manufacturer')) {
                $idObj = (int) Tools::getValue('id_manufacturer');
                $objectName = 'Manufacturer';
            }
            if (Tools::getValue('id_supplier')) {
                $idObj = (int) Tools::getValue('id_supplier');
                $objectName = 'Supplier';
            }
            if (Tools::getValue('id_cms')) {
                $idObj = (int) Tools::getValue('id_cms');
                $objectName = 'Cms';
            }
            if (isset($idObj) && isset($objectName)) {
                $this->context->smarty->assign(array(
                    'idObj' => $idObj,
                    'objectName' => $objectName,
                    'zone' => 'displayWrapperBottom',
                ));
                return $this->display(__FILE__, 'views/templates/hook/prettyblocks.tpl');
            }
        }
    }

    public function hookActionCheckoutRender($params)
    {
        $stepTitle = $this->getConfigInMultipleLangs('EVEROPTIONS_TITLE');
        if (!$stepTitle[$this->context->language->id]
            || empty($stepTitle[$this->context->language->id])
        ) {
            return;
        }
        $this->translator = Context::getContext()->getTranslator();

        /** @var CheckoutProcess $process */
        $process = $params['checkoutProcess'];
        $steps = $process->getSteps();

        $everStep = new EverblockCheckoutStep(
            $this->context,
            $this->translator,
            Module::getInstanceByName($this->name)
        );
        $everStep->setCheckoutProcess($process);
        switch ((int) Configuration::get('EVEROPTIONS_POSITION')) {
            case 1:
                $newSteps = [
                    $steps[0],
                    $everStep,
                    $steps[1],
                    $steps[2],
                    $steps[3]
                ];
                break;
            
            case 2:
                $newSteps = [
                    $steps[0],
                    $steps[1],
                    $everStep,
                    $steps[2],
                    $steps[3]
                ];
                break;
            
            case 3:
                $newSteps = [
                    $steps[0],
                    $steps[1],
                    $steps[2],
                    $everStep,
                    $steps[3]
                ];
                break;

            default:
                $newSteps = [
                    $steps[0],
                    $everStep,
                    $steps[1],
                    $steps[2],
                    $steps[3]
                ];
                break;
        }
        $process->setSteps($newSteps);
    }

    public function hookDisplayOrderDetail($params)
    {
        return $this->hookDisplayOrderConfirmation($params);
    }

    public function hookDisplayOrderConfirmation($params)
    {
        try {
            $order = $params['order'];
            $checkoutSessionData = $this->getCartSessionDatas(
                $order->id_cart
            );
            if (isset($checkoutSessionData) && $checkoutSessionData) {
                $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                if (!$checkoutSessionData || empty($checkoutSessionData)) {
                    return;
                }
                $hiddenKeys = [
                    'hidden',
                    'everHide',
                    'submitCustomStep',
                    'controller',
                ];
                if (is_array($checkoutSessionData) && !empty($checkoutSessionData)) {
                    foreach ($checkoutSessionData as $key => $value) {
                        if (in_array($key, $hiddenKeys)) {
                            unset($checkoutSessionData[$key]);
                        }
                        if (empty($value)) {
                            unset($checkoutSessionData[$key]);
                        }
                    }
                    $this->context->smarty->assign(array(
                        'checkoutSessionData' => $checkoutSessionData,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        try {
            $order = new Order((int) $params['id_order']);
            $checkoutSessionData = $this->getCartSessionDatas(
                $order->id_cart
            );
            if (isset($checkoutSessionData) && $checkoutSessionData) {
                $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                if (is_array($checkoutSessionData) && !empty($checkoutSessionData)) {
                    $this->context->smarty->assign(array(
                        'checkoutSessionData' => $checkoutSessionData,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookDisplayPDFDeliverySlip($params)
    {
        return $this->hookDisplayPDFInvoice($params);
    }

    public function hookDisplayPDFInvoice($params)
    {
        try {
            $order = new Order((int) $params['object']->id_order);
            $checkoutSessionData = $this->getCartSessionDatas(
                $order->id_cart
            );
            if (isset($checkoutSessionData) && $checkoutSessionData) {
                $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                $hiddenKeys = [
                    'hidden',
                    'everHide',
                    'submitCustomStep',
                    'controller',
                ];
                if (is_array($checkoutSessionData) && !empty($checkoutSessionData)) {
                    foreach ($checkoutSessionData as $key => $value) {
                        if (in_array($key, $hiddenKeys)) {
                            unset($checkoutSessionData[$key]);
                        }
                        if (empty($value)) {
                            unset($checkoutSessionData[$key]);
                        }
                    }
                    $this->context->smarty->assign(array(
                        'checkoutSessionData' => $checkoutSessionData,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/pdf.tpl');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookActionEmailAddAfterContent($params)
    {
        try {
            $context = Context::getContext();
            $languageId = $params['id_lang'];
            $lang = new Language(
                (int) $languageId
            );
            $context->language = $lang;
            $params['template_txt'] = EverblockTools::renderShortcodes($params['template_txt'], $context, $this);
            $params['template_html'] = EverblockTools::renderShortcodes($params['template_html'], $context, $this);
            return $params;
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookActionEmailSendBefore($params)
    {
        if (isset($params['templateVars']['{id_order}'])) {
            try {
                $id_order = (int) $params['templateVars'] ["{id_order}"];
                $order = new Order(
                    (int) $id_order
                );
                $checkoutSessionData = $this->getCartSessionDatas(
                    $order->id_cart
                );
                if (isset($checkoutSessionData) && $checkoutSessionData) {
                    $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                    $hiddenKeys = [
                        'hidden',
                        'everHide',
                        'submitCustomStep',
                        'controller',
                    ];
                    if (is_array($checkoutSessionData) && !empty($checkoutSessionData)) {
                        foreach ($checkoutSessionData as $key => $value) {
                            if (in_array($key, $hiddenKeys)) {
                                unset($checkoutSessionData[$key]);
                            }
                            if (empty($value)) {
                                unset($checkoutSessionData[$key]);
                            }
                        }
                        $this->context->smarty->assign(array(
                            'checkoutSessionData' => $checkoutSessionData,
                        ));
                        $optionsHtml = $this->context->smarty->fetch(
                            $this->local_path . 'views/templates/admin/pdf.tpl'
                        );
                        $params['templateVars']['{order_options}'] = $optionsHtml;
                    }
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
                EverblockTools::setLog(
                    $this->name . date('y-m-d'),
                    $e->getMessage()
                );
            }
        }
        return $params;
    }

    public static function getCartSessionDatas($idCart)
    {
        $sql = new DbQuery;
        $sql->select('checkout_session_data');
        $sql->from(
            'cart'
        );
        $sql->where(
            'id_cart = ' . (int) $idCart
        );
        $res =  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if (!$res) {
            return;
        }
        $checkout_session_data = json_decode(
           $res
        );
        foreach ($checkout_session_data as $key => $value) {
            if ($key == 'ever-checkout-step') {
                return $value->everdata;
            }
        }
        return false;
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (!$params['id_product']) {
            return;
        }

        $productId = (int) $params['id_product'];
        $tabsNumber = max((int) Configuration::get('EVERPS_TAB_NB'), 1);
        $flagsNumber = max((int) Configuration::get('EVERPS_FLAG_NB'), 1);
        
        $everpstabs = EverblockTabsClass::getByIdProductInAdmin($productId, $this->context->shop->id);
        $everpsflags = EverblockFlagsClass::getByIdProductInAdmin($productId, $this->context->shop->id);

        $tabsData = [];
        $flagsData = [];
        for ($i = 1; $i <= $tabsNumber; $i++) {
            foreach ($everpstabs as $everpstab) {
                if (Validate::isLoadedObject($everpstab)
                    && $everpstab->id_tab == $i
                ) {
                    $tabsData[$i] = $everpstab;
                    break;
                }
            }

            if (!array_key_exists($i, $tabsData)) {
                $tabsData[$i] = null;
            }
        }

        for ($i = 1; $i <= $flagsNumber; $i++) {
            foreach ($everpsflags as $everpsflag) {
                if (Validate::isLoadedObject($everpsflag)
                    && $everpsflag->id_flag == $i
                ) {
                    $flagsData[$i] = $everpsflag;
                    break;
                }
            }

            if (!array_key_exists($i, $flagsData)) {
                $flagsData[$i] = null;
            }
        }

        $everAjaxUrl = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'token' => Tools::getAdminTokenLite('AdminModules')]);

        $this->smarty->assign([
            'tabsData' => $tabsData,
            'flagsData' => $flagsData,
            'default_language' => $this->context->employee->id_lang,
            'ever_languages' => Language::getLanguages(false),
            'ever_ajax_url' => $everAjaxUrl,
            'ever_product_id' => $productId,
            'tabsRange' => range(1, $tabsNumber),
            'flagsRange' => range(1, $flagsNumber),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/productTab.tpl');
    }

    public function hookActionObjectEverBlockClassDeleteAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockClass_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'fetchInstagramImages';
        EverblockCache::cacheDropByPattern($cachePattern);
    }

    public function hookActionObjectEverBlockClassUpdateAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockClass_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'fetchInstagramImages';
        EverblockCache::cacheDropByPattern($cachePattern);
    }

    public function hookActionObjectEverBlockFlagsDeleteAfter($params)
    {
        $cachePattern = $this->name . 'EverblockFlagsClass_getByIdProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockFlags_getBlocks_';
    }

    public function hookActionObjectEverBlockFlagsUpdateAfter($params)
    {
        $cachePattern = $this->name . 'EverblockFlagsClass_getByIdProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
    }

    public function hookActionObjectEverblockFaqDeleteAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockShortcode_getFaqByTagName_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'fetchInstagramImages';
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
            // Traitement des tabs
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
                    $tabTitle = Tools::getValue((int) $tab . '_everblock_title_' . $language['id_lang']);
                    if ($tabTitle && !Validate::isCleanHtml($tabTitle)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Title is not valid'),
                            ]
                        ));
                    } else {
                        $everpstabs->title[$language['id_lang']] = $tabTitle;
                    }
                    
                    $tabContent = Tools::getValue((int) $tab . '_everblock_content_' . $language['id_lang']);
                    if ($tabContent && !Validate::isCleanHtml($tabContent)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Content is not valid'),
                            ]
                        ));
                    } else {
                        $everpstabs->content[$language['id_lang']] = $tabContent;
                    }
                }
                $everpstabs->id_tab = (int) $tab;
                $everpstabs->id_product = (int) $params['object']->id;
                $everpstabs->id_shop = (int) $context->shop->id;
                $everpstabs->save();
            }

            // Traitement des flags
            $flagsNumber = (int) Configuration::get('EVERPS_FLAG_NB');
            if ($flagsNumber < 1) {
                $flagsNumber = 1;
                Configuration::updateValue('EVERPS_FLAG_NB', 1);
            }
            $flagsRange = range(1, $flagsNumber);
            foreach ($flagsRange as $flag) {
                $everpsflags = EverblockFlagsClass::getByIdProductIdFlag(
                    (int) $params['object']->id,
                    (int) $context->shop->id,
                    (int) $flag
                );
                foreach (Language::getLanguages(true) as $language) {
                    $flagTitle = Tools::getValue((int) $flag . '_everflag_title_' . $language['id_lang']);
                    if ($flagTitle && !Validate::isCleanHtml($flagTitle)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Title is not valid'),
                            ]
                        ));
                    } else {
                        $everpsflags->title[$language['id_lang']] = $flagTitle;
                    }
                    
                    $flagContent = Tools::getValue((int) $flag . '_everflag_content_' . $language['id_lang']);
                    if ($flagContent && !Validate::isCleanHtml($flagContent)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Content is not valid'),
                            ]
                        ));
                    } else {
                        $everpsflags->content[$language['id_lang']] = $flagContent;
                    }
                }
                $everpsflags->id_flag = (int) $flag;
                $everpsflags->id_product = (int) $params['object']->id;
                $everpsflags->id_shop = (int) $context->shop->id;
                $everpsflags->save();
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
            (int) $params['object']->id,
            (int) $this->context->shop->id
        );
        foreach ($everpstabs as $everpstab) {
            if (Validate::isLoadedObject($everpstab)) {
                $everpstab->delete();
            }
        }
        $everpsflags = EverblockFlagsClass::getByIdProductInAdmin(
            (int) $params['object']->id,
            (int) $this->context->shop->id
        );
        foreach ($everpsflags as $everpsflag) {
            if (Validate::isLoadedObject($everpsflag)) {
                $everpsflag->delete();
            }
        }
    }

    public function hookActionProductFlagsModifier($params)
    {
        try {
            $productId = (int) $params['product']['id_product'];
            $shopId = (int) Context::getContext()->shop->id;
            $languageId = (int) Context::getContext()->language->id;
            // Current product flags
            $everpsflags = EverblockFlagsClass::getByIdProduct($productId, $shopId, $languageId);
            if ($everpsflags && !empty($everpsflags)) {
                foreach ($everpsflags as $everpsflag) {
                    if (Validate::isLoadedObject($everpsflag) && $everpsflag->title && $everpsflag->content) {
                        $params['flags']['custom-flag-' . $everpsflag->id_flag] = [
                            'type' => 'custom-flag ' . $everpsflag->id_flag,
                            'label' => strip_tags($everpsflag->content),
                            'module' => $this->name,
                        ];
                    }
                }
            }
            // Product features as flags
            $bannedFeatures = $this->getFeaturesAsFlags();
            $features = $this->getFeatures($productId);
            if (!empty($features) && !empty($bannedFeatures)) {
                foreach ($features as $feature) {
                    if (in_array($feature['id_feature'], $bannedFeatures)) {
                        $params['flags'][] = array(
                            'type' => 'ever_feature_flag_' . $feature['id_feature'],
                            'label' => $feature['value'],
                            'module' => $this->name,
                            'style' => 'style="background-color:' . Configuration::get('EVERPS_FEATURE_COLOR_' . $feature['id_feature']) . ';color:#fff;"'
                        );
                    }
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error on hookActionProductFlagsModifier : ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
            return false;
        }
    }

    protected function getFeatures($productId)
    {
        $cacheId = $this->name . '_getFeatures_' . (int) $productId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = 'SELECT fp.id_feature, fv.value
                    FROM ' . _DB_PREFIX_ . 'feature_product fp
                    JOIN ' . _DB_PREFIX_ . 'feature_value_lang fv ON fp.id_feature_value = fv.id_feature_value
                    WHERE fp.id_product = ' . (int) $productId . '
                    AND fv.id_lang = ' . (int) $this->context->language->id;
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            EverblockCache::cacheStore($cacheId, $result);
            return $result;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    protected function getFeaturesAsFlags()
    {
        $cacheId = $this->name . '_getFeaturesAsFlags_' . (int) $this->context->shop->id;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $bannedFeatures = Configuration::get('EVERPS_FEATURES_AS_FLAGS');
            if (!$bannedFeatures) {
                $bannedFeatures = [];
            } else {
                $bannedFeatures = json_decode($bannedFeatures);
            }
            EverblockCache::cacheStore($cacheId, $bannedFeatures);
            return $bannedFeatures;
        }
        return EverblockCache::cacheRetrieve($cacheId);
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
        if (count($tab) > 0) {
            return $tab;
        }
        return false;
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
            $everToken = $this->encrypt($this->name . '/everlogin');
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
            $everToken = $this->encrypt($this->name . '/everlogin');
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
        $position = isset($args[0]['position']) ? (int) $args[0]['position'] : null;
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
        . (int) $context->getDevice()
        . $position;
        if (!EverblockCache::isCacheStored(str_replace('|', '-', $cacheId))) {
            if (isset($context->customer->id) && $context->customer->id) {
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
                if ((bool) $block['modal'] === true
                    && (bool) EverblockTools::isBot() === true
                ) {
                    continue;
                }
                if (Validate::isInt($position) && (int) $block['position'] != (int) $position) {
                    continue;
                }
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
                    (int) $id_entity
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
                if (in_array($method, $this->bypassedControllers)) {
                    $block['content'] = strip_tags($block['content']);
                    $context->smarty->assign([
                        'is_bypassed' => true,
                    ]);
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
            $cached = EverblockCache::cacheStore(
                str_replace('|', '-', $cacheId),
                $tpl
            );
            return $tpl;
        }
        return EverblockCache::cacheRetrieve(
            str_replace('|', '-', $cacheId)
        );
    }

    public function hookDisplayHeader()
    {
        $this->registerHook('beforeRenderingEverblockProductHighlight');
        if (Tools::getValue('eac')
            && Validate::isInt(Tools::getValue('eac'))
        ) {
            EverblockTools::addToCartByUrl(
                $this->context,
                (int) Tools::getValue('id_product'),
                (int) Tools::getValue('id_product_attribute'),
                (int) Tools::getValue('qty')
            );
        }
        // Google Shopping hack
        $modelId = (int) Tools::getValue('model_id');
        if ($modelId) {
            $modelAttributeId = (int) Tools::getValue('model_attribute_id');

            $product = new Product($modelId, true, $this->context->language->id);
            if (Validate::isLoadedObject($product)) {
                $presentedProducts = EverblockTools::everPresentProducts(
                    [$product->id],
                    $this->context,
                    $this
                );
                $presentedProduct = reset($presentedProducts);

                // Injection de la bonne combinaison si précisée
                if (!empty($modelAttributeId) && isset($presentedProduct['combinations'])) {
                    foreach ($presentedProduct['combinations'] as $comb) {
                        if ((int) $comb['id_product_attribute'] === $modelAttributeId) {
                            $presentedProduct['id_product_attribute'] = $modelAttributeId;
                            $presentedProduct['combination'] = $comb;
                            // Important si tu veux forcer l'affichage correct de prix/image
                            $presentedProduct['price'] = $comb['price'];
                            $presentedProduct['price_amount'] = $comb['price_amount'];
                            if (!empty($comb['images'][0])) {
                                $presentedProduct['cover'] = $comb['images'][0]; // remplace l'image principale si nécessaire
                            }
                            break;
                        }
                    }
                }

                $this->context->smarty->assign('ever_model', $presentedProduct);
            }
        }

        $idShop = (int) $this->context->shop->id;
        // Register your CSS file
        $this->context->controller->registerStylesheet(
            'module-' . $this->name . '-css',
            'modules/' . $this->name . '/views/css/' . $this->name . '.css',
            ['media' => 'all', 'priority' => 200]
        );
        $flagsCssFile = _PS_MODULE_DIR_ . $this->name . '/views/css/feature-flags-' . $idShop . '.css';
        if (file_exists($flagsCssFile) && filesize($flagsCssFile) > 0) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-feature-flags-css',
                'modules/' . $this->name . '/views/css/feature-flags-' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200]
            );
        }
        if ((bool) Configuration::get('EVERBLOCK_USE_SLICK') === true) {
            $this->context->controller->registerStylesheet(
                'module-slick-min-css',
                'modules/' . $this->name . '/views/css/slick-min.css',
                ['media' => 'all', 'priority' => 200]
            );
            $this->context->controller->registerJavascript(
                'module-slick-min-js',
                'modules/' . $this->name . '/views/js/slick-min.js',
                ['position' => 'bottom', 'priority' => 200]
            );
            Media::addJsDef([
                'everblock_slick' => true,
            ]);
        }
        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-js',
            'modules/' . $this->name . '/views/js/' . $this->name . '.js',
            ['position' => 'bottom', 'priority' => 200]
        );
        if ((bool) EverblockCache::getModuleConfiguration('EVERBLOCK_USE_OBF') === true) {
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-obf-js',
                'modules/' . $this->name . '/views/js/' . $this->name . '-obfuscation.js',
                ['position' => 'bottom', 'priority' => 200]
            );
        }
        $compressedCss = _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $customJs = _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom' . $idShop . '.js';
        if (file_exists($compressedCss) && filesize($compressedCss) > 0) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-custom-compressed-css',
                'modules/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200]
            );
        }
        if (file_exists($customJs) && filesize($customJs) > 0) {
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-compressed-js',
                'modules/' . $this->name . '/views/js/custom' . $idShop . '.js',
                ['position' => 'bottom', 'priority' => 200]
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
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 200]
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
                    ['server' => 'remote', 'media' => 'all', 'priority' => 200]
                );
            }
        }
        // Do not show GMAP api KEY on Everblock cache
        $apiKey = Configuration::get('EVERBLOCK_GMAP_KEY');
        if (Tools::getValue('controller') == 'cms') {
            $filename = 'store-locator-' . $idShop . '.js';
            $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
            if (file_exists($filePath) && filesize($filePath) > 0) {
                if ($apiKey) {
                    $this->context->controller->registerJavascript(
                        'module-' . $this->name . '-custom-gmap-js',
                        'https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&libraries=places,geometry',
                        ['server' => 'remote', 'position' => 'bottom', 'priority' => 300, 'attributes' => 'defer']
                    );
                }
                $this->context->controller->registerJavascript(
                    'module-' . $this->name . '-shop-map-js',
                    'modules/' . $this->name . '/views/js/' . $filename,
                    ['server' => 'local', 'position' => 'bottom', 'priority' => 400, 'attributes' => 'defer']
                );
            }
        }
        $contactLink = base64_encode(
            $this->context->link->getModuleLink(
                $this->name,
                'contact'
            )
        );
        $modalLink = base64_encode(
            $this->context->link->getModuleLink(
                $this->name,
                'modal'
            )
        );
        $shortcodeLink = base64_encode(
            $this->context->link->getModuleLink(
                $this->name,
                'shortcode'
            )
        );
        Media::addJsDef([
            'evercontact_link' => $contactLink,
            'evermodal_link' => $modalLink,
            'evershortcode_link' => $shortcodeLink,
            'everblock_token' => Tools::getToken(),
        ]);
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/header-scripts-' . $this->context->shop->id . '.js';
        if (file_exists($filePath) && filesize($filePath) > 0) {
            return PHP_EOL . file_get_contents($filePath) . PHP_EOL;
        }
    }

    public function hookDisplayContentWrapperTop()
    {
        return $this->display(__FILE__, 'views/templates/hook/displayEverModel.tpl');
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
            if ($response && Tools::version_compare($response, $this->version, '>')) {
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
        $indexContent = Tools::getDefaultIndexContent();
        // Utiliser le chemin du module actuel comme point de départ
        $moduleDir = $this->getLocalPath();
        // Fonction récursive pour ajouter le fichier index.php
        static::addIndexFileRecursively($moduleDir, $indexContent);
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

    protected function uploadBlocksFile()
    {
        if (isset($_FILES['BLOCKS_FILE'])
            && isset($_FILES['BLOCKS_FILE']['tmp_name'])
            && !empty($_FILES['BLOCKS_FILE']['tmp_name'])
        ) {
            $filename = $_FILES['BLOCKS_FILE']['name'];
            $exploded_filename = explode('.', $filename);
            $ext = end($exploded_filename);
            if (Tools::strtolower($ext) != 'xlsx') {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['BLOCKS_FILE']['tmp_name'], $tmp_name)
            ) {
                return false;
            }

            copy($tmp_name, _PS_MODULE_DIR_ . $this->name . '/input/blocks.xlsx');
            $this->processBlocksFile();
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

    protected function processBlocksFile()
    {
        $blocksFile = _PS_MODULE_DIR_ . $this->name . '/input/blocks.xlsx';
        if (!file_exists($blocksFile)) {
            return;
        }
        $file = new ImportFile($blocksFile);
        $lines = $file->getLines();
        foreach ($lines as $line) {
            $this->createBlockFromExcel($line);
        }
        unlink($blocksFile);
        Tools::clearAllCache();
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
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    protected function createBlockFromExcel($line)
    {
        if (!isset($line['name']) || !Validate::isGenericName($line['name'])) {
            $this->postErrors[] = $this->l('Missing or invalid name column');
            return;
        }
        if (!isset($line['hook']) || !Validate::isHookName($line['hook'])) {
            $this->postErrors[] = $this->l('Missing or invalid hook column');
            return;
        }
        $idHook = (int) Hook::getIdByName($line['hook']);
        if (!$idHook) {
            PrestaShopLogger::addLog($this->name . ' | ' . $this->l('Hook not found') . ' : ' . $line['hook']);
            return;
        }
        if (!isset($line['id_shop']) || !Validate::isInt($line['id_shop'])) {
            $this->postErrors[] = $this->l('Missing or invalid id_shop column');
            return;
        }
        if (!isset($line['id_lang']) || !Validate::isInt($line['id_lang'])) {
            $this->postErrors[] = $this->l('Missing or invalid id_lang column');
            return;
        }

        $block = new EverblockClass();
        $block->name = pSQL($line['name']);
        $block->id_hook = $idHook;
        $block->id_shop = (int) $line['id_shop'];

        if (isset($line['position']) && Validate::isUnsignedInt($line['position'])) {
            $block->position = (int) $line['position'];
        } else {
            $block->position = 0;
        }
        if (isset($line['active']) && Validate::isBool($line['active'])) {
            $block->active = (int) $line['active'];
        } else {
            $block->active = 1;
        }
        if (isset($line['only_home']) && Validate::isBool($line['only_home'])) {
            $block->only_home = $line['only_home'];
        }
        if (isset($line['only_category']) && Validate::isBool($line['only_category'])) {
            $block->only_category = $line['only_category'];
        }
        if (isset($line['only_category_product']) && Validate::isBool($line['only_category_product'])) {
            $block->only_category_product = $line['only_category_product'];
        }
        if (isset($line['device']) && Validate::isUnsignedInt($line['device'])) {
            $block->device = $line['device'];
        }
        if (isset($line['categories']) && Validate::isString($line['categories'])) {
            $block->categories = json_encode(explode(',', $line['categories']));
        }
        if (isset($line['groups']) && Validate::isString($line['groups'])) {
            $block->groups = json_encode(explode(',', $line['groups']));
        }
        if (isset($line['only_manufacturer']) && Validate::isBool($line['only_manufacturer'])) {
            $block->only_manufacturer = $line['only_manufacturer'];
        }
        if (isset($line['only_supplier']) && Validate::isBool($line['only_supplier'])) {
            $block->only_supplier = $line['only_supplier'];
        }
        if (isset($line['only_cms_category']) && Validate::isBool($line['only_cms_category'])) {
            $block->only_cms_category = $line['only_cms_category'];
        }
        if (isset($line['manufacturers']) && Validate::isString($line['manufacturers'])) {
            $block->manufacturers = json_encode(explode(',', $line['manufacturers']));
        }
        if (isset($line['suppliers']) && Validate::isString($line['suppliers'])) {
            $block->suppliers = json_encode(explode(',', $line['suppliers']));
        }
        if (isset($line['cms_categories']) && Validate::isString($line['cms_categories'])) {
            $block->cms_categories = json_encode(explode(',', $line['cms_categories']));
        }
        if (isset($line['obfuscate_link']) && Validate::isBool($line['obfuscate_link'])) {
            $block->obfuscate_link = $line['obfuscate_link'];
        }
        if (isset($line['add_container']) && Validate::isBool($line['add_container'])) {
            $block->add_container = $line['add_container'];
        }
        if (isset($line['lazyload']) && Validate::isBool($line['lazyload'])) {
            $block->lazyload = $line['lazyload'];
        }
        if (isset($line['background']) && Validate::isColor($line['background'])) {
            $block->background = $line['background'];
        }
        if (isset($line['css_class']) && Validate::isString($line['css_class'])) {
            $block->css_class = $line['css_class'];
        }
        if (isset($line['data_attribute']) && Validate::isString($line['data_attribute'])) {
            $block->data_attribute = $line['data_attribute'];
        }
        if (isset($line['bootstrap_class']) && Validate::isString($line['bootstrap_class'])) {
            $block->bootstrap_class = $line['bootstrap_class'];
        }
        if (isset($line['modal']) && Validate::isBool($line['modal'])) {
            $block->modal = $line['modal'];
        }
        if (isset($line['delay']) && Validate::isUnsignedInt($line['delay'])) {
            $block->delay = $line['delay'];
        }
        if (isset($line['timeout']) && Validate::isUnsignedInt($line['timeout'])) {
            $block->timeout = $line['timeout'];
        }
        if (isset($line['date_start']) && Validate::isDateFormat($line['date_start'])) {
            $block->date_start = $line['date_start'];
        }
        if (isset($line['date_end']) && Validate::isDateFormat($line['date_end'])) {
            $block->date_end = $line['date_end'];
        }
        if (isset($line['content']) && Validate::isAnything($line['content'])) {
            $block->content[(int) $line['id_lang']] = $line['content'];
        }
        if (isset($line['custom_code']) && Validate::isAnything($line['custom_code'])) {
            $block->custom_code[(int) $line['id_lang']] = $line['custom_code'];
        }

        try {
            $block->save();
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
        }
    }

    public function hookBeforeRenderingEverblockProductHighlight($params)
    {
        $product = false;
        if (!empty($params['block']['settings']['id_product'])) {
            $presented = EverblockTools::everPresentProducts(
                [(int) $params['block']['settings']['id_product']],
                $this->context
            );
            if (!empty($presented)) {
                $product = reset($presented);
            }
        }

        return ['product' => $product];
    }

    public function encrypt($data)
    {
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            return Tools::hash($data);
        }

        return Tools::encrypt($data);
    }
}
