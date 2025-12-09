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

require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockFaq.php';

class EverblockFaqsModuleFrontController extends ModuleFrontController
{
    /** @var string */
    protected $tagName = '';
    /** @var bool */
    protected $isAllFaqsPage = false;

    public function init()
    {
        parent::init();
        $this->tagName = (string) Tools::getValue('tag', '');
        $this->isAllFaqsPage = trim($this->tagName) === '';
    }

    public function initContent()
    {
        parent::initContent();

        $tagName = trim($this->tagName);

        $itemsPerPage = (int) Configuration::get('EVERBLOCK_FAQ_PER_PAGE');
        if ($itemsPerPage <= 0) {
            $itemsPerPage = 10;
        }

        $currentPage = max(1, (int) Tools::getValue('page', 1));
        $shopId = (int) $this->context->shop->id;
        $langId = (int) $this->context->language->id;

        if ($this->isAllFaqsPage) {
            $totalItems = EverblockFaq::countAllActive($shopId);
            $totalPages = $totalItems > 0 ? (int) ceil($totalItems / $itemsPerPage) : 0;
            if ($totalPages > 0 && $currentPage > $totalPages) {
                $currentPage = $totalPages;
            }

            $faqs = EverblockFaq::getAllActivePaginated(
                $shopId,
                $langId,
                $currentPage,
                $itemsPerPage
            );
        } else {
            $totalItems = EverblockFaq::countActiveByTagName($shopId, $tagName);
            $totalPages = $totalItems > 0 ? (int) ceil($totalItems / $itemsPerPage) : 0;
            if ($totalPages > 0 && $currentPage > $totalPages) {
                $currentPage = $totalPages;
            }

            $faqs = EverblockFaq::getFaqByTagNamePaginated(
                $shopId,
                $langId,
                $tagName,
                $currentPage,
                $itemsPerPage
            );
        }

        $faqsWithLinks = $this->attachTagLinks($faqs);

        $title = $this->isAllFaqsPage
            ? $this->trans('FAQ', [], 'Modules.Everblock.Front')
            : $this->trans('FAQ', [], 'Modules.Everblock.Front') . ' - ' . $tagName;
        $description = $this->isAllFaqsPage
            ? $this->trans('All frequently asked questions, across every group.', [], 'Modules.Everblock.Front')
            : $this->trans('Frequently asked questions grouped by tag.', [], 'Modules.Everblock.Front');

        $this->context->smarty->assign([
            'everblock_tag_name' => $tagName,
            'everblock_faqs' => $faqsWithLinks,
            'everblock_pagination' => $this->buildPagination(
                $currentPage,
                $totalPages,
                $itemsPerPage,
                $totalItems
            ),
            'everblock_structured_data' => $this->buildStructuredData($faqs),
            'everblock_is_all_faqs_page' => $this->isAllFaqsPage,
        ]);

        $this->setTemplate('module:everblock/views/templates/front/faqs.tpl');

        $this->setTemplateMeta($title, $description);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->trans('FAQ', [], 'Modules.Everblock.Front'),
            'url' => $this->isAllFaqsPage ? $this->getCanonicalURL() : '',
        ];

        if ($this->tagName !== '') {
            $breadcrumb['links'][] = [
                'title' => $this->tagName,
                'url' => $this->getCanonicalURL(),
            ];
        }

        return $breadcrumb;
    }

    protected function buildPagination(int $currentPage, int $totalPages, int $itemsPerPage, int $totalItems): array
    {
        $baseParams = [];
        if (!$this->isAllFaqsPage) {
            $baseParams['tag'] = $this->tagName;
        }

        $baseLink = $this->context->link->getModuleLink($this->module->name, 'faqs', $baseParams);
        $pages = [];

        for ($page = 1; $page <= $totalPages; ++$page) {
            $pages[] = [
                'number' => $page,
                'link' => $page === 1
                    ? $baseLink
                    : $this->context->link->getModuleLink($this->module->name, 'faqs', array_merge($baseParams, [
                        'page' => $page,
                    ])),
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
                    : $this->context->link->getModuleLink($this->module->name, 'faqs', array_merge($baseParams, [
                        'page' => $currentPage - 1,
                    ])))
                : $baseLink,
            'next_link' => $currentPage < $totalPages
                ? $this->context->link->getModuleLink($this->module->name, 'faqs', array_merge($baseParams, [
                    'page' => $currentPage + 1,
                ]))
                : $baseLink,
            'pages' => $pages,
        ];
    }

    protected function buildStructuredData(array $faqs): array
    {
        if (empty($faqs)) {
            return [];
        }

        $entities = [];
        foreach ($faqs as $faq) {
            $entities[] = [
                '@type' => 'Question',
                'name' => $faq->title,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags((string) $faq->content),
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'name' => $this->tagName === ''
                ? $this->trans('FAQ', [], 'Modules.Everblock.Front')
                : $this->trans('FAQ', [], 'Modules.Everblock.Front') . ' - ' . $this->tagName,
            'mainEntity' => $entities,
        ];
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
        $params = [];
        if (!$this->isAllFaqsPage) {
            $params['tag'] = $this->tagName;
        }

        return $this->context->link->getModuleLink($this->module->name, 'faqs', $params);
    }

    protected function attachTagLinks(array $faqs): array
    {
        foreach ($faqs as $faq) {
            if (!isset($faq->tag_link)) {
                $faq->tag_link = $this->context->link->getModuleLink($this->module->name, 'faqs', [
                    'tag' => $faq->tag_name,
                ]);
            }
        }

        return $faqs;
    }
}
