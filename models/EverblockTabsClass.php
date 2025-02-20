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
if (!defined('_PS_VERSION_')) {
    exit;
}
class EverblockTabsClass extends ObjectModel
{
    public $id_everblock_tabs;
    public $id_product;
    public $id_shop;
    public $id_tab;
    public $title;
    public $content;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'everblock_tabs',
        'primary' => 'id_everblock_tabs',
        'multilang' => true,
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'id_tab' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'title' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
        ],
    ];

    /**
     * get tab object per product & shop, admin only (no cache)
     * @param int productId
     * @param int shopId
     * @return $obj
    */
    public static function getByIdProductInAdmin(int $productId, int $shopId): array
    {
        $sql = new DbQuery();
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int) $productId);
        $sql->where('id_shop = ' . (int) $shopId);
        $tabIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $return = [];
        foreach ($tabIds as $tab) {
            $return[] = new self(
                (int) $tab[self::$definition['primary']]
            );
        }
        return $return;
    }

    public static function getByIdProductIdTab(int $productId, int $shopId, int $tabId)
    {
        $sql = new DbQuery();
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int) $productId);
        $sql->where('id_shop = ' . (int) $shopId);
        $sql->where('id_tab = ' . (int) $tabId);
        $tabId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($tabId) {
            return new self(
                (int) $tabId
            );
        }
        return new self();
    }

    /**
     * get tab object per product & shop
     * @param int productId
     * @param int shopId
     * @return array
    */
    public static function getByIdProduct(int $productId, int $shopId, int $langId):array
    {
        $sql = new DbQuery();
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int) $productId);
        $sql->where('id_shop = ' . (int) $shopId);
        $sql->orderBy('id_tab');
        $tabIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $return = [];
        foreach ($tabIds as $tab) {
            $return[] = new self(
                (int) $tab[self::$definition['primary']],
                (int) $langId,
                (int) $shopId
            );
        }
        return $return;
    }
}
