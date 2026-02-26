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
    /** @var EverblockPage|null */
    protected $everblockPage;

    public function init()
    {
        parent::init();
        $this->page_name = 'everblock-page';
    }

    public function initContent()
    {
        parent::initContent();

        $rewrite = Tools::getValue('rewrite');
        $customerGroups = EverblockPage::getCustomerGroups($this->context);

        $page = $this->ensureEverblockPage($customerGroups);
        if (!$page) {
            Tools::redirect('index.php?controller=404');

            return;
        }

        $expectedRewrite = $page->link_rewrite;
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

        $metaTitle = isset($page->meta_title) && $page->meta_title ? $page->meta_title : $page->title;
        $metaDescription = $page->meta_description ?: ($page->short_description ?? '');

        $renderedContent = $page->content;
        $isPrettyBlocksEnabled = $this->isPrettyBlocksEnabled();

        $pages = EverblockPage::getPages(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            true,
            $customerGroups
        );

        $pageLinks = [];
        foreach ($pages as $pageItem) {
            $pageLinks[(int) $pageItem->id] = $this->context->link->getModuleLink(
                $this->module->name,
                'page',
                [
                    'id_everblock_page' => (int) $pageItem->id,
                    'rewrite' => $pageItem->link_rewrite,
                ]
            );
        }

        $this->everblockPage = $page;

        $this->context->smarty->assign([
            'everblock_page' => $page,
            'everblock_page_content' => $renderedContent,
            'everblock_page_image' => $page->cover_image
                ? $this->context->link->getMediaLink(_PS_IMG_ . 'pages/' . $page->cover_image)
                : '',
            'everblock_structured_data' => $this->buildItemListStructuredData($pages, $pageLinks),
            'everblock_prettyblocks_enabled' => $isPrettyBlocksEnabled,
            'everblock_prettyblocks_zone_name' => $isPrettyBlocksEnabled ? 'everblock_page_zone_' . (int) $page->id : '',
        ]);

        $this->setTemplate('module:everblock/views/templates/front/page.tpl');

        $this->setTemplateMeta($metaTitle, $metaDescription);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->trans('Guides et tutoriels', [], 'Modules.Everblock.Front'),
            'url' => $this->context->link->getModuleLink($this->module->name, 'pages'),
        ];

        if ($this->ensureEverblockPage() instanceof EverblockPage) {
            $breadcrumb['links'][] = [
                'title' => $this->everblockPage->title,
                'url' => '',
            ];
        }

        return $breadcrumb;
    }

    protected function setTemplateMeta(string $title, string $description): void
    {
        $this->meta_title = $title;
        $this->meta_description = $description;
        $this->context->smarty->assign([
            'meta_description' => $description,
            'meta_title' => $title,
            'meta_robots' => 'index,follow',
            'canonical' => $this->getCanonicalURL(),
        ]);

        $page = $this->context->controller->getTemplateVarPage();
        $page['meta']['title'] = $title;
        $page['meta']['description'] = $description;
        $page['meta']['robots'] = 'index,follow';
        $page['canonical'] = $this->getCanonicalURL();

        $this->context->smarty->assign('page', $page);
    }

    public function getCanonicalURL()
    {
        if ($this->everblockPage instanceof EverblockPage) {
            return $this->context->link->getModuleLink(
                $this->module->name,
                'page',
                [
                    'id_everblock_page' => (int) $this->everblockPage->id,
                    'rewrite' => $this->everblockPage->link_rewrite,
                ]
            );
        }

        return parent::getCanonicalURL();
    }

    protected function isPrettyBlocksEnabled(): bool
    {
        return (bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) Everblock\Tools\Service\EverblockTools::moduleDirectoryExists('prettyblocks') === true;
    }

    protected function ensureEverblockPage(array $customerGroups = []): ?EverblockPage
    {
        if ($this->everblockPage instanceof EverblockPage) {
            return $this->everblockPage;
        }

        $pageId = (int) Tools::getValue('id_everblock_page');
        if ($pageId <= 0) {
            return null;
        }

        $page = EverblockPage::getById($pageId, (int) $this->context->language->id, (int) $this->context->shop->id);
        if (!$page) {
            return null;
        }

        if (empty($customerGroups)) {
            $customerGroups = EverblockPage::getCustomerGroups($this->context);
        }

        if (!$page->active || !EverblockPage::isGroupAllowed($page, $customerGroups)) {
            return null;
        }

        $this->everblockPage = $page;

        return $this->everblockPage;
    }

    protected function buildItemListStructuredData(array $pages, array $pageLinks): array
    {
        if (empty($pages)) {
            return [];
        }

        $elements = [];
        $fallbackPosition = 1;
        $langId = (int) $this->context->language->id;

        foreach ($pages as $page) {
            $position = isset($page->position) ? (int) $page->position : $fallbackPosition;
            if ($position <= 0) {
                $position = $fallbackPosition;
            }

            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'url' => $pageLinks[(int) $page->id] ?? '',
                'name' => $page->name,
            ];

            ++$fallbackPosition;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $this->trans('Guides et tutoriels', [], 'Modules.Everblock.Front'),
            'description' => $this->trans('Découvrez nos guides pratiques pour ...', [], 'Modules.Everblock.Front'),
            'itemListElement' => $elements,
        ];
    }
}
