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
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockTools.php';

class AdminEverBlockController extends ModuleAdminController
{
    private $html;
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = true;
        $this->table = 'everblock';
        $this->className = 'EverBlockClass';
        $this->context = Context::getContext();
        $this->identifier = 'id_everblock';
        $this->name = 'AdminEverBlockController';
        EverblockTools::checkAndFixDatabase();
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $this->context->smarty->assign([
            'module_link' => $module_link,
            'everblock_dir' => _MODULE_DIR_ . '/everblock/',
        ]);
        $this->_select = 'h.title AS hname';

        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'hook` h
        ON (
            h.`id_hook` = a.`id_hook`
        )';
        $this->fields_list = [
            'id_everblock' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'name' => [
                'title' => $this->l('Name'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'hname' => [
                'title' => $this->l('Hook'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'position' => [
                'title' => $this->l('Position'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'only_home' => [
                'title' => $this->l('Home only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
            ],
            'only_category' => [
                'title' => $this->l('Category only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
            ],
            'only_manufacturer' => [
                'title' => $this->l('Manufacturer only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
            ],
            'only_supplier' => [
                'title' => $this->l('Supplier only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
            ],
            'only_cms_category' => [
                'title' => $this->l('CMS category only'),
                'align' => 'left',
                'width' => 'auto',
                'type' => 'bool',
            ],
            'date_start' => [
                'title' => $this->l('Date start'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'date_end' => [
                'title' => $this->l('Date end'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'active' => [
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
        ];
        $this->_where = 'AND a.id_shop =' . (int) $this->context->shop->id;
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
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('duplicate');
        $this->toolbar_title = $this->l('HTML blocks Configuration');
        if (Configuration::get('EVERGPT_API_KEY')) {
            $this->bulk_actions = [
                'duplicateall' => [
                    'text' => $this->l('Duplicate selected items'),
                    'confirm' => $this->l('Duplicate selected items ?'),
                ],
                'gptgenerate' => [
                    'text' => $this->l('Generate content using chatGPT'),
                    'confirm' => $this->l('Generate content using chatGPT ?'),
                ],
                'delete' => [
                    'text' => $this->l('Delete selected items'),
                    'confirm' => $this->l('Delete selected items ?'),
                ],
            ];
        } else {
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
        }

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
        if (Tools::isSubmit('submitBulkgptgenerate' . $this->table)) {
            $this->processBulkGptGenerate();
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
        $this->html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/' . $this->table . '/views/templates/admin/header.tpl');
        if ($moduleInstance->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . '/' . $this->table . '/views/templates/admin/upgrade.tpl');
        }
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $this->html .= Tools::displayError($error);
            }
        }
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/admin/configure.tpl');
        $this->html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/admin/footer.tpl');

        return $this->html;
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
        $manufacturersList = Manufacturer::getLiteManufacturersList(
            (int) $this->context->language->id
        );
        $suppliersList = Supplier::getLiteSuppliersList(
            (int) $this->context->language->id
        );
        $cmsCategoriesList = CMSCategory::getSimpleCategories(
            (int) $this->context->language->id
        );
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
                'id_bootstrap' => 6,
                'size' => $this->l('1/6')
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
                'id_bootstrap' => 2,
                'size' => $this->l('1/2')
            ],
            [
                'id_bootstrap' => 1,
                'size' => $this->l('100%')
            ],
            [
                'id_bootstrap' => 0,
                'size' => $this->l('None')
            ],
        ];
        $everblock_obj = $this->loadObject(true);
        $everblock_obj->categories = json_decode($everblock_obj->categories);

        // Building the Add/Edit form
        $fields_form[] = [
            'form' => [
                'tinymce' => true,
                'description' => $this->l('Manage HTML block.'),
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
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => $this->identifier,
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
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'desc' => $this->l('As a reminder, wont be shown'),
                        'hint' => $this->l('This reminder will be shown on admin only'),
                        'required' => true,
                        'name' => 'name',
                        'lang' => false,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Devices management'),
                        'desc' => $this->l('Please specify the device on which the block should be displayed'),
                        'hint' => $this->l('Select "all devices" for a global view'),
                        'name' => 'device',
                        'required' => true,
                        'options' => [
                            'query' => $devices,
                            'id' => 'id_device',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'group',
                        'label' => $this->l('Group access'),
                        'name' => 'groupBox',
                        'values' => Group::getGroups($this->context->language->id),
                        'desc' => $this->l('Block will be shown to these groups'),
                        'hint' => $this->l('Please select at least one customer group'),
                        'required' => true,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Block background color'),
                        'desc' => $this->l('Enter block background color'),
                        'hint' => $this->l('Leave empty for no use'),
                        'required' => false,
                        'name' => 'background',
                        'lang' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Block custom class name'),
                        'desc' => $this->l('Enter block custom class name'),
                        'hint' => $this->l('Leave empty for no use'),
                        'required' => false,
                        'name' => 'css_class',
                        'lang' => false,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Bloc size'),
                        'desc' => $this->l('Please select bloc size'),
                        'hint' => $this->l('Block will have this size'),
                        'name' => 'bootstrap_class',
                        'class' => 'chosen',
                        'required' => true,
                        'options' => [
                            'query' => $bootstrapSizes,
                            'id' => 'id_bootstrap',
                            'name' => 'size',
                        ],
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
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Block position'),
                        'desc' => $this->l('Enter block position number'),
                        'hint' => $this->l('Blocks will be ordered using this number'),
                        'required' => true,
                        'name' => 'position',
                        'lang' => false,
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->l('Date start'),
                        'desc' => $this->l('Date block will start to appear'),
                        'hint' => $this->l('Leave empty for no use'),
                        'name' => 'date_start',
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->l('Date end'),
                        'desc' => $this->l('Date block will end'),
                        'hint' => $this->l('Leave empty for no use'),
                        'name' => 'date_end',
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
                    ],
                ],
            ],
        ];
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
        $render = '';
        $render .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/' . $this->table . '/views/templates/admin/header.tpl');
        if ($moduleInstance->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . '/' . $this->table . '/views/templates/admin/upgrade.tpl');
        }
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $this->html .= Tools::displayError($error);
            }
        }
        $render .= $helper->generateForm($fields_form);
        $render .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/admin/configure.tpl');
        $render .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/admin/footer.tpl');

        return $render;
    }

    protected function getConfigFormValues($obj)
    {
        $groups = Group::getGroups($this->context->language->id);
        $formValues = [];
        if (Validate::isLoadedObject($obj)) {
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
                'add_container' => (!empty(Tools::getValue('add_container')))
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
                'bootstrap_class' => (!empty(Tools::getValue('bootstrap_class')))
                ? Tools::getValue('bootstrap_class')
                : $obj->bootstrap_class,
                'device' => (!empty(Tools::getValue('device')))
                ? Tools::getValue('device')
                : $obj->device,
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
            foreach (Language::getLanguages(false) as $language) {
                $content[$language['id_lang']] = '';
            }
            foreach ($groups as $group) {
                $formValues[] = [
                    'groupBox_' . $group['id_group'] => Tools::getValue('groupBox_' . $group['id_group'], false)
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
                'add_container' => (!empty(Tools::getValue('add_container')))
                ? Tools::getValue('add_container')
                : '',
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
                'bootstrap_class' => (!empty(Tools::getValue('bootstrap_class')))
                ? Tools::getValue('bootstrap_class')
                : '',
                'device' => (!empty(Tools::getValue('device')))
                ? Tools::getValue('device')
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
        if (Tools::getIsset('duplicate'.$this->table)) {
            $this->duplicate(
                (int)Tools::getValue($this->identifier)
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
            if (Tools::getValue('bootstrap_class')
                && !Validate::isString(Tools::getValue('bootstrap_class'))
            ) {
                $this->errors[] = $this->l('Size name is not valid');
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
            $everblock_obj->date_start = pSQL(Tools::getValue('date_start'));
            $everblock_obj->date_end = pSQL(Tools::getValue('date_end'));
            $everblock_obj->active = Tools::getValue('active');
            $everblock = Module::getInstanceByName('everblock');
            foreach (Language::getLanguages(false) as $language) {
                $everblock_obj->content[$language['id_lang']] = Tools::getValue('content_' . $language['id_lang']);
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
            $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
        }
    }

    protected function processBulkGptGenerate()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverBlock) {
            try {
                $obj = new $this->className(
                    (int) $idEverBlock
                );
                $chatGPT = new EverblockGpt();
                $chatGPT->initialize('text');
                $results = [];
                foreach (Language::getLanguages(true) as $language) {
                    $prompt = EverblockGpt::getObjectPrompt(
                        $obj,
                        (int) $obj->id,
                        (int) $language['id_lang'],
                        (int) $this->context->shop->id
                    );
                    if ($prompt) {
                        $requestResult = $chatGPT->createTextRequest($prompt);
                        if ($requestResult) {
                            $obj->content[$language['id_lang']] = $requestResult;
                        }
                    }
                }
                $obj->save();
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'Admin Everblock GPT : ' . $e->getMessage()
                );
            }
        }
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
}
