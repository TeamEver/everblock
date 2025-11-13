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
use Everblock\Tools\Service\EverblockTools;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;

class AdminEverBlockController extends ModuleAdminController
{
    private $html;

    protected function displayConfirmation($message)
    {
        if (is_array($message)) {
            $message = implode('<br>', array_map(function ($item) {
                return Tools::safeOutput((string) $item);
            }, $message));
        } else {
            $message = Tools::safeOutput((string) $message);
        }

        if ('' === trim($message)) {
            return '';
        }

        return '<div class="bootstrap"><div class="alert alert-success" role="alert">'
            . $message
            . '</div></div>';
    }

    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = true;
        $this->table = 'everblock';
        $this->className = 'EverBlockClass';
        $this->context = Context::getContext();
        $this->identifier = 'id_everblock';
        $this->name = 'AdminEverBlockController';
        $this->position_identifier = 'id_everblock';
        $this->allow_export = true;
        if (!Tools::getIsset('module_name')) {
            $_GET['module_name'] = 'everblock';
            $_REQUEST['module_name'] = 'everblock';
        }
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $m = Module::getInstanceByName('everblock');
        $this->context->smarty->assign([
            $m->name . '_version' => $m->version,
            'module_name' => $m->displayName,
            'module_link' => $module_link,
            'everblock_dir' => _MODULE_DIR_ . '/everblock/',
            'donation_link' => 'https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE',
            'everblock_shortcode_docs' => ShortcodeDocumentationProvider::getDocumentation($m),
        ]);

        $this->_select = 'a.*, h.title AS hname, CONCAT(h.title, LPAD(a.position, 10, "0")) as sort_key';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON (h.`id_hook` = a.`id_hook`)';
        $this->_orderBy = 'sort_key';
        $this->_orderWay = 'ASC';
        $this->_where = 'AND a.id_shop = ' . (int) $this->context->shop->id;

        $this->fields_list = [
            'id_everblock' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!id_everblock',
            ],
            'name' => [
                'title' => $this->l('Name'),
                'filter_key' => 'a!name',
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
            ],
            'hname' => [
                'title' => $this->l('Hook'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'h!title', // Jointure avec la table hook
            ],
            'position' => [
                'title' => $this->l('Position'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!position',
            ],
            'only_home' => [
                'title' => $this->l('Home only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!only_home',
            ],
            'only_category' => [
                'title' => $this->l('Category only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!only_category',
            ],
            'only_manufacturer' => [
                'title' => $this->l('Manufacturer only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!only_manufacturer',
            ],
            'only_supplier' => [
                'title' => $this->l('Supplier only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!only_supplier',
            ],
            'only_cms_category' => [
                'title' => $this->l('CMS category only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!only_cms_category',
            ],
            'date_start' => [
                'title' => $this->l('Date start'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!date_start',
            ],
            'date_end' => [
                'title' => $this->l('Date end'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!date_end',
            ],
            'modal' => [
                'title' => $this->l('Is modal'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!modal',
            ],
            'active' => [
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => true,
                'search' => true,
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!active',
            ],
        ];
        $this->colorOnBackground = true;
        EverblockTools::checkAndFixDatabase();
        parent::__construct();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            'Modules.Everblock.Admineverblockcontroller'
        );
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new element'),
            'icon' => 'process-icon-new',
        ];
        $this->page_header_toolbar_btn['clear'] = [
            'href' => self::$currentIndex . '&clearcache=1&token=' . $this->token,
            'desc' => $this->l('Clear cache'),
            'icon' => 'process-icon-refresh',
        ];
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $this->page_header_toolbar_btn['configuration'] = [
            'href' => $module_link,
            'desc' => $this->l('Configuration'),
            'icon' => 'process-icon-save',
        ];
        $this->page_header_toolbar_btn['tabs'] = [
            'href' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/input/sample/tabs.xlsx',
            'desc' => $this->l('Download Excel tabs sample file'),
            'icon' => 'process-icon-download',
        ];
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('duplicate');
        $this->addRowAction('export');
        $this->toolbar_title = $this->l('HTML blocks Configuration');
        $this->bulk_actions = [
            'duplicateall' => [
                'text' => $this->l('Duplicate selected items'),
                'confirm' => $this->l('Duplicate selected items ?'),
            ],
            'delete' => [
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?'),
            ],
        ];

        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $this->processBulkDelete();
        }
        if (Tools::isSubmit('submitBulkdisableSelection' . $this->table)) {
            $this->processBulkDisable();
        }
        if (Tools::isSubmit('submitBulkenableSelection' . $this->table)) {
            $this->processBulkEnable();
        }
        if (Tools::isSubmit('submitBulkduplicateall' . $this->table)) {
            $this->processBulkDuplicate();
        }
        if (Tools::getValue('clearcache')) {
            Tools::clearAllCache();
            Tools::redirectAdmin(self::$currentIndex . '&cachecleared=1&token=' . $this->token);
        }
        if (Tools::getValue('cachecleared')) {
            $this->confirmations[] = $this->l('Cache has been cleared');
        }
        if (Tools::isSubmit('status' . $this->table)) {
            $db = Db::getInstance();
            if ($idObj = (int) Tools::getValue($this->identifier)) {
                $updated = $db->execute(
                    'UPDATE `' . _DB_PREFIX_ . 'everblock`
                    SET `active` = (1 - `active`)
                    WHERE `' . $this->identifier . '` = ' . (int) $idObj .' LIMIT 1'
                );
            }
            if (isset($updated) && $updated) {
                $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
            } else {
                $this->errors[] = $this->l('An error occurred while updating the status.');
            }
        }

        $lists = parent::renderList();

        $moduleInstance = Module::getInstanceByName($this->table);
        $displayUpgrade = $moduleInstance->checkLatestEverModuleVersion();

        $notifications = '';
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $notifications .= Tools::displayError($error);
            }
        }
        if (is_array($this->confirmations) && count($this->confirmations)) {
            foreach ($this->confirmations as $confirmation) {
                $notifications .= $this->displayConfirmation($confirmation);
            }
        }

        $this->context->smarty->assign([
            'everblock_notifications' => $notifications,
            'everblock_form' => $lists,
            'display_upgrade' => $displayUpgrade,
        ]);

        $content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everblock/views/templates/admin/header.tpl'
        );
        $content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everblock/views/templates/admin/configure.tpl'
        );
        $content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everblock/views/templates/admin/footer.tpl'
        );

        return $content;
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
                    && $nonDocumentationInputs[$nextIndex]['tab'] === $tab) {
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

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new blocks.');
        }
        if (count($this->errors)) {
            return false;
        }
        $obj = new $this->className(
            (int) Tools::getValue($this->identifier)
        );
        $fields_form = [];
        $hooks_list = $this->getHooks(false, true);
        $categories_list = Category::getCategories(
            false,
            true,
            false
        );
        foreach ($categories_list as &$cat) {
            $cat['name'] = $cat['id_category'] . ' - ' . $cat['name'];
        }
        $manufacturersList = Manufacturer::getLiteManufacturersList(
            (int) $this->context->language->id
        );
        $suppliersList = Supplier::getLiteSuppliersList(
            (int) $this->context->language->id
        );
        $cmsCategoriesList = CMSCategory::getSimpleCategories(
            (int) $this->context->language->id
        );
        $groups = Group::getGroups($this->context->language->id);
        // IDs are set depending on context values
        $devices = [
            [
                'id_device' => 0,
                'name' => $this->l('All devices')
            ],
            [
                'id_device' => 4,
                'name' => $this->l('Only mobile devices')
            ],
            [
                'id_device' => 2,
                'name' => $this->l('Only tablet devices')
            ],
            [
                'id_device' => 1,
                'name' => $this->l('Only desktop devices')
            ]
        ];
        $bootstrapSizes = [
            [
                'id_bootstrap' => 0,
                'size' => $this->l('None')
            ],
            [
                'id_bootstrap' => 1,
                'size' => $this->l('100%')
            ],
            [
                'id_bootstrap' => 2,
                'size' => $this->l('1/2')
            ],
            [
                'id_bootstrap' => 4,
                'size' => $this->l('1/3')
            ],
            [
                'id_bootstrap' => 3,
                'size' => $this->l('1/4')
            ],
            [
                'id_bootstrap' => 6,
                'size' => $this->l('1/6')
            ],
        ];
        $everblock_obj = $this->loadObject(true);
        $everblock_obj->categories = json_decode($everblock_obj->categories);

