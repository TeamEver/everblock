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

class AdminEverBlockHookController extends ModuleAdminController
{
    private $html;
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'hook';
        $this->className = 'Hook';
        $this->context = Context::getContext();
        $this->identifier = 'id_hook';
        $this->name = 'AdminEverBlockHook';
        EverblockTools::checkAndFixDatabase();
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $this->context->smarty->assign([
            'module_link' => $module_link,
            'everblock_dir' => _MODULE_DIR_ . '/everblock/',
        ]);
        $this->fields_list = [
            'id_hook' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'name' => [
                'title' => $this->l('Name'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'title' => [
                'title' => $this->l('Title'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'description' => [
                'title' => $this->l('Description'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'active' => [
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
        ];
        // Do not load action hooks
        $this->_where = 'AND INSTR(name, "action") = 0 AND INSTR(name, "filter") = 0';
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
            'desc' => $this->l('Add new hook'),
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
        $this->toolbar_title = $this->l('Hooks management');
        if (Tools::getValue('clearcache')) {
            Tools::clearAllCache();
            Tools::redirectAdmin(self::$currentIndex . '&cachecleared=1&token=' . $this->token);
        }
        if (Tools::getValue('cachecleared')) {
            $this->confirmations[] = $this->l('Cache has been cleared');
        }

        $this->bulk_actions = [
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
        if (Tools::isSubmit('status' . $this->table)) {
            $hook = new Hook(
                (int) Tools::getValue($this->identifier)
            );
            $hook->active = (bool) !$hook->active;
            if ($hook->save()) {
                $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
            } else {
                $this->errors[] = $this->l('An error occurred while updating the status.');
            }
        }

        $lists = parent::renderList();

        $moduleInstance = Module::getInstanceByName('everblock');
        $this->html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/admin/header.tpl');
        if ($moduleInstance->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . '/everblock/views/templates/admin/upgrade.tpl');
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
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP
            && Shop::isFeatureActive()
        ) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new hooks.');
        }
        if (count($this->errors)) {
            return false;
        }
        $obj = new Hook(
            (int)Tools::getValue('id_hook')
        );
        $fields_form = [];
        $hook = $this->loadObject(true);

        // Building the Add/Edit form
        $fields_form[] = [
            'form' => [
                'tinymce' => true,
                'description' => $this->l('Hook management'),
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
                        'name' => 'id_hook',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'desc' => $this->l('Please type a valid hook name, such as "displayMyHookName"'),
                        'hint' => $this->l('This hook will have this name'),
                        'required' => true,
                        'name' => 'name',
                        'lang' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'desc' => $this->l('As a reminder, will be shown on positions admin page'),
                        'hint' => $this->l('This reminder will be shown on admin only'),
                        'required' => true,
                        'name' => 'title',
                        'lang' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Description'),
                        'desc' => $this->l('As a reminder, will be shown on positions admin page'),
                        'hint' => $this->l('This reminder will be shown on admin only'),
                        'required' => true,
                        'name' => 'description',
                        'lang' => false,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'desc' => $this->l('Enable this hook ?'),
                        'hint' => $this->l('Only active hooks will be shown'),
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


        $moduleInstance = Module::getInstanceByName('everblock');
        $render = '';
        $render .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/admin/header.tpl');
        if ($moduleInstance->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . '/everblock/views/templates/admin/upgrade.tpl');
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
        $formValues = [];
        if (Validate::isLoadedObject($obj)) {
            $formValues[] = [
                'id_hook' => (!empty(Tools::getValue('id_hook')))
                ? Tools::getValue('id_hook')
                : $obj->id_hook,
                'name' => (!empty(Tools::getValue('name')))
                ? Tools::getValue('name')
                : $obj->name,
                'title' => (!empty(Tools::getValue('title')))
                ? Tools::getValue('title')
                : $obj->title,
                'description' => (!empty(Tools::getValue('description')))
                ? Tools::getValue('description')
                : $obj->description,
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : $obj->active,
            ];
        } else {
            $formValues[] = [
                'id_hook' => (!empty(Tools::getValue('id_hook')))
                ? Tools::getValue('id_hook')
                : '',
                'name' => (!empty(Tools::getValue('name')))
                ? Tools::getValue('name')
                : '',
                'title' => (!empty(Tools::getValue('title')))
                ? Tools::getValue('title')
                : '',
                'description' => (!empty(Tools::getValue('description')))
                ? Tools::getValue('description')
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
        if (Tools::isSubmit('delete' . $this->table)) {
            $hook = new Hook(
                (int) Tools::getValue('id_hook')
            );
            if (!$hook->delete()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t delete the current object');
            }
        }
        if (Tools::isSubmit('save') || Tools::isSubmit('stay')) {
            $hook = new Hook(
                (int) Tools::getValue('id_hook')
            );
            if (!Tools::getValue('name')
                || !Validate::isHookName(Tools::getValue('name'))
            ) {
                $this->errors[] = $this->l('Name is not valid or missing');
            }
            if (!Tools::getValue('title')
                || !Validate::isGenericName(Tools::getValue('title'))
            ) {
                $this->errors[] = $this->l('Title is not valid or missing');
            }
            if (!Tools::getValue('description')
                || !Validate::isCleanHtml(Tools::getValue('description'))
            ) {
                $this->errors[] = $this->l('Title is not valid or missing');
            }
            $hook->name = Tools::getValue('name');
            $hook->title = Tools::getValue('title');
            $hook->description = Tools::getValue('description');
            $hook->position = 1;
            $hook->active = (bool) Tools::getValue('active');
            if (!count($this->errors)) {
                try {
                    $hook->save();
                    if ((bool) Tools::isSubmit('stay') === true) {
                        Tools::redirectAdmin(
                            self::$currentIndex
                            . '&updatehook=&id_hook='
                            . (int) $hook->id
                            . '&token='
                            . $this->token
                        );
                    }
                    if (Module::isInstalled('prettyblocks')) {
                        $m = Module::getInstanceByName('prettyblocks');
                        $m->registerHook($hook->name);
                    }
                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update save block');
                }
            }
        }
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idObj) {
            $everBlock = new Hook((int) $idObj);

            if (!$everBlock->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idObj) {
            $everBlock = new Hook((int) $idObj);
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
            $everBlock = new Hook((int) $idObj);
            if (!$everBlock->active) {
                $everBlock->active = true;
            }

            if (!$everBlock->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }
}
