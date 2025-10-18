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
use DateTime;
use Everblock\Tools\Service\EverBlockProvider;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use RuntimeException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverBlockClass extends ObjectModel
{
    /** @var EverBlockProvider|null */
    protected static $provider;

    private static function triggerLegacyDeprecation(string $method): void
    {
        @trigger_error(sprintf('%s::%s() is deprecated and will be removed in a future major version.', __CLASS__, $method), E_USER_DEPRECATED);
    }

    public $id_everblock;
    public $name;
    public $content;
    public $custom_code;    
    public $only_home;
    public $only_category;
    public $only_category_product;
    public $only_manufacturer;
    public $only_supplier;
    public $only_cms_category;
    public $obfuscate_link;
    public $add_container;
    public $lazyload;
    public $id_hook;
    public $device;
    public $groups;
    public $background;
    public $css_class;
    public $data_attribute;
    public $bootstrap_class;
    public $position;
    public $id_shop;
    public $categories;
    public $manufacturers;
    public $suppliers;
    public $cms_categories;
    public $modal;
    public $delay;
    public $timeout;
    public $date_start;
    public $date_end;
    public $active;

    public static $definition = [
        'table' => 'everblock',
        'primary' => 'id_everblock',
        'multilang' => true,
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => true,
            ],
            'id_hook' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'only_home' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'only_category' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'only_category_product' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'only_manufacturer' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'only_supplier' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'only_cms_category' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'obfuscate_link' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'add_container' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'lazyload' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'device' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'categories' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isJson',
                'required' => false,
            ],
            'manufacturers' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isJson',
                'required' => false,
            ],
            'suppliers' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isJson',
                'required' => false,
            ],
            'cms_categories' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isJson',
                'required' => false,
            ],
            'groups' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isJson',
                'required' => false,
            ],
            'background' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isColor',
                'required' => false,
            ],
            'css_class' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false,
            ],
            'data_attribute' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false,
            ],
            'bootstrap_class' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'delay' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'timeout' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'modal' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'date_start' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'date_end' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            // lang fields
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
            'custom_code' => [
                'type' => self::TYPE_NOTHING,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
        ],
    ];

    public static function setProvider(EverBlockProvider $provider): void
    {
        static::triggerLegacyDeprecation(__METHOD__);
        static::$provider = $provider;
    }

    protected static function getProvider(): EverBlockProvider
    {
        static::triggerLegacyDeprecation(__METHOD__);
        if (static::$provider instanceof EverBlockProvider) {
            return static::$provider;
        }

        if (class_exists(SymfonyContainer::class)) {
            $container = SymfonyContainer::getInstance();
            if (null !== $container && $container->has(EverBlockProvider::class)) {
                $provider = $container->get(EverBlockProvider::class);
                if ($provider instanceof EverBlockProvider) {
                    static::$provider = $provider;

                    return $provider;
                }
            }
        }

        throw new RuntimeException('EverBlockProvider service is not available.');
    }

    public static function getAllBlocks(int $idLang, int $idShop): array
    {
        static::triggerLegacyDeprecation(__METHOD__);
        return static::getProvider()->getAllBlocks($idLang, $idShop);
    }

    public static function cleanBlocksCacheOnDate(int $idLang, int $idShop)
    {
        static::triggerLegacyDeprecation(__METHOD__);
        $provider = static::getProvider();
        $blocks = $provider->getAllBlocks($idLang, $idShop);
        foreach ($blocks as $block) {
            $cacheNeedFlush = false;
            $now = new DateTime();
            $now = $now->format('Y-m-d H:i:s');
            if (!empty($block['date_start'])
                && $block['date_start'] !== '0000-00-00 00:00:00'
                && $block['date_start'] > $now
            ) {
                $cacheNeedFlush = true;
            }
            if (!empty($block['date_end'])
                && $block['date_end'] !== '0000-00-00 00:00:00'
                && $block['date_end'] < $now
            ) {
                $cacheNeedFlush = true;
            }
            if ((bool) $cacheNeedFlush === true) {
                $provider->clearCacheForHook((int) $block['id_hook']);
            }
        }
    }

    public static function getBlocks(int $idHook, int $idLang, int $idShop): array
    {
        static::triggerLegacyDeprecation(__METHOD__);
        return static::getProvider()->getBlocks($idHook, $idLang, $idShop);
    }

    public static function getBootstrapColClass(int $colNumber)
    {
        static::triggerLegacyDeprecation(__METHOD__);
        return static::getProvider()->getBootstrapColClass($colNumber);
    }
}
