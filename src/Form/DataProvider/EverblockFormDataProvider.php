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

namespace Everblock\Tools\Form\DataProvider;

use Category;
use CMSCategory;
use Context;
use EverBlockClass;
use Group;
use Hook;
use Language;
use Manufacturer;
use Supplier;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockFormDataProvider
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultData(): array
    {
        $languages = $this->getLanguages();
        $content = [];
        $customCode = [];

        foreach ($languages as $language) {
            $content[$language['id_lang']] = '';
            $customCode[$language['id_lang']] = '';
        }

        $groupIds = array_map(function (array $group) {
            return (int) $group['id_group'];
        }, $this->getGroups());

        return [
            'id_everblock' => null,
            'name' => '',
            'id_hook' => null,
            'content' => $content,
            'custom_code' => $customCode,
            'active' => true,
            'groupBox' => $groupIds,
            'categories' => [],
            'manufacturers' => [],
            'suppliers' => [],
            'cms_categories' => [],
            'only_home' => false,
            'only_category' => false,
            'only_category_product' => false,
            'only_manufacturer' => false,
            'only_supplier' => false,
            'only_cms_category' => false,
            'obfuscate_link' => false,
            'add_container' => true,
            'lazyload' => false,
            'background' => '',
            'css_class' => '',
            'data_attribute' => '',
            'bootstrap_class' => '',
            'position' => '',
            'modal' => false,
            'delay' => '',
            'timeout' => '',
            'date_start' => '',
            'date_end' => '',
            'device' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(int $everblockId): array
    {
        $default = $this->getDefaultData();
        $block = new EverBlockClass($everblockId);

        if (!\Validate::isLoadedObject($block)) {
            return $default;
        }

        $default['id_everblock'] = (int) $block->id;
        $default['name'] = (string) $block->name;
        $default['id_hook'] = (int) $block->id_hook;
        $default['content'] = (array) $block->content;
        $default['custom_code'] = (array) $block->custom_code;
        $default['active'] = (bool) $block->active;
        $default['groupBox'] = (array) json_decode((string) $block->groups, true) ?: [];
        $default['categories'] = (array) json_decode((string) $block->categories, true) ?: [];
        $default['manufacturers'] = (array) json_decode((string) $block->manufacturers, true) ?: [];
        $default['suppliers'] = (array) json_decode((string) $block->suppliers, true) ?: [];
        $default['cms_categories'] = (array) json_decode((string) $block->cms_categories, true) ?: [];
        $default['only_home'] = (bool) $block->only_home;
        $default['only_category'] = (bool) $block->only_category;
        $default['only_category_product'] = (bool) $block->only_category_product;
        $default['only_manufacturer'] = (bool) $block->only_manufacturer;
        $default['only_supplier'] = (bool) $block->only_supplier;
        $default['only_cms_category'] = (bool) $block->only_cms_category;
        $default['obfuscate_link'] = (bool) $block->obfuscate_link;
        $default['add_container'] = (bool) $block->add_container;
        $default['lazyload'] = (bool) $block->lazyload;
        $default['background'] = (string) $block->background;
        $default['css_class'] = (string) $block->css_class;
        $default['data_attribute'] = (string) $block->data_attribute;
        $default['bootstrap_class'] = (string) $block->bootstrap_class;
        $default['position'] = (string) $block->position;
        $default['modal'] = (bool) $block->modal;
        $default['delay'] = (string) $block->delay;
        $default['timeout'] = (string) $block->timeout;
        $default['date_start'] = (string) $block->date_start;
        $default['date_end'] = (string) $block->date_end;
        $default['device'] = (int) $block->device;

        return $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        $tabs = [
            'general' => $this->trans('General'),
            'targeting' => $this->trans('Targeting'),
            'display' => $this->trans('Display'),
            'modal' => $this->trans('Modal'),
            'schedule' => $this->trans('Schedule'),
        ];

        $documentation = $this->moveDocumentationToTabEnd($this->getDocumentationBlocks());

        return [
            'languages' => $this->getLanguages(),
            'hooks' => $this->getHooks(),
            'categories' => $this->getCategories(),
            'manufacturers' => $this->getManufacturers(),
            'suppliers' => $this->getSuppliers(),
            'cms_categories' => $this->getCmsCategories(),
            'groups' => $this->getGroups(),
            'bootstrap_sizes' => $this->getBootstrapSizes(),
            'devices' => $this->getDevices(),
            'tabs' => $tabs,
            'documentation' => $documentation,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getHooks(): array
    {
        $hooks = Hook::getHooks(true, true);
        $result = [];

        foreach ($hooks as $hook) {
            $result[] = [
                'id' => (int) $hook['id_hook'],
                'name' => sprintf('%s (%s)', $hook['id_hook'], $hook['name']),
            ];
        }

        usort($result, function (array $left, array $right) {
            return strcmp($left['name'], $right['name']);
        });

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCategories(): array
    {
        $categories = Category::getCategories((int) $this->context->language->id, false, false);
        $result = [];

        foreach ($categories as $category) {
            $result[] = [
                'id' => (int) $category['id_category'],
                'name' => sprintf('%d - %s', $category['id_category'], $category['name']),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getManufacturers(): array
    {
        $list = Manufacturer::getLiteManufacturersList((int) $this->context->language->id);
        $result = [];

        foreach ($list as $manufacturer) {
            $result[] = [
                'id' => (int) $manufacturer['id_manufacturer'],
                'name' => sprintf('%d - %s', $manufacturer['id_manufacturer'], $manufacturer['name']),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSuppliers(): array
    {
        $list = Supplier::getLiteSuppliersList((int) $this->context->language->id);
        $result = [];

        foreach ($list as $supplier) {
            $result[] = [
                'id' => (int) $supplier['id_supplier'],
                'name' => sprintf('%d - %s', $supplier['id_supplier'], $supplier['name']),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCmsCategories(): array
    {
        $list = CMSCategory::getSimpleCategories((int) $this->context->language->id);
        $result = [];

        foreach ($list as $category) {
            $result[] = [
                'id' => (int) $category['id_cms_category'],
                'name' => sprintf('%d - %s', $category['id_cms_category'], $category['name']),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getGroups(): array
    {
        return Group::getGroups((int) $this->context->language->id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getBootstrapSizes(): array
    {
        return [
            [
                'id' => 0,
                'name' => $this->trans('None'),
            ],
            [
                'id' => 1,
                'name' => $this->trans('100%'),
            ],
            [
                'id' => 2,
                'name' => $this->trans('1/2'),
            ],
            [
                'id' => 4,
                'name' => $this->trans('1/3'),
            ],
            [
                'id' => 3,
                'name' => $this->trans('1/4'),
            ],
            [
                'id' => 6,
                'name' => $this->trans('1/6'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDevices(): array
    {
        return [
            [
                'id' => 0,
                'name' => $this->trans('All devices'),
            ],
            [
                'id' => 4,
                'name' => $this->trans('Only mobile devices'),
            ],
            [
                'id' => 2,
                'name' => $this->trans('Only tablet devices'),
            ],
            [
                'id' => 1,
                'name' => $this->trans('Only desktop devices'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getLanguages(): array
    {
        return Language::getLanguages(false);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function getDocumentationBlocks(): array
    {
        $docTemplates = [
            'general' => 'general.tpl',
            'targeting' => 'targeting.tpl',
            'display' => 'display.tpl',
            'modal' => 'modal.tpl',
            'schedule' => 'schedule.tpl',
        ];

        $documentation = [];

        foreach ($docTemplates as $tab => $template) {
            $docPath = _PS_MODULE_DIR_ . 'everblock/views/templates/admin/block/docs/' . $template;

            if (!file_exists($docPath)) {
                continue;
            }

            $documentation[] = [
                'tab' => $tab,
                'content' => $this->context->smarty->fetch($docPath),
            ];
        }

        return $documentation;
    }

    /**
     * @param array<int, array<string, string>> $documentation
     *
     * @return array<int, array<string, string>>
     */
    private function moveDocumentationToTabEnd(array $documentation): array
    {
        if (empty($documentation)) {
            return $documentation;
        }

        $ordered = [];
        $byTab = [];

        foreach ($documentation as $doc) {
            $tab = $doc['tab'] ?? null;
            if ($tab === null) {
                $ordered[] = $doc;
                continue;
            }

            $byTab[$tab][] = $doc;
        }

        foreach ($this->getFormTabs() as $tabKey) {
            if (empty($byTab[$tabKey])) {
                continue;
            }

            foreach ($byTab[$tabKey] as $doc) {
                $ordered[] = $doc;
            }
            unset($byTab[$tabKey]);
        }

        foreach ($byTab as $docList) {
            foreach ($docList as $doc) {
                $ordered[] = $doc;
            }
        }

        return $ordered;
    }

    /**
     * @return array<int, string>
     */
    private function getFormTabs(): array
    {
        return ['general', 'targeting', 'display', 'modal', 'schedule'];
    }

    private function trans(string $message): string
    {
        return $this->context->getTranslator()->trans($message, [], 'Modules.Everblock.Admin');
    }
}
