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

class EverblockFaqProduct extends ObjectModel
{
    /** @var int */
    public $id_everblock_faq_product;

    /** @var int */
    public $id_product;

    /** @var int */
    public $id_shop;

    /** @var int */
    public $id_everblock_faq;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = [
        'table' => 'everblock_faq_product',
        'primary' => 'id_everblock_faq_product',
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_everblock_faq' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        $result = parent::add($autoDate, $nullValues);

        if ($result) {
            static::clearCacheForProduct((int) $this->id_product, (int) $this->id_shop);
        }

        return $result;
    }

    public function delete()
    {
        $productId = (int) $this->id_product;
        $shopId = (int) $this->id_shop;

        $result = parent::delete();

        if ($result) {
            static::clearCacheForProduct($productId, $shopId);
        }

        return $result;
    }

    public static function addAssociation(int $productId, int $shopId, int $faqId): bool
    {
        if ($productId <= 0 || $shopId <= 0 || $faqId <= 0) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int) $productId);
        $sql->where('id_shop = ' . (int) $shopId);
        $sql->where('id_everblock_faq = ' . (int) $faqId);

        $exists = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if ($exists > 0) {
            return true;
        }

        $association = new self();
        $association->id_product = (int) $productId;
        $association->id_shop = (int) $shopId;
        $association->id_everblock_faq = (int) $faqId;

        $saved = $association->save();

        if ($saved) {
            static::clearCacheForProduct($productId, $shopId);
        }

        return $saved;
    }

    public static function removeAssociation(int $productId, int $shopId, int $faqId): bool
    {
        if ($productId <= 0 || $shopId <= 0 || $faqId <= 0) {
            return false;
        }

        $deleted = Db::getInstance()->delete(
            self::$definition['table'],
            'id_product = ' . (int) $productId
            . ' AND id_shop = ' . (int) $shopId
            . ' AND id_everblock_faq = ' . (int) $faqId
        );

        if ($deleted) {
            static::clearCacheForProduct($productId, $shopId);
        }

        return (bool) $deleted;
    }

    public static function deleteByFaq(int $faqId): bool
    {
        if ($faqId <= 0) {
            return true;
        }

        $associations = static::getProductAssociationsByFaq($faqId);

        if (empty($associations)) {
            return true;
        }

        $deleted = Db::getInstance()->delete(
            self::$definition['table'],
            'id_everblock_faq = ' . (int) $faqId
        );

        if ($deleted) {
            static::clearCacheForAssociations($associations);
        }

        return (bool) $deleted;
    }

    public static function getFaqIdsByProduct(int $productId, int $shopId): array
    {
        if ($productId <= 0 || $shopId <= 0) {
            return [];
        }

        $sql = new DbQuery();
        $sql->select('id_everblock_faq');
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int) $productId);
        $sql->where('id_shop = ' . (int) $shopId);

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (empty($rows)) {
            return [];
        }

        return array_map(static function ($row) {
            return (int) $row['id_everblock_faq'];
        }, $rows);
    }

    public static function getProductAssociationsByFaq(int $faqId): array
    {
        if ($faqId <= 0) {
            return [];
        }

        $sql = new DbQuery();
        $sql->select('id_product, id_shop');
        $sql->from(self::$definition['table']);
        $sql->where('id_everblock_faq = ' . (int) $faqId);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function clearCacheForProduct(int $productId, int $shopId): void
    {
        if ($productId <= 0 || $shopId <= 0) {
            return;
        }

        EverblockCache::clearFaqProductCache((int) $shopId, (int) $productId);
    }

    public static function clearCacheForFaq(int $faqId): void
    {
        $associations = static::getProductAssociationsByFaq($faqId);

        if (empty($associations)) {
            return;
        }

        static::clearCacheForAssociations($associations);
    }

    public static function clearCacheForShop(int $shopId): void
    {
        if ($shopId <= 0) {
            return;
        }

        $sql = new DbQuery();
        $sql->select('DISTINCT id_product');
        $sql->from(self::$definition['table']);
        $sql->where('id_shop = ' . (int) $shopId);

        $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
            static::clearCacheForProduct((int) $product['id_product'], (int) $shopId);
        }
    }

    protected static function clearCacheForAssociations(array $associations): void
    {
        if (empty($associations)) {
            return;
        }

        $handled = [];

        foreach ($associations as $association) {
            $productId = (int) $association['id_product'];
            $shopId = (int) $association['id_shop'];

            if ($productId <= 0 || $shopId <= 0) {
                continue;
            }

            $key = $shopId . '-' . $productId;

            if (isset($handled[$key])) {
                continue;
            }

            $handled[$key] = true;
            static::clearCacheForProduct($productId, $shopId);
        }
    }
}
