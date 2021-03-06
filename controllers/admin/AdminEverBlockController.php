<?php
/**
 * 2019-2021 Team Ever
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

class AdminEverBlockController extends ModuleAdminController
{
    private $html;
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = true;
        $this->table = 'everblock';
        $this->module_name = 'everblock';
        $this->className = 'EverBlockClass';
        $this->context = Context::getContext();
        $this->identifier = "id_everblock";
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->name = 'AdminEverBlockController';
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $this->context->smarty->assign(array(
            'module_link' => $module_link,
            'everblock_dir' => _MODULE_DIR_ . '/everblock/'
        ));
        $this->_select = 'h.name AS hname';

        $this->_join =
            'LEFT JOIN `'._DB_PREFIX_.'hook` h
                ON (
                    h.`id_hook` = a.`id_hook`
                )';
        $this->fields_list = array(
            'id_everblock' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'align' => 'left',
                'width' => 'auto'
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'align' => 'left',
                'width' => 'auto'
            ),
            'hname' => array(
                'title' => $this->l('Hook'),
                'align' => 'left',
                'width' => 'auto'
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            )
        );
        $this->_where = 'AND a.id_shop ='.(int)$this->context->shop->id;
        $this->colorOnBackground = true;

        parent::__construct();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everblock.Admineverblockcontroller'
            );
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new element'),
            'icon' => 'process-icon-new'
        );
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('duplicate');
        $this->toolbar_title = $this->l('HTML blocks Configuration');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?')
            ),
            'duplicateall' => array(
                'text' => $this->l('Duplicate selected items'),
                'confirm' => $this->l('Duplicate selected items ?')
            ),
        );

        if (Tools::isSubmit('submitBulkdelete'.$this->table)) {
            $this->processBulkDelete();
        }
        if (Tools::isSubmit('submitBulkdisableSelection'.$this->table)) {
            $this->processBulkDisable();
        }
        if (Tools::isSubmit('submitBulkenableSelection'.$this->table)) {
            $this->processBulkEnable();
        }
        if (Tools::isSubmit('submitBulkduplicateall'.$this->table)) {
            $this->processBulkDuplicate();
        }
        if (Tools::isSubmit('status'.$this->table)) {
            $db = Db::getInstance();
            if ($id_everblock = (int)Tools::getValue($this->identifier)) {
                $update = $db->execute(
                    'UPDATE `'._DB_PREFIX_.'everblock`
                    SET `active` = (1 - `active`)
                    WHERE `id_everblock` = '.(int)$id_everblock.' LIMIT 1'
                );
            }
            if (isset($update) && $update) {
                $this->redirect_after = self::$currentIndex.'&conf=5&token='.$this->token;
            } else {
                $this->errors[] = $this->l('An error occurred while updating the status.');
            }
        }

        $lists = parent::renderList();

        $blog_instance = Module::getInstanceByName($this->module_name);
        $this->html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/'.$this->module_name.'/views/templates/admin/header.tpl');
        if ($blog_instance->checkLatestEverModuleVersion($this->module_name, $blog_instance->version)) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_ .'/'.$this->module_name.'/views/templates/admin/upgrade.tpl');
        }
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $this->html .= Tools::displayError($error);
            }
        }
        $this->html .= $lists;
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
        $obj = new EverBlockClass(
            (int)Tools::getValue('id_everblock')
        );
        $fields_form = array();
        $hooks_list = Hook::getHooks(false, true);
        $categories_list = Category::getCategories(
            false,
            true,
            false
        );
        $everblock_obj = $this->loadObject(true);
        $everblock_obj->categories = json_decode($everblock_obj->categories);
        // die(var_dump($everblock_obj));

        // Building the Add/Edit form
        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'description' => $this->l('Add a new block.'),
                'submit' => array(
                    'name' => 'save',
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-success pull-right'
                ),
                'buttons' => array(
                    'import' => array(
                        'name' => 'stay',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & stay')
                    ),
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_everblock'
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Hook'),
                        'desc' => $this->l('Please select hook'),
                        'hint' => $this->l('Block will be shown on this hook'),
                        'name' => 'id_hook',
                        'required' => true,
                        'options' => array(
                            'query' => $hooks_list,
                            'id' => 'id_hook',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Only on homepage ?'),
                        'desc' => $this->l('Will only be set on homepage'),
                        'hint' => $this->l('Else will be shown depending on hook and next settings'),
                        'name' => 'only_home',
                        'bool' => true,
                        'lang' => false,
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Only on specific category ?'),
                        'desc' => $this->l('Only if hook is available on categories'),
                        'hint' => $this->l('Set to now to show this block on each category'),
                        'name' => 'only_category',
                        'bool' => true,
                        'lang' => false,
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate')
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'class' => 'chosen',
                        'multiple' => true,
                        'label' => $this->l('Limit on categories ?'),
                        'desc' => $this->l('Only if chosen hook is on categories'),
                        'hint' => $this->l('Depends on previous setting'),
                        'name' => 'categories[]',
                        'required' => false,
                        'options' => array(
                            'query' => $categories_list,
                            'id' => 'id_category',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'desc' => $this->l('As a reminder, wont be shown'),
                        'hint' => $this->l('This reminder will be shown on admin only'),
                        'required' => true,
                        'name' => 'name',
                        'lang' => false
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('HTML block content'),
                        'desc' => $this->l('Please type your block content'),
                        'hint' => $this->l('HTML content depends on your shop settings'),
                        'required' => true,
                        'name' => 'content',
                        'lang' => true,
                        'autoload_rte' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Block position'),
                        'desc' => $this->l('Enter block position number'),
                        'hint' => $this->l('Blocks will be ordered using this number'),
                        'required' => true,
                        'name' => 'position',
                        'lang' => false
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'desc' => $this->l('Enable this block ?'),
                        'hint' => $this->l('Only active blocks will be shown'),
                        'name' => 'active',
                        'bool' => true,
                        'lang' => false,
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Activate')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Desactivate')
                            )
                        )
                    ),
                )
            )
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->toolbar_scroll = true;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->token = Tools::getValue('token');
        $helper->submit_action = 'save';
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues($obj), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int)Context::getContext()->language->id,
        );
        $helper->currentIndex = AdminController::$currentIndex;
        return $helper->generateForm($fields_form);
    }

    protected function getConfigFormValues($obj)
    {
        $formValues = array();
        if (Validate::isLoadedObject($obj)) {
            $formValues[] = array(
                'id_everblock' => (!empty(Tools::getValue('id_everblock')))
                ? Tools::getValue('id_everblock')
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
                'only_home' => (!empty(Tools::getValue('only_home')))
                ? Tools::getValue('only_home')
                : $obj->only_home,
                'only_category' => (!empty(Tools::getValue('only_category')))
                ? Tools::getValue('only_category')
                : $obj->only_category,
                'position' => (!empty(Tools::getValue('position')))
                ? Tools::getValue('position')
                : $obj->position,
                'id_shop' => (!empty(Tools::getValue('id_shop')))
                ? Tools::getValue('id_shop')
                : $obj->id_shop,
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : $obj->active,
            );
        } else {
            $categories = array();
            $content = array();
            foreach (Language::getLanguages(false) as $language) {
                $content[$language['id_lang']] = '';
            }
            $formValues[] = array(
                'id_everblock' => (!empty(Tools::getValue('id_everblock')))
                ? Tools::getValue('id_everblock')
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
                'only_home' => (!empty(Tools::getValue('only_home')))
                ? Tools::getValue('only_home')
                : '',
                'only_category' => (!empty(Tools::getValue('only_category')))
                ? Tools::getValue('only_category')
                : '',
                'position' => (!empty(Tools::getValue('position')))
                ? Tools::getValue('position')
                : '',
                'id_shop' => (!empty(Tools::getValue('id_shop')))
                ? Tools::getValue('id_shop')
                : '',
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : '',
            );
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
            $everblock_obj = new EverBlockClass(
                (int)Tools::getValue('id_everblock')
            );
            if (!$everblock_obj->delete()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t delete the current object');
            }
        }
        if (Tools::isSubmit('save') || Tools::isSubmit('stay')) {
            $everblock_obj = new EverBlockClass(
                (int)Tools::getValue('id_everblock')
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
            if (Tools::getValue('only_home')
                && Tools::getValue('only_category')
            ) {
                $this->errors[] = $this->l('"Only category" and "Only home" ae both selected');
            }
            if (Tools::getValue('categories')
                && !Validate::isArrayWithIds(Tools::getValue('categories'))
            ) {
                $this->errors[] = $this->l('Categories are not valid');
            }
            if (Tools::getValue('position')
                && !Validate::isUnsignedInt(Tools::getValue('position'))
            ) {
                $this->errors[] = $this->l('Position is not valid');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                $this->errors[] = $this->l('Active is not valid');
            }
            $everblock_obj = new EverBlockClass(
                (int)Tools::getValue('id_everblock')
            );
            $everblock_obj->name = Tools::getValue('name');
            $everblock_obj->id_shop = (int)$this->context->shop->id;
            $everblock_obj->id_hook = (int)Tools::getValue('id_hook');
            $everblock_obj->only_home = (bool)Tools::getValue('only_home');
            $everblock_obj->only_category = (bool)Tools::getValue('only_category');
            $everblock_obj->categories = json_encode(
                Tools::getValue('categories')
            );
            $everblock_obj->position = (int)Tools::getValue('position');
            $hook_name = Hook::getNameById((int)Tools::getValue('id_hook'));
            $everblock_obj->active = Tools::getValue('active');
            $everblock = Module::getInstanceByName('everblock');
            foreach (Language::getLanguages(false) as $language) {
                $everblock_obj->content[$language['id_lang']] = Tools::getValue('content_'.$language['id_lang']);
            }
            if (!count($this->errors)) {
                $everblock_obj->save();
                $everblock->registerHook(
                    $hook_name
                );
                Tools::clearSmartyCache();
                if ((bool)Tools::isSubmit('stay') === true) {
                    Tools::redirectAdmin(
                        self::$currentIndex
                        .'&updateeverblock=&id_everblock='
                        .(int)$everblock_obj->id
                        .'&token='
                        .$this->token
                    );
                }
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }
        }
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverBlock) {
            $everBlock = new EverBlockClass((int)$idEverBlock);

            if (!$everBlock->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverBlock) {
            $everBlock = new EverBlockClass((int)$idEverBlock);
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
        foreach (Tools::getValue($this->table.'Box') as $idEverBlock) {
            $everBlock = new EverBlockClass((int)$idEverBlock);
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
        foreach (Tools::getValue($this->table.'Box') as $idEverBlock) {
            $this->duplicate($idEverBlock);
        }
    }

    protected function duplicate($id)
    {
        $everBlock = new EverBlockClass((int)$id);
        $newBlock = new EverBlockClass();
        $newBlock->name = $everBlock->name;
        $newBlock->content = $everBlock->content;
        $newBlock->only_home = $everBlock->only_home;
        $newBlock->only_category = $everBlock->only_category;
        $newBlock->id_hook = $everBlock->id_hook;
        $newBlock->id_shop = $everBlock->id_shop;
        $newBlock->categories = $everBlock->categories;
        $newBlock->active = $everBlock->active;
        $newBlock->position = $everBlock->position;

        if (!$newBlock->save()) {
            $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
        }
    }

}
