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

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock\Provider;

use CMSCategory;
use Category;
use Context;
use Db;
use Group;
use Hook;
use Manufacturer;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use Supplier;

class EverBlockFormChoicesProvider
{
    private $legacyContext;

    public function __construct(LegacyContext $legacyContext)
    {
        $this->legacyContext = $legacyContext;
    }

    public function getHookChoices(): array
    {
        $choices = [];
        foreach ($this->getHookCollection() as $hook) {
            $choices[(int) $hook['id_hook']] = $hook['evername'];
        }

        return $choices;
    }

    public function getHookCollection(): array
    {
        $hooks = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'hook` h ORDER BY h.`name`');
        $collection = [];

        foreach ($hooks as $hook) {
            if (!Hook::isDisplayHookName($hook['name'])) {
                continue;
            }

            $collection[] = [
                'id_hook' => (int) $hook['id_hook'],
                'name' => $hook['name'],
                'title' => $hook['title'],
                'evername' => sprintf('%s - %s', $hook['name'], $hook['title']),
            ];
        }

        return $collection;
    }

    public function getDeviceChoices(): array
    {
        $context = $this->legacyContext->getContext();
        return [
            0 => $context->getTranslator()->trans('All devices', [], 'Modules.Everblock.Admineverblockcontroller'),
            4 => $context->getTranslator()->trans('Only mobile devices', [], 'Modules.Everblock.Admineverblockcontroller'),
            2 => $context->getTranslator()->trans('Only tablet devices', [], 'Modules.Everblock.Admineverblockcontroller'),
            1 => $context->getTranslator()->trans('Only desktop devices', [], 'Modules.Everblock.Admineverblockcontroller'),
        ];
    }

    public function getBootstrapChoices(): array
    {
        $context = $this->legacyContext->getContext();

        return [
            0 => $context->getTranslator()->trans('None', [], 'Modules.Everblock.Admineverblockcontroller'),
            1 => $context->getTranslator()->trans('100%', [], 'Modules.Everblock.Admineverblockcontroller'),
            2 => $context->getTranslator()->trans('1/2', [], 'Modules.Everblock.Admineverblockcontroller'),
            4 => $context->getTranslator()->trans('1/3', [], 'Modules.Everblock.Admineverblockcontroller'),
            3 => $context->getTranslator()->trans('1/4', [], 'Modules.Everblock.Admineverblockcontroller'),
            6 => $context->getTranslator()->trans('1/6', [], 'Modules.Everblock.Admineverblockcontroller'),
        ];
    }

    public function getCategoryCollection(): array
    {
        $context = $this->legacyContext->getContext();
        $categories = Category::getCategories(false, true, false);
        $collection = [];

        $this->collectCategories($categories, $collection, (int) $context->language->id);

        return $collection;
    }

    public function getCategoryChoices(): array
    {
        $choices = [];
        foreach ($this->getCategoryCollection() as $category) {
            $choices[(int) $category['id_category']] = $category['name'];
        }

        return $choices;
    }

    public function getManufacturerCollection(): array
    {
        $context = $this->legacyContext->getContext();
        $manufacturers = Manufacturer::getLiteManufacturersList((int) $context->language->id);
        $collection = [];

        foreach ($manufacturers as $manufacturer) {
            $collection[] = [
                'id' => (int) $manufacturer['id'],
                'name' => $manufacturer['name'],
            ];
        }

        return $collection;
    }

    public function getManufacturerChoices(): array
    {
        $choices = [];
        foreach ($this->getManufacturerCollection() as $manufacturer) {
            $choices[(int) $manufacturer['id']] = $manufacturer['name'];
        }

        return $choices;
    }

    public function getSupplierCollection(): array
    {
        $context = $this->legacyContext->getContext();
        $suppliers = Supplier::getLiteSuppliersList((int) $context->language->id);
        $collection = [];

        foreach ($suppliers as $supplier) {
            $collection[] = [
                'id' => (int) $supplier['id'],
                'name' => $supplier['name'],
            ];
        }

        return $collection;
    }

    public function getSupplierChoices(): array
    {
        $choices = [];
        foreach ($this->getSupplierCollection() as $supplier) {
            $choices[(int) $supplier['id']] = $supplier['name'];
        }

        return $choices;
    }

    public function getCmsCategoryCollection(): array
    {
        $context = $this->legacyContext->getContext();
        $categories = CMSCategory::getSimpleCategories((int) $context->language->id);
        $collection = [];

        foreach ($categories as $category) {
            $collection[] = [
                'id_cms_category' => (int) $category['id_cms_category'],
                'name' => sprintf('%d - %s', $category['id_cms_category'], $category['name']),
            ];
        }

        return $collection;
    }

    public function getCmsCategoryChoices(): array
    {
        $choices = [];
        foreach ($this->getCmsCategoryCollection() as $category) {
            $choices[(int) $category['id_cms_category']] = $category['name'];
        }

        return $choices;
    }

    public function getGroupCollection(): array
    {
        $context = $this->legacyContext->getContext();
        return Group::getGroups((int) $context->language->id);
    }

    public function getGroupChoices(): array
    {
        $choices = [];
        foreach ($this->getGroupCollection() as $group) {
            $choices[(int) $group['id_group']] = $group['name'];
        }

        return $choices;
    }

    private function collectCategories(array $categories, array &$collection, int $idLang)
    {
        foreach ($categories as $category) {
            if (isset($category['id_category'])) {
                $name = isset($category['name']) ? $category['name'] : '';
                $collection[] = [
                    'id_category' => (int) $category['id_category'],
                    'name' => sprintf('%d - %s', $category['id_category'], $name),
                ];
            }

            if (isset($category[$idLang]) && is_array($category[$idLang])) {
                $localized = $category[$idLang];
                if (isset($localized['id_category'])) {
                    $collection[] = [
                        'id_category' => (int) $localized['id_category'],
                        'name' => sprintf('%d - %s', $localized['id_category'], $localized['name']),
                    ];
                }
            }

            foreach ($category as $value) {
                if (is_array($value)) {
                    $this->collectCategories($value, $collection, $idLang);
                }
            }
        }
    }

    public function getContext(): Context
    {
        return $this->legacyContext->getContext();
    }
}
