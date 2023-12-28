<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
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
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
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

    public static function getAllShortcodes($id_shop, $id_lang)
    {
        $cache_id = 'EverblockShortcode::getAllShortcodes_'
        . (int) $id_shop
        . '_'
        . (int) $id_shop;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('everblock_shortcode');
            $shortcodes = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            foreach ($shortcodes as $short_array) {
                $shortcode = new self(
                    (int) $short_array['id_everblock_shortcode'],
                    (int) $id_lang,
                    (int) $id_shop
                );
                $return[] = $shortcode;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getAllShortcodeIds($idShop)
    {
        $cache_id = 'EverblockShortcode::getAllShortcodeIds_'
        . (int) $idShop;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('everblock_shortcode');
            $sql->where('id_shop = ' . (int) $idShop);
            $return = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getSeoShortcodeById($idShortcode, $id_shop, $id_lang)
    {
        $cache_id = 'EverblockShortcode::getSeoShortcodeById_'
        . (int) $idShortcode
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_lang;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('everblock_shortcode');
            $sql->where(
                'id_everblock_shortcode = ' . (int) $idShortcode
            );
            $sql->where(
                'id_lang = ' . (int) $id_lang
            );
            $sql->where(
                'id_shop = ' . (int) $id_shop
            );
            $return = new self(
                (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql),
                (int) $id_lang,
                (int) $id_shop
            );
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getEverShortcode($shortcode, $id_shop, $id_lang)
    {
        $cache_id = 'EverblockShortcode::getEverShortcode_'
        . (string) $shortcode
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_lang;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('everblock_shortcode');
            $sql->where(
                'shortcode = ' . pSQL($shortcode)
            );
            $sql->where(
                'id_lang = ' . (int) $id_lang
            );
            $sql->where(
                'id_shop = ' . (int) $id_shop
            );
            $shortcode = new self(
                (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql),
                (int) $id_lang,
                (int) $id_shop
            );
            $return = $shortcode->content;
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }
}