        $docTemplates = [
            'general' => 'general.tpl',
            'targeting' => 'targeting.tpl',
            'display' => 'display.tpl',
            'modal' => 'modal.tpl',
            'schedule' => 'schedule.tpl',
        ];

        $docInputs = [];

        foreach ($docTemplates as $tab => $template) {
            $docPath = _PS_MODULE_DIR_ . 'everblock/views/templates/admin/block/docs/' . $template;

            if (!Tools::file_exists_cache($docPath)) {
                continue;
            }

            $docInputs[] = [
                'type' => 'html',
                'name' => 'documentation_' . $tab,
                'tab' => $tab,
                'html_content' => $this->context->smarty->fetch($docPath),
            ];
        }

        // Building the Add/Edit form
        $fields_form[] = [
            'form' => [
                'tinymce' => true,
                'tabs' => [
                    'general' => $this->l('General'),
                    'targeting' => $this->l('Targeting'),
                    'display' => $this->l('Display'),
                    'modal' => $this->l('Modal'),
                    'schedule' => $this->l('Schedule'),
                ],
                'submit' => [
                    'name' => 'save',
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-success pull-right',
                ],
                'buttons' => [
                    'import' => [
                        'name' => 'stay',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & stay'),
                    ],
                ],
                'input' => array_merge([
                    [
                        'type' => 'hidden',
                        'name' => $this->identifier,
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'desc' => $this->l('As a reminder, wont be shown'),
                        'hint' => $this->l('This reminder will be shown on admin only'),
                        'required' => true,
                        'name' => 'name',
                        'lang' => false,
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('HTML block content'),
                        'desc' => $this->l('Please type your block content'),
                        'hint' => $this->l('HTML content depends on your shop settings'),
                        'required' => true,
                        'name' => 'content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'class' => 'evertranslatable',
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Hook'),
                        'desc' => $this->l('Please select hook'),
                        'hint' => $this->l('Block will be shown on this hook'),
                        'name' => 'id_hook',
                        'class' => 'chosen',
                        'required' => true,
                        'options' => [
                            'query' => $hooks_list,
                            'id' => 'id_hook',
                            'name' => 'evername',
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom code'),
                        'desc' => $this->l('Please set here custom code, such as css or js'),
                        'hint' => $this->l('Custom code will be rendered before block content'),
                        'required' => false,
                        'name' => 'custom_code',
                        'lang' => true,
                        'autoload_rte' => false,
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'desc' => $this->l('Enable this block ?'),
                        'hint' => $this->l('Only active blocks will be shown'),
                        'name' => 'active',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Devices management'),
                        'desc' => $this->l('Please specify the device on which the block should be displayed'),
                        'hint' => $this->l('Select "all devices" for a global view'),
                        'name' => 'device',
                        'required' => false,
                        'options' => [
                            'query' => $devices,
                            'id' => 'id_device',
                            'name' => 'name',
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'group',
                        'label' => $this->l('Group access'),
                        'name' => 'groupBox',
                        'values' => $groups,
                        'desc' => $this->l('Block will be shown to these groups'),
                        'hint' => $this->l('Please select at least one customer group'),
                        'required' => false,
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Only on homepage ?'),
                        'desc' => $this->l('Will only be set on homepage'),
                        'hint' => $this->l('Else will be shown depending on hook and next settings'),
                        'name' => 'only_home',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Only on specific category ?'),
                        'desc' => $this->l('Only if hook is available on categories'),
                        'hint' => $this->l('Set to no to show this block on each category'),
                        'name' => 'only_category',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Only on specific product categories ?'),
                        'desc' => $this->l('Only if hook is available on product categories'),
                        'hint' => $this->l('Set to no to show this block on each product pages on specific categories'),
                        'name' => 'only_category_product',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'multiple' => true,
                        'label' => $this->l('Limit on categories ?'),
                        'desc' => $this->l('Only if chosen hook is on categories'),
                        'hint' => $this->l('Depends on previous setting'),
                        'name' => 'categories[]',
                        'required' => false,
                        'options' => [
                            'query' => $categories_list,
                            'id' => 'id_category',
                            'name' => 'name',
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Only on specific manufacturer ?'),
                        'desc' => $this->l('Only if hook is available on manufacturers'),
                        'hint' => $this->l('Set to no to show this block on each manufacturer'),
                        'name' => 'only_manufacturer',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'multiple' => true,
                        'label' => $this->l('Limit on manufacturers ?'),
                        'desc' => $this->l('Only if chosen hook is on manufacturers'),
                        'hint' => $this->l('Depends on previous setting'),
                        'name' => 'manufacturers[]',
                        'required' => false,
                        'options' => [
                            'query' => $manufacturersList,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Only on specific supplier ?'),
                        'desc' => $this->l('Only if hook is available on suppliers'),
                        'hint' => $this->l('Set to no to show this block on each supplier'),
                        'name' => 'only_supplier',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'multiple' => true,
                        'label' => $this->l('Limit on suppliers ?'),
                        'desc' => $this->l('Only if chosen hook is on suppliers'),
                        'hint' => $this->l('Depends on previous setting'),
                        'name' => 'suppliers[]',
                        'required' => false,
                        'options' => [
                            'query' => $suppliersList,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Only on specific CMS category ?'),
                        'desc' => $this->l('Only if hook is available on CMS categories'),
                        'hint' => $this->l('Set to no to show this block on each CMS categories'),
                        'name' => 'only_cms_category',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'multiple' => true,
                        'label' => $this->l('Limit on CMS categories ?'),
                        'desc' => $this->l('Only if chosen hook is on CMS categories'),
                        'hint' => $this->l('Depends on previous setting'),
                        'name' => 'cms_categories[]',
                        'required' => false,
                        'options' => [
                            'query' => $cmsCategoriesList,
                            'id' => 'id_cms_category',
                            'name' => 'name',
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Obfuscate all links on block content ?'),
                        'desc' => $this->l('Will obfuscate all links found on block content'),
                        'hint' => $this->l('Else links will remain as set on content'),
                        'name' => 'obfuscate_link',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Add div with class container on block ?'),
                        'desc' => $this->l('The block will be in a div with the class container'),
                        'hint' => $this->l('Otherwise the block will not be in a div with the class container'),
                        'name' => 'add_container',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Lazyload images on block content ?'),
                        'desc' => $this->l('Will add lazyload class and lazy value to loading attribute'),
                        'hint' => $this->l('Else images will remain as set on content'),
                        'name' => 'lazyload',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Block background color'),
                        'desc' => $this->l('Enter block background color'),
                        'hint' => $this->l('Leave empty for no use'),
                        'required' => false,
                        'name' => 'background',
                        'lang' => false,
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Block custom class name'),
                        'desc' => $this->l('Enter block custom class name'),
                        'hint' => $this->l('Leave empty for no use'),
                        'required' => false,
                        'name' => 'css_class',
                        'lang' => false,
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Block data attributes'),
                        'desc' => $this->l('Add custom data attributes'),
                        'hint' => $this->l('Leave empty for no use'),
                        'required' => false,
                        'name' => 'data_attribute',
                        'lang' => false,
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Bloc size'),
                        'desc' => $this->l('Please select bloc size'),
                        'hint' => $this->l('Block will have this size'),
                        'name' => 'bootstrap_class',
                        'class' => 'chosen',
                        'required' => false,
                        'options' => [
                            'query' => $bootstrapSizes,
                            'id' => 'id_bootstrap',
                            'name' => 'size',
                        ],
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Block position'),
                        'desc' => $this->l('Enter block position number'),
                        'hint' => $this->l('Blocks will be ordered using this number'),
                        'required' => false,
                        'name' => 'position',
                        'lang' => false,
                        'tab' => 'display',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show as modal ?'),
                        'desc' => $this->l('Will be shown as modal. Hooks won\'t be rendered, neither store locator'),
                        'hint' => $this->l('Else will be shown as block'),
                        'name' => 'modal',
                        'bool' => true,
                        'lang' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate'),
                            ],
                        ],
                        'tab' => 'modal',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Lifetime of the cookie (in days). Modal only'),
                        'desc' => $this->l('If disabled, the modal will show systematically'),
                        'hint' => $this->l('Set 0 for debug or disable. Please set a number.'),
                        'name' => 'delay',
                        'required' => false,
                        'lang' => false,
                        'tab' => 'modal',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Delay'),
                        'desc' => $this->l('Delay before popup appears'),
                        'hint' => $this->l('Value must be in milliseconds'),
                        'name' => 'timeout',
                        'required' => false,
                        'lang' => false,
                        'tab' => 'modal',
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->l('Date start'),
                        'desc' => $this->l('Date block will start to appear'),
                        'hint' => $this->l('Leave empty for no use'),
                        'name' => 'date_start',
                        'tab' => 'schedule',
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->l('Date end'),
                        'desc' => $this->l('Date block will end'),
                        'hint' => $this->l('Leave empty for no use'),
                        'name' => 'date_end',
                        'tab' => 'schedule',
                    ],
                ], $docInputs),
            ],
        ];
        $lastFormIndex = count($fields_form) - 1;
        $fields_form[$lastFormIndex]['form']['input'] = $this->moveDocumentationToTabEnd(
            $fields_form[$lastFormIndex]['form']['input']
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->name_controller = $this->table;
        $helper->toolbar_scroll = true;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $this->fields_form = [];
        $helper->identifier = $this->identifier;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->token = Tools::getValue('token');
        $helper->submit_action = 'save';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues($obj),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int) Context::getContext()->language->id,
        ];
        $helper->currentIndex = AdminController::$currentIndex;
        $moduleInstance = Module::getInstanceByName($this->table);
        $displayUpgrade = $moduleInstance->checkLatestEverModuleVersion();

        $notifications = '';
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $notifications .= Tools::displayError($error);
            }
        }
        if (is_array($this->confirmations) && count($this->confirmations)) {
            foreach ($this->confirmations as $confirmation) {
                $notifications .= $this->displayConfirmation($confirmation);
            }
        }

        $previewAvailable = Validate::isLoadedObject($obj) && (int) $obj->id > 0;
        $previewContexts = $this->buildPreviewContexts(
            $obj,
            [
                'categories' => $categories_list,
                'manufacturers' => $manufacturersList,
                'suppliers' => $suppliersList,
                'cms_categories' => $cmsCategoriesList,
                'groups' => $groups,
            ]
        );
        $previewUrl = '';

        if ($previewAvailable) {
            $previewUrl = $this->context->link->getModuleLink(
                'everblock',
                'preview',
                [
                    'token' => Tools::getAdminTokenLite('AdminEverBlockController'),
                    'id_everblock' => (int) $obj->id,
                ],
                true
            );
        }
        $this->context->smarty->assign([
            'everblock_notifications' => $notifications,
            'everblock_form' => $helper->generateForm($fields_form),
            'display_upgrade' => $displayUpgrade,
            'everblock_preview_contexts' => $previewContexts,
            'everblock_preview_url' => $previewUrl,
            'everblock_preview_available' => $previewAvailable,
        ]);

        $content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everblock/views/templates/admin/header.tpl'
        );
        $content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everblock/views/templates/admin/configure.tpl'
        );
        $content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everblock/views/templates/admin/footer.tpl'
        );

        return $content;
    }

    protected function buildPreviewContexts($block, array $lists): array
    {
        $languages = [];
        foreach (Language::getLanguages(true) as $language) {
            $languages[] = [
                'id' => (int) $language['id_lang'],
                'iso_code' => isset($language['iso_code']) ? (string) $language['iso_code'] : '',
                'label' => isset($language['name']) ? (string) $language['name'] : (string) $language['id_lang'],
            ];
        }

        $shops = [];
        $shopRows = Shop::getShops(true);
        if (is_array($shopRows)) {
            foreach ($shopRows as $shop) {
                $shops[] = [
                    'id' => isset($shop['id_shop']) ? (int) $shop['id_shop'] : 0,
                    'label' => isset($shop['name']) ? (string) $shop['name'] : '',
                ];
            }
        }

        $shopId = isset($block->id_shop) && (int) $block->id_shop > 0
            ? (int) $block->id_shop
            : (int) $this->context->shop->id;

        $categories = $this->formatPreviewOptions(
            isset($lists['categories']) ? (array) $lists['categories'] : [],
            'id_category',
            'name'
        );
        $manufacturers = $this->formatPreviewOptions(
            isset($lists['manufacturers']) ? (array) $lists['manufacturers'] : [],
            'id',
            'name'
        );
        $suppliers = $this->formatPreviewOptions(
            isset($lists['suppliers']) ? (array) $lists['suppliers'] : [],
            'id',
            'name'
        );
        $cmsCategories = $this->formatPreviewOptions(
            isset($lists['cms_categories']) ? (array) $lists['cms_categories'] : [],
            'id_cms_category',
            'name'
        );
        $groupOptions = $this->formatPreviewOptions(
            isset($lists['groups']) ? (array) $lists['groups'] : [],
            'id_group',
            'name'
        );

        $selectedCategories = $this->normalizeIdList(isset($block->categories) ? $block->categories : []);
        $selectedManufacturers = $this->normalizeIdList(isset($block->manufacturers) ? $block->manufacturers : []);
        $selectedSuppliers = $this->normalizeIdList(isset($block->suppliers) ? $block->suppliers : []);
        $selectedCmsCategories = $this->normalizeIdList(isset($block->cms_categories) ? $block->cms_categories : []);
        $selectedGroups = $this->normalizeIdList(isset($block->groups) ? $block->groups : []);

        $defaultCategoryId = $this->resolveDefaultIdentifier($selectedCategories, $categories);
        $defaultManufacturerId = $this->resolveDefaultIdentifier($selectedManufacturers, $manufacturers);
        $defaultSupplierId = $this->resolveDefaultIdentifier($selectedSuppliers, $suppliers);
        $defaultCmsCategoryId = $this->resolveDefaultIdentifier($selectedCmsCategories, $cmsCategories);
        $defaultProductId = $this->resolveDefaultProductId($selectedCategories, $shopId);

        $groupRestrictions = [];
        if (!empty($selectedGroups)) {
            foreach ($groupOptions as $group) {
                if (in_array((int) $group['id'], $selectedGroups, true)) {
                    $groupRestrictions[] = $group;
                }
            }
        }

        // Définition des contextes disponibles
        $contextDefinitions = [
            'index' => [
                'key' => 'index',
                'controller' => 'index',
                'page_name' => 'index',
                'label' => $this->l('Homepage'),
                'fields' => [],
            ],
            'category' => [
                'key' => 'category',
                'controller' => 'category',
                'page_name' => 'category',
                'label' => $this->l('Category page'),
                'fields' => [
                    [
                        'name' => 'id_category',
                        'type' => 'select',
                        'label' => $this->l('Category'),
                        'options' => $categories,
                        'value' => $defaultCategoryId,
                    ],
                ],
            ],
            'product' => [
                'key' => 'product',
                'controller' => 'product',
                'page_name' => 'product',
                'label' => $this->l('Product page'),
                'fields' => [
                    [
                        'name' => 'id_product',
                        'type' => 'number',
                        'label' => $this->l('Product ID'),
                        'value' => $defaultProductId,
                        'placeholder' => '0',
                        'help' => $defaultProductId ? '' : $this->l('No product found in selected categories. Enter an ID manually.'),
                        'min' => 0,
                    ],
                ],
            ],
            'manufacturer' => [
                'key' => 'manufacturer',
                'controller' => 'manufacturer',
                'page_name' => 'manufacturer',
                'label' => $this->l('Manufacturer page'),
                'fields' => [
                    [
                        'name' => 'id_manufacturer',
                        'type' => 'select',
                        'label' => $this->l('Manufacturer'),
                        'options' => $manufacturers,
                        'value' => $defaultManufacturerId,
                    ],
                ],
            ],
            'supplier' => [
                'key' => 'supplier',
                'controller' => 'supplier',
                'page_name' => 'supplier',
                'label' => $this->l('Supplier page'),
                'fields' => [
                    [
                        'name' => 'id_supplier',
                        'type' => 'select',
                        'label' => $this->l('Supplier'),
                        'options' => $suppliers,
                        'value' => $defaultSupplierId,
                    ],
                ],
            ],
            'cms_category' => [
                'key' => 'cms_category',
                'controller' => 'cms',
                'page_name' => 'cms_category',
                'label' => $this->l('CMS category page'),
                'fields' => [
                    [
                        'name' => 'id_cms_category',
                        'type' => 'select',
                        'label' => $this->l('CMS category'),
                        'options' => $cmsCategories,
                        'value' => $defaultCmsCategoryId,
                    ],
                ],
            ],
        ];

        // 🌟 Nouvelle logique : toujours permettre la preview, mais contextualisée
        $enabledKeys = ['index', 'category', 'product', 'manufacturer', 'supplier', 'cms_category'];

        if (Validate::isLoadedObject($block)) {
            $specificKeys = [];

            if (!empty($block->only_home)) {
                $specificKeys[] = 'index';
            }
            if (!empty($block->only_category)) {
                $specificKeys[] = 'category';
            }
            if (!empty($block->only_category_product)) {
                $specificKeys[] = 'product';
            }
            if (!empty($block->only_manufacturer)) {
                $specificKeys[] = 'manufacturer';
            }
            if (!empty($block->only_supplier)) {
                $specificKeys[] = 'supplier';
            }
            if (!empty($block->only_cms_category)) {
                $specificKeys[] = 'cms_category';
            }

            // Si le bloc a des restrictions explicites, on garde uniquement celles-là
            if (!empty($specificKeys)) {
                $enabledKeys = $specificKeys;
            }
        }

        $controllers = [];
        foreach ($enabledKeys as $key) {
            if (isset($contextDefinitions[$key])) {
                $controllers[] = $contextDefinitions[$key];
            }
        }

        // Si aucun contexte particulier, on montre une page où le hook est présent
        if (empty($controllers)) {
            $controllers[] = [
                'key' => 'index',
                'controller' => 'index',
                'page_name' => 'index',
                'label' => $this->l('Generic page (hook displayable)'),
                'fields' => [],
            ];
        }

        $defaultContext = $controllers[0]['key'] ?? 'index';

        return [
            'id_everblock' => Validate::isLoadedObject($block) ? (int) $block->id : 0,
            'languages' => $languages,
            'shops' => $shops,
            'controllers' => $controllers,
            'defaults' => [
                'context' => $defaultContext,
                'id_lang' => (int) $this->context->language->id,
                'id_shop' => $shopId,
                'id_currency' => isset($this->context->currency->id) ? (int) $this->context->currency->id : null,
                'position' => isset($block->position) ? (int) $block->position : null,
            ],
            'groups' => $groupRestrictions,
        ];
    }

    protected function formatPreviewOptions(array $items, string $idKey, string $labelKey): array
    {
        $options = [];

        foreach ($items as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }

            if (!is_array($item) || !isset($item[$idKey])) {
                continue;
            }

            $options[] = [
                'id' => (int) $item[$idKey],
                'label' => isset($item[$labelKey]) ? (string) $item[$labelKey] : (string) $item[$idKey],
            ];
        }

        return $options;
    }

    protected function normalizeIdList($value): array
    {
        if (is_array($value)) {
            return array_map('intval', array_filter($value));
        }

        if (is_object($value)) {
            $value = (array) $value;
            return array_map('intval', array_filter($value));
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_map('intval', array_filter($decoded));
            }
        }

        return [];
    }

    protected function resolveDefaultIdentifier(array $selectedIds, array $options): ?int
    {
        $selectedIds = array_map('intval', array_filter($selectedIds));

        foreach ($selectedIds as $selected) {
            foreach ($options as $option) {
                if ((int) $option['id'] === (int) $selected) {
                    return (int) $selected;
                }
            }
        }

        if (!empty($options)) {
            return (int) $options[0]['id'];
        }

        return null;
    }

    protected function resolveDefaultProductId(array $categoryIds, int $shopId): ?int
    {
        $categoryIds = array_map('intval', array_filter($categoryIds));

        if (empty($categoryIds)) {
            return null;
        }

        foreach ($categoryIds as $categoryId) {
            $query = new DbQuery();
            $query->select('p.id_product');
            $query->from('product', 'p');
            $query->innerJoin('product_shop', 'ps', 'p.id_product = ps.id_product');
            $query->innerJoin('category_product', 'cp', 'cp.id_product = p.id_product');
            $query->where('ps.id_shop = ' . (int) $shopId);
            $query->where('cp.id_category = ' . (int) $categoryId);
            $query->where('ps.active = 1');
            $query->orderBy('ps.date_upd DESC');
            $query->limit(1);

            $productId = (int) Db::getInstance()->getValue($query);
            if ($productId > 0) {
                return $productId;
            }
        }

        return null;
    }

    protected function getConfigFormValues($obj)
    {
        $groups = Group::getGroups($this->context->language->id);
        $formValues = [];
        if (Validate::isLoadedObject($obj)) {
            // dump($obj);
            // die();
            $groupsIds = (array)json_decode($obj->groups);
            foreach ($groups as $group) {
                $formValues[] = [
                    'groupBox_' . $group['id_group'] => Tools::getValue('groupBox_' . $group['id_group'], (in_array($group['id_group'], $groupsIds)))
                ];
            }
            $formValues[] = [
                $this->identifier => (!empty(Tools::getValue($this->identifier)))
                ? Tools::getValue($this->identifier)
                : $obj->id,
                'id_hook' => (!empty(Tools::getValue('id_hook')))
                ? Tools::getValue('id_hook')
                : $obj->id_hook,
                'name' => (!empty(Tools::getValue('name')))
                ? Tools::getValue('name')
                : $obj->name,
                'content' => (!empty(Tools::getValue('content')))
                ? Tools::getValue('content')
                : $obj->content,
                'custom_code' => (!empty(Tools::getValue('custom_code')))
                ? Tools::getValue('custom_code')
                : $obj->custom_code,                
                'categories[]' => (!empty(Tools::getValue('categories')))
                ? Tools::getValue('categories')
                : json_decode($obj->categories),
                'manufacturers[]' => (!empty(Tools::getValue('manufacturers')))
                ? Tools::getValue('manufacturers')
                : json_decode($obj->manufacturers),
                'suppliers[]' => (!empty(Tools::getValue('suppliers')))
                ? Tools::getValue('suppliers')
                : json_decode($obj->suppliers),
                'cms_categories[]' => (!empty(Tools::getValue('cms_categories')))
                ? Tools::getValue('cms_categories')
                : json_decode($obj->cms_categories),
                'only_home' => (!empty(Tools::getValue('only_home')))
                ? Tools::getValue('only_home')
                : $obj->only_home,
                'only_category' => (!empty(Tools::getValue('only_category')))
                ? Tools::getValue('only_category')
                : $obj->only_category,
                'only_manufacturer' => (!empty(Tools::getValue('only_manufacturer')))
                ? Tools::getValue('only_manufacturer')
                : $obj->only_manufacturer,
                'only_supplier' => (!empty(Tools::getValue('only_supplier')))
                ? Tools::getValue('only_supplier')
                : $obj->only_supplier,
                'only_cms_category' => (!empty(Tools::getValue('only_cms_category')))
                ? Tools::getValue('only_cms_category')
                : $obj->only_cms_category,
                'only_category_product' => (!empty(Tools::getValue('only_category_product')))
                ? Tools::getValue('only_category_product')
                : $obj->only_category_product,
                'obfuscate_link' => (!empty(Tools::getValue('obfuscate_link')))
                ? Tools::getValue('obfuscate_link')
                : $obj->obfuscate_link,
                'add_container' => (Tools::getValue('add_container') !== '')
                ? Tools::getValue('add_container')
                : $obj->add_container,
                'lazyload' => (!empty(Tools::getValue('lazyload')))
                ? Tools::getValue('lazyload')
                : $obj->lazyload,                
                'position' => (!empty(Tools::getValue('position')))
                ? Tools::getValue('position')
                : $obj->position,
                'background' => (!empty(Tools::getValue('background')))
                ? Tools::getValue('background')
                : $obj->background,
                'css_class' => (!empty(Tools::getValue('css_class')))
                ? Tools::getValue('css_class')
                : $obj->css_class,
                'data_attribute' => (!empty(Tools::getValue('data_attribute')))
                ? Tools::getValue('data_attribute')
                : $obj->data_attribute,
                'bootstrap_class' => (!empty(Tools::getValue('bootstrap_class')))
                ? Tools::getValue('bootstrap_class')
                : $obj->bootstrap_class,
                'device' => (!empty(Tools::getValue('device')))
                ? Tools::getValue('device')
                : $obj->device,
                'delay' => (!empty(Tools::getValue('delay')))
                ? Tools::getValue('delay')
                : $obj->delay,
                'timeout' => (!empty(Tools::getValue('timeout')))
                ? Tools::getValue('timeout')
                : $obj->timeout,
                'modal' => (!empty(Tools::getValue('modal')))
                ? Tools::getValue('modal')
                : $obj->modal,
                'date_start' => (!empty(Tools::getValue('date_start')))
                ? Tools::getValue('date_start')
                : $obj->date_start,
                'date_end' => (!empty(Tools::getValue('date_end')))
                ? Tools::getValue('date_end')
                : $obj->date_end,
                'id_shop' => (!empty(Tools::getValue('id_shop')))
                ? Tools::getValue('id_shop')
                : $obj->id_shop,
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : $obj->active,
            ];
        } else {
            $categories = [];
            $content = [];
            $custom_code = [];
            foreach (Language::getLanguages(false) as $language) {
                $content[$language['id_lang']] = '';
                $custom_code[$language['id_lang']] = '';
            }
            foreach ($groups as $group) {
                $formValues[] = [
                    'groupBox_' . $group['id_group'] => Tools::getValue('groupBox_' . $group['id_group'], true)
                ];
            }
            $formValues[] = [
                $this->identifier => (!empty(Tools::getValue($this->identifier)))
                ? Tools::getValue($this->identifier)
                : '',
                'id_hook' => (!empty(Tools::getValue('id_hook')))
                ? Tools::getValue('id_hook')
                : '',
                'name' => (!empty(Tools::getValue('name')))
                ? Tools::getValue('name')
                : '',
                'content' => (!empty(Tools::getValue('content')))
                ? Tools::getValue('content')
                : $content,
                'custom_code' => (!empty(Tools::getValue('custom_code')))
                ? Tools::getValue('custom_code')
                : $custom_code,
                'categories[]' => (!empty(Tools::getValue('categories')))
                ? Tools::getValue('categories')
                :'',
                'manufacturers[]' => (!empty(Tools::getValue('manufacturers')))
                ? Tools::getValue('manufacturers')
                :'',
                'suppliers[]' => (!empty(Tools::getValue('suppliers')))
                ? Tools::getValue('suppliers')
                :'',
                'cms_categories[]' => (!empty(Tools::getValue('cms_categories')))
                ? Tools::getValue('cms_categories')
                :'',
                'only_home' => (!empty(Tools::getValue('only_home')))
                ? Tools::getValue('only_home')
                : '',
                'only_category' => (!empty(Tools::getValue('only_category')))
                ? Tools::getValue('only_category')
                : '',
                'only_category_product' => (!empty(Tools::getValue('only_category_product')))
                ? Tools::getValue('only_category_product')
                : '',
                'only_manufacturer' => (!empty(Tools::getValue('only_manufacturer')))
                ? Tools::getValue('only_manufacturer')
                : '',
                'only_supplier' => (!empty(Tools::getValue('only_supplier')))
                ? Tools::getValue('only_supplier')
                : '',
                'only_cms_category' => (!empty(Tools::getValue('only_cms_category')))
                ? Tools::getValue('only_cms_category')
                : '',
                'obfuscate_link' => (!empty(Tools::getValue('obfuscate_link')))
                ? Tools::getValue('obfuscate_link')
                : '',
                'add_container' => (Tools::getValue('add_container') !== '')
                ? Tools::getValue('add_container')
                : '1',
                'lazyload' => (!empty(Tools::getValue('lazyload')))
                ? Tools::getValue('lazyload')
                : '',
                'position' => (!empty(Tools::getValue('position')))
                ? Tools::getValue('position')
                : '',
                'background' => (!empty(Tools::getValue('background')))
                ? Tools::getValue('background')
                : '',
                'css_class' => (!empty(Tools::getValue('css_class')))
                ? Tools::getValue('css_class')
                : '',
                'data_attribute' => (!empty(Tools::getValue('data_attribute')))
                ? Tools::getValue('data_attribute')
                : '',
                'bootstrap_class' => (!empty(Tools::getValue('bootstrap_class')))
                ? Tools::getValue('bootstrap_class')
                : '',
                'device' => (!empty(Tools::getValue('device')))
                ? Tools::getValue('device')
                : '',
                'delay' => (!empty(Tools::getValue('delay')))
                ? Tools::getValue('delay')
                : '',
                'timeout' => (!empty(Tools::getValue('timeout')))
                ? Tools::getValue('timeout')
                : '',
                'modal' => (!empty(Tools::getValue('modal')))
                ? Tools::getValue('modal')
                : '',
                'date_start' => (!empty(Tools::getValue('date_start')))
                ? Tools::getValue('date_start')
                : '',
                'date_end' => (!empty(Tools::getValue('date_end')))
                ? Tools::getValue('date_end')
                : '',
                'id_shop' => (!empty(Tools::getValue('id_shop')))
                ? Tools::getValue('id_shop')
                : '',
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : '',
            ];
        }
        $values = call_user_func_array('array_merge', $formValues);
        return $values;
    }

    public function postProcess()
    {
        parent::postProcess();
        
        if (Tools::isSubmit('submitFilter'.$this->table)) {
            $this->processFilter();
        }
        
        if (Tools::getIsset('duplicate' . $this->table)) {
            $this->duplicate(
                (int)Tools::getValue($this->identifier)
            );
        }
        if (Tools::getIsset('export' . $this->table)) {
            $this->exportBlock(
                (int) Tools::getValue($this->identifier)
            );
        }
        if (Tools::isSubmit('deleteeverblock')) {
            $everblock_obj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            if (!$everblock_obj->delete()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t delete the current object');
            }
        }
        if (Tools::isSubmit('save') || Tools::isSubmit('stay')) {
            $everblock_obj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            if (!Tools::getValue('name')
                || !Validate::isGenericName(Tools::getValue('name'))
            ) {
                $this->errors[] = $this->l('Name is not valid or missing');
            }
            if (Tools::getValue('id_hook')
                && !Validate::isUnsignedInt(Tools::getValue('id_hook'))
            ) {
                $this->errors[] = $this->l('Hook is not valid');
            }
            if (Tools::getValue('only_home')
                && !Validate::isBool(Tools::getValue('only_home'))
            ) {
                $this->errors[] = $this->l('Only home is not valid');
            }
            if (Tools::getValue('only_category')
                && !Validate::isBool(Tools::getValue('only_category'))
            ) {
                $this->errors[] = $this->l('Only category is not valid');
            }
            if (Tools::getValue('only_manufacturer')
                && !Validate::isBool(Tools::getValue('only_manufacturer'))
            ) {
                $this->errors[] = $this->l('Only manufacturer is not valid');
            }
            if (Tools::getValue('only_supplier')
                && !Validate::isBool(Tools::getValue('only_supplier'))
            ) {
                $this->errors[] = $this->l('Only supplier is not valid');
            }
            if (Tools::getValue('only_category_product')
                && !Validate::isBool(Tools::getValue('only_category_product'))
            ) {
                $this->errors[] = $this->l('Only product page with specific categories is not valid');
            }
            if (Tools::getValue('obfuscate_link')
                && !Validate::isBool(Tools::getValue('obfuscate_link'))
            ) {
                $this->errors[] = $this->l('Obfuscate links is not valid');
            }
            if (Tools::getValue('add_container')
                && !Validate::isBool(Tools::getValue('add_container'))
            ) {
                $this->errors[] = $this->l('Add div with class container is not valid');
            }
            if (Tools::getValue('only_home')
                && Tools::getValue('only_category')
            ) {
                $this->errors[] = $this->l('"Only category" and "Only home" ae both selected');
            }
            if (Tools::getValue('only_home')
                && Tools::getValue('only_category_product')
            ) {
                $this->errors[] = $this->l('"Only product categories" and "Only home" ae both selected');
            }
            if (Tools::getValue('only_category')
                && Tools::getValue('categories')
                && !Validate::isArrayWithIds(Tools::getValue('categories'))
            ) {
                $this->errors[] = $this->l('Categories are not valid');
            }
            if (Tools::getValue('position')
                && !Validate::isUnsignedInt(Tools::getValue('position'))
            ) {
                $this->errors[] = $this->l('Position is not valid');
            }
            if (Tools::getValue('background')
                && !Validate::isColor(Tools::getValue('background'))
            ) {
                $this->errors[] = $this->l('Background color is not valid');
            }
            if (Tools::getValue('css_class')
                && !Validate::isString(Tools::getValue('css_class'))
            ) {
                $this->errors[] = $this->l('Custom class name is not valid');
            }
            if (Tools::getValue('data_attribute')
                && !Validate::isString(Tools::getValue('data_attribute'))
            ) {
                $this->errors[] = $this->l('Data attributes value is not valid');
            }
            if (Tools::getValue('bootstrap_class')
                && !Validate::isString(Tools::getValue('bootstrap_class'))
            ) {
                $this->errors[] = $this->l('Size name is not valid');
            }
            if (Tools::getValue('delay')
                && !Validate::isUnsignedInt(Tools::getValue('delay'))
            ) {
                $this->errors[] = $this->l('Modal delay is not valid');
            }
            if (Tools::getValue('timeout')
                && !Validate::isUnsignedInt(Tools::getValue('timeout'))
            ) {
                $this->errors[] = $this->l('Modal timeout is not valid');
            }
            if (Tools::getValue('modal')
                && !Validate::isBool(Tools::getValue('modal'))
            ) {
                $this->errors[] = $this->l('Modal is not valid');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                $this->errors[] = $this->l('Active is not valid');
            }
            if (Tools::getValue('device')
                && !Validate::isUnsignedInt(Tools::getValue('device'))
            ) {
                $this->errors[] = $this->l('Device is not valid');
            }
            $everblock_obj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            $everblock_obj->name = pSQL(Tools::getValue('name'));
            $everblock_obj->id_shop = (int) $this->context->shop->id;
            $everblock_obj->id_hook = (int) Tools::getValue('id_hook');
            $everblock_obj->only_home = (bool) Tools::getValue('only_home');
            $everblock_obj->only_category = (bool) Tools::getValue('only_category');
            $everblock_obj->only_category_product = (bool) Tools::getValue('only_category_product');
            $everblock_obj->only_manufacturer = (bool) Tools::getValue('only_manufacturer');
            $everblock_obj->only_supplier = (bool) Tools::getValue('only_supplier');
            $everblock_obj->only_cms_category = (bool) Tools::getValue('only_cms_category');
            $everblock_obj->obfuscate_link = (bool) Tools::getValue('obfuscate_link');
            $everblock_obj->add_container = (bool) Tools::getValue('add_container');
            $everblock_obj->lazyload = (bool) Tools::getValue('lazyload');
            $everblock_obj->categories = json_encode(
                Tools::getValue('categories')
            );
            $everblock_obj->manufacturers = json_encode(
                Tools::getValue('manufacturers')
            );
            $everblock_obj->suppliers = json_encode(
                Tools::getValue('suppliers')
            );
            $everblock_obj->cms_categories = json_encode(
                Tools::getValue('cms_categories')
            );
            $everblock_obj->position = (int) Tools::getValue('position');
            $everblock_obj->background =  pSQL(Tools::getValue('background'));
            $everblock_obj->css_class =  pSQL(Tools::getValue('css_class'));
            $everblock_obj->data_attribute =  pSQL(Tools::getValue('data_attribute'));
            $everblock_obj->bootstrap_class =  pSQL(Tools::getValue('bootstrap_class'));
            $everblock_obj->device = (int) Tools::getValue('device');
            if (!Tools::getValue('groupBox')
                || !Validate::isArrayWithIds(Tools::getValue('groupBox'))
            ) {
                $groups = Group::getGroups(
                    (int)$this->context->language->id,
                    (int)$this->context->shop->id
                );
                $groupCondition = [];
                foreach ($groups as $group) {
                    $groupCondition[] = (int) $group['id_group'];
                }
            }
            if (isset($groupCondition)) {
                $everblock_obj->groups = json_encode($groupCondition);
            } else {
                $everblock_obj->groups = json_encode(Tools::getValue('groupBox'));
            }
            $hook_name = Hook::getNameById((int) Tools::getValue('id_hook'));
            $everblock_obj->delay = (int) Tools::getValue('delay');
            $everblock_obj->timeout = (int) Tools::getValue('timeout');
            $everblock_obj->modal = (int) Tools::getValue('modal');
            $everblock_obj->date_start = pSQL(Tools::getValue('date_start'));
            $everblock_obj->date_end = pSQL(Tools::getValue('date_end'));
            $everblock_obj->active = Tools::getValue('active');
            $everblock = Module::getInstanceByName('everblock');
            foreach (Language::getLanguages(false) as $language) {
                $contentKey = 'content_' . $language['id_lang'];
                $originalContent = Tools::getValue($contentKey);
                $convertedContent = EverblockTools::convertImagesToWebP($originalContent);
                $everblock_obj->content[$language['id_lang']] = $convertedContent;
                $everblock_obj->custom_code[$language['id_lang']] = Tools::getValue('custom_code_' . $language['id_lang']);
            }
            if (!count($this->errors)) {
                try {
                    $everblock_obj->save();
                    $everblock->registerHook(
                        $hook_name
                    );
                    if ((bool) Configuration::get('EVERPSCSS_CACHE') === true) {
                        Tools::clearAllCache();
                    }
                    if ((bool) Tools::isSubmit('stay') === true) {
                        Tools::redirectAdmin(
                            self::$currentIndex
                            . '&updateeverblock=&' . $this->identifier . '='
                            . (int) $everblock_obj->id
                            . '&token='
                            . $this->token
                        );
                    } else {
                        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
                    }
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update save block : ' . $e->getMessage());
                }
            }
        }
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idObj) {
            $everBlock = new $this->className((int) $idObj);

            if (!$everBlock->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idObj) {
            $everBlock = new $this->className((int) $idObj);
            if ($everBlock->active) {
                $everBlock->active = false;
            }

            if (!$everBlock->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkEnable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idObj) {
            $everBlock = new $this->className((int) $idObj);
            if (!$everBlock->active) {
                $everBlock->active = true;
            }

            if (!$everBlock->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkDuplicate()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idObj) {
            $this->duplicate($idObj);
        }
    }

    protected function duplicate($id)
    {
        $everBlock = new $this->className((int) $id);
        $newBlock = new $this->className();
        $newBlock->name = $everBlock->name;
        $newBlock->content = $everBlock->content;
        $newBlock->only_home = $everBlock->only_home;
        $newBlock->only_category = $everBlock->only_category;
        $newBlock->only_category_product = $everBlock->only_category_product;
        $newBlock->id_hook = $everBlock->id_hook;
        $newBlock->device = $everBlock->device;
        $newBlock->id_shop = $everBlock->id_shop;
        $newBlock->categories = $everBlock->categories;
        $newBlock->groups = $everBlock->groups;
        $newBlock->active = false;
        $newBlock->position = $everBlock->position;

        if (!$newBlock->save()) {
            $this->errors[] = $this->l('An error has occurred: Can\'t duplicate the current object');
        }
    }

    protected function exportBlock($id)
    {
        $sql = EverblockTools::exportBlockSQL((int) $id);
        if ($sql) {
            $filename = 'everblock_' . (int) $id . '.sql';
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $sql;
            exit;
        } else {
            $this->errors[] = $this->l('An error has occurred during export.');
        }
    }

    public function displayExportLink($token, $id)
    {
        $href = self::$currentIndex . '&export' . $this->table . '&' . $this->identifier . '=' . (int) $id . '&token=' . $token;
        $this->context->smarty->assign([
            'href' => $href,
            'action' => $this->l('Export SQL'),
        ]);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'everblock/views/templates/admin/list_action_export.tpl');
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
            'SELECT * FROM `' . _DB_PREFIX_ . 'hook` h
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

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        if (empty($order_by)) {
            $order_by = $this->_orderBy;
        }
        if (empty($order_way)) {
            $order_way = $this->_orderWay;
        }

        if (Tools::getValue($this->table.'Orderby')) {
            $order_by = Tools::getValue($this->table.'Orderby');
        }
        if (Tools::getValue($this->table.'Orderway')) {
            $order_way = Tools::getValue($this->table.'Orderway');
        }

        // Dodaj sortowanie po position jako drugorzędne
        if ($order_by === 'hname') {
            $this->_orderBy = 'hname, position';
        } else {
            $this->_orderBy = $order_by;
        }

        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
    }
}
