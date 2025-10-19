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

require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockTools.php';
require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockFaq.php';
require_once _PS_MODULE_DIR_ . 'everblock/controllers/admin/EverblockConfirmationTrait.php';

use Everblock\Tools\Service\ShortcodeDocumentationProvider;

class AdminEverBlockFaqController extends ModuleAdminController
{
    use EverblockConfirmationTrait;

    private $html;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = true;
        $this->table = 'everblock_faq';
        $this->className = 'EverblockFaq';
        $this->context = Context::getContext();
        $this->identifier = 'id_everblock_faq';
        $this->name = 'AdminEverFaq';
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

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ],
        ];

        $this->fields_list = [
            'id_everblock_faq' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'tag_name' => [
                'title' => $this->l('FAQ tag'),
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
                'callback' => 'renderContentWithoutHtml',
            ],
            'position' => [
                'title' => $this->l('Position'),
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
            'date_add' => [
                'title' => $this->l('Date add'),
                'align' => 'left',
                'width' => 'auto',
            ],
            'date_upd' => [
                'title' => $this->l('Date upd'),
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
            'Modules.Everblock.Admineverblockfaqcontroller'
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
        $this->toolbar_title = $this->l('Registered FAQ');
        if (Tools::getValue('clearcache')) {
            Tools::clearAllCache();
            Tools::redirectAdmin(self::$currentIndex . '&cachecleared=1&token=' . $this->token);
        }
        if (Tools::getValue('cachecleared')) {
            $this->confirmations[] = $this->l('Cache has been cleared');
        }
        $lists = parent::renderList();

        $moduleInstance = Module::getInstanceByName('everblock');
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

    public function renderContentWithoutHtml($value, $row)
    {
        $cleanContent = trim(strip_tags($value));

        if (Tools::strlen($cleanContent) > 120) {
            $cleanContent = Tools::substr($cleanContent, 0, 117) . '...';
        }

        return $cleanContent;
    }

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new FAQ.');
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
                    'label' => $this->l('FAQ tag name'),
                    'desc' => $this->l('All FAQs with the same tag will be grouped together'),
                    'hint' => $this->l('Enter a simple word, without spaces or special characters'),
                    'required' => true,
                    'name' => 'tag_name',
                    'lang' => false,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('FAQ element title'),
                    'desc' => $this->l('This title will be the title of the FAQ tab'),
                    'hint' => $this->l('Leaving blank will not display the FAQ'),
                    'required' => false,
                    'name' => 'title',
                    'lang' => true,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('FAQ element content'),
                    'desc' => $this->l('This content will be displayed under the FAQ title'),
                    'hint' => $this->l('Leaving blank will not display the FAQ'),
                    'required' => false,
                    'name' => 'content',
                    'autoload_rte' => true,
                    'lang' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('FAQ position'),
                    'desc' => $this->l('Enter FAQ position number'),
                    'hint' => $this->l('FAQs will be ordered using this number'),
                    'required' => false,
                    'name' => 'position',
                    'lang' => false,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'desc' => $this->l('Enable this FAQ ?'),
                    'hint' => $this->l('Only active FAQs will be shown'),
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
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::getIsset('duplicate'.$this->table)) {
            $this->duplicate(
                (int)Tools::getValue($this->identifier)
            );
        }
        if (Tools::isSubmit('save') || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            $everblock_obj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            $everblock_obj->tag_name = str_replace(' ', '', Tools::getValue('tag_name'));
            $everblock_obj->position = (int) Tools::getValue('position');
            $everblock_obj->active = (int) Tools::getValue('active');
            $everblock_obj->id_shop = (int) $this->context->shop->id;

            $defaultLanguageId = (int) Configuration::get('PS_LANG_DEFAULT');
            $hasDefaultTitle = false;
            $hasDefaultContent = false;

            foreach (Language::getLanguages(false) as $language) {
                $langId = (int) $language['id_lang'];

                $titleValue = Tools::getValue('title_' . $langId, '');
                if ($titleValue === false) {
                    $titleValue = '';
                }
                if ($titleValue !== '') {
                    $everblock_obj->title[$langId] = $titleValue;
                }
                if ($langId === $defaultLanguageId && $titleValue !== '') {
                    $hasDefaultTitle = true;
                }

                $contentKey = 'content_' . $langId;
                $contentValue = Tools::getValue($contentKey, '');
                if (is_string($contentValue) && $contentValue !== '') {
                    $convertedContent = EverblockTools::convertImagesToWebP($contentValue);
                    $everblock_obj->content[$langId] = $convertedContent;
                }
                if ($langId === $defaultLanguageId && is_string($contentValue) && $contentValue !== '') {
                    $hasDefaultContent = true;
                }
            }

            if (!$hasDefaultTitle) {
                $this->errors[] = $this->l('Title is required for the default language.');
            }

            if (!$hasDefaultContent) {
                $this->errors[] = $this->l('Content is required for the default language.');
            }

            if (!Tools::getValue('position')) {
                $everblock_obj->position = 0;
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
        if (Tools::isSubmit('delete' . $this->table)) {
            $faq = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            if (!$faq->delete()) {
                $this->errors[] = $this->l('An error has occurred : Can\'t delete the current object');
            }
        }
        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
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
                    $faqs = Tools::getValue($this->table . 'Box');
                    if (is_array($faqs)) {
                        foreach ($faqs as $faqId) {
                            $faq = new $this->className(
                                (int) $faqId
                            );
                            if (!count($this->errors) && $faq->delete()) {
                            } else {
                                $this->errors[] = $this->l('Errors on deleting object ') . $faqId;
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

    protected function duplicate($id)
    {
        $oldObj = new $this->className((int) $id);
        $newObj = new $this->className();
        $newObj->id_shop = $oldObj->id_shop;
        $newObj->tag_name = $oldObj->tag_name;
        $newObj->active = $oldObj->active;
        $newObj->position = $oldObj->position;
        $newObj->title = $oldObj->title;
        $newObj->content = $oldObj->content;

        if (!$newObj->save()) {
            $this->errors[] = $this->l('An error has occurred: Can\'t duplicate the current object');
        }
    }
}
