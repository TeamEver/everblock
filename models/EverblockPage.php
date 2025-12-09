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

use Everblock\Tools\Service\EverblockCache;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockPage extends ObjectModel
{
    public $id_everblock_page;
    public $id_shop;
    public $groups;
    public $active;
    public $cover_image;
    public $position;
    public $date_add;
    public $date_upd;

    public $name;
    public $title;
    public $meta_description;
    public $short_description;
    public $link_rewrite;
    public $content;

    public static $definition = [
        'table' => 'everblock_page',
        'primary' => 'id_everblock_page',
        'multilang' => true,
        'fields' => [
            'id_shop' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'groups' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
                'required' => false,
                'default' => 1,
            ],
            'cover_image' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isFileName',
                'required' => false,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false,
                'default' => 0,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'title' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'meta_description' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
            'short_description' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
            'link_rewrite' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite',
                'required' => true,
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => true,
            ],
        ],
    ];

    protected static function resolveShopId(?int $shopId = null): int
    {
        if ($shopId) {
            return (int) $shopId;
        }

        return (int) Context::getContext()->shop->id;
    }

    public function getAllowedGroups(): array
    {
        if (!$this->groups) {
            return [];
        }

        $decoded = json_decode($this->groups, true);

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }

    public function save($nullValues = false, $autoDate = true, $useCache = true)
    {
        $this->id_shop = static::resolveShopId((int) $this->id_shop);
        if ($this->position === null) {
            $this->position = static::getNextPosition($this->id_shop);
        }
        $this->sanitizeLinkRewrite();

        return parent::save($nullValues, $autoDate, $useCache);
    }

    public static function getPages(
        int $langId,
        ?int $shopId = null,
        bool $onlyActive = true,
        array $allowedGroups = [],
        int $page = 1,
        ?int $perPage = null
    ): array
    {
        $shopId = static::resolveShopId($shopId);
        $page = max(1, (int) $page);
        $perPage = $perPage !== null ? (int) $perPage : null;
        $cacheId = 'EverblockPage_getPages_'
            . (int) $langId . '_'
            . (int) $shopId . '_'
            . (int) $onlyActive . '_'
            . md5(json_encode($allowedGroups)) . '_'
            . (int) $page . '_'
            . (int) $perPage;

        if (EverblockCache::isCacheStored($cacheId)) {
            return EverblockCache::cacheRetrieve($cacheId);
        }

        $sql = new DbQuery();
        $sql->select('p.*, pl.*');
        $sql->from(self::$definition['table'], 'p');
        $sql->innerJoin(self::$definition['table'] . '_lang', 'pl', 'p.id_everblock_page = pl.id_everblock_page AND pl.id_lang = ' . (int) $langId);
        $sql->where('p.id_shop = ' . (int) $shopId);

        if ($onlyActive) {
            $sql->where('p.active = 1');
        }

        $sql->orderBy('p.position ASC, p.date_add DESC');

        if ($perPage !== null && $perPage > 0) {
            $sql->limit($perPage, ($page - 1) * $perPage);
        }

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $pages = [];
        foreach ($rows as $row) {
            $page = new self((int) $row[self::$definition['primary']], (int) $langId, (int) $shopId);
            if (!static::isGroupAllowed($page, $allowedGroups)) {
                continue;
            }
            $pages[] = $page;
        }

        EverblockCache::cacheStore($cacheId, $pages);

        return $pages;
    }

    public static function countPages(int $langId, ?int $shopId = null, bool $onlyActive = true, array $allowedGroups = []): int
    {
        $shopId = static::resolveShopId($shopId);
        $cacheId = 'EverblockPage_countPages_'
            . (int) $langId . '_'
            . (int) $shopId . '_'
            . (int) $onlyActive . '_'
            . md5(json_encode($allowedGroups));

        if (EverblockCache::isCacheStored($cacheId)) {
            return (int) EverblockCache::cacheRetrieve($cacheId);
        }

        $sql = new DbQuery();
        $sql->select('p.id_everblock_page, p.groups');
        $sql->from(self::$definition['table'], 'p');
        $sql->innerJoin(
            self::$definition['table'] . '_lang',
            'pl',
            'p.id_everblock_page = pl.id_everblock_page AND pl.id_lang = ' . (int) $langId
        );
        $sql->where('p.id_shop = ' . (int) $shopId);

        if ($onlyActive) {
            $sql->where('p.active = 1');
        }

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $count = 0;

        foreach ($rows as $row) {
            $page = new self((int) $row['id_everblock_page'], (int) $langId, (int) $shopId);
            if (!static::isGroupAllowed($page, $allowedGroups)) {
                continue;
            }
            ++$count;
        }

        EverblockCache::cacheStore($cacheId, $count);

        return $count;
    }

    public static function getById(int $pageId, int $langId, ?int $shopId = null): ?self
    {
        $shopId = static::resolveShopId($shopId);
        $cacheId = 'EverblockPage_getById_' . (int) $pageId . '_' . (int) $langId . '_' . (int) $shopId;

        if (EverblockCache::isCacheStored($cacheId)) {
            return EverblockCache::cacheRetrieve($cacheId);
        }

        $page = new self($pageId, $langId, $shopId);
        if (!Validate::isLoadedObject($page)) {
            EverblockCache::cacheStore($cacheId, null);

            return null;
        }

        EverblockCache::cacheStore($cacheId, $page);

        return $page;
    }

    public static function isGroupAllowed(self $page, array $customerGroups = []): bool
    {
        $allowed = $page->getAllowedGroups();
        if (empty($allowed)) {
            return true;
        }

        return !empty(array_intersect($allowed, $customerGroups));
    }

    public static function getNextPosition(int $shopId): int
    {
        $shopId = static::resolveShopId($shopId);

        $maxPosition = (int) Db::getInstance()->getValue(
            'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'everblock_page` WHERE `id_shop` = ' . (int) $shopId
        );

        return $maxPosition + 1;
    }

    protected function sanitizeLinkRewrite(): void
    {
        foreach (Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $rewrite = $this->link_rewrite[$langId] ?? '';
            $name = $this->name[$langId] ?? '';
            if (!$rewrite) {
                $rewrite = $name;
            }
            $this->link_rewrite[$langId] = Tools::link_rewrite((string) $rewrite);
        }
    }

    public static function getCustomerGroups(Context $context): array
    {
        if ($context->customer && $context->customer->id) {
            return Customer::getGroupsStatic((int) $context->customer->id);
        }

        $groups = [];
        $groups[] = (int) Configuration::get('PS_UNIDENTIFIED_GROUP');
        $groups[] = (int) Configuration::get('PS_GUEST_GROUP');
        $groups[] = (int) Configuration::get('PS_CUSTOMER_GROUP');

        return array_values(array_unique(array_filter($groups)));
    }
}
