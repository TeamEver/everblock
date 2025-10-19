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
 */

namespace Everblock\Tools\Form\Admin;

use Category;
use CMSCategory;
use Context;
use EverBlockClass;
use Group;
use Hook;
use Language;
use Manufacturer;
use Supplier;

class EverBlockFormDataProvider
{
    public function getData(?int $idEverBlock = null): array
    {
        $context = Context::getContext();
        $languages = Language::getLanguages(false);

        $data = [
            'name' => '',
            'content' => [],
            'custom_code' => [],
            'id_hook' => null,
            'only_home' => false,
            'only_category' => false,
            'only_category_product' => false,
            'only_manufacturer' => false,
            'only_supplier' => false,
            'only_cms_category' => false,
            'obfuscate_link' => false,
            'add_container' => true,
            'lazyload' => false,
            'categories' => [],
            'manufacturers' => [],
            'suppliers' => [],
            'cms_categories' => [],
            'groups' => [],
            'position' => 0,
            'background' => '',
            'css_class' => '',
            'data_attribute' => '',
            'bootstrap_class' => 0,
            'device' => 0,
            'delay' => 0,
            'timeout' => 0,
            'modal' => 0,
            'date_start' => null,
            'date_end' => null,
            'active' => true,
        ];

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $data['content'][$idLang] = '';
            $data['custom_code'][$idLang] = '';
        }

        if ($idEverBlock) {
            $block = new EverBlockClass($idEverBlock);
            if ($block->id) {
                $data['name'] = $block->name;
                $data['id_hook'] = $block->id_hook;
                $data['only_home'] = (bool) $block->only_home;
                $data['only_category'] = (bool) $block->only_category;
                $data['only_category_product'] = (bool) $block->only_category_product;
                $data['only_manufacturer'] = (bool) $block->only_manufacturer;
                $data['only_supplier'] = (bool) $block->only_supplier;
                $data['only_cms_category'] = (bool) $block->only_cms_category;
                $data['obfuscate_link'] = (bool) $block->obfuscate_link;
                $data['add_container'] = (bool) $block->add_container;
                $data['lazyload'] = (bool) $block->lazyload;
                $data['categories'] = $block->categories ? json_decode($block->categories, true) ?: [] : [];
                $data['manufacturers'] = $block->manufacturers ? json_decode($block->manufacturers, true) ?: [] : [];
                $data['suppliers'] = $block->suppliers ? json_decode($block->suppliers, true) ?: [] : [];
                $data['cms_categories'] = $block->cms_categories ? json_decode($block->cms_categories, true) ?: [] : [];
                $data['groups'] = $block->groups ? json_decode($block->groups, true) ?: [] : [];
                $data['position'] = (int) $block->position;
                $data['background'] = $block->background;
                $data['css_class'] = $block->css_class;
                $data['data_attribute'] = $block->data_attribute;
                $data['bootstrap_class'] = (int) $block->bootstrap_class;
                $data['device'] = (int) $block->device;
                $data['delay'] = (int) $block->delay;
                $data['timeout'] = (int) $block->timeout;
                $data['modal'] = (int) $block->modal;
                $data['date_start'] = $block->date_start;
                $data['date_end'] = $block->date_end;
                $data['active'] = (bool) $block->active;

                foreach ($languages as $language) {
                    $idLang = (int) $language['id_lang'];
                    $data['content'][$idLang] = $block->content[$idLang] ?? '';
                    $data['custom_code'][$idLang] = $block->custom_code[$idLang] ?? '';
                }
            }
        }

        if (empty($data['groups'])) {
            $data['groups'] = array_map('intval', $this->getGroupIds($context));
        }

        return $data;
    }

    public function getOptions(): array
    {
        return [
            'hooks' => $this->getHookChoices(),
            'categories' => $this->getCategoryChoices(),
            'manufacturers' => $this->getManufacturerChoices(),
            'suppliers' => $this->getSupplierChoices(),
            'cms_categories' => $this->getCmsCategoryChoices(),
            'groups' => $this->getGroupChoices(),
            'bootstrap_sizes' => $this->getBootstrapSizes(),
            'devices' => $this->getDeviceChoices(),
            'languages' => Language::getLanguages(false),
        ];
    }

    private function getHookChoices(): array
    {
        $hooks = Hook::getHooks();
        $choices = [];
        foreach ($hooks as $hook) {
            $label = sprintf('%s (%s)', $hook['name'], $hook['title']);
            $choices[$label] = (int) $hook['id_hook'];
        }

        asort($choices);

        return $choices;
    }

    private function getCategoryChoices(): array
    {
        $categories = Category::getCategories(false, true, false);
        $choices = [];
        foreach ($categories as $category) {
            $choices[$category['id_category'] . ' - ' . $category['name']] = (int) $category['id_category'];
        }

        return $choices;
    }

    private function getManufacturerChoices(): array
    {
        $manufacturers = Manufacturer::getLiteManufacturersList((int) Context::getContext()->language->id);
        $choices = [];
        foreach ($manufacturers as $manufacturer) {
            $choices[$manufacturer['name']] = (int) $manufacturer['id'];
        }

        return $choices;
    }

    private function getSupplierChoices(): array
    {
        $suppliers = Supplier::getLiteSuppliersList((int) Context::getContext()->language->id);
        $choices = [];
        foreach ($suppliers as $supplier) {
            $choices[$supplier['name']] = (int) $supplier['id'];
        }

        return $choices;
    }

    private function getCmsCategoryChoices(): array
    {
        $categories = CMSCategory::getSimpleCategories((int) Context::getContext()->language->id);
        $choices = [];
        foreach ($categories as $category) {
            $choices[$category['name']] = (int) $category['id_cms_category'];
        }

        return $choices;
    }

    private function getGroupChoices(): array
    {
        $context = Context::getContext();
        $groups = Group::getGroups((int) $context->language->id, (int) $context->shop->id);
        $choices = [];
        foreach ($groups as $group) {
            $choices[$group['name']] = (int) $group['id_group'];
        }

        return $choices;
    }

    private function getGroupIds(Context $context): array
    {
        $groups = Group::getGroups((int) $context->language->id, (int) $context->shop->id);
        $ids = [];
        foreach ($groups as $group) {
            $ids[] = (int) $group['id_group'];
        }

        return $ids;
    }

    private function getBootstrapSizes(): array
    {
        $translator = Context::getContext()->getTranslator();

        return [
            $translator->trans('None', [], 'Modules.Everblock.Admin') => 0,
            '100%' => 1,
            '1/2' => 2,
            '1/3' => 4,
            '1/4' => 3,
            '1/6' => 6,
        ];
    }

    private function getDeviceChoices(): array
    {
        $translator = Context::getContext()->getTranslator();

        return [
            $translator->trans('All devices', [], 'Modules.Everblock.Admin') => 0,
            $translator->trans('Only mobile devices', [], 'Modules.Everblock.Admin') => 4,
            $translator->trans('Only tablet devices', [], 'Modules.Everblock.Admin') => 2,
            $translator->trans('Only desktop devices', [], 'Modules.Everblock.Admin') => 1,
        ];
    }
}
