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
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockModal.php';

use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use Everblock\Tools\Checkout\EverblockCheckoutStep;
use Everblock\Tools\Service\EverblockPrettyBlocks;
use Everblock\Tools\Service\EverblockCache;
use Everblock\Tools\Service\ImportFile;
use Everblock\Tools\Service\RecaptchaValidator;
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
        'fetchwordpressposts',
    ];
    private $bypassedControllers = [
        'hookDisplayInvoiceLegalFreeText',
    ];

    public function __construct()
    {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '8.0.2';
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
        Configuration::updateValue('EVERWP_API_URL', '');
        Configuration::updateValue('EVERWP_API_USER', '');
        Configuration::updateValue('EVERWP_API_PWD', '');
        Configuration::updateValue('EVERWP_POST_NBR', 3);
        Configuration::updateValue('EVER_SOLDOUT_COLOR', '#ff0000');
        Configuration::updateValue('EVER_SOLDOUT_TEXTCOLOR', '#ffffff');
        Configuration::updateValue('EVERINSTA_SHOW_CAPTION', 0);
        Configuration::updateValue('EVERBLOCK_CONTACT_MAX_UPLOAD_SIZE', 2097152);
        Configuration::updateValue(
            'EVERBLOCK_CONTACT_ALLOWED_EXTENSIONS',
            json_encode(['pdf', 'jpg', 'jpeg', 'png']),
            true
        );
        Configuration::updateValue(
            'EVERBLOCK_CONTACT_ALLOWED_MIME_TYPES',
            json_encode(['application/pdf', 'image/jpeg', 'image/png']),
            true
        );
        Configuration::updateValue(
            'EVERPS_FEATURES_AS_FLAGS',
            json_encode([1]),
            true
        );
        Configuration::updateValue('EVERBLOCK_SOLDOUT_FLAG', 0);
        Configuration::updateValue('EVERBLOCK_LOW_STOCK_THRESHOLD', 5);
        Configuration::updateValue('EVERBLOCK_STORELOCATOR_TOGGLE', 0);
        Configuration::updateValue('EVERBLOCK_RECAPTCHA_ENABLED', 0);
        Configuration::updateValue('EVERBLOCK_RECAPTCHA_SITE_KEY', '');
        Configuration::updateValue('EVERBLOCK_RECAPTCHA_SECRET_KEY', '');
        Configuration::updateValue('EVERBLOCK_RECAPTCHA_PROTECT_CONTACT', 0);
        Configuration::updateValue('EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT', '0.5');
        $recaptchaMessage = [];
        foreach (Language::getLanguages(false) as $lang) {
            $recaptchaMessage[$lang['id_lang']] = $this->l('Your submission could not be validated. Please try again.');
        }
        Configuration::updateValue('EVERBLOCK_RECAPTCHA_ERROR_MESSAGE', $recaptchaMessage, true);
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
        // Hook beforeRenderingEverblockSpecialEvent
        if (!Hook::getIdByName('beforeRenderingEverblockSpecialEvent')) {
            $hook = new Hook();
            $hook->name = 'beforeRenderingEverblockSpecialEvent';
            $hook->title = 'Before rendering special event block';
            $hook->description = 'This hook triggers before special event block is rendered';
            $hook->save();
        }
        return (parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionFrontControllerInitBefore')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionRegisterBlock')
            && $this->registerHook('beforeRenderingEverblockSpecialEvent')
            && $this->installModuleTab('AdminEverBlockParent', 'IMPROVE', $this->l('Ever Block'))
            && $this->installModuleTab('AdminEverBlockConfiguration', 'AdminEverBlockParent', $this->l('Configuration'))
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
        Configuration::deleteByName('EVERWP_API_URL');
        Configuration::deleteByName('EVERWP_API_USER');
        Configuration::deleteByName('EVERWP_API_PWD');
        Configuration::deleteByName('EVERWP_POST_NBR');
        Configuration::deleteByName('EVER_SOLDOUT_COLOR');
        Configuration::deleteByName('EVER_SOLDOUT_TEXTCOLOR');
        Configuration::deleteByName('EVERBLOCK_SOLDOUT_FLAG');
        Configuration::deleteByName('EVERINSTA_SHOW_CAPTION');
        Configuration::deleteByName('EVERBLOCK_CONTACT_MAX_UPLOAD_SIZE');
        Configuration::deleteByName('EVERBLOCK_CONTACT_ALLOWED_EXTENSIONS');
        Configuration::deleteByName('EVERBLOCK_CONTACT_ALLOWED_MIME_TYPES');
        Configuration::deleteByName('EVERBLOCK_LOW_STOCK_THRESHOLD');
        Configuration::deleteByName('EVERBLOCK_STORELOCATOR_TOGGLE');
        Configuration::deleteByName('EVERBLOCK_RECAPTCHA_ENABLED');
        Configuration::deleteByName('EVERBLOCK_RECAPTCHA_SITE_KEY');
        Configuration::deleteByName('EVERBLOCK_RECAPTCHA_SECRET_KEY');
        Configuration::deleteByName('EVERBLOCK_RECAPTCHA_PROTECT_CONTACT');
        Configuration::deleteByName('EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT');
        Configuration::deleteByName('EVERBLOCK_RECAPTCHA_ERROR_MESSAGE');
        return (parent::uninstall()
            && $this->uninstallModuleTab('AdminEverBlockParent')
            && $this->uninstallModuleTab('AdminEverBlockConfiguration')
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
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) EverblockTools::moduleDirectoryExists('prettyblocks') === true
        ) {
            if (!Hook::getIdByName('beforeRenderingEverblockProductHighlight')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockProductHighlight';
                $hook->title = 'Before rendering product highlight block';
                $hook->description = 'This hook triggers before product highlight block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockCategoryTabs')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockCategoryTabs';
                $hook->title = 'Before rendering category tabs block';
                $hook->description = 'This hook triggers before category tabs block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockCategoryPrice')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockCategoryPrice';
                $hook->title = 'Before rendering category price block';
                $hook->description = 'This hook triggers before category price block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockLookbook')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockLookbook';
                $hook->title = 'Before rendering lookbook block';
                $hook->description = 'This hook triggers before lookbook block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockFlashDeals')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockFlashDeals';
                $hook->title = 'Before rendering flash deals block';
                $hook->description = 'This hook triggers before flash deals block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockCategoryProducts')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockCategoryProducts';
                $hook->title = 'Before rendering category products block';
                $hook->description = 'This hook triggers before category products block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockSpecialEvent')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockSpecialEvent';
                $hook->title = 'Before rendering special event block';
                $hook->description = 'This hook triggers before special event block is rendered';
                $hook->save();
            }
            $this->registerHook('beforeRenderingEverblockProductHighlight');
            $this->registerHook('beforeRenderingEverblockCategoryTabs');
            $this->registerHook('beforeRenderingEverblockCategoryPrice');
            $this->registerHook('beforeRenderingEverblockLookbook');
            $this->registerHook('beforeRenderingEverblockFlashDeals');
            $this->registerHook('beforeRenderingEverblockCategoryProducts');
            $this->registerHook('beforeRenderingEverblockSpecialEvent');
            $this->registerHook('beforeRenderingEverblockEverblock');
        } else {
            $this->unregisterHook('beforeRenderingEverblockProductHighlight');
            $this->unregisterHook('beforeRenderingEverblockCategoryTabs');
            $this->unregisterHook('beforeRenderingEverblockCategoryPrice');
            $this->unregisterHook('beforeRenderingEverblockLookbook');
            $this->unregisterHook('beforeRenderingEverblockFlashDeals');
            $this->unregisterHook('beforeRenderingEverblockCategoryProducts');
            $this->unregisterHook('beforeRenderingEverblockSpecialEvent');
            $this->unregisterHook('beforeRenderingEverblockEverblock');
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
        $this->registerHook('displayAdminProductsMainStepLeftColumnBottom');
        $this->registerHook('actionObjectProductAddAfter');
        $this->registerHook('displayReassurance');
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
        $this->updateProductFlagsHook();
        $this->registerHook('actionEmailAddAfterContent');
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) EverblockTools::moduleDirectoryExists('prettyblocks') === true
        ) {
            $this->registerHook('actionRegisterBlock');
            $this->registerHook('beforeRenderingEverblockProductSelector');
            $this->registerHook('beforeRenderingEverblockCategoryProducts');
        } else {
            $this->unregisterHook('actionRegisterBlock');
        }
    }

    protected function updateProductFlagsHook()
    {
        $idShop = (int) $this->context->shop->id;
        $cacheId = $this->name . 'NeedProductFlagsHook_' . $idShop;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $needHook = false;

            if (Configuration::get('EVERBLOCK_SOLDOUT_FLAG')) {
                $needHook = true;
            }

            $featuresAsFlags = json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
            if (!empty($featuresAsFlags)) {
                $needHook = true;
            }

            $sql = new DbQuery();
            $sql->select('id_everblock_flags');
            $sql->from(EverblockFlagsClass::$definition['table']);
            $sql->where('id_shop = ' . (int) $idShop);
            if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
                $needHook = true;
            }

            EverblockCache::cacheStore($cacheId, $needHook);
        }

        $needHook = (bool) EverblockCache::cacheRetrieve($cacheId);

        if ($needHook) {
            $this->registerHook('actionProductFlagsModifier');
        } else {
            $this->unregisterHook('actionProductFlagsModifier');
        }
    }

    public function getContent()
    {
        $this->createUpgradeFile();
        $this->secureModuleFolder();
        EverblockTools::checkAndFixDatabase();
        $this->checkHooks();
        $this->html = '';

        if (Tools::isSubmit('deleteEVERBLOCK_MARKER_ICON')) {
            $icon = Configuration::get('EVERBLOCK_MARKER_ICON');
            if ($icon) {
                $path = _PS_MODULE_DIR_ . $this->name . '/views/img/' . $icon;
                if (file_exists($path)) {
                    @unlink($path);
                }
                Configuration::deleteByName('EVERBLOCK_MARKER_ICON');
                $this->postSuccess[] = $this->l('Marker icon removed.');
            }
        }

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
        if ((bool) Tools::isSubmit('submitUploadSvg') === true) {
            $this->uploadSvgFile();
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
            $configBackuped = EverblockTools::exportConfigurationSQL();
            if ((bool) $backuped === true && (bool) $configBackuped === true) {
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
        $displayUpgrade = $this->checkLatestEverModuleVersion();
        $notifications = $this->html;
        $this->html = '';
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
            'donation_link' => 'https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE',
            'everblock_notifications' => $notifications,
            'everblock_form' => $this->renderForm(),
            'display_upgrade' => $displayUpgrade,
        ]);
        $output = $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/header.tpl'
        );
        $output .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/configure.tpl'
        );
        $output .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/footer.tpl'
        );

        return $output;
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

        $tabs = [
            'settings' => $this->l('Réglages'),
            'meta_tools' => $this->l('Meta Tools'),
            'google_maps' => $this->l('Google Maps'),
            'migration' => $this->l('Migration des URL'),
            'tools' => $this->l('Outils'),
            'protection' => $this->l('Protection'),
            'files' => $this->l('Gestionnaire de fichiers'),
            'flags' => $this->l('Flags'),
        ];

        $isPrettyBlocksEnabled = (bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) EverblockTools::moduleDirectoryExists('prettyblocks') === true;

        if ($isPrettyBlocksEnabled) {
            $tabs['prettyblock'] = $this->l('Prettyblock');
        }

        $tabs['holiday'] = $this->l('Holiday opening hours by store');

        $tabs['cron'] = $this->l('Tâches crons');

        $form = [
            'form' => [
                'tabs' => $tabs,
                'input' => [],
                'buttons' => [],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $docTemplates = [
            'settings' => 'settings.tpl',
            'meta_tools' => 'meta_tools.tpl',
            'google_maps' => 'google_maps.tpl',
            'migration' => 'migration.tpl',
            'tools' => 'tools.tpl',
            'protection' => 'protection.tpl',
            'files' => 'files.tpl',
            'flags' => 'flags.tpl',
            'holiday' => 'holiday.tpl',
            'cron' => 'cron.tpl',
        ];

        if ($isPrettyBlocksEnabled) {
            $docTemplates['prettyblock'] = 'prettyblock.tpl';
        }

        foreach ($docTemplates as $tab => $template) {
            $docPath = $this->local_path . 'views/templates/admin/config/docs/' . $template;

            if (!Tools::file_exists_cache($docPath)) {
                continue;
            }

            $form['form']['input'][] = [
                'type' => 'html',
                'name' => 'documentation_' . $tab,
                'tab' => $tab,
                'html_content' => $this->context->smarty->fetch($docPath),
            ];
        }

        $form['form']['input'][] = [
            'type' => 'html',
            'name' => 'anchor_everblock_protection',
            'html_content' => '<span id="everblock_protection"></span>',
            'tab' => 'protection',
        ];

        $protectionInputs = [
            [
                'type' => 'switch',
                'label' => $this->l('Enable Invisible reCAPTCHA'),
                'desc' => $this->l('Activates Google reCAPTCHA v3 for Ever Block contact forms.'),
                'name' => 'EVERBLOCK_RECAPTCHA_ENABLED',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'EVERBLOCK_RECAPTCHA_ENABLED_on',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'EVERBLOCK_RECAPTCHA_ENABLED_off',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Site key'),
                'desc' => $this->l('Public key provided by Google for your Invisible reCAPTCHA property.'),
                'name' => 'EVERBLOCK_RECAPTCHA_SITE_KEY',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Secret key'),
                'desc' => $this->l('Secret key used server-side to verify reCAPTCHA tokens.'),
                'name' => 'EVERBLOCK_RECAPTCHA_SECRET_KEY',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Error message'),
                'desc' => $this->l('Displayed to the customer when the anti-spam verification fails.'),
                'name' => 'EVERBLOCK_RECAPTCHA_ERROR_MESSAGE',
                'lang' => true,
                'rows' => 3,
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Protect Everblock contact forms'),
                'desc' => $this->l('Applies reCAPTCHA to the contact forms created with Ever Block shortcodes.'),
                'name' => 'EVERBLOCK_RECAPTCHA_PROTECT_CONTACT',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'EVERBLOCK_RECAPTCHA_PROTECT_CONTACT_on',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'EVERBLOCK_RECAPTCHA_PROTECT_CONTACT_off',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Minimum score for Everblock contact forms'),
                'desc' => $this->l('Allowed range: 0 to 1. Requests scoring below this threshold will be rejected.'),
                'name' => 'EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT',
            ],
        ];

        foreach ($protectionInputs as $input) {
            $input['tab'] = 'protection';
            $form['form']['input'][] = $input;
        }

        $form['form']['input'][] = [
            'type' => 'html',
            'name' => 'anchor_everblock_tools',
            'html_content' => '<span id="everblock_tools"></span>',
            'tab' => 'tools',
        ];

        $toolButtons = [
            [
                'name' => 'submitEmptyCache',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Empty cache'),
            ],
            [
                'name' => 'submitEmptyLogs',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Empty logs'),
            ],
            [
                'name' => 'submitDropUnusedLangs',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Drop unused langs'),
            ],
            [
                'name' => 'submitSecureModuleFoldersWithApache',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Secure all modules folders using Apache'),
            ],
            [
                'name' => 'submitBackupBlocks',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Backup all blocks'),
            ],
            [
                'name' => 'submitRestoreBackup',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Restore backup'),
            ],
        ];

        foreach ($toolButtons as $button) {
            $button['tab'] = 'tools';
            $form['form']['buttons'][] = $button;
        }

        $settingsInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_settings',
                'html_content' => '<span id="everblock_settings"></span>',
            ],
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
        ];

        foreach ($settingsInputs as $input) {
            $input['tab'] = 'settings';
            $form['form']['input'][] = $input;
        }

        $metaToolsInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_meta_tools',
                'html_content' => '<span id="everblock_meta_tools"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Instagram access token'),
                'desc' => $this->l('Add here your Instagram access token'),
                'hint' => $this->l('Without access token, you wont be able to show Instagram slider'),
                'name' => 'EVERINSTA_ACCESS_TOKEN',
            ],
        ];

        foreach ($metaToolsInputs as $input) {
            $input['tab'] = 'meta_tools';
            $form['form']['input'][] = $input;
        }

        $wordpressInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_wordpress',
                'html_content' => '<span id="everblock_wordpress"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('WordPress API URL'),
                'desc' => $this->l('Full REST API endpoint for posts'),
                'hint' => $this->l('Example: https://example.com/wp-json/wp/v2/posts'),
                'name' => 'EVERWP_API_URL',
            ],
            [
                'type' => 'text',
                'label' => $this->l('WordPress API user'),
                'name' => 'EVERWP_API_USER',
            ],
            [
                'type' => 'text',
                'label' => $this->l('WordPress API password'),
                'name' => 'EVERWP_API_PWD',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Number of blog posts to display'),
                'name' => 'EVERWP_POST_NBR',
            ],
        ];

        foreach ($wordpressInputs as $input) {
            $input['tab'] = 'meta_tools';
            $form['form']['input'][] = $input;
        }

        $markerIcon = Configuration::get('EVERBLOCK_MARKER_ICON');
        $googleMapsInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_google_maps',
                'html_content' => '<span id="everblock_google_maps"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Google Map API key (CMS page only)'),
                'desc' => $this->l('Add here your Google Map API key'),
                'hint' => $this->l('Without API key, auto complete wont work'),
                'name' => 'EVERBLOCK_GMAP_KEY',
            ],
            [
                'type' => 'file',
                'label' => $this->l('Store locator marker icon'),
                'desc' => $this->l('Upload an SVG icon used as the Google Maps marker'),
                'hint' => $this->l('Only SVG files are allowed'),
                'name' => 'EVERBLOCK_MARKER_ICON',
                'display_image' => true,
                'image' => $markerIcon
                    ? $this->context->link->getBaseLink(null, null) . 'modules/' . $this->name . '/views/img/' . $markerIcon
                    : false,
                'delete_url' => $this->context->link->getAdminLink('AdminModules')
                    . '&configure=' . $this->name . '&deleteEVERBLOCK_MARKER_ICON=1',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Display map toggle button'),
                'desc' => $this->l('Add a button next to the store search to hide or show the map.'),
                'name' => 'EVERBLOCK_STORELOCATOR_TOGGLE',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'everblock_storelocator_toggle_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'everblock_storelocator_toggle_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
        ];

        foreach ($googleMapsInputs as $input) {
            $input['tab'] = 'google_maps';
            $form['form']['input'][] = $input;
        }

        $designInputs = [
            [
                'type' => 'switch',
                'label' => $this->l('Show Sold out flag'),
                'desc' => $this->l('Display a Sold out flag when product stock is empty and orders are not allowed'),
                'hint' => $this->l('Combinations and stock settings will be checked'),
                'name' => 'EVERBLOCK_SOLDOUT_FLAG',
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
            [
                'type' => 'html',
                'name' => 'anchor_everblock_feature_colors',
                'html_content' => '<span id="everblock_feature_colors"></span>',
            ],
        ];

        $toolsInputs = [
            [
                'type' => 'textarea',
                'label' => $this->l('Code CSS personnalisé'),
                'desc' => $this->l('Add here your custom CSS rules'),
                'hint' => $this->l('Webdesigners here can manage CSS rules'),
                'name' => 'EVERPSCSS',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Javascript / jQuery personnalisé'),
                'desc' => $this->l('Add here your custom Javascript rules'),
                'hint' => $this->l('Webdesigners here can manage Javascript rules'),
                'name' => 'EVERPSJS',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Liens CSS personnalisés'),
                'desc' => $this->l('Add here your custom CSS links, one per line'),
                'hint' => $this->l('Add one link per line, must be CSS'),
                'name' => 'EVERPSCSS_LINKS',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Liens javascript personnalisés'),
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
        ];

        foreach ($designInputs as $input) {
            if ($input['name'] === 'anchor_everblock_feature_colors') {
                $input['tab'] = 'flags';
            } else {
                $input['tab'] = 'settings';
            }
            $form['form']['input'][] = $input;
        }

        foreach ($toolsInputs as $input) {
            $input['tab'] = 'tools';
            $form['form']['input'][] = $input;
        }

        $bannedFeatures = json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
        if (!is_array($bannedFeatures)) {
            $bannedFeatures = [];
        }

        foreach ($bannedFeatures as $bannedFeature) {
            $featureId = (int) $bannedFeature;
            $feature = new Feature((int) $featureId);
            $idLang = (int) Context::getContext()->language->id;
            $featureName = isset($feature->name[$idLang]) ? $feature->name[$idLang] : $this->l('Unnamed feature');

            $form['form']['input'][] = [
                'type' => 'color',
                'label' => $this->l('Background color for Feature: ') . $featureName,
                'name' => 'EVERPS_FEATURE_COLOR_' . $featureId,
                'size' => 20,
                'required' => false,
                'tab' => 'flags',
            ];

            $form['form']['input'][] = [
                'type' => 'color',
                'label' => $this->l('Text color for Feature: ') . $featureName,
                'name' => 'EVERPS_FEATURE_TEXTCOLOR_' . $featureId,
                'size' => 20,
                'required' => false,
                'tab' => 'flags',
            ];
        }

        $form['form']['input'][] = [
            'type' => 'html',
            'name' => 'anchor_everblock_soldout_colors',
            'html_content' => '<span id="everblock_soldout_colors"></span>',
            'tab' => 'flags',
        ];

        $form['form']['input'][] = [
            'type' => 'color',
            'label' => $this->l('Background color for Sold out flag'),
            'name' => 'EVER_SOLDOUT_COLOR',
            'size' => 20,
            'required' => false,
            'tab' => 'flags',
        ];

        $form['form']['input'][] = [
            'type' => 'color',
            'label' => $this->l('Text color for Sold out flag'),
            'name' => 'EVER_SOLDOUT_TEXTCOLOR',
            'size' => 20,
            'required' => false,
            'tab' => 'flags',
        ];

        if (Configuration::get('EVERINSTA_ACCESS_TOKEN')) {
            $instagramInputs = [
                [
                    'type' => 'html',
                    'name' => 'anchor_everblock_instagram',
                    'html_content' => '<span id="everblock_instagram"></span>',
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
                    'label' => $this->l('Display Instagram post text'),
                    'desc' => $this->l('Show caption text below images'),
                    'name' => 'EVERINSTA_SHOW_CAPTION',
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
            ];

            foreach ($instagramInputs as $input) {
                $input['tab'] = 'meta_tools';
                $form['form']['input'][] = $input;
            }
        }

        if ($isPrettyBlocksEnabled) {
            $prettyBlocksInputs = [
                [
                    'type' => 'html',
                    'name' => 'anchor_everblock_prettyblocks',
                    'html_content' => '<span id="everblock_prettyblocks"></span>',
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Upload custom SVG'),
                    'desc' => $this->l('Upload your own SVG icon for PrettyBlocks'),
                    'hint' => $this->l('Only SVG files are allowed'),
                    'name' => 'CUSTOM_SVG',
                    'display_image' => false,
                    'required' => false,
                ],
            ];

            foreach ($prettyBlocksInputs as $input) {
                $input['tab'] = 'prettyblock';
                $form['form']['input'][] = $input;
            }

            $form['form']['input'][] = [
                'type' => 'html',
                'name' => 'submitUploadSvgButton',
                'html_content' => sprintf(
                    '<button type="submit" name="%s" class="btn btn-default pull-right"><i class="process-icon-download"></i> %s</button>',
                    'submitUploadSvg',
                    htmlspecialchars($this->l('Upload SVG'), ENT_QUOTES, 'UTF-8')
                ),
                'tab' => 'prettyblock',
            ];
        }

        $importInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_file_management',
                'html_content' => '<span id="everblock_file_management"></span>',
            ],
            [
                'type' => 'file',
                'label' => $this->l('Upload Excel tabs file'),
                'desc' => $this->l('Will upload Excel tabs file and import datas into this module'),
                'hint' => $this->l('You can then import this file in order to set up your tabs in bulk on the product sheets'),
                'name' => 'TABS_FILE',
                'display_image' => false,
                'required' => false,
            ],
        ];

        foreach ($importInputs as $input) {
            $input['tab'] = 'files';
            $form['form']['input'][] = $input;
        }

        $form['form']['input'][] = [
            'type' => 'html',
            'name' => 'submitUploadTabsFileButton',
            'html_content' => sprintf(
                '<button type="submit" name="%s" class="btn btn-default pull-right"><i class="process-icon-download"></i> %s</button>',
                'submitUploadTabsFile',
                htmlspecialchars($this->l('Upload file'), ENT_QUOTES, 'UTF-8')
            ),
            'tab' => 'files',
        ];

        $advancedInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_advanced',
                'html_content' => '<span id="everblock_advanced"></span>',
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
        ];

        foreach ($advancedInputs as $input) {
            if ($input['name'] === 'EVERPS_FEATURES_AS_FLAGS[]'
                || $input['name'] === 'EVERPS_FLAG_NB'
            ) {
                $input['tab'] = 'flags';
            } else {
                $input['tab'] = 'settings';
            }
            $form['form']['input'][] = $input;
        }

        $migrationInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_migration',
                'html_content' => '<span id="everblock_migration"></span>',
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
        ];

        foreach ($migrationInputs as $input) {
            $input['tab'] = 'migration';
            $form['form']['input'][] = $input;
        }

        $form['form']['buttons'][] = [
            'name' => 'submitMigrateUrls',
            'type' => 'submit',
            'class' => 'btn btn-light',
            'icon' => 'process-icon-refresh',
            'title' => $this->l('Migrate URLS'),
            'tab' => 'migration',
        ];

        $form['form']['buttons'][] = [
            'name' => 'submitCreateProduct',
            'type' => 'submit',
            'class' => 'btn btn-light',
            'icon' => 'process-icon-refresh',
            'title' => $this->l('Create fake products'),
            'tab' => 'settings',
        ];

        $stores = Store::getStores((int) $this->context->language->id);
        $holidayInputs = [];
        if (!empty($stores)) {
            $holidays = EverblockTools::getFrenchHolidays((int) date('Y'));
            foreach ($stores as $store) {
                foreach ($holidays as $date) {
                    $holidayInputs[] = [
                        'type' => 'text',
                        'label' => sprintf(
                            $this->l('Holiday hours for %s on %s'),
                            $store['name'],
                            $date
                        ),
                        'name' => 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date,
                    ];
                }
            }
        }
        if (!empty($holidayInputs)) {
            array_unshift($holidayInputs, [
                'type' => 'html',
                'name' => 'anchor_everblock_holiday',
                'html_content' => '<span id="everblock_holiday"></span>',
            ]);
            foreach ($holidayInputs as $input) {
                $input['tab'] = 'holiday';
                $form['form']['input'][] = $input;
            }
        }

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

        if (!empty($cronLinks)) {
            $cronHtml = '<div class="everblock-cron-links">';
            foreach ($cronLinks as $action => $link) {
                $cronHtml .= '<p><a class="btn btn-info" target="_blank" rel="noopener noreferrer" href="' .
                    htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">' .
                    htmlspecialchars($this->l('Cron for'), ENT_QUOTES, 'UTF-8') . ' ' .
                    htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '</a></p>';
            }
            $cronHtml .= '</div>';

            $form['form']['input'][] = [
                'type' => 'html',
                'name' => 'everblock_cron_links',
                'html_content' => $cronHtml,
                'tab' => 'cron',
            ];
        }

        $form['form']['input'] = $this->moveDocumentationToTabEnd($form['form']['input']);

        return [$form];
    }

    private function moveDocumentationToTabEnd(array $inputs)
    {
        $documentationByTab = [];
        $orphanDocumentation = [];
        $nonDocumentationInputs = [];

        foreach ($inputs as $input) {
            if (!isset($input['name']) || strpos($input['name'], 'documentation_') !== 0) {
                $nonDocumentationInputs[] = $input;
                continue;
            }

            if (isset($input['tab'])) {
                $documentationByTab[$input['tab']][] = $input;
            } else {
                $orphanDocumentation[] = $input;
            }
        }

        if (empty($documentationByTab) && empty($orphanDocumentation)) {
            return $inputs;
        }

        $orderedInputs = [];
        $countNonDocumentation = count($nonDocumentationInputs);

        foreach ($nonDocumentationInputs as $index => $input) {
            $orderedInputs[] = $input;

            if (!isset($input['tab'])) {
                continue;
            }

            $tab = $input['tab'];

            if (empty($documentationByTab[$tab])) {
                continue;
            }

            $isLastForTab = true;

            for ($nextIndex = $index + 1; $nextIndex < $countNonDocumentation; $nextIndex++) {
                if (isset($nonDocumentationInputs[$nextIndex]['tab'])
                    && $nonDocumentationInputs[$nextIndex]['tab'] === $tab
                ) {
                    $isLastForTab = false;
                    break;
                }
            }

            if ($isLastForTab) {
                foreach ($documentationByTab[$tab] as $documentationInput) {
                    $orderedInputs[] = $documentationInput;
                }

                unset($documentationByTab[$tab]);
            }
        }

        foreach ($documentationByTab as $documentationInputs) {
            foreach ($documentationInputs as $documentationInput) {
                $orderedInputs[] = $documentationInput;
            }
        }

        foreach ($orphanDocumentation as $documentationInput) {
            $orderedInputs[] = $documentationInput;
        }

        return $orderedInputs;
    }

    protected function getConfigFormValues()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $custom_css = Tools::file_get_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom' . $idShop . '.css'
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
            'EVERINSTA_SHOW_CAPTION' => Configuration::get('EVERINSTA_SHOW_CAPTION'),
            'EVERWP_API_URL' => Configuration::get('EVERWP_API_URL'),
            'EVERWP_API_USER' => Configuration::get('EVERWP_API_USER'),
            'EVERWP_API_PWD' => Configuration::get('EVERWP_API_PWD'),
            'EVERWP_POST_NBR' => Configuration::get('EVERWP_POST_NBR'),
            'EVERBLOCK_GMAP_KEY' => Configuration::get('EVERBLOCK_GMAP_KEY'),
            'EVERBLOCK_MARKER_ICON' => Configuration::get('EVERBLOCK_MARKER_ICON'),
            'EVERBLOCK_STORELOCATOR_TOGGLE' => Configuration::get('EVERBLOCK_STORELOCATOR_TOGGLE'),
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
            'EVERBLOCK_CACHE' => Configuration::get('EVERBLOCK_CACHE'),
            'EVERBLOCK_USE_OBF' => Configuration::get('EVERBLOCK_USE_OBF'),
            'EVERBLOCK_USE_SLICK' => Configuration::get('EVERBLOCK_USE_SLICK'),
            'EVERBLOCK_SOLDOUT_FLAG' => Configuration::get('EVERBLOCK_SOLDOUT_FLAG'),
            'EVER_SOLDOUT_COLOR' => Configuration::get('EVER_SOLDOUT_COLOR'),
            'EVER_SOLDOUT_TEXTCOLOR' => Configuration::get('EVER_SOLDOUT_TEXTCOLOR'),
            'EVERPSCSS' => $custom_css,
            'EVERPSJS' => $custom_js,
            'EVERPSCSS_LINKS' => Configuration::get('EVERPSCSS_LINKS'),
            'EVERPSJS_LINKS' => Configuration::get('EVERPSJS_LINKS'),
            'EVERPS_HEADER_SCRIPTS' => $headerScripts,
            'EVERPS_FEATURES_AS_FLAGS[]' => json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS')),
            'EVERPS_DUMMY_NBR' => Configuration::get('EVERPS_DUMMY_NBR'),
            'EVERPSCSS_P_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_P_LLOREM_NUMBER'),
            'EVERPSCSS_S_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_S_LLOREM_NUMBER'),
            'EVERBLOCK_TINYMCE' => Configuration::get('EVERBLOCK_TINYMCE'),
            'EVERBLOCK_RECAPTCHA_ENABLED' => Configuration::get('EVERBLOCK_RECAPTCHA_ENABLED'),
            'EVERBLOCK_RECAPTCHA_SITE_KEY' => Configuration::get('EVERBLOCK_RECAPTCHA_SITE_KEY'),
            'EVERBLOCK_RECAPTCHA_SECRET_KEY' => Configuration::get('EVERBLOCK_RECAPTCHA_SECRET_KEY'),
            'EVERBLOCK_RECAPTCHA_PROTECT_CONTACT' => Configuration::get('EVERBLOCK_RECAPTCHA_PROTECT_CONTACT'),
            'EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT' => Configuration::get('EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT'),
            'EVERBLOCK_RECAPTCHA_ERROR_MESSAGE' => $this->getConfigInMultipleLangs('EVERBLOCK_RECAPTCHA_ERROR_MESSAGE'),
            'EVERBLOCK_DISABLE_WEBP' => Configuration::get('EVERBLOCK_DISABLE_WEBP'),
            'EVERPS_OLD_URL' => '',
            'EVERPS_NEW_URL' => '',
            'EVER_TAB_CONTENT' => $this->getConfigInMultipleLangs('EVER_TAB_CONTENT'),
            'EVER_TAB_TITLE' => $this->getConfigInMultipleLangs('EVER_TAB_TITLE'),
            'EVERPS_TAB_NB' => Configuration::get('EVERPS_TAB_NB'),
            'EVERPS_FLAG_NB' => Configuration::get('EVERPS_FLAG_NB'),
            'TABS_FILE' => '',
            'BLOCKS_FILE' => '',
            'CUSTOM_SVG' => '',
        ];
        $stores = Store::getStores((int) $this->context->language->id);
        $holidays = EverblockTools::getFrenchHolidays((int) date('Y'));
        foreach ($stores as $store) {
            foreach ($holidays as $date) {
                $hoursKey = 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date;
                $configData[$hoursKey] = Configuration::get($hoursKey);
            }
        }
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
            if (Tools::getValue('EVERBLOCK_SOLDOUT_FLAG')
                && !Validate::isBool(Tools::getValue('EVERBLOCK_SOLDOUT_FLAG'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show Sold out flag" is not valid'
                );
            }
            if (Tools::getValue('EVERWP_POST_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERWP_POST_NBR'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Number of blog posts" is not valid'
                );
            }
            if (Tools::getValue('EVERPS_FEATURES_AS_FLAGS')
                && !Validate::isArrayWithIds(Tools::getValue('EVERPS_FEATURES_AS_FLAGS'))
            ) {
                $this->postErrors[] = $this->l('Error: selected features are not valid');
            }

            $booleanKeys = [
                'EVERBLOCK_RECAPTCHA_ENABLED',
                'EVERBLOCK_RECAPTCHA_PROTECT_CONTACT',
            ];
            foreach ($booleanKeys as $booleanKey) {
                if (Tools::getValue($booleanKey) !== ''
                    && !Validate::isBool(Tools::getValue($booleanKey))
                ) {
                    $this->postErrors[] = sprintf(
                        $this->l('Error: the field "%s" must be boolean.'),
                        $booleanKey
                    );
                }
            }

            $scoreKeys = [
                'EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT',
            ];

            foreach ($scoreKeys as $scoreKey) {
                $scoreValue = Tools::getValue($scoreKey);
                if ($scoreValue === '' || $scoreValue === null) {
                    continue;
                }

                $normalizedScore = str_replace(',', '.', (string) $scoreValue);
                if (!Validate::isFloat($normalizedScore)) {
                    $this->postErrors[] = sprintf(
                        $this->l('Error: the field "%s" must be a number between 0 and 1.'),
                        $scoreKey
                    );
                    continue;
                }

                $floatScore = (float) $normalizedScore;
                if ($floatScore < 0 || $floatScore > 1) {
                    $this->postErrors[] = sprintf(
                        $this->l('Error: the field "%s" must be between 0 and 1.'),
                        $scoreKey
                    );
                }
            }

            if (Tools::getValue('EVERBLOCK_RECAPTCHA_ENABLED')) {
                if (!Tools::getValue('EVERBLOCK_RECAPTCHA_SITE_KEY')) {
                    $this->postErrors[] = $this->l('Error: the reCAPTCHA site key is required when protection is enabled.');
                }
                if (!Tools::getValue('EVERBLOCK_RECAPTCHA_SECRET_KEY')) {
                    $this->postErrors[] = $this->l('Error: the reCAPTCHA secret key is required when protection is enabled.');
                }
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
        $jsCode = Tools::getValue('EVERPSJS');
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
        $recaptchaMessages = [];
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
            $recaptchaMessages[$lang['id_lang']] = (string) Tools::getValue(
                'EVERBLOCK_RECAPTCHA_ERROR_MESSAGE_' . $lang['id_lang']
            );
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
            'EVERBLOCK_RECAPTCHA_ERROR_MESSAGE',
            $recaptchaMessages,
            true
        );
        Configuration::updateValue(
            'EVERBLOCK_RECAPTCHA_ENABLED',
            (int) (bool) Tools::getValue('EVERBLOCK_RECAPTCHA_ENABLED')
        );
        Configuration::updateValue(
            'EVERBLOCK_RECAPTCHA_SITE_KEY',
            trim((string) Tools::getValue('EVERBLOCK_RECAPTCHA_SITE_KEY'))
        );
        Configuration::updateValue(
            'EVERBLOCK_RECAPTCHA_SECRET_KEY',
            trim((string) Tools::getValue('EVERBLOCK_RECAPTCHA_SECRET_KEY'))
        );
        Configuration::updateValue(
            'EVERBLOCK_RECAPTCHA_PROTECT_CONTACT',
            (int) (bool) Tools::getValue('EVERBLOCK_RECAPTCHA_PROTECT_CONTACT')
        );
        $recaptchaScoreContact = trim((string) Tools::getValue('EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT'));
        if ($recaptchaScoreContact === '') {
            $recaptchaScoreContact = '0.5';
        }
        Configuration::updateValue(
            'EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT',
            str_replace(',', '.', $recaptchaScoreContact)
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
            'EVERINSTA_SHOW_CAPTION',
            Tools::getValue('EVERINSTA_SHOW_CAPTION')
        );
        Configuration::updateValue(
            'EVERWP_API_URL',
            Tools::getValue('EVERWP_API_URL')
        );
        Configuration::updateValue(
            'EVERWP_API_USER',
            Tools::getValue('EVERWP_API_USER')
        );
        Configuration::updateValue(
            'EVERWP_API_PWD',
            Tools::getValue('EVERWP_API_PWD')
        );
        Configuration::updateValue(
            'EVERWP_POST_NBR',
            Tools::getValue('EVERWP_POST_NBR')
        );
        Configuration::updateValue(
            'EVERBLOCK_GMAP_KEY',
            Tools::getValue('EVERBLOCK_GMAP_KEY')
        );
        Configuration::updateValue(
            'EVERBLOCK_STORELOCATOR_TOGGLE',
            Tools::getValue('EVERBLOCK_STORELOCATOR_TOGGLE')
        );
        if (isset($_FILES['EVERBLOCK_MARKER_ICON'])
            && isset($_FILES['EVERBLOCK_MARKER_ICON']['tmp_name'])
            && !empty($_FILES['EVERBLOCK_MARKER_ICON']['tmp_name'])
        ) {
            $filename = $_FILES['EVERBLOCK_MARKER_ICON']['name'];
            $extension = Tools::strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($extension !== 'svg') {
                $this->postErrors[] = $this->l('Marker icon must be an SVG file.');
            } elseif (!($tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['EVERBLOCK_MARKER_ICON']['tmp_name'], $tmpName)
            ) {
                $this->postErrors[] = $this->l('Error while uploading marker icon.');
            } else {
                $dest = _PS_MODULE_DIR_ . $this->name . '/views/img/store-locator-marker.svg';
                copy($tmpName, $dest);
                @unlink($tmpName);
                Configuration::updateValue('EVERBLOCK_MARKER_ICON', 'store-locator-marker.svg');
            }
        }
        $stores = Store::getStores((int) $this->context->language->id);
        $holidays = EverblockTools::getFrenchHolidays((int) date('Y'));
        foreach ($stores as $store) {
            foreach ($holidays as $date) {
                $hoursKey = 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date;
                Configuration::updateValue($hoursKey, Tools::getValue($hoursKey));
            }
        }
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
            'EVERBLOCK_SOLDOUT_FLAG',
            Tools::getValue('EVERBLOCK_SOLDOUT_FLAG')
        );
        Configuration::updateValue(
            'EVER_SOLDOUT_COLOR',
            Tools::getValue('EVER_SOLDOUT_COLOR')
        );
        Configuration::updateValue(
            'EVER_SOLDOUT_TEXTCOLOR',
            Tools::getValue('EVER_SOLDOUT_TEXTCOLOR')
        );
        Configuration::updateValue(
            'EVERPS_TAB_NB',
            Tools::getValue('EVERPS_TAB_NB')
        );
        Configuration::updateValue(
            'EVERPS_FLAG_NB',
            Tools::getValue('EVERPS_FLAG_NB')
        );
        $cacheId = $this->name . 'NeedProductFlagsHook_' . $idShop;
        EverblockCache::cacheDrop($cacheId);
        $this->updateProductFlagsHook();
        if ((bool) Tools::getValue('EVERPSCSS_CACHE') === true) {
            $this->emptyAllCache();
        }
        $stores = EverblockTools::getStoreLocatorData();
        $filename = 'store-locator-' . $idShop . '.js';
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
        if (!empty($stores) && Tools::getValue('EVERBLOCK_GMAP_KEY')) {
            $markers = [];
            $context = Context::getContext();
            $markerIcon = Configuration::get('EVERBLOCK_MARKER_ICON');
            foreach ($stores as $store) {
                $storeId = isset($store['id']) ? (int) $store['id'] : (int) $store['id_store'];
                if (!empty($store['is_open'])) {
                    $status = sprintf($this->l('Open today until %s'), $store['open_until']);
                } elseif (!empty($store['opens_at'])) {
                    $status = sprintf($this->l('Open today at %s'), $store['opens_at']);
                } else {
                    $status = $this->l('Closed');
                }
                $marker = [
                    'id' => $storeId,
                    'lat' => $store['latitude'],
                    'lng' => $store['longitude'],
                    'title' => $store['name'],
                    'address1' => $store['address1'],
                    'address2' => $store['address2'],
                    'postcode' => $store['postcode'],
                    'city' => $store['city'],
                    'phone' => $store['phone'],
                    'img' => $context->link->getBaseLink(null, null) . 'img/st/' . $storeId . '.jpg',
                    'status' => $status,
                    'cms_link' => $store['cms_link'],
                    'directions_label' => $this->l('Get directions'),
                    'hours_label' => $this->l('See hours'),
                ];
                if ($markerIcon) {
                    $marker['icon'] = $context->link->getBaseLink(null, null) . 'modules/' . $this->name . '/views/img/' . $markerIcon;
                }
                $markers[] = $marker;
            }
            $gmapScript = EverblockTools::generateGoogleMapScript($markers);
            if ($gmapScript) {
                file_put_contents($filePath, $gmapScript);
            }
        } elseif (file_exists($filePath)) {
            unlink($filePath);
        }
        $this->generateFeatureFlagsCssFile();
        $this->generateSoldOutFlagCssFile();
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

    protected function generateSoldOutFlagCssFile()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/css/outofstock-flag-' . $idShop . '.css';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $bgColor = Configuration::get('EVER_SOLDOUT_COLOR');
        $textColor = Configuration::get('EVER_SOLDOUT_TEXTCOLOR');

        if (empty($bgColor) && empty($textColor)) {
            return;
        }

        $css = "/* Auto-generated sold out flag CSS */\n";
        $css .= ".product-flags .out_of_stock {\n";
        if ($bgColor) {
            $css .= "  background-color: {$bgColor}!important;\n";
        }
        if ($textColor) {
            $css .= "  color: {$textColor}!important;\n";
        }
        $css .= "}\n";

        file_put_contents($filePath, $css);
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

    protected function uploadSvgFile()
    {
        if (isset($_FILES['CUSTOM_SVG'])
            && isset($_FILES['CUSTOM_SVG']['tmp_name'])
            && !empty($_FILES['CUSTOM_SVG']['tmp_name'])
        ) {
            $filename = $_FILES['CUSTOM_SVG']['name'];
            $exploded_filename = explode('.', $filename);
            $ext = end($exploded_filename);
            if (Tools::strtolower($ext) != 'svg') {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['CUSTOM_SVG']['tmp_name'], $tmp_name)
            ) {
                return false;
            }
            $content = file_get_contents($tmp_name);
            if ($content === false) {
                return false;
            }
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            if (!$dom->loadXML($content, LIBXML_NONET)) {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            foreach ($xpath->query('//*[translate(local-name(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="script" or translate(local-name(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="foreignobject"]') as $node) {
                $node->parentNode->removeChild($node);
            }
            foreach ($xpath->query('//@*') as $attr) {
                if (stripos($attr->nodeName, 'on') === 0 || preg_match('/^\s*javascript:/i', $attr->nodeValue)) {
                    $attr->ownerElement->removeAttributeNode($attr);
                }
            }

            $clean = $dom->saveXML();
            $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename);
            file_put_contents(_PS_MODULE_DIR_ . $this->name . '/views/img/svg/' . $safeName, $clean);
            unlink($tmp_name);
            $this->html .= $this->displayConfirmation($this->l('File has been uploaded'));
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

    public function hookBeforeRenderingEverblockEverblock($params)
    {
        $states = $params['block']['states'] ?? [];

        foreach ($states as &$state) {
            if (empty($state['id_everblock'])) {
                $state['content'] = '';
                continue;
            }

            $idEverblock = (int) trim(explode('-', $state['id_everblock'], 2)[0]);
            $everblock   = new EverBlockClass(
                $idEverblock,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );

            $state['content'] = Validate::isLoadedObject($everblock) ? $everblock->content : '';
        }
        unset($state);

        // Les données retournées sont disponibles dans $block.extra
        return ['states' => $states];
    }

    public function hookBeforeRenderingEverblockCategoryTabs($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['id_category'])) {
                    continue;
                }
                $limit = isset($state['nb_products']) ? (int) $state['nb_products'] : 0;
                if ($limit <= 0) {
                    $limit = (int) Configuration::get('PS_PRODUCTS_PER_PAGE');
                }
                $rawProducts = EverblockTools::getProductsByCategoryId(
                    (int) $state['id_category'],
                    $limit
                );
                $presented = EverblockTools::everPresentProducts(
                    array_column($rawProducts, 'id_product'),
                    $this->context
                );
                $products[$key] = $presented;
            }
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockCategoryPrice($params)
    {
        $states = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                $info = [
                    'category_link' => '#',
                    'image_url' => '',
                    'image_width' => 0,
                    'image_height' => 0,
                    'title' => '',
                    'min_price' => false,
                ];
                if (!empty($state['category']['id'])) {
                    $idCategory = (int) $state['category']['id'];
                    $info['category_link'] = $this->context->link->getCategoryLink($idCategory);
                    if (!empty($state['image']['url'])) {
                        $info['image_url'] = $state['image']['url'];
                    } else {
                        $info['image_url'] = $this->context->link->getCatImageLink(
                            ImageType::getFormattedName('category'),
                            $idCategory
                        );
                    }
                    $category = new Category(
                        $idCategory,
                        (int) $this->context->language->id
                    );
                    $info['title'] = !empty($state['name']) ? $state['name'] : $category->name;
                    $products = $category->getProducts(
                        (int) $this->context->language->id,
                        1,
                        1,
                        'price',
                        'asc',
                        false,
                        true,
                        false,
                        1,
                        $this->context
                    );
                    if (!empty($products)) {
                        $info['min_price'] = $products[0]['price'];
                    }
                } else {
                    $info['title'] = !empty($state['name']) ? $state['name'] : '';
                    if (!empty($state['image']['url'])) {
                        $info['image_url'] = $state['image']['url'];
                    }
                }

                if (!empty($info['image_url'])) {
                    $size = false;
                    $path = parse_url($info['image_url'], PHP_URL_PATH);
                    if ($path) {
                        $absolute = _PS_ROOT_DIR_ . (strpos($path, '/') === 0 ? $path : '/' . $path);
                        if (Tools::file_exists_no_cache($absolute)) {
                            $size = @getimagesize($absolute);
                        }
                    }
                    if (!$size) {
                        $size = @getimagesize($info['image_url']);
                    }
                    if ($size) {
                        $info['image_width'] = (int) $size[0];
                        $info['image_height'] = (int) $size[1];
                    }
                }

                $states[$key] = $info;
            }
        }

        return ['state_data' => $states];
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

    public function hookBeforeRenderingEverblockProductSelector($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['product'])) {
                    continue;
                }
                $idProduct = $state['product']['id'];
                if ($idProduct <= 0) {
                    continue;
                }
                $presented = EverblockTools::everPresentProducts([
                    $idProduct,
                ], $this->context);
                if (!empty($presented)) {
                    $products[$key] = reset($presented);
                }
            }
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockVideoProducts($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['product_ids'])) {
                    continue;
                }
                $ids = array_filter(array_map('intval', explode(',', $state['product_ids'])));
                if (empty($ids)) {
                    continue;
                }
                $presented = EverblockTools::everPresentProducts($ids, $this->context);
                if (!empty($presented)) {
                    $products[$key] = $presented;
                }
            }
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockSpecialEvent($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['product_ids'])) {
                    continue;
                }
                $ids = array_filter(array_map('intval', explode(',', $state['product_ids'])));
                if (empty($ids)) {
                    continue;
                }
                $presented = EverblockTools::everPresentProducts($ids, $this->context);
                if (!empty($presented)) {
                    $products[$key] = $presented;
                }
            }
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockFlashDeals($params)
    {
        $deals = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            $idCustomer = (int) $this->context->customer->id;
            $groupIds = Customer::getGroupsStatic($idCustomer);
            if (empty($groupIds)) {
                $groupIds = [(int) Configuration::get('PS_UNIDENTIFIED_GROUP')];
            }
            $groupIds = array_map('intval', $groupIds);
            $groupList = implode(',', $groupIds);
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['product']['id'])) {
                    continue;
                }
                $idProduct = (int) $state['product']['id'];
                if ($idProduct <= 0) {
                    continue;
                }
                $endDate = Db::getInstance()->getValue(
                    'SELECT MIN(cr.date_to) FROM ' . _DB_PREFIX_ . 'cart_rule cr '
                    . 'INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_product_rule_group crprg ON cr.id_cart_rule = crprg.id_cart_rule '
                    . 'INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_product_rule crpr ON crprg.id_product_rule_group = crpr.id_product_rule_group '
                    . 'INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_product_rule_value crprv ON crpr.id_product_rule = crprv.id_product_rule '
                    . 'LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule_group crg ON cr.id_cart_rule = crg.id_cart_rule '
                    . 'WHERE cr.active = 1 AND cr.date_to > NOW() AND crprv.id_item = ' . $idProduct
                    . ' AND (cr.id_customer = 0 OR cr.id_customer = ' . $idCustomer . ')'
                    . ' AND (crg.id_group IS NULL OR crg.id_group IN (' . $groupList . '))'
                );
                if (!$endDate) {
                    $endDate = Db::getInstance()->getValue(
                        'SELECT MIN(`to`) FROM ' . _DB_PREFIX_ . 'specific_price '
                        . 'WHERE id_product = ' . $idProduct . ' AND `to` > NOW()'
                        . ' AND (id_customer = 0 OR id_customer = ' . $idCustomer . ')'
                        . ' AND (id_group = 0 OR id_group IN (' . $groupList . '))'
                    );
                }
                if (!$endDate) {
                    continue;
                }
                $presented = EverblockTools::everPresentProducts([
                    $idProduct,
                ], $this->context);
                if (!empty($presented)) {
                    $product = reset($presented);
                    $product['end_date'] = $endDate;
                    $deals[$key] = $product;
                }
            }
        }

        if (empty($deals)) {
            return false;
        }

        return ['deals' => $deals];
    }

    public function hookBeforeRenderingEverblockGuidedSelector($params)
    {
        $states = $params['block']['states'] ?? [];

        foreach ($states as &$state) {
            $question = isset($state['question']) ? trim($state['question']) : '';
            $state['question'] = $question;
            $state['key'] = Tools::link_rewrite($question);

            $answers = [];
            $lines = preg_split("/(\r\n|\r|\n)/", $state['answers'] ?? '');
            foreach ($lines as $line) {
                $parts = explode('|', $line);
                $text = trim($parts[0] ?? '');
                if ($text === '') {
                    continue;
                }
                $link = trim($parts[1] ?? '');
                $answers[] = [
                    'text' => $text,
                    'link' => $link,
                    'value' => Tools::link_rewrite($text),
                ];
            }
            $state['answers'] = $answers;
        }
        unset($state);

        return ['states' => $states];
    }

    public function hookBeforeRenderingEverblockLookbook($params)
    {
        return [];
    }

    public function hookBeforeRenderingEverblockCategoryProducts($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['category']['id'])) {
                    continue;
                }
                $idCategory = (int) $state['category']['id'];
                if ($idCategory <= 0) {
                    continue;
                }
                $limit = !empty($state['product_limit']) ? (int) $state['product_limit'] : 4;
                $includeSub = !empty($state['include_subcategories']);
                $categoryProducts = EverblockTools::getProductsByCategoryId(
                    $idCategory,
                    $limit,
                    'id_product',
                    'ASC',
                    $includeSub
                );
                if (!empty($categoryProducts)) {
                    $ids = array_column($categoryProducts, 'id_product');
                    $presented = EverblockTools::everPresentProducts($ids, $this->context);
                    if (!empty($presented)) {
                        $products[$key] = $presented;
                    }
                }
            }
        }

        return ['products' => $products];
    }

    public function hookDisplayReassurance($params)
    {
        if (!isset($this->context->controller) || !is_object($this->context->controller)) {
            return;
        }

        if (!class_exists('ProductController') || !($this->context->controller instanceof ProductController)) {
            return;
        }

        $idProduct = (int) Tools::getValue('id_product');
        if (!$idProduct
            && property_exists($this->context->controller, 'product')
            && isset($this->context->controller->product->id)
        ) {
            $idProduct = (int) $this->context->controller->product->id;
        }

        if ($idProduct <= 0) {
            return;
        }

        $modal = EverblockModal::getByProductId(
            $idProduct,
            (int) $this->context->shop->id
        );

        // Vérifie si objet chargé
        if (!Validate::isLoadedObject($modal)) {
            return;
        }
        $idLang = (int) $this->context->language->id;

        // Cas 1 : contenu texte dispo
        $hasContent = !empty($modal->content[$idLang]);

        // Cas 2 : fichier image dispo
        $hasFile = !empty($modal->file);

        if (!$hasContent && !$hasFile) {
            return;
        }

        $this->smarty->assign([
            'everblock_modal_id' => (int) $modal->id_everblock_modal,
            'everblock_modal_file' => $modal->file,
            'everblock_modal_content' => $modal->content[$idLang] ?? '',
        ]);

        return $this->fetch('module:everblock/views/templates/hook/modal.tpl');
    }

    public function encrypt($data)
    {
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            return Tools::hash($data);
        }

        return Tools::encrypt($data);
    }
}
