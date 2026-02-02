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

require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockPage.php';

use Everblock\Tools\Service\EverblockTools;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;

class AdminEverBlockPageController extends ModuleAdminController
{
    protected $delete_mode = 'physical';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = true;
        $this->table = 'everblock_page';
        $this->className = 'EverblockPage';
        $this->identifier = 'id_everblock_page';
        $this->context = Context::getContext();
        $this->_defaultOrderBy = 'position';
        $this->_orderWay = 'ASC';

        if (!Tools::getIsset('module_name')) {
            $_GET['module_name'] = 'everblock';
            $_REQUEST['module_name'] = 'everblock';
        }

        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $moduleInstance = Module::getInstanceByName('everblock');
        $this->context->smarty->assign([
            $moduleInstance->name . '_version' => $moduleInstance->version,
            'module_name' => $moduleInstance->displayName,
            'module_link' => $module_link,
            'everblock_dir' => _MODULE_DIR_ . '/everblock/',
            'donation_link' => 'https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE',
            'everblock_shortcode_docs' => ShortcodeDocumentationProvider::getDocumentation($moduleInstance),
        ]);

        $this->_select = 'pl.name, pl.title';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'everblock_page_lang` pl ON (pl.`id_everblock_page` = a.`id_everblock_page` AND pl.`id_lang` = ' . (int) $this->context->language->id . ')';

        $this->fields_list = [
            'id_everblock_page' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 40,
            ],
            'name' => [
                'title' => $this->l('Name'),
                'align' => 'left',
            ],
            'title' => [
                'title' => $this->l('Meta title'),
                'align' => 'left',
            ],
            'position' => [
                'title' => $this->l('Position'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ],
            'id_shop' => [
                'title' => $this->l('Shop'),
                'align' => 'left',
            ],
            'active' => [
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'class' => 'fixed-width-sm',
            ],
            'date_add' => [
                'title' => $this->l('Date add'),
            ],
            'date_upd' => [
                'title' => $this->l('Date upd'),
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash',
            ],
        ];

        EverblockTools::checkAndFixDatabase();

