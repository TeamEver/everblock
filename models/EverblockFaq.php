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

require_once _PS_MODULE_DIR_ . 'everblock/models/EverblockFaqProduct.php';

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

    public static function getAllFaq(int $shopId, int $langId): array
    {
        $cache_id = 'EverblockFaq_getAllFaq_' 
        . (int) $shopId
        . '_'
        . (int) $langId;
        if (!EverblockCache::isCacheStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(self::definition['table']);
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

    public static function getFaqsWithTranslationsForShop(int $shopId): array
    {
        if ($shopId <= 0) {
            return [];
        }

        $query = new DbQuery();
        $query->select('f.' . self::$definition['primary'] . ', f.tag_name, f.active, f.position, fl.id_lang, fl.title');
        $query->from(self::$definition['table'], 'f');
        $query->leftJoin(
            self::$definition['table'] . '_lang',
            'fl',
            'f.' . self::$definition['primary'] . ' = fl.' . self::$definition['primary']
        );
        $query->where('f.id_shop = ' . (int) $shopId);
        $query->orderBy('f.tag_name ASC, f.position ASC, f.' . self::$definition['primary'] . ' ASC');

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (empty($rows)) {
            return [];
        }

        $faqs = [];

        foreach ($rows as $row) {
            $faqId = (int) $row[self::$definition['primary']];

            if (!isset($faqs[$faqId])) {
                $faqs[$faqId] = [
                    'id_everblock_faq' => $faqId,
                    'tag_name' => (string) $row['tag_name'],
                    'active' => (int) $row['active'],
                    'titles' => [],
                ];
            }

            if (isset($row['id_lang'])) {
                $faqs[$faqId]['titles'][(int) $row['id_lang']] = (string) $row['title'];
            }
        }

        return array_values($faqs);
    }

    public static function filterFaqIdsByShop(array $faqIds, int $shopId): array
    {
        if ($shopId <= 0) {
            return [];
        }

        $faqIds = array_map('intval', $faqIds);
        $faqIds = array_values(array_unique(array_filter($faqIds, static function ($faqId) {
            return $faqId > 0;
        })));

        if (empty($faqIds)) {
            return [];
        }

        $query = new DbQuery();
        $query->select(self::$definition['primary']);
        $query->from(self::$definition['table']);
        $query->where('id_shop = ' . (int) $shopId);
        $query->where(self::$definition['primary'] . ' IN (' . implode(',', $faqIds) . ')');

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (empty($rows)) {
            return [];
        }

        $validIds = array_map(static function ($row) {
            return (int) $row[self::$definition['primary']];
        }, $rows);

        sort($validIds);

        return $validIds;
    }

    public static function updatePositions(array $orderedIds, int $shopId): bool
    {
        if (empty($orderedIds)) {
            return true;
        }

        $db = Db::getInstance();
        $position = 0;
        $seen = [];

        foreach ($orderedIds as $id) {
            $id = (int) $id;
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;

            $updated = $db->update(
                'everblock_faq',
                ['position' => (int) $position],
                'id_everblock_faq = ' . (int) $id . ' AND id_shop = ' . (int) $shopId
            );

            if (!$updated) {
                return false;
            }

            EverblockFaqProduct::clearCacheForFaq((int) $id);
            ++$position;
        }

        static::clearPositionsCache((int) $shopId);

        return true;
    }

    protected static function clearPositionsCache(int $shopId): void
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            EverblockCache::cacheDrop('EverblockFaq_getAllFaq_' . (int) $shopId . '_' . $idLang);
            EverblockCache::cacheDropByPattern('EverblockFaq_getFaqByTagName_' . (int) $shopId . '_' . $idLang . '_');
        }

        EverblockCache::cacheDropByPattern('EverblockFaq_getAllFaq_' . (int) $shopId . '_');
        EverblockCache::cacheDropByPattern('EverblockFaq_getFaqByTagName_' . (int) $shopId . '_');
        EverblockFaqProduct::clearCacheForShop((int) $shopId);
    }

    public static function getActiveFaqsByProductGroupedByTag(int $productId, int $shopId, int $langId): array
    {
        if ($productId <= 0 || $shopId <= 0 || $langId <= 0) {
            return [];
        }

        $cacheId = EverblockCache::buildFaqProductCacheKey((int) $shopId, (int) $langId, (int) $productId);

        if (!EverblockCache::isCacheStored($cacheId)) {
            $query = new DbQuery();
            $query->select('f.' . self::$definition['primary']);
            $query->from(self::$definition['table'], 'f');
            $query->innerJoin(
                'everblock_faq_product',
                'fp',
                'fp.id_everblock_faq = f.' . self::$definition['primary']
            );
            $query->where('fp.id_product = ' . (int) $productId);
            $query->where('fp.id_shop = ' . (int) $shopId);
            $query->where('f.id_shop = ' . (int) $shopId);
            $query->where('f.active = 1');
            $query->orderBy('f.tag_name ASC, f.position ASC, f.' . self::$definition['primary'] . ' ASC');

            $faqIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

            $groupedFaqs = [];

            foreach ($faqIds as $faqRow) {
                $faqId = (int) $faqRow[self::$definition['primary']];

                if ($faqId <= 0) {
                    continue;
                }

                $faq = new self($faqId, (int) $langId, (int) $shopId);

                if (!(int) $faq->active) {
                    continue;
                }

                $tag = (string) $faq->tag_name;

                if (!isset($groupedFaqs[$tag])) {
                    $groupedFaqs[$tag] = [];
                }

                $groupedFaqs[$tag][] = $faq;
            }

            EverblockCache::cacheStore($cacheId, $groupedFaqs);
        }

        $cachedFaqs = EverblockCache::cacheRetrieve($cacheId);

        if (!is_array($cachedFaqs)) {
            return [];
        }

        return $cachedFaqs;
    }

    public function add($autoDate = true, $nullValues = false)
    {
        $result = parent::add($autoDate, $nullValues);

        if ($result) {
            EverblockFaqProduct::clearCacheForFaq((int) $this->id);
        }

        return $result;
    }

    public function update($nullValues = false)
    {
        $result = parent::update($nullValues);

        if ($result) {
            EverblockFaqProduct::clearCacheForFaq((int) $this->id);
        }

        return $result;
    }

    public function delete()
    {
        $faqId = (int) $this->id;

        if (!parent::delete()) {
            return false;
        }

        $associationsDeleted = EverblockFaqProduct::deleteByFaq($faqId);
        EverblockFaqProduct::clearCacheForFaq($faqId);

        return (bool) $associationsDeleted;
    }

    public function toggleStatus()
    {
        $result = parent::toggleStatus();

        if ($result) {
            EverblockFaqProduct::clearCacheForFaq((int) $this->id);
        }

        return $result;
    }
}
