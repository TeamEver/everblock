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

class EverblockPagesModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $customerGroups = EverblockPage::getCustomerGroups($this->context);
        $pages = EverblockPage::getPages(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            true,
            $customerGroups
        );

        $pageLinks = [];
        foreach ($pages as $page) {
            $pageLinks[(int) $page->id] = $this->context->link->getModuleLink(
                $this->module->name,
                'page',
                [
                    'id_everblock_page' => (int) $page->id,
                    'rewrite' => $page->link_rewrite[(int) $this->context->language->id] ?? '',
                ]
            );
        }

        $this->context->smarty->assign([
            'everblock_pages' => $pages,
            'everblock_page_links' => $pageLinks,
            'everblock_lang_id' => (int) $this->context->language->id,
        ]);

        $this->setTemplate('module:everblock/views/templates/front/pages.tpl');
    }
}
