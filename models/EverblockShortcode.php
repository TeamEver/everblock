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

class EverblockShortcode extends ObjectModel
{
    public $id_everblock_shortcode;
    public $shortcode;
    public $id_shop;
    public $id_lang;
    public $title;
    public $content;

    public static $definition = [
        'table' => 'everblock_shortcode',
        'primary' => 'id_everblock_shortcode',
        'multilang' => true,
        'fields' => [
            'id_shop' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
            ],
            'shortcode' => [
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml',
                'required' => true,
            ],
            // lang fields
            'title' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'required' => true,
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
        ],
    ];

    public static function getAllShortcodes(int $idShop, int $langId): array
    {
        $cache_id = 'EverblockShortcode_getAllShortcodes_'
        . (int) $idShop
        . '_'
        . (int) $langId;
        if (!EverblockCache::isCacheStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('everblock_shortcode');
            $shortcodes = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            foreach ($shortcodes as $short_array) {
                $shortcode = new self(
                    (int) $short_array['id_everblock_shortcode'],
                    (int) $langId,
                    (int) $idShop
                );
                $return[] = $shortcode;
            }
            EverblockCache::cacheStore($cache_id, $return);
            return $return;
        }
        return EverblockCache::cacheRetrieve($cache_id);
    }

    public static function getAllShortcodeIds(int $idShop): array
    {
        $cache_id = 'EverblockShortcode_getAllShortcodeIds_'
        . (int) $idShop;
        if (!EverblockCache::isCacheStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('everblock_shortcode');
            $sql->where('id_shop = ' . (int) $idShop);
            $return = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            EverblockCache::cacheStore($cache_id, $return);
            return $return;
        }
        return EverblockCache::cacheRetrieve($cache_id);
    }

    /**
     * @param array<int, string> $shortcodes
     *
     * @return array<int, self>
     */
    public static function getShortcodesByList(array $shortcodes, int $idShop, int $langId): array
    {
        $shortcodes = array_values(array_unique(array_filter(array_map('trim', $shortcodes))));
        if (empty($shortcodes)) {
            return [];
        }

        $cacheId = 'EverblockShortcode_getShortcodesByList_'
            . (int) $idShop
            . '_'
            . (int) $langId
            . '_'
            . md5(implode('|', $shortcodes));
        if (EverblockCache::isCacheStored($cacheId)) {
            return EverblockCache::cacheRetrieve($cacheId);
        }

        $escaped = array_map('pSQL', $shortcodes);
        $quoted = array_map(function (string $shortcode): string {
            return '"' . $shortcode . '"';
        }, $escaped);

        $sql = new DbQuery();
        $sql->select('id_everblock_shortcode');
        $sql->from('everblock_shortcode');
        $sql->where('id_shop = ' . (int) $idShop);
        $sql->where('shortcode IN (' . implode(',', $quoted) . ')');

        $ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (empty($ids)) {
            EverblockCache::cacheStore($cacheId, []);
            return [];
        }

        $return = [];
        foreach ($ids as $row) {
            $return[] = new self(
                (int) $row['id_everblock_shortcode'],
                (int) $langId,
                (int) $idShop
            );
        }
        EverblockCache::cacheStore($cacheId, $return);

        return $return;
    }

    public static function getEverShortcode(string $shortcode, int $shopId, int $langId): string
    {
        $cache_id = 'EverblockShortcode_getEverShortcode_'
        . trim($shortcode)
        . '_'
        . (int) $shopId
        . '_'
        . (int) $langId;
        if (!EverblockCache::isCacheStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('everblock_shortcode');
            $sql->where(
                'shortcode = "' . pSQL($shortcode) . '"'
            );
            $sql->where(
                'id_lang = ' . (int) $langId
            );
            $sql->where(
                'id_shop = ' . (int) $shopId
            );
            $shortcode = new self(
                (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql),
                (int) $langId,
                (int) $shopId
            );
            $return = $shortcode->content;
            EverblockCache::cacheStore($cache_id, $return);
            return $return;
        }
        return EverblockCache::cacheRetrieve($cache_id);
    }
}
