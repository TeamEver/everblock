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
        $this->version = '5.3.2';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Ever Block');
        $this->description = $this->l('Add HTML block everywhere !');
        $this->confirmUninstall = $this->l('Do yo really want to uninstall this module ?');
    }

    /**
     * Return Hooks List.
     * @param bool $position
     * @return array Hooks List
     */
    protected function getHooks($position = false, $only_display_hooks = false)
    {
        $return = [];
        $hooks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT * FROM `' . _DB_PREFIX_ . 'hook` h
            ' . ($position ? 'WHERE h.`position` = 1' : '') . '
            ORDER BY `name`'
        );

        if ($only_display_hooks) {
            $returnedHooks = array_filter($hooks, function ($hook) {
                return Hook::isDisplayHookName($hook['name']);
            });
        } else {
            $returnedHooks = $hooks;
        }
        foreach ($returnedHooks as $hook) {
            $hook['evername'] = $hook['name'] . ' - ' . $hook['title'];
            $return[] = $hook;
        }
        return $return;
    }

    public function __call($method, $args)
    {
        $controllerTypes = [
            'admin',
            'moduleadmin',
        ];
        if (in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
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
        EverblockTools::checkAndFixDatabase();
        $this->checkHooks();
        $this->html = '';
        if (((bool) Tools::isSubmit('submit' . $this->name . 'Module')) == true) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }

        if ((bool) Tools::isSubmit('submitEmptyCache') === true) {
            $this->emptyAllCache();
        }

        if ((bool) Tools::isSubmit('submitAddHooksToTheme') === true) {
            EverblockTools::addHooksToTheme();
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
                $this->postErrors[] = $migration['postErrors'];
            }
            if (is_array($migration)
                && isset($migration['querySuccess'])
                && count($migration['querySuccess']) > 0
            ) {
                $this->postSuccess[] = $migration['querySuccess'];
            }
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
            $this->name . '_dir' => $this->_path,
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
                        'label' => $this->l('Redirect all users except registered IP'),
                        'desc' => $this->l('Will redirect all users based on maintenance IP'),
                        'hint' => $this->l('Enable if you have troubles with maintenance mode'),
                        'name' => 'EVERPS_MAINTENANCE',
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
                        'label' => $this->l('Redirect users to this URL if SEO maintenance is ON'),
                        'desc' => $this->l('Will redirect to this URL only if SEO maintenance is ON'),
                        'hint' => $this->l('Default will be google.com'),
                        'name' => 'EVERPS_MAINTENANCE_URL',
                        'lang' => false,
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
                ],
                'buttons' => [
                    'emptyCache' => [
                        'name' => 'submitEmptyCache',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Empty cache'),
                    ],
                    'addHooksToTheme' => [
                        'name' => 'submitAddHooksToTheme',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Add Pretty Block widgets into theme'),
                    ],
                    'migrateUrls' => [
                        'name' => 'submitMigrateUrls',
                        'type' => 'submit',
                        'class' => 'btn btn-light',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Migrate URLS'),
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
            'EVERPS_MAINTENANCE' => Configuration::get('EVERPS_MAINTENANCE'),
            'EVERPS_MAINTENANCE_URL' => Configuration::get('EVERPS_MAINTENANCE_URL'),
            'EVERBLOCK_USE_GMAP' => Configuration::get('EVERBLOCK_USE_GMAP'),
            'EVERBLOCK_GMAP_KEY' => Configuration::get('EVERBLOCK_GMAP_KEY'),
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
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
            'EVERPS_MAINTENANCE',
            Tools::getValue('EVERPS_MAINTENANCE')
        );
        Configuration::updateValue(
            'EVERPS_MAINTENANCE_URL',
            Tools::getValue('EVERPS_MAINTENANCE_URL')
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
                        'title' => $store['name'], // Nom du magasin
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
        Tools::clearAllCache();
        $this->postSuccess[] = $this->l('Cache has been cleared');
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path . 'views/css/ever.css');
        if (Tools::getValue('id_' . $this->name)
            || Tools::getIsset('add' . $this->name)
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

    public function hookActionDispatcherBefore()
    {
        if ((bool) Configuration::get('EVERPS_MAINTENANCE') === true) {
            if ((bool) EverblockTools::isMaintenanceIpAddress() === false
                && (bool) EverblockTools::isEmployee() === false
            ) {
                if (!Configuration::get('EVERPS_MAINTENANCE_URL')) {
                    $maintenanceUrl = 'https://www.google.com';
                } else {
                    $maintenanceUrl = Configuration::get('EVERPS_MAINTENANCE_URL');
                }
                if (!Tools::getValue('qcd') || Tools::getValue('qcd') != 'gone') {
                    Tools::redirect(
                        $maintenanceUrl
                    );
                }
            }
        }
    }

    public function hookActionOutputHTMLBefore($params)
    {
        $cacheId = 'EverBlockClass::hookActionOutputHTMLBefore_'
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
                    '[llorem]' => EverblockTools::generateLoremIpsum(),
                    '[theme_uri]' => $theme_uri,
                    '[storelocator]' => EverblockTools::generateGoogleMap(),
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
                $txt = EverblockTools::getQcdAcfCode($txt);
                $txt = EverblockTools::renderSmartyVars($txt);
                $params['html'] = $txt;
                Cache::store($cacheId, $txt);
                return $params['html'];
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'Ever Block hookActionOutputHTMLBefore : ' . $e->getMessage()
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
        if (Tools::getValue('id_manufacturer')) {
            $idObj = Tools::getValue('id_manufacturer');
        }
        if (Tools::getValue('id_supplier')) {
            $idObj = Tools::getValue('id_supplier');
        }
        if (Tools::getValue('id_cms')) {
            $idObj = Tools::getValue('id_cms');
        }
        // Let's cache
        $cacheId = $this->getCacheId($this->name . '-id_hook-' . $id_hook . '-controller-' . Tools::getValue('controller') . '-hookName' . $hookName . '-idObj-' . $idObj . '-device-' . $this->context->getDevice());
        if (!$this->isCached($this->name . '.tpl', $cacheId)) {
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
                    'everblock' => &$currentBlock,
                    'args' => $args,
                ]
            );
            $this->smarty->assign([
                'prettyblocks_installed' => (bool) Module::isInstalled('prettyblocks') && (bool) Module::isEnabled('prettyblocks'),
                'everhook' => trim($method),
                'everblock' => $currentBlock,
                'args' => $args,
            ]);
            Hook::exec(
                'actionRenderBlockAfter',
                [
                    'everhook' => trim($method),
                    'everblock' => $currentBlock,
                    'args' => $args,
                ]
            );
        }
        return $this->display(__FILE__, $this->name . '.tpl', $cacheId);
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
        if (file_exists($compressedCss) && filesize($compressedCss) > 0) {
            $this->context->controller->registerStylesheet(
                'module-everblock-custom-compressed-css',
                'modules/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200, 'version' => $this->version]
            );
        }
        if (file_exists($compressedJs) && filesize($compressedJs) > 0) {
            $this->context->controller->registerJavascript(
                'module-everblock-compressed-js',
                'modules/' . $this->name . '/views/js/custom-compressed' . $idShop . '.js',
                ['position' => 'bottom', 'priority' => 200, 'version' => $this->version]
            );
        }
        $externalJs = Configuration::get('EVERPSJS_LINKS');
        $jsLinksArray = [];
        if ($externalJs) {
            $jsLinksArray = explode("\n", $externalJs);
            foreach ($jsLinksArray as $key => $value) {
                $this->context->controller->registerJavascript(
                    'module-everblock-custom-' . (int) $key . '-js',
                    $value,
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 200, 'version' => $this->version]
                );
            }
        }
        $externalCss = Configuration::get('EVERPSCSS_LINKS');
        $cssLinksArray = [];
        if ($externalCss) {
            $cssLinksArray = explode("\n", $externalCss);
            foreach ($cssLinksArray as $key => $value) {
                $this->context->controller->registerStylesheet(
                    'module-everblock-custom-' . (int) $key . '-js',
                    $value,
                    ['server' => 'remote', 'media' => 'all', 'priority' => 200, 'version' => $this->version]
                );
            }
        }
        $apiKey = Configuration::get('EVERBLOCK_GMAP_KEY');
        if ($apiKey && Tools::getValue('controller') == 'cms') {
            $filename = 'store-locator-' . $idShop . '.js';
            $filePath = _PS_MODULE_DIR_ . 'everblock/views/js/' . $filename;
            if (file_exists($filePath) && filesize($filePath) > 0) {
                $this->context->controller->registerJavascript(
                    'module-everblock-custom-gmap-js',
                    'https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&libraries=places,geometry',
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 300, 'version' => $this->version, 'attributes' => 'defer']
                );
                $this->context->controller->registerJavascript(
                    'module-everblock-shop-gmap-js',
                    'modules/' . $this->name . '/views/js/' . $filename,
                    ['server' => 'local', 'position' => 'bottom', 'priority' => 400, 'version' => $this->version, 'attributes' => 'defer']
                );
            }
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
        
        // Recherche des shortcodes [product X] ou [product X,Y,Z]
        preg_match_all('/\[product\s+(\d+(?:,\s*\d+)*)\]/i', $message, $matches);

        foreach ($matches[1] as $match) {
            $productIdsArray = array_map('intval', explode(',', $match));

            $everPresentProducts = $this->everPresentProducts($productIdsArray);
            
            if (!empty($everPresentProducts)) {
                $this->context->smarty->assign('everPresentProducts', $everPresentProducts);
                $shortcode = '[product ' . $match . ']';
                $productShortcodes[$shortcode] = $this->context->smarty->fetch($this->getTemplatePath('ever_presented_products.tpl'));
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
            $presentationSettings->showPrices = true;
            foreach ($result as $productId) {
                $psProduct = new Product(
                    (int) $productId
                );
                if (!Validate::isLoadedObject($psProduct)) {
                    continue;
                }
                if ((bool) $psProduct->active === false) {
                    continue;
                }
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
            $smartyTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_smarty.tpl';
            $modalTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_modal.tpl';
            $alertTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_alert.tpl';
            $buttonTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_button.tpl';
            $gmapTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_gmap.tpl';
            $shortcodeTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_shortcode.tpl';
            $iframeTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_iframe.tpl';
            $loginTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_login.tpl';
            $contactTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_contact.tpl';
            $hookTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_hook.tpl';
            $productTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_product.tpl';
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
            $imgTemplate = 'module:' . $this->name . '/views/templates/hook/prettyblocks/prettyblock_img.tpl';
            $defaultLogo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/logo.png';
            $blocks = [];
            $allShortcodes = EverblockShortcode::getAllShortcodes(
                (int) $this->context->language->id,
                (int) $this->context->shop->id
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
                'name' => $this->l('Divider'),
                'description' => $this->l('Show divider'),
                'code' => 'everblock_divider',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $dividerTemplate,
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
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Accordeons'),
                'description' => $this->l('Add horizontal accordeon'),
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
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            // $blocks[] =  [
            //     'name' => $this->l('Native smarty code'),
            //     'description' => $this->l('Add native smarty code'),
            //     'code' => 'smarty',
            //     'tab' => 'general',
            //     'icon_path' => $defaultLogo,
            //     'need_reload' => true,
            //     'templates' => [
            //         'default' => $smartyTemplate,
            //     ],
            //     'repeater' => [
            //         'name' => 'Smarty',
            //         'nameFrom' => 'name',
            //         'groups' => [
            //             'name' => [
            //                 'type' => 'text',
            //                 'label' => 'Smarty block title',
            //                 'default' => Configuration::get('PS_SHOP_NAME'),
            //             ],
            //             'content' => [
            //                 'type' => 'text',
            //                 'label' => 'Smarty block variable',
            //                 'default' => '',
            //             ],
            //             'css_class' => [
            //                 'type' => 'text',
            //                 'label' => $this->l('Custom CSS class'),
            //                 'default' => '',
            //             ],
            //         ],
            //     ],
            // ];
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
                'repeater' => [
                    'name' => 'Video',
                    'nameFrom' => 'name',
                    'groups' => [
                        'iframe_link' => [
                            'type' => 'text',
                            'label' => $this->l('Iframe embed code (like https://www.youtube.com/embed/jfKfPfyJRdk)'),
                            'default' => 'https://www.youtube.com/embed/jfKfPfyJRdk',
                        ],
                        'iframe_source' => [
                            'type' => 'radio_group', // type of field
                            'label' => $this->l('Iframe source'), // label to display
                            'default' => 'youtube', // default value (String)
                            'choices' => [
                                'youtube' =>'youtube',
                                'vimeo' => 'vimeo',
                                'dailymotion' => 'dailymotion',
                                'vidyard' => 'vidyard',
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
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Layout'),
                'description' => $this->l('Add layout'),
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
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => '',
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
                'repeater' => [
                    'name' => $this->l('Modal title'),
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
                            'default' => '',
                            'label' => $this->l('Modal title color')
                        ],
                        'open_modal_button_bg_color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '',
                            'label' => $this->l('Open modal button background color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->displayName . ' Shortcodes',
                'description' => $this->l('Ever block shortcodes'),
                'code' => 'everblock_shortcode',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $shortcodeTemplate,
                ],
                'repeater' => [
                    'name' => $this->l('Shortcode title'),
                    'nameFrom' => 'name',
                    'groups' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'Shortcode title',
                            'default' => '',
                        ],
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
                'name' => $this->l('Simple image'),
                'description' => $this->l('Add simple image'),
                'code' => 'everblock_img',
                'tab' => 'general',
                'icon_path' => $defaultLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $imgTemplate,
                ],
                'repeater' => [
                    'name' => $this->l('Image title'),
                    'nameFrom' => 'name',
                    'groups' => [
                        'name' => [
                            'type' => 'text',
                            'label' => 'Image title',
                            'default' => '',
                        ],
                        'alt' => [
                            'type' => 'text',
                            'label' =>  $this->l('alt attribute'),
                            'default' => $this->l( 'My alt attribute')
                        ],
                        'url' => [
                            'type' => 'text',
                            'label' =>  $this->l('URL'),
                            'default' =>  $this->l('#')
                        ],
                        'banner' => [
                            'type' => 'fileupload',
                            'label' => 'Images',
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
                            'default' => '',
                        ],
                    ],
                ],
            ];
            $blocks[] =  [
                'name' => $this->l('Text and image'),
                'description' => $this->l('Add image and text layout'),
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
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => '',
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
                            'path' => '$/modules/everblock/views/img/prettyblocks/',
                            'default' => [
                                'url' => '',
                            ],
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
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
                            'type' => 'radio_group', // type of field
                            'label' => $this->l('Alert type'), // label to display
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
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
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
                                'url' => '',
                            ],
                        ],
                        'content' => [
                            'type' => 'editor',
                            'label' => 'Tab content',
                            'default' => '[llorem]',
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
                            'label' => $this->l('Button type'), // label to display
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
                            'label' => $this->l('Button text'),
                            'default' => '',
                        ],
                        'button_link' => [
                            'type' => 'text',
                            'label' => $this->l('Button link'),
                            'default' => '',
                        ],
                        'color' => [
                            'tab' => 'design',
                            'type' => 'color',
                            'default' => '#fff',
                            'label' => $this->l('Button text color')
                        ],
                        'css_class' => [
                            'type' => 'text',
                            'label' => $this->l('Custom CSS class'),
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
            ];
            Cache::store($cacheId, $blocks);
            return $blocks;
        }

        return Cache::retrieve($cacheId);
    }

    public function hookBeforeRenderingSmarty($params)
    {
        $templateVars = [
            'currency' => $this->context->controller->getTemplateVarCurrency(),
            'customer' => $this->context->controller->getTemplateVarCustomer(),
            'page' => $this->context->controller->getTemplateVarPage(),
            'shop' => $this->context->controller->getTemplateVarShop(),
            'urls' => $this->context->controller->getTemplateVarUrls(),
            'configuration' => $this->context->controller->getTemplateVarConfiguration(),
            'breadcrumb' => $this->context->controller->getBreadcrumb(),
        ];

        if (isset($params['block']['repeater_db']) && $params['block']['repeater_db']) {
            foreach ($params['block']['repeater_db'] as $key => $value) {
                $contentValue = $value['content']['value'];
                // Utilisez eval pour interpréter la valeur si elle est une expression Smarty
                if (strpos($contentValue, '$') === 0) {
                    $smarty = Context::getContext()->smarty;
                    $contentValue = $smarty->fetch('string:' . $contentValue);
                }
                $params['block']['repeater_db'][$key]['content']['value'] = $contentValue;
            }
        }
        return $params;
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
        }
    }
}
