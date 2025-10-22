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
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

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
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $m = Module::getInstanceByName('everblock');
        $this->context->smarty->assign([
            $m->name . '_version' => $m->version,
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
        $everblock_obj = $this->loadObject(true);
        $dataProvider = $this->getEverBlockFormDataProvider();
        $legacyChoices = $dataProvider->getLegacyChoices();
        $docInputs = $dataProvider->getDocumentationInputs();

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
                            'query' => $legacyChoices['hooks'],
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
                            'query' => $legacyChoices['devices'],
                            'id' => 'id_device',
                            'name' => 'name',
                        ],
                        'tab' => 'targeting',
                    ],
                    [
                        'type' => 'group',
                        'label' => $this->l('Group access'),
                        'name' => 'groupBox',
                        'values' => $legacyChoices['groups'],
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
                            'query' => $legacyChoices['categories'],
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
                            'query' => $legacyChoices['manufacturers'],
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
                            'query' => $legacyChoices['suppliers'],
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
                            'query' => $legacyChoices['cms_categories'],
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
                            'query' => $legacyChoices['bootstrap'],
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
            'fields_value' => $this->getConfigFormValues($everblock_obj),
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

        $this->context->smarty->assign([
            'everblock_notifications' => $notifications,
            'everblock_form' => $helper->generateForm($fields_form),
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

    protected function getConfigFormValues($obj)
    {
        $dataProvider = $this->getEverBlockFormDataProvider();

        return $dataProvider->getLegacyFormValues($obj, $_POST);
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
            $handler = $this->getEverBlockFormHandler();
            $result = $handler->handle($_POST);

            if (!$result->isSuccessful()) {
                $this->errors = array_merge($this->errors, $result->getErrors());
            } else {
                $blockId = (int) $result->getBlockId();
                if (Tools::isSubmit('stay')) {
                    Tools::redirectAdmin(
                        self::$currentIndex
                        . '&updateeverblock=&' . $this->identifier . '=' . $blockId
                        . '&token=' . $this->token
                    );
                } else {
                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
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

    private function getEverBlockFormDataProvider()
    {
        return $this->getSymfonyContainer()->get('everblock.form.data_provider.ever_block');
    }

    private function getEverBlockFormHandler()
    {
        return $this->getSymfonyContainer()->get('everblock.form.handler.ever_block');
    }

    private function getSymfonyContainer()
    {
        return SymfonyContainer::getInstance();
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
