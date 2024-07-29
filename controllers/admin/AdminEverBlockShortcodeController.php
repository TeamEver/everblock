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
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockShortcode.php';

class AdminEverBlockShortcodeController extends ModuleAdminController
{
    private $html;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = true;
        $this->table = 'everblock_shortcode';
        $this->className = 'EverblockShortcode';
        $this->context = Context::getContext();
        $this->identifier = 'id_everblock_shortcode';
        $this->name = 'AdminEverBlockShortcode';
        EverblockTools::checkAndFixDatabase();
        $module_link  = 'index.php?controller=AdminModules&configure=everblock&token=';
        $module_link .= Tools::getAdminTokenLite('AdminModules');
        $m = Module::getInstanceByName('everblock');
        $this->context->smarty->assign([
            $m->name . '_version' => $m->version,
            'module_link' => $module_link,
            'everblock_dir' => _MODULE_DIR_ . '/everblock/',
        ]);

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ],
        ];

        $this->fields_list = [
            'id_everblock_shortcode' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'shortcode' => [
                'title' => $this->l('Shortcode'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'title' => [
                'title' => $this->l('Title'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'content' => [
                'title' => $this->l('Content'),
                'align' => 'left',
                'width' => 'auto',
            ],
        ];

        $this->colorOnBackground = true;
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
            'Modules.Everblock.Admineverblockshortcodecontroller'
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
        $this->html = '';

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->toolbar_title = $this->l('Registered shortcodes');
        if (Tools::getValue('clearcache')) {
            Tools::clearAllCache();
            EverblockCache::cleanThemeCache();
            Tools::redirectAdmin(self::$currentIndex . '&cachecleared=1&token=' . $this->token);
        }
        if (Tools::getValue('cachecleared')) {
            $this->confirmations[] = $this->l('Cache has been cleared');
        }
        $lists = parent::renderList();

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
            $this->errors[] = $this->l('You have to select a shop before creating or editing new shortcode.');
        }

        if (count($this->errors)) {
            return false;
        }

        $this->fields_form = [
            'submit' => [
                'name' => 'save',
                'title' => $this->l('Save'),
                'class' => 'button btn btn-success pull-right',
            ],
            'buttons' => [
                'save-and-stay' => [
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd' . $this->table . 'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save',
                ],
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'desc' => $this->l('Title is just a reminder, won\'t be shown'),
                    'hint' => $this->l('Will be only shown on admin list'),
                    'required' => true,
                    'name' => 'title',
                    'lang' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Shortcode, no space allowed'),
                    'desc' => $this->l('Please type shortcode with brackets like [shortcode], no space allowed'),
                    'hint' => $this->l('Type shortcode like [shortcode], no space allowed'),
                    'required' => true,
                    'name' => 'shortcode',
                    'lang' => false,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Shortcode content'),
                    'desc' => $this->l('Shortcode will be changed to this value'),
                    'hint' => $this->l('Module will auto translate shortcode using this value'),
                    'required' => true,
                    'name' => 'content',
                    'lang' => true,
                ],
            ],
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::isSubmit('save') || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            if (!Tools::getValue('title')
                || !Validate::isGenericName(Tools::getValue('title'))
            ) {
                 $this->errors[] = $this->l('Title is not valid or missing');
            }
            $everblock_obj = new EverblockShortcode(
                (int) $this->identifier
            );
            $everblock_obj->title = Tools::getValue('title');
            $everblock_obj->shortcode = Tools::getValue('shortcode');
            $everblock_obj->id_shop = (int) Context::getContext()->shop->id;
            foreach (Language::getLanguages(false) as $language) {
                if (!Tools::getValue('content_' . $language['id_lang'])
                ) {
                    $this->errors[] = $this->l('Content is missing for lang ') . $language['id_lang'];
                } else {
                    $everblock_obj->content[$language['id_lang']] = Tools::getValue('content_' . $language['id_lang']);
                }
            }

            if (!count($this->errors)) {
                if ($everblock_obj->save()) {
                    if (Tools::isSubmit('save')) {
                        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
                    }
                } else {
                    $this->errors[] = $this->l('Can\'t update the current object');
                }
            }
        }

        if (Tools::isSubmit('deleteever_shortcodes')) {
            $shortcode = new EverblockShortcode(
                (int) Tools::getValue('id_everblock_shortcode')
            );

            if (!$shortcode->delete()) {
                $this->errors[] = $this->l('An error has occurred : Can\'t delete the current object');
            }
        }

        if (Tools::isSubmit('submitBulkdeleteever_shortcodes')) {
            $this->processBulkDelete();
        }
    }

    protected function processBulkDelete()
    {
        if ($this->access('delete')) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                $object = new $this->className();

                if (isset($object->noZeroObject)) {
                    $this->errors[] = $this->l('You need at least one object.');
                } else {
                    $shortcode = Tools::getValue($this->table . 'Box');
                    if (is_array($shortcode)) {
                        foreach ($shortcode as $id_everblock_shortcode) {
                            $shortcode = new EverblockShortcode(
                                (int) $id_everblock_shortcode
                            );
                            if (!count($this->errors) && $shortcode->delete()) {
                            } else {
                                $this->errors[] = $this->l('Errors on deleting object ') . $id_everblock_shortcode;
                            }
                        }
                    }
                }
            } else {
                $this->errors[] = $this->l('You must select at least one element to delete.');
            }
        } else {
            $this->errors[] = $this->l('You do not have permission to delete this.');
        }
    }

    protected function displayError($message, $description = false)
    {
        /**
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }
}
