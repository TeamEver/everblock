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
        $itemsPerPage = (int) Configuration::get('EVERBLOCK_PAGES_PER_PAGE');
        if ($itemsPerPage <= 0) {
            $itemsPerPage = 9;
        }

        $currentPage = max(1, (int) Tools::getValue('page', 1));
        $totalItems = EverblockPage::countPages(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            true,
            $customerGroups
        );
        $totalPages = $totalItems > 0 ? (int) ceil($totalItems / $itemsPerPage) : 0;
        if ($totalPages > 0 && $currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $pages = EverblockPage::getPages(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            true,
            $customerGroups,
            $currentPage,
            $itemsPerPage
        );

        $pageLinks = [];
        foreach ($pages as $page) {
            $pageLinks[(int) $page->id] = $this->context->link->getModuleLink(
                $this->module->name,
                'page',
                [
                    'id_everblock_page' => (int) $page->id,
                    'rewrite' => $page->link_rewrite,
                ]
            );

            $page->cover_image_data = $page->getCoverImageData($this->context);
        }

        $structuredData = $this->buildItemListStructuredData($pages, $pageLinks);
        $isPrettyBlocksEnabled = $this->isPrettyBlocksEnabled();
        $pagination = $this->buildPagination(
            $currentPage,
            $totalPages,
            $itemsPerPage,
            $totalItems
        );

        $this->context->smarty->assign([
            'everblock_pages' => $pages,
            'everblock_page_links' => $pageLinks,
            'everblock_structured_data' => $structuredData,
            'everblock_prettyblocks_enabled' => $isPrettyBlocksEnabled,
            'everblock_prettyblocks_top_zone_name' => $isPrettyBlocksEnabled ? 'everblock_pages_listing_zone_top' : '',
            'everblock_prettyblocks_bottom_zone_name' => $isPrettyBlocksEnabled ? 'everblock_pages_listing_zone_bottom' : '',
            'everblock_pagination' => $pagination,
        ]);

        $this->setTemplate('module:everblock/views/templates/front/pages.tpl');

        $this->setTemplateMeta(
            $this->trans('Guides et tutoriels', [], 'Modules.Everblock.Front'),
            $this->trans('Découvrez nos guides pratiques pour ...', [], 'Modules.Everblock.Front')
        );
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->trans('Guides et tutoriels', [], 'Modules.Everblock.Front'),
            'url' => '',
        ];

        return $breadcrumb;
    }

    protected function buildPagination(int $currentPage, int $totalPages, int $itemsPerPage, int $totalItems): array
    {
        $baseLink = $this->context->link->getModuleLink($this->module->name, 'pages');
        $pages = [];

        for ($page = 1; $page <= $totalPages; ++$page) {
            $pages[] = [
                'number' => $page,
                'link' => $page === 1
                    ? $baseLink
                    : $this->context->link->getModuleLink($this->module->name, 'pages', ['page' => $page]),
                'active' => $page === $currentPage,
            ];
        }

        return [
            'current' => $currentPage,
            'total_pages' => $totalPages,
            'items_per_page' => $itemsPerPage,
            'total_items' => $totalItems,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_link' => $currentPage > 1
                ? ($currentPage - 1 === 1
                    ? $baseLink
                    : $this->context->link->getModuleLink($this->module->name, 'pages', ['page' => $currentPage - 1]))
                : $baseLink,
            'next_link' => $currentPage < $totalPages
                ? $this->context->link->getModuleLink($this->module->name, 'pages', ['page' => $currentPage + 1])
                : $baseLink,
            'pages' => $pages,
        ];
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
                'name' => $page->title,
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

    protected function isPrettyBlocksEnabled(): bool
    {
        return (bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) Everblock\Tools\Service\EverblockTools::moduleDirectoryExists('prettyblocks') === true;
    }

    protected function setTemplateMeta(string $title, string $description): void
    {
        $this->meta_title = $title;
        $this->meta_description = $description;
        $this->context->smarty->assign([
            'meta_title' => $title,
            'meta_description' => $description,
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
        return $this->context->link->getModuleLink($this->module->name, 'pages');
    }
}