        parent::__construct();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS(_PS_MODULE_DIR_ . 'everblock/views/css/ever.css');
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            'Modules.Everblock.Admineverblockpagecontroller'
        );
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new page'),
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
        $this->addRowAction('viewfront');

        if (Tools::getValue('clearcache')) {
            Tools::clearAllCache();
            Tools::redirectAdmin(self::$currentIndex . '&cachecleared=1&token=' . $this->token);
        }

        if (Tools::getValue('cachecleared')) {
            $this->confirmations[] = $this->l('Cache has been cleared');
        }

        $lists = parent::renderList();

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
            'everblock_show_hero' => false,
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

    public function displayViewfrontLink($token, $id, $name = null)
    {
        $pageId = (int) $id;
        if ($pageId <= 0) {
            return '';
        }

        $page = new $this->className($pageId, (int) $this->context->language->id);
        if (!$page->active) {
            return '';
        }

        $rewrite = trim((string) $page->link_rewrite);
        if ($rewrite === '') {
            return '';
        }

        $link = $this->context->link->getModuleLink(
            $this->module->name,
            'page',
            [
                'id_everblock_page' => (int) $page->id,
                'rewrite' => $rewrite,
            ]
        );

        $title = sprintf(
            $this->l('View "%s" guide on front office'),
            $page->title ?: $page->name
        );

        return sprintf(
            '<a class="btn btn-default" href="%s" title="%s" target="_blank">'
            . '<i class="icon-search"></i> %s'
            . '</a>',
            Tools::safeOutput($link),
            Tools::safeOutput($title),
            $this->l('View on front')
        );
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return '';
        }

        $coverImage = false;
        if ($obj->id && $obj->cover_image) {
            $coverImage = _PS_IMG_ . 'pages/' . $obj->cover_image;
        }

        $employeeChoices = $this->getEmployeeChoices();

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Page'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Enabled'),
                    'name' => 'active',
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
                    'label' => $this->l('Page name'),
                    'name' => 'name',
                    'lang' => true,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Meta title'),
                    'name' => 'title',
                    'lang' => true,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Meta description'),
                    'name' => 'meta_description',
                    'lang' => true,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Author'),
                    'name' => 'id_employee',
                    'required' => false,
                    'options' => [
                        'query' => $employeeChoices,
                        'id' => 'id_employee',
                        'name' => 'name',
                    ],
                    'desc' => $this->l('Select a PrestaShop employee to display as the guide author.'),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Short description'),
                    'name' => 'short_description',
                    'lang' => true,
                    'autoload_rte' => true,
                    'desc' => $this->l('Displayed on listing pages and as an optional intro on the page detail.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Friendly URL'),
                    'name' => 'link_rewrite',
                    'lang' => true,
                    'hint' => $this->l('If empty, it will be generated from the page name'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Position'),
                    'name' => 'position',
                    'class' => 'fixed-width-sm',
                    'desc' => $this->l('Lower numbers appear first in the listing. Left empty, the position will be set automatically.'),
                ],
                [
                    'type' => 'textarea',
                    'autoload_rte' => true,
                    'label' => $this->l('Content'),
                    'name' => 'content',
                    'lang' => true,
                    'required' => true,
                    'desc' => $this->l('Content can include PrettyBlocks zones when the module is installed.'),
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Featured image'),
                    'name' => 'cover_image',
                    'display_image' => true,
                    'image' => $coverImage,
                    'hint' => $this->l('Large hero image displayed on the front-office page'),
                ],
                [
                    'type' => 'group',
                    'label' => $this->l('Allowed customer groups'),
                    'name' => 'groupBox',
                    'values' => Group::getGroups($this->context->language->id),
                    'hint' => $this->l('If no group is selected, the page will be visible to everyone.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('submitAdd' . $this->table) || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            $page = new EverblockPage((int) Tools::getValue($this->identifier));
            $page->id_shop = (int) $this->context->shop->id;
            $authorId = (int) Tools::getValue('id_employee');
            $page->id_employee = $authorId > 0 ? $authorId : null;
            $page->active = (int) Tools::getValue('active');
            $page->groups = json_encode($this->getSelectedGroups());
            $positionInput = Tools::getValue('position');
            $page->position = ($positionInput === '' || $positionInput === null)
                ? EverblockPage::getNextPosition((int) $this->context->shop->id)
                : (int) $positionInput;

            foreach (Language::getLanguages(false) as $language) {
                $langId = (int) $language['id_lang'];
                $page->name[$langId] = Tools::getValue('name_' . $langId);
                $page->title[$langId] = Tools::getValue('title_' . $langId);
                $page->meta_description[$langId] = Tools::getValue('meta_description_' . $langId);
                $page->short_description[$langId] = Tools::getValue('short_description_' . $langId, false);
                $rewrite = Tools::getValue('link_rewrite_' . $langId);
                if (!$rewrite) {
                    $rewrite = Tools::getValue('name_' . $langId);
                }
                $page->link_rewrite[$langId] = method_exists('Tools', 'str2url')
                    ? Tools::str2url($rewrite)
                    : Tools::link_rewrite($rewrite);
                $page->content[$langId] = Tools::getValue('content_' . $langId, false);
            }

            $upload = $this->handleImageUpload();
            if ($upload !== false) {
                $page->cover_image = $upload;
            }

            if (!count($this->errors)) {
                if (!$page->save()) {
                    $this->errors[] = $this->l('Cannot save the page.');
                } else {
                    Cache::clean('EverblockPage_');
                    Tools::clearAllCache();
                }
            }
        }

        if (Tools::isSubmit('deleteeverblock_page') || Tools::isSubmit('submitBulkdeleteeverblock_page')) {
            Cache::clean('EverblockPage_');
            Tools::clearAllCache();
        }

        if (Tools::isSubmit('status' . $this->table)
            || Tools::isSubmit('submitBulkenableSelection')
            || Tools::isSubmit('submitBulkdisableSelection')
        ) {
            Cache::clean('EverblockPage_');
            Tools::clearAllCache();
        }
    }

    protected function getSelectedGroups(): array
    {
        $groups = Tools::getValue('groupBox');
        if (!is_array($groups)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $groups)));
    }

    protected function getEmployeeChoices(): array
    {
        $choices = [
            [
                'id_employee' => 0,
                'name' => $this->l('No author'),
            ],
        ];

        $employees = Employee::getEmployees((int) $this->context->language->id, true);
        foreach ($employees as $employee) {
            $choices[] = [
                'id_employee' => (int) $employee['id_employee'],
                'name' => trim($employee['firstname'] . ' ' . $employee['lastname']),
            ];
        }

        return $choices;
    }

    protected function handleImageUpload()
    {
        if (!isset($_FILES['cover_image']) || empty($_FILES['cover_image']['tmp_name'])) {
            return false;
        }

        $destination = _PS_IMG_DIR_ . 'pages/';
        if (!is_dir($destination)) {
            @mkdir($destination, 0755, true);
        }

        $uploader = new HelperUploader('cover_image');
        $uploader->setAcceptTypes(['jpg', 'jpeg', 'png', 'gif']);
        $uploader->setSavePath($destination);
        $uploader->setMaxSize((int) Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024);
        $files = $uploader->process($_FILES['cover_image']);

        if (!is_array($files) || empty($files[0]['save_path'])) {
            $this->errors[] = $this->l('Unable to upload the featured image.');

            return false;
        }

        return basename($files[0]['save_path']);
    }
}
