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
                    'rewrite' => $page->link_rewrite,
                ]
            );
        }

        $structuredData = $this->buildItemListStructuredData($pages, $pageLinks);
        $isPrettyBlocksEnabled = $this->isPrettyBlocksEnabled();

        $this->context->smarty->assign([
            'everblock_pages' => $pages,
            'everblock_page_links' => $pageLinks,
            'everblock_structured_data' => $structuredData,
            'everblock_prettyblocks_enabled' => $isPrettyBlocksEnabled,
            'everblock_prettyblocks_zone_name' => $isPrettyBlocksEnabled ? 'everblock_pages_listing_zone' : '',
        ]);

        $this->setTemplate('module:everblock/views/templates/front/pages.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->trans('Guides et tutoriels', [], 'Modules.Everblock.Front'),
            'url' => $this->context->link->getModuleLink($this->module->name, 'pages'),
        ];

        return $breadcrumb;
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

    protected function isPrettyBlocksEnabled(): bool
    {
        return (bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) Everblock\Tools\Service\EverblockTools::moduleDirectoryExists('prettyblocks') === true;
    }
}
