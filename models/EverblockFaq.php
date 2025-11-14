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

class EverblockFaq extends ObjectModel
{
    public $id_everblock_faq;
    public $id_shop;
    public $id_lang;
    public $tag_name;
    public $position;
    public $active;
    public $title;
    public $content;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'everblock_faq',
        'primary' => 'id_everblock_faq',
        'multilang' => true,
        'fields' => [
            'id_shop' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
            ],
            // lang fields
            'tag_name' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => true,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false,
                'default' => 0,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            // lang fields
            'title' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'required' => false,
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
                'required' => false,
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

    protected static function getFaqProductTable(): string
    {
        return _DB_PREFIX_ . 'everblock_faq_product';
    }

    protected static function getProductCacheKey(int $productId, int $shopId): string
    {
        return 'EverblockFaq_getFaqIdsByProduct_' . (int) $shopId . '_' . (int) $productId;
    }

    protected static function getFaqCacheKey(int $faqId, int $shopId): string
    {
        return 'EverblockFaq_getProductsByFaq_' . (int) $shopId . '_' . (int) $faqId;
    }

    protected static function clearRelationCaches(int $shopId, array $productIds = [], array $faqIds = []): void
    {
        $productIds = array_unique(array_filter(array_map('intval', $productIds)));
        $faqIds = array_unique(array_filter(array_map('intval', $faqIds)));

        foreach ($productIds as $productId) {
            EverblockCache::cacheDrop(static::getProductCacheKey($productId, $shopId));
        }

        foreach ($faqIds as $faqId) {
            EverblockCache::cacheDrop(static::getFaqCacheKey($faqId, $shopId));
        }
    }

    public static function getAllFaq(int $shopId, int $langId): array
    {
        $cache_id = 'EverblockFaq_getAllFaq_'
        . (int) $shopId
        . '_'
        . (int) $langId;
        if (!EverblockCache::isCacheStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(self::$definition['table']);
            $sql->where('id_shop = ' . (int) $shopId);
            $sql->where('active = 1');
            $sql->orderBy('position ASC');
            $faqs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            foreach ($faqs as $f) {
                $return[] = new self(
                    (int) $f[self::$definition['primary']],
                    (int) $langId,
                    (int) $shopId
                );
            }
            EverblockCache::cacheStore($cache_id, $return);
            return $return;
        }
        return EverblockCache::cacheRetrieve($cache_id);
    }

    public static function getFaqByTagName(int $shopId, int $langId, string $tagName): array
    {
        $cache_id = 'EverblockFaq_getFaqByTagName_'
        . (int) $shopId
        . '_'
        . (int) $langId
        . '_'
        . trim($tagName);
        if (!EverblockCache::isCacheStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(self::$definition['table']);
            $sql->where('id_shop = ' . (int) $shopId);
            $sql->where('tag_name = "' . pSQL($tagName) . '"');
            $sql->where('active = 1');
            $sql->orderBy('position ASC');
            $faqs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            foreach ($faqs as $f) {
                $return[] = new self(
                    (int) $f[self::$definition['primary']],
                    (int) $langId,
                    (int) $shopId
                );
            }
            EverblockCache::cacheStore($cache_id, $return);
            return $return;
        }
        return EverblockCache::cacheRetrieve($cache_id);
    }

    public static function getFaqIdsByProduct(int $productId, ?int $shopId = null): array
    {
        $shopId = static::resolveShopId($shopId);
        $productId = (int) $productId;

        if ($productId <= 0) {
            return [];
        }

        $cacheId = static::getProductCacheKey($productId, $shopId);
        if (EverblockCache::isCacheStored($cacheId)) {
            return (array) EverblockCache::cacheRetrieve($cacheId);
        }

        $sql = new DbQuery();
        $sql->select('id_everblock_faq');
        $sql->from('everblock_faq_product');
        $sql->where('id_product = ' . (int) $productId);
        $sql->where('id_shop = ' . (int) $shopId);
        $sql->orderBy('position ASC, id_everblock_faq_product ASC');

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $faqIds = [];
        foreach ($rows as $row) {
            $faqIds[] = (int) $row['id_everblock_faq'];
        }

        EverblockCache::cacheStore($cacheId, $faqIds);

        return $faqIds;
    }

