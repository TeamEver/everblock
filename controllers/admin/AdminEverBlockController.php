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
        $this->toolbar_title = $this->l('HTML blocks Configuration');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?')
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
        $hooks_list = Hook::getHooks(false, true);
        $categories_list = Category::getCategories(
            false,
            true,
            false
        );

        // Building the Add/Edit form
        $this->fields_form = array(
            'tinymce' => true,
            'description' => $this->l('Add a new block.'),
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Save'),
                'class' => 'button pull-right'
            ),
            'buttons' => array(
                'import' => array(
                    'name' => 'save_and_stay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save',
                    'title' => $this->l('Save & stay')
                ),
            ),
            'input' => array(
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
                    'label' => $this->l('Limit on categories ?'),
                    'desc' => $this->l('Only if chosen hook is on categories'),
                    'hint' => $this->l('Depends on previous setting'),
                    'name' => 'id_category',
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
        );
        $lists = parent::renderForm();

        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'/everblock/views/templates/admin/header.tpl'
        );
        $this->html .= $lists;
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $this->html .= Tools::displayError($error);
            }
        }
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'/everblock/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('deleteeverblock')) {
            $everblock_obj = new EverBlockClass(
                (int)Tools::getValue('id_everblock')
            );
            if (!$everblock_obj->delete()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t delete the current object');
            }
        }
        if (Tools::isSubmit('save') || Tools::isSubmit('save_and_stay')) {
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
            if (Tools::getValue('id_category')
                && !Validate::isUnsignedInt(Tools::getValue('id_category'))
            ) {
                $this->errors[] = $this->l('Category is not valid');
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
            $everblock_obj->only_home = (int)Tools::getValue('only_home');
            $everblock_obj->only_category = (int)Tools::getValue('only_category');
            $everblock_obj->id_category = (int)Tools::getValue('id_category');
            $everblock_obj->position = (int)Tools::getValue('position');
            $hook_name = Hook::getNameById((int)Tools::getValue('id_hook'));
            $everblock_obj->active = Tools::getValue('active');
            $everblock = Module::getInstanceByName('everblock');
            foreach (Language::getLanguages(false) as $language) {
                $everblock_obj->content[$language['id_lang']] = Tools::getValue('content_'.$language['id_lang']);
            }
            if (!count($this->errors)) {
                if ($everblock_obj->save()) {
                    $everblock->registerHook(
                        $hook_name
                    );
                    Tools::clearSmartyCache();
                    if (Tools::isSubmit('save') === true) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    }
                    if (Tools::isSubmit('save_and_stay') === true) {
                        Tools::redirectAdmin(
                            self::$currentIndex
                            .'&updateeverblock=&id_everblock='
                            .(int)$everblock_obj->id
                            .'&token='
                            .$this->token
                        );
                    }
                } else {
                    $this->errors[] = $this->l('Can\'t update the current object');
                }
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
}
