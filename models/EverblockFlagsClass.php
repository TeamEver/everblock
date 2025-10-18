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
use Everblock\Tools\Service\EverBlockFlagProvider;
use Everblock\Tools\Service\EverblockCache;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if (!defined('_PS_VERSION_')) {
    exit;
}
class EverblockFlagsClass extends ObjectModel
{
    /** @var EverBlockFlagProvider|null */
    protected static $provider;

    public $id_everblock_flags;
    public $id_product;
    public $id_shop;
    public $id_flag;
    public $title;
    public $content;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'everblock_flags',
        'primary' => 'id_everblock_flags',
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
            'id_flag' => [
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

    public static function setProvider(EverBlockFlagProvider $provider): void
    {
        static::$provider = $provider;
    }

    protected static function getProvider(): ?EverBlockFlagProvider
    {
        if (static::$provider instanceof EverBlockFlagProvider) {
            return static::$provider;
        }

        if (class_exists(SymfonyContainer::class)) {
            $container = SymfonyContainer::getInstance();
            if (null !== $container && $container->has(EverBlockFlagProvider::class)) {
                $provider = $container->get(EverBlockFlagProvider::class);
                if ($provider instanceof EverBlockFlagProvider) {
                    static::$provider = $provider;

                    return $provider;
                }
            }
        }

        return null;
    }

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
        $flagIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $return = [];
        foreach ($flagIds as $tab) {
            $return[] = new self(
                (int) $tab[self::$definition['primary']]
            );
        }
        return $return;
    }

    public static function getByIdProductIdFlag(int $productId, int $shopId, int $flagId)
    {
        $sql = new DbQuery();
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int) $productId);
        $sql->where('id_shop = ' . (int) $shopId);
        $sql->where('id_flag = ' . (int) $flagId);
        $flagId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($flagId) {
            return new self(
                (int) $flagId
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
    public static function getByIdProduct(int $productId, int $shopId, int $langId): array
    {
        $cache_id = 'EverblockFlagsClass_getByIdProduct_'
        . (int) $productId
        . '_'
        . (int) $shopId
        . '_'
        . (int) $langId;
        if (!EverblockCache::isCacheStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select(self::$definition['primary']);
            $sql->from(self::$definition['table']);
            $sql->where('id_product = ' . (int) $productId);
            $sql->where('id_shop = ' . (int) $shopId);
            $sql->orderBy('id_flag');
            $flagIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            foreach ($flagIds as $flag) {
                $return[] = new self(
                    (int) $flag[self::$definition['primary']],
                    (int) $langId,
                    (int) $shopId
                );
            }
            EverblockCache::cacheStore($cache_id, $return);
            return $return;
        }
        return EverblockCache::cacheRetrieve($cache_id);
    }
}