    public static function getProductsByFaq(int $faqId, ?int $shopId = null): array
    {
        $shopId = static::resolveShopId($shopId);
        $faqId = (int) $faqId;

        if ($faqId <= 0) {
            return [];
        }

        $cacheId = static::getFaqCacheKey($faqId, $shopId);
        if (EverblockCache::isCacheStored($cacheId)) {
            return (array) EverblockCache::cacheRetrieve($cacheId);
        }

        $sql = new DbQuery();
        $sql->select('id_product, position');
        $sql->from('everblock_faq_product');
        $sql->where('id_everblock_faq = ' . (int) $faqId);
        $sql->where('id_shop = ' . (int) $shopId);
        $sql->orderBy('position ASC, id_everblock_faq_product ASC');

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $products = [];
        foreach ($rows as $row) {
            $products[] = [
                'id_product' => (int) $row['id_product'],
                'position' => (int) $row['position'],
            ];
        }

        EverblockCache::cacheStore($cacheId, $products);

        return $products;
    }

    public static function linkToProduct(int $faqId, int $productId, ?int $shopId = null, ?int $position = null): bool
    {
        $shopId = static::resolveShopId($shopId);
        $faqId = (int) $faqId;
        $productId = (int) $productId;

        if ($faqId <= 0 || $productId <= 0) {
            return false;
        }

        $db = Db::getInstance();

        if ($position === null) {
            $position = (int) $db->getValue(
                'SELECT IFNULL(MAX(position), -1) FROM `' . static::getFaqProductTable() . '`'
                . ' WHERE id_product = ' . (int) $productId
                . ' AND id_shop = ' . (int) $shopId
            ) + 1;
        }

        $data = [
            'id_everblock_faq' => $faqId,
            'id_product' => $productId,
            'id_shop' => $shopId,
            'position' => (int) $position,
        ];

        $where = 'id_everblock_faq = ' . (int) $faqId
            . ' AND id_product = ' . (int) $productId
            . ' AND id_shop = ' . (int) $shopId;

        $existingId = $db->getValue(
            'SELECT id_everblock_faq_product FROM `' . static::getFaqProductTable() . '` WHERE ' . $where
        );

        if ($existingId) {
            $result = $db->update('everblock_faq_product', ['position' => (int) $position], $where);
        } else {
            $result = $db->insert('everblock_faq_product', $data);
        }

        if ($result) {
            static::clearRelationCaches($shopId, [$productId], [$faqId]);
        }

        return (bool) $result;
    }

    public static function unlinkProductFaqs(int $productId, ?int $shopId = null, ?array $faqIds = null): bool
    {
        $shopId = static::resolveShopId($shopId);
        $productId = (int) $productId;

        if ($productId <= 0) {
            return false;
        }

        $db = Db::getInstance();

        $whereParts = [
            'id_product = ' . (int) $productId,
            'id_shop = ' . (int) $shopId,
        ];

        $faqIdsToDelete = [];

        if ($faqIds !== null) {
            foreach ($faqIds as $faqId) {
                $faqId = (int) $faqId;
                if ($faqId > 0) {
                    $faqIdsToDelete[] = $faqId;
                }
            }

            if (empty($faqIdsToDelete)) {
                return true;
            }

            $whereParts[] = 'id_everblock_faq IN (' . implode(',', $faqIdsToDelete) . ')';
        } else {
            $existing = $db->executeS(
                'SELECT id_everblock_faq FROM `' . static::getFaqProductTable() . '`'
                . ' WHERE id_product = ' . (int) $productId
                . ' AND id_shop = ' . (int) $shopId
            );
            foreach ($existing as $row) {
                $faqIdsToDelete[] = (int) $row['id_everblock_faq'];
            }
        }

        $result = $db->delete('everblock_faq_product', implode(' AND ', $whereParts));

        if ($result) {
            static::clearRelationCaches($shopId, [$productId], $faqIdsToDelete);
        }

        return (bool) $result;
    }

    public static function invalidateRelationsForFaq(int $faqId, ?int $shopId = null): void
    {
        $shopId = static::resolveShopId($shopId);
        $faqId = (int) $faqId;

        if ($faqId <= 0) {
            return;
        }

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT id_product FROM `' . static::getFaqProductTable() . '`'
            . ' WHERE id_everblock_faq = ' . (int) $faqId
            . ' AND id_shop = ' . (int) $shopId
        );

        $productIds = [];
        foreach ($rows as $row) {
            $productIds[] = (int) $row['id_product'];
        }

        static::clearRelationCaches($shopId, $productIds, [$faqId]);
    }
}
