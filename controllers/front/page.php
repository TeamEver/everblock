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

class EverblockPageModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        $this->page_name = 'everblock-page';
    }

    public function initContent()
    {
        parent::initContent();

        $pageId = (int) Tools::getValue('id_everblock_page');
        $rewrite = Tools::getValue('rewrite');
        $customerGroups = EverblockPage::getCustomerGroups($this->context);

        $page = EverblockPage::getById($pageId, (int) $this->context->language->id, (int) $this->context->shop->id);
        if (!$page || !$page->active || !EverblockPage::isGroupAllowed($page, $customerGroups)) {
            Tools::redirect('index.php?controller=404');

            return;
        }

        $expectedRewrite = $page->link_rewrite[(int) $this->context->language->id] ?? '';
        if ($expectedRewrite && $rewrite && $rewrite !== $expectedRewrite) {
            Tools::redirect($this->context->link->getModuleLink(
                $this->module->name,
                'page',
                [
                    'id_everblock_page' => (int) $page->id,
                    'rewrite' => $expectedRewrite,
                ]
            ));
        }

        $metaDescription = $page->meta_description[(int) $this->context->language->id] ?? '';

        $renderedContent = $page->content[(int) $this->context->language->id] ?? '';
        if ($this->isPrettyBlocksEnabled()) {
            $renderedContent = $this->context->smarty->fetch('string:' . $renderedContent);
        }

        $this->context->smarty->assign([
            'everblock_page' => $page,
            'everblock_page_content' => $renderedContent,
            'everblock_page_image' => $page->cover_image ? _MODULE_DIR_ . 'everblock/views/img/pages/' . $page->cover_image : '',
            'everblock_lang_id' => (int) $this->context->language->id,
        ]);

        $this->setTemplate('module:everblock/views/templates/front/page.tpl');

        $this->setTemplateMeta($page->title[(int) $this->context->language->id] ?? '', $metaDescription);
    }

    protected function setTemplateMeta(string $title, string $description): void
    {
        $this->context->smarty->assign('meta_description', $description);
        $this->context->smarty->assign('meta_title', $title);
        $this->context->smarty->assign('meta_robots', 'index,follow');
    }

    protected function isPrettyBlocksEnabled(): bool
    {
        return (bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) Everblock\Tools\Service\EverblockTools::moduleDirectoryExists('prettyblocks') === true;
    }
}
