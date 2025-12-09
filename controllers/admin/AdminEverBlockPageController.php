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
use Everblock\Tools\Service\EverblockCache;

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
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $this->page_header_toolbar_btn['configuration'] = [
            'href' => $module_link,
            'desc' => $this->l('Configuration'),
            'icon' => 'process-icon-save',
        ];

        parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return '';
        }

        $coverImage = false;
        if ($obj->id && $obj->cover_image) {
            $coverImage = _MODULE_DIR_ . 'everblock/views/img/pages/' . $obj->cover_image;
        }

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
                    'type' => 'text',
                    'label' => $this->l('Friendly URL'),
                    'name' => 'link_rewrite',
                    'lang' => true,
                    'hint' => $this->l('If empty, it will be generated from the page name'),
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
            $page->active = (int) Tools::getValue('active');
            $page->groups = json_encode($this->getSelectedGroups());

            foreach (Language::getLanguages(false) as $language) {
                $langId = (int) $language['id_lang'];
                $page->name[$langId] = Tools::getValue('name_' . $langId);
                $page->title[$langId] = Tools::getValue('title_' . $langId);
                $page->meta_description[$langId] = Tools::getValue('meta_description_' . $langId);
                $rewrite = Tools::getValue('link_rewrite_' . $langId);
                if (!$rewrite) {
                    $rewrite = Tools::getValue('name_' . $langId);
                }
                $page->link_rewrite[$langId] = Tools::link_rewrite($rewrite);
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
                    EverblockCache::cacheDropByPattern('EverblockPage_');
                    Tools::clearAllCache();
                }
            }
        }

        if (Tools::isSubmit('deleteeverblock_page') || Tools::isSubmit('submitBulkdeleteeverblock_page')) {
            EverblockCache::cacheDropByPattern('EverblockPage_');
            Tools::clearAllCache();
        }

        if (Tools::isSubmit('status' . $this->table)
            || Tools::isSubmit('submitBulkenableSelection')
            || Tools::isSubmit('submitBulkdisableSelection')
        ) {
            EverblockCache::cacheDropByPattern('EverblockPage_');
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

    protected function handleImageUpload()
    {
        if (!isset($_FILES['cover_image']) || empty($_FILES['cover_image']['tmp_name'])) {
            return false;
        }

        $uploader = new HelperUploader('cover_image');
        $uploader->setAcceptTypes(['jpg', 'jpeg', 'png', 'gif']);
        $uploader->setSavePath(_PS_MODULE_DIR_ . 'everblock/views/img/pages/');
        $uploader->setMaxSize((int) Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024);
        $files = $uploader->process($_FILES['cover_image']);

        if (!is_array($files) || empty($files[0]['save_path'])) {
            $this->errors[] = $this->l('Unable to upload the featured image.');

            return false;
        }

        return basename($files[0]['save_path']);
    }
}
