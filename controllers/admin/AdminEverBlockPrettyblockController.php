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

class AdminEverBlockPrettyblockController extends ModuleAdminController
{
    private $html;
    private $prettyblocksColumns = [];
    private $hookField;
    private $hookFilterKey;
    private $nameField;
    private $nameFilterKey;
    private $hasShopColumn = false;
    private $hasLangColumn = false;
    private $hasPositionColumn = false;
    private $hasActiveColumn = false;
    private $hasDateAddColumn = false;
    private $hasDateUpdColumn = false;

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
        $this->lang = false;
        $this->table = 'prettyblocks';
        $this->className = 'PrettyBlocksModel';
        $this->context = Context::getContext();
        $this->identifier = 'id_prettyblocks';
        $this->name = 'AdminEverBlockPrettyblock';
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

        $this->loadPrettyblocksSchema();
        $this->configurePrettyblocksList();

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?'),
            ],
        ];

        $this->colorOnBackground = true;
        EverblockTools::checkAndFixDatabase();
        parent::__construct();
    }

    private function loadPrettyblocksSchema(): void
    {
        $columns = [];
        try {
            $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'prettyblocks`');
        } catch (Exception $e) {
            $columns = [];
        }

        foreach ($columns as $column) {
            if (!empty($column['Field'])) {
                $this->prettyblocksColumns[] = $column['Field'];
            }
        }

        $this->hasShopColumn = $this->hasColumn('id_shop');
        $this->hasLangColumn = $this->hasColumn('id_lang');
        $this->hasPositionColumn = $this->hasColumn('position');
        $this->hasActiveColumn = $this->hasColumn('active');
        $this->hasDateAddColumn = $this->hasColumn('date_add');
        $this->hasDateUpdColumn = $this->hasColumn('date_upd');

        if ($this->hasColumn('id_hook')) {
            $this->_select = 'a.*, h.title AS hook_title';
            $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON (h.`id_hook` = a.`id_hook`)';
            $this->hookField = 'hook_title';
            $this->hookFilterKey = 'h!title';
        } elseif ($this->hasColumn('hook')) {
            $this->hookField = 'hook';
            $this->hookFilterKey = 'a!hook';
        }

        $this->nameField = $this->resolveNameField();
        if ($this->nameField) {
            $this->nameFilterKey = 'a!' . $this->nameField;
        }
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->prettyblocksColumns, true);
    }

    private function resolveNameField(): ?string
    {
        $candidates = [
            'name',
            'title',
            'zone_name',
            'code',
        ];

        foreach ($candidates as $candidate) {
            if ($this->hasColumn($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function configurePrettyblocksList(): void
    {
        $this->_orderBy = 'id_prettyblocks';
        $this->_orderWay = 'DESC';

        $fields = [
            'id_prettyblocks' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!id_prettyblocks',
            ],
        ];

        if ($this->hookField) {
            $fields[$this->hookField] = [
                'title' => $this->l('Hook'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => $this->hookFilterKey,
            ];
        }

        if ($this->nameField) {
            $fields[$this->nameField] = [
                'title' => $this->l('Name'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => $this->nameFilterKey,
            ];
        }

        if ($this->hasShopColumn) {
            $fields['id_shop'] = [
                'title' => $this->l('Shop'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!id_shop',
            ];
        }

        if ($this->hasLangColumn) {
            $fields['id_lang'] = [
                'title' => $this->l('Language'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!id_lang',
            ];
        }

        if ($this->hasPositionColumn) {
            $fields['position'] = [
                'title' => $this->l('Position'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!position',
            ];
        }

        if ($this->hasActiveColumn) {
            $fields['active'] = [
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => true,
                'search' => true,
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!active',
            ];
        }

        if ($this->hasDateAddColumn) {
            $fields['date_add'] = [
                'title' => $this->l('Date added'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!date_add',
            ];
        }

        if ($this->hasDateUpdColumn) {
            $fields['date_upd'] = [
                'title' => $this->l('Date updated'),
                'align' => 'left',
                'width' => 'auto',
                'search' => true,
                'orderby' => true,
                'filter_key' => 'a!date_upd',
            ];
        }

        $this->fields_list = $fields;
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            'Modules.Everblock.Admineverblockprettyblockcontroller'
        );
    }

    public function initPageHeaderToolbar()
    {
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
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::getIsset('duplicate' . $this->table)) {
            $this->duplicatePrettyblock(
                (int) Tools::getValue($this->identifier)
            );
        }

        if (Tools::isSubmit('submitConvertPrettyblocksShop')) {
            $this->processDuplicateShop();
        }

        if (Tools::isSubmit('submitConvertPrettyblocksLang')) {
            $this->processDuplicateLang();
        }

        if (Tools::getValue('clearcache')) {
            Tools::clearAllCache();
            Tools::redirectAdmin(self::$currentIndex . '&cachecleared=1&token=' . $this->token);
        }
        if (Tools::getValue('cachecleared')) {
            $this->confirmations[] = $this->l('Cache has been cleared');
        }
    }

    public function renderList()
    {
        $this->addRowAction('delete');
        $this->addRowAction('duplicate');
        $this->toolbar_title = $this->l('PrettyBlocks management');

        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $this->processBulkDelete();
        }

        $lists = parent::renderList();
        $lists .= $this->renderConversionForms();

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

    private function renderConversionForms(): string
    {
        $forms = '';
        $action = self::$currentIndex . '&token=' . $this->token;

        if ($this->hasShopColumn) {
            $shops = Shop::getShops(false);
            $options = '';
            foreach ($shops as $shop) {
                $selectedFrom = (int) $shop['id_shop'] === (int) $this->context->shop->id ? ' selected="selected"' : '';
                $options .= sprintf(
                    '<option value="%d"%s>%s</option>',
                    (int) $shop['id_shop'],
                    $selectedFrom,
                    Tools::safeOutput((string) $shop['name'])
                );
            }

            $forms .= '<div class="panel">';
            $forms .= '<h3><i class="icon-copy"></i> ' . $this->l('Duplicate between shops') . '</h3>';
            $forms .= '<form method="post" action="' . $action . '">';
            $forms .= '<div class="form-group">';
            $forms .= '<label class="control-label">' . $this->l('From shop') . '</label>';
            $forms .= '<select name="prettyblocks_from_shop" class="form-control">' . $options . '</select>';
            $forms .= '</div>';
            $forms .= '<div class="form-group">';
            $forms .= '<label class="control-label">' . $this->l('To shop') . '</label>';
            $forms .= '<select name="prettyblocks_to_shop" class="form-control">' . $options . '</select>';
            $forms .= '</div>';
            $forms .= '<p class="help-block">' . $this->l('All PrettyBlocks from the source shop will be duplicated to the destination shop.') . '</p>';
            $forms .= '<button type="submit" name="submitConvertPrettyblocksShop" class="btn btn-default">'
                . $this->l('Duplicate to shop') . '</button>';
            $forms .= '</form>';
            $forms .= '</div>';
        }

        if ($this->hasLangColumn) {
            $languages = Language::getLanguages(false);
            $options = '';
            foreach ($languages as $language) {
                $selectedFrom = (int) $language['id_lang'] === (int) $this->context->language->id ? ' selected="selected"' : '';
                $options .= sprintf(
                    '<option value="%d"%s>%s</option>',
                    (int) $language['id_lang'],
                    $selectedFrom,
                    Tools::safeOutput((string) $language['name'])
                );
            }

            $forms .= '<div class="panel">';
            $forms .= '<h3><i class="icon-copy"></i> ' . $this->l('Duplicate between languages') . '</h3>';
            $forms .= '<form method="post" action="' . $action . '">';
            $forms .= '<div class="form-group">';
            $forms .= '<label class="control-label">' . $this->l('From language') . '</label>';
            $forms .= '<select name="prettyblocks_from_lang" class="form-control">' . $options . '</select>';
            $forms .= '</div>';
            $forms .= '<div class="form-group">';
            $forms .= '<label class="control-label">' . $this->l('To language') . '</label>';
            $forms .= '<select name="prettyblocks_to_lang" class="form-control">' . $options . '</select>';
            $forms .= '</div>';
            $forms .= '<p class="help-block">' . $this->l('All PrettyBlocks from the source language will be duplicated to the destination language.') . '</p>';
            $forms .= '<button type="submit" name="submitConvertPrettyblocksLang" class="btn btn-default">'
                . $this->l('Duplicate to language') . '</button>';
            $forms .= '</form>';
            $forms .= '</div>';
        }

        return $forms;
    }

    private function processDuplicateShop(): void
    {
        if (!$this->hasShopColumn) {
            $this->errors[] = $this->l('Shop duplication is not available.');
            return;
        }

        $fromShop = (int) Tools::getValue('prettyblocks_from_shop');
        $toShop = (int) Tools::getValue('prettyblocks_to_shop');

        if (!Validate::isUnsignedId($fromShop) || !Validate::isUnsignedId($toShop)) {
            $this->errors[] = $this->l('Invalid shop selection.');
            return;
        }

        if ($fromShop === $toShop) {
            $this->errors[] = $this->l('Source and destination shops must be different.');
            return;
        }

        $blocks = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'prettyblocks` WHERE `id_shop` = ' . (int) $fromShop
        );
        if (!$blocks) {
            $this->errors[] = $this->l('No PrettyBlocks found for the selected shop.');
            return;
        }

        $inserted = 0;
        foreach ($blocks as $block) {
            unset($block[$this->identifier]);
            $block['id_shop'] = $toShop;
            if ($this->hasDateAddColumn) {
                $block['date_add'] = date('Y-m-d H:i:s');
            }
            if ($this->hasDateUpdColumn) {
                $block['date_upd'] = date('Y-m-d H:i:s');
            }
            if (Db::getInstance()->insert('prettyblocks', $block)) {
                $inserted++;
            }
        }

        if ($inserted === 0) {
            $this->errors[] = $this->l('An error occurred while duplicating shops.');
            return;
        }

        $this->confirmations[] = sprintf(
            $this->l('%d PrettyBlocks duplicated to the selected shop.'),
            $inserted
        );
    }

    private function processDuplicateLang(): void
    {
        if (!$this->hasLangColumn) {
            $this->errors[] = $this->l('Language duplication is not available.');
            return;
        }

        $fromLang = (int) Tools::getValue('prettyblocks_from_lang');
        $toLang = (int) Tools::getValue('prettyblocks_to_lang');

        if (!Validate::isUnsignedId($fromLang) || !Validate::isUnsignedId($toLang)) {
            $this->errors[] = $this->l('Invalid language selection.');
            return;
        }

        if ($fromLang === $toLang) {
            $this->errors[] = $this->l('Source and destination languages must be different.');
            return;
        }

        $blocks = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'prettyblocks` WHERE `id_lang` = ' . (int) $fromLang
        );
        if (!$blocks) {
            $this->errors[] = $this->l('No PrettyBlocks found for the selected language.');
            return;
        }

        $inserted = 0;
        foreach ($blocks as $block) {
            unset($block[$this->identifier]);
            $block['id_lang'] = $toLang;
            if ($this->hasDateAddColumn) {
                $block['date_add'] = date('Y-m-d H:i:s');
            }
            if ($this->hasDateUpdColumn) {
                $block['date_upd'] = date('Y-m-d H:i:s');
            }
            if (Db::getInstance()->insert('prettyblocks', $block)) {
                $inserted++;
            }
        }

        if ($inserted === 0) {
            $this->errors[] = $this->l('An error occurred while duplicating languages.');
            return;
        }

        $this->confirmations[] = sprintf(
            $this->l('%d PrettyBlocks duplicated to the selected language.'),
            $inserted
        );
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idObj) {
            $prettyBlock = new $this->className((int) $idObj);
            if (!$prettyBlock->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    private function duplicatePrettyblock(int $id): void
    {
        $db = Db::getInstance();
        $block = $db->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'prettyblocks` WHERE `' . pSQL($this->identifier) . '` = ' . (int) $id
        );

        if (!$block) {
            $this->errors[] = $this->l('Unable to find the selected PrettyBlocks entry.');
            return;
        }

        unset($block[$this->identifier]);

        if ($this->nameField && isset($block[$this->nameField])) {
            $block[$this->nameField] = $block[$this->nameField] . ' ' . $this->l('(copy)');
        }

        if ($this->hasDateAddColumn) {
            $block['date_add'] = date('Y-m-d H:i:s');
        }

        if ($this->hasDateUpdColumn) {
            $block['date_upd'] = date('Y-m-d H:i:s');
        }

        if (!$db->insert('prettyblocks', $block)) {
            $this->errors[] = $this->l('An error has occurred: Can\'t duplicate the current object');
        }
    }
}
