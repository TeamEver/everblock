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

use Configuration;
use Context;
use Controller;
use Currency;
use Customer;
use Db;
use DbQuery;
use EverBlockClass;
use Everblock;
use Exception;
use Group;
use Hook;
use Language;
use Shop;
use Tools;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockPreviewBuilder
{
    /** @var Everblock */
    private $module;

    /** @var Context */
    private $context;

    /** @var array */
    private $shopContextSnapshot = [];

    public function __construct(Everblock $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    public function buildPreview(EverBlockClass $block, array $params): array
    {
        $contextSnapshot = $this->snapshotContext($this->context);
        $globalsSnapshot = $this->snapshotGlobals();
        $this->snapshotShopContext();

        try {
            $hookName = Hook::getNameById((int) $block->id_hook);
            if (!$hookName) {
                throw new Exception($this->module->l('This block cannot be previewed because the associated hook is missing.'));
            }

            $this->prepareShop($params);
            $this->prepareLanguage($params);
            $this->prepareCurrency($params);

            $previewCustomer = $this->prepareCustomer($block, $params);
            $controller = $this->prepareController($params);
            $params['controller'] = $controller->php_self;

            $this->injectPreviewParameters($params);

            $methodName = 'hook' . Tools::toCamelCase($hookName);
            $arguments = [[
                'everblock_preview' => true,
                'position' => isset($params['position']) ? (int) $params['position'] : null,
            ] + $params];

            $html = $this->module->everHook($methodName, $arguments);

            $groupIds = $this->resolveGroupIds($block, $previewCustomer);
            $groupLabels = $this->resolveGroupLabels($groupIds, (int) $this->context->language->id);

            return [
                'html' => (string) $html,
                'hook' => $hookName,
                'info' => [
                    'controller' => $controller->php_self,
                    'page_name' => $controller->page_name,
                    'ids' => $this->extractIdentifiers($params),
                    'language' => $this->context->language,
                    'currency' => $this->context->currency,
                    'shop' => $this->context->shop,
                    'customer' => $previewCustomer,
                    'groups' => $groupLabels,
                    'groups_raw' => $groupIds,
                ],
            ];
        } finally {
            $this->restoreGlobals($globalsSnapshot);
            $this->restoreShopContext();
            $this->restoreContext($this->context, $contextSnapshot);
        }
    }

    protected function snapshotContext(Context $context): array
    {
        return [
            'language' => $context->language,
            'currency' => $context->currency,
            'shop' => $context->shop,
            'customer' => $context->customer,
            'controller' => $context->controller,
            'controller_page' => $context->controller ? $context->controller->page_name : null,
            'controller_self' => $context->controller ? $context->controller->php_self : null,
        ];
    }

    protected function restoreContext(Context $context, array $snapshot): void
    {
        $context->language = $snapshot['language'];
        $context->currency = $snapshot['currency'];
        $context->shop = $snapshot['shop'];
        $context->customer = $snapshot['customer'];

        $context->controller = $snapshot['controller'];
        if ($context->controller) {
            $context->controller->page_name = $snapshot['controller_page'];
            $context->controller->php_self = $snapshot['controller_self'];
        }
    }

    protected function snapshotGlobals(): array
    {
        return [
            '_GET' => $_GET,
            '_REQUEST' => $_REQUEST,
        ];
    }

    protected function restoreGlobals(array $snapshot): void
    {
        $_GET = $snapshot['_GET'];
        $_REQUEST = $snapshot['_REQUEST'];
    }

    protected function snapshotShopContext(): void
    {
        $this->shopContextSnapshot = [
            'context' => Shop::getContext(),
            'id_shop' => Shop::getContextShopID(),
            'id_shop_group' => Shop::getContextShopGroupID(),
        ];
    }

    protected function restoreShopContext(): void
    {
        $contextType = isset($this->shopContextSnapshot['context']) ? (int) $this->shopContextSnapshot['context'] : Shop::CONTEXT_ALL;
        $shopId = isset($this->shopContextSnapshot['id_shop']) ? (int) $this->shopContextSnapshot['id_shop'] : null;
        $shopGroupId = isset($this->shopContextSnapshot['id_shop_group']) ? (int) $this->shopContextSnapshot['id_shop_group'] : null;

        switch ($contextType) {
            case Shop::CONTEXT_SHOP:
                Shop::setContext(Shop::CONTEXT_SHOP, $shopId);
                break;
            case Shop::CONTEXT_GROUP:
                Shop::setContext(Shop::CONTEXT_GROUP, $shopGroupId);
                break;
            default:
                Shop::setContext(Shop::CONTEXT_ALL);
        }
    }

    protected function prepareShop(array $params): void
    {
        $shopId = isset($params['id_shop']) ? (int) $params['id_shop'] : (int) $this->context->shop->id;
        $shop = new Shop($shopId);
        if (Validate::isLoadedObject($shop)) {
            $this->context->shop = $shop;
            Shop::setContext(Shop::CONTEXT_SHOP, (int) $shop->id);
            if ($this->context->cookie) {
                $this->context->cookie->id_shop = (int) $shop->id;
                $this->context->cookie->id_shop_group = (int) $shop->id_shop_group;
            }
        }
    }

    protected function prepareLanguage(array $params): void
    {
        $languageId = isset($params['id_lang']) ? (int) $params['id_lang'] : (int) Configuration::get('PS_LANG_DEFAULT');
        $language = new Language($languageId);
        if (Validate::isLoadedObject($language)) {
            $this->context->language = $language;
            if ($this->context->cookie) {
                $this->context->cookie->id_lang = (int) $language->id;
            }
        }
    }

    protected function prepareCurrency(array $params): void
    {
        $currencyId = isset($params['id_currency']) ? (int) $params['id_currency'] : (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $currency = new Currency($currencyId);
        if (Validate::isLoadedObject($currency)) {
            $this->context->currency = $currency;
            if ($this->context->cookie) {
                $this->context->cookie->id_currency = (int) $currency->id;
            }
        }
    }

    protected function prepareCustomer(EverBlockClass $block, array $params): Customer
    {
        if (!empty($params['id_customer'])) {
            $customer = new Customer((int) $params['id_customer']);
            if (Validate::isLoadedObject($customer)) {
                $this->context->customer = $customer;
                return $customer;
            }
        }

        $allowedGroups = $this->getBlockGroups($block);
        if (!empty($allowedGroups)) {
            $customer = $this->findCustomerForGroups($allowedGroups);
            if ($customer) {
                $this->context->customer = $customer;
                return $customer;
            }
        }

        if ($this->context->customer instanceof Customer) {
            return $this->context->customer;
        }

        $customer = new Customer();
        $customer->id = 0;
        $customer->id_default_group = (int) Configuration::get('PS_UNIDENTIFIED_GROUP');
        $this->context->customer = $customer;

        return $customer;
    }

    protected function prepareController(array $params): Controller
    {
        $controllerName = isset($params['controller']) ? (string) $params['controller'] : 'index';
        $controllerClass = $this->resolveControllerClass($controllerName);
        /** @var Controller $controller */
        $controller = Controller::getController($controllerClass);
        $controller->controller_type = 'front';
        $controller->module = $this->module;
        $controller->php_self = $controllerName;
        $controller->page_name = isset($params['page_name']) && $params['page_name'] !== ''
            ? (string) $params['page_name']
            : $controllerName;
        $this->context->controller = $controller;

        return $controller;
    }

    protected function resolveControllerClass(string $controller): string
    {
        $normalized = preg_replace('/[^a-z0-9]/i', '', (string) $controller);
        if ($normalized === '') {
            $normalized = 'index';
        }

        $class = Tools::toCamelCase(Tools::strtolower($normalized)) . 'Controller';

        if (!class_exists($class)) {
            return 'PageNotFoundController';
        }

        return $class;
    }

    protected function injectPreviewParameters(array $params): void
    {
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
        }
    }

    protected function extractIdentifiers(array $params): array
    {
        $keys = [
            'id_product',
            'id_category',
            'id_cms',
            'id_cms_category',
            'id_manufacturer',
            'id_supplier',
            'id_cart',
            'id_order',
            'id_order_return',
        ];

        $ids = [];
        foreach ($keys as $key) {
            if (isset($params[$key]) && (int) $params[$key] > 0) {
                $ids[$key] = (int) $params[$key];
            }
        }

        return $ids;
    }

    protected function getBlockGroups(EverBlockClass $block): array
    {
        $groups = [];
        if (!empty($block->groups)) {
            $decoded = json_decode($block->groups, true);
            if (is_array($decoded)) {
                $groups = array_map('intval', $decoded);
            }
        }

        return $groups;
    }

    protected function findCustomerForGroups(array $groupIds): ?Customer
    {
        $groupIds = array_filter(array_map('intval', $groupIds));
        if (empty($groupIds)) {
            return null;
        }

        $query = new DbQuery();
        $query->select('cg.id_customer');
        $query->from('customer_group', 'cg');
        $query->innerJoin('customer', 'c', 'c.id_customer = cg.id_customer');
        $query->where('c.active = 1');
        $query->where('cg.id_group IN (' . implode(',', $groupIds) . ')');
        $query->orderBy('c.date_upd DESC');

        $customerId = (int) Db::getInstance()->getValue($query);
        if ($customerId > 0) {
            $customer = new Customer($customerId);
            if (Validate::isLoadedObject($customer)) {
                return $customer;
            }
        }

        return null;
    }

    protected function resolveGroupIds(EverBlockClass $block, Customer $customer): array
    {
        $groups = $this->getBlockGroups($block);
        if (!empty($groups)) {
            return $groups;
        }

        if (Validate::isLoadedObject($customer) && (int) $customer->id > 0) {
            $customerGroups = Customer::getGroupsStatic((int) $customer->id);
            if (is_array($customerGroups)) {
                return array_map('intval', $customerGroups);
            }
        }

        return [];
    }

    protected function resolveGroupLabels(array $groupIds, int $idLang): array
    {
        if (empty($groupIds)) {
            return [];
        }

        $groupIds = array_unique(array_map('intval', $groupIds));
        $availableGroups = Group::getGroups($idLang, $this->context->shop ? (int) $this->context->shop->id : false);

        $labels = [];
        if (is_array($availableGroups)) {
            foreach ($availableGroups as $group) {
                $groupId = isset($group['id_group']) ? (int) $group['id_group'] : 0;
                if (in_array($groupId, $groupIds, true)) {
                    $labels[] = [
                        'id' => $groupId,
                        'name' => isset($group['name']) ? (string) $group['name'] : (string) $groupId,
                    ];
                }
            }
        }

        if (empty($labels)) {
            foreach ($groupIds as $groupId) {
                $labels[] = [
                    'id' => (int) $groupId,
                    'name' => (string) $groupId,
                ];
            }
        }

        return $labels;
    }
}
