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
use Everblock\Tools\Service\EverBlockFaqProvider;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockFaq extends ObjectModel
{
    /** @var EverBlockFaqProvider|null */
    protected static $provider;

    private static function triggerLegacyDeprecation(string $method): void
    {
        @trigger_error(sprintf('%s::%s() is deprecated and will be removed in a future major version.', __CLASS__, $method), E_USER_DEPRECATED);
    }

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

    public static function setProvider(EverBlockFaqProvider $provider): void
    {
        static::triggerLegacyDeprecation(__METHOD__);
        static::$provider = $provider;
    }

    protected static function getProvider(): EverBlockFaqProvider
    {
        static::triggerLegacyDeprecation(__METHOD__);
        if (static::$provider instanceof EverBlockFaqProvider) {
            return static::$provider;
        }

        throw new \RuntimeException('EverBlockFaqProvider service is not available.');
    }

    public static function getAllFaq(int $shopId, int $langId): array
    {
        static::triggerLegacyDeprecation(__METHOD__);
        return static::getProvider()->getAllFaq($shopId, $langId);
    }

    public static function getFaqByTagName(int $shopId, int $langId, string $tagName): array
    {
        static::triggerLegacyDeprecation(__METHOD__);
        return static::getProvider()->getFaqByTagName($shopId, $langId, $tagName);
    }
}
