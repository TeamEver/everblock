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
class EverblockTabsClass extends ObjectModel
{
    public $id_everblock_tabs;
    public $id_product;
    public $id_shop;
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
     * get tab object per product & shop
     * @param int productId
     * @param int shopId
     * @return $obj
    */
    public static function getByIdProduct($productId, $shopId)
    {
        $cacheId = 'EverblockTabsClass::getByIdProduct_'
        . (int) $productId
        . '_'
        . (int) $shopId;
        if (!Cache::isStored($cacheId)) {
            $sql = new DbQuery();
            $sql->select('id_everblock_tabs');
            $sql->from('everblock_tabs');
            $sql->where('id_product = ' . (int) $productId);
            $sql->where('id_shop = ' . (int) $shopId);
            $tabId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            if ($tabId) {
                $obj = new self(
                    (int) $tabId
                );
            } else {
                $obj = new self();
            }
            Cache::store($cacheId, $obj);
            return $obj;
        }
        return Cache::retrieve($cacheId);
    }
}
