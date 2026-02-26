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

namespace Everblock\Tools\Service;

use Context;
use EverBlockClass;
use Everblock;
use EverblockPage;
use Module;
use Tools;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

class QcdThirdPartyBlockRenderer
{
    /** @var Everblock */
    private $module;

    /** @var Context */
    private $context;

    public function __construct(Everblock $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    public function renderFromHookFilterQcdPageBuilderThirdPartyBlockFrontRender(array &$params): void
    {
        if (!isset($params['context']) || !is_array($params['context'])) {
            return;
        }

        $context =& $params['context'];
        $this->ensureNormalizedContext($context);

        $blockType = $this->resolveBlockType($context);
        if ($this->renderEverblockSelectContext($context, $blockType)) {
            return;
        }

        $this->renderLatestPagesContext($context, $blockType);
    }

    public function renderEverblockSelectContext(array &$context, string $blockType): bool
    {
        $isEverblockSelect = in_array($blockType, ['everblock_select', 'everblock'], true);

        if (!$isEverblockSelect && isset($context['normalized']['attributes']['id_everblock'])) {
            $isEverblockSelect = true;
        }

        if (!$isEverblockSelect) {
            return false;
        }

        $context['owner_module'] = $this->module->name;
        $context['template'] = 'views/templates/hook/everblock.tpl';

        $idEverblock = (int) $this->getContextValue($context, [
            ['normalized', 'attributes', 'id_everblock'],
            ['attributes', 'id_everblock'],
        ], 0);
        $context['normalized']['attributes']['id_everblock'] = $idEverblock;

        if ($idEverblock <= 0) {
            $this->context->smarty->assign([
                'everblock' => [],
                'everhook' => 'qcdpagebuilder',
            ]);

            return true;
        }

        $everblock = new EverBlockClass(
            $idEverblock,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );

        if (!Validate::isLoadedObject($everblock)) {
            $this->context->smarty->assign([
                'everblock' => [],
                'everhook' => 'qcdpagebuilder',
            ]);

            return true;
        }

        /** @var Qcdpagebuilder|null $builder */
        $builder = Module::getInstanceByName('qcdpagebuilder');
        $everblock->content = $builder
            ? (string) $builder->renderTargetField(
                'everblock',
                (int) $everblock->id,
                'content',
                (string) $everblock->content,
                (int) $this->context->shop->id,
                (int) $this->context->language->id
            )
            : (string) $everblock->content;

        $this->context->smarty->assign([
            'everblock' => [
                ['block' => $everblock],
            ],
            'everhook' => 'qcdpagebuilder',
        ]);

        return true;
    }

    public function renderLatestPagesContext(array &$context, string $blockType): bool
    {
        if (!in_array($blockType, ['everblock_latest_pages', 'latest_pages'], true)) {
            return false;
        }

        $context['owner_module'] = $this->module->name;
        if ($this->shouldUseAlternatePagesTemplate($context)) {
            $context['template'] = 'views/templates/front/pages-alt.tpl';
        }

        $limit = (int) $this->getContextValue($context, [
            ['normalized', 'attributes', 'limit'],
            ['attributes', 'limit'],
        ], 10);
        if ($limit <= 0) {
            $limit = 10;
        }
        $limit = min($limit, 50);
        $context['normalized']['attributes']['limit'] = $limit;

        $customerGroups = EverblockPage::getCustomerGroups($this->context);
        $pages = EverblockPage::getPages(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            true,
            $customerGroups,
            1,
            $limit
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

        $this->context->smarty->assign([
            'everblock_pages' => $pages,
            'everblock_page_links' => $pageLinks,
        ]);

        return true;
    }

    private function shouldUseAlternatePagesTemplate(array $context): bool
    {
        $templateVariant = (string) $this->getContextValue($context, [
            ['normalized', 'attributes', 'template_variant'],
            ['attributes', 'template_variant'],
        ], '');

        return Tools::strtolower(trim($templateVariant)) === 'alt';
    }

    private function ensureNormalizedContext(array &$context): void
    {
        if (!isset($context['normalized']) || !is_array($context['normalized'])) {
            $context['normalized'] = [];
        }

        if (!isset($context['normalized']['attributes']) || !is_array($context['normalized']['attributes'])) {
            $context['normalized']['attributes'] = [];
        }
    }

    private function resolveBlockType(array &$context): string
    {
        $helperBlockType = $this->resolveBlockTypeUsingHelper($context);
        if ($helperBlockType !== null && $helperBlockType !== '') {
            return $helperBlockType;
        }

        $rawBlockType = (string) $this->getContextValue($context, [
            ['block_type'],
            ['type'],
            ['code'],
            ['normalized', 'block_type'],
            ['normalized', 'type'],
            ['normalized', 'code'],
        ], '');

        return $this->normalizeBlockType($rawBlockType);
    }

    private function resolveBlockTypeUsingHelper(array $context): ?string
    {
        if (!class_exists('ThirdPartyRenderContextHelper')) {
            return null;
        }

        $helperClass = 'ThirdPartyRenderContextHelper';
        $methods = ['resolveBlockType', 'extractBlockType', 'getBlockType', 'normalizeBlockType'];

        foreach ($methods as $method) {
            if (!is_callable([$helperClass, $method])) {
                continue;
            }

            $rawBlockType = call_user_func([$helperClass, $method], $context, $this->module->name);
            if (!is_string($rawBlockType)) {
                continue;
            }

            return $this->normalizeBlockType($rawBlockType);
        }

        return null;
    }

    private function normalizeBlockType(string $rawBlockType): string
    {
        $blockType = Tools::strtolower(trim($rawBlockType));
        if ($blockType === '' || !preg_match('/[.:\/]/', $blockType)) {
            return $blockType;
        }

        $parts = preg_split('/[.:\/]/', $blockType);
        if (!isset($parts[0], $parts[count($parts) - 1]) || $parts[0] !== $this->module->name) {
            return $blockType;
        }

        return (string) $parts[count($parts) - 1];
    }

    private function getContextValue(array $context, array $paths, $default = null)
    {
        foreach ($paths as $path) {
            $current = $context;
            $found = true;

            foreach ($path as $segment) {
                if (!is_array($current) || !array_key_exists($segment, $current)) {
                    $found = false;
                    break;
                }

                $current = $current[$segment];
            }

            if ($found) {
                return $current;
            }
        }

        return $default;
    }
}
