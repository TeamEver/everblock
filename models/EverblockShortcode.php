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
use Everblock\Tools\Service\EverBlockShortcodeProvider;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockShortcode extends ObjectModel
{
    /** @var EverBlockShortcodeProvider|null */
    protected static $provider;

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

    public static function setProvider(EverBlockShortcodeProvider $provider): void
    {
        static::$provider = $provider;
    }

    protected static function getProvider(): EverBlockShortcodeProvider
    {
        if (static::$provider instanceof EverBlockShortcodeProvider) {
            return static::$provider;
        }

        if (class_exists(SymfonyContainer::class)) {
            $container = SymfonyContainer::getInstance();
            if (null !== $container && $container->has(EverBlockShortcodeProvider::class)) {
                $provider = $container->get(EverBlockShortcodeProvider::class);
                if ($provider instanceof EverBlockShortcodeProvider) {
                    static::$provider = $provider;

                    return $provider;
                }
            }
        }

        throw new \RuntimeException('EverBlockShortcodeProvider service is not available.');
    }

    public static function getAllShortcodes(int $idShop, int $langId): array
    {
        return static::getProvider()->getAllShortcodes($idShop, $langId);
    }

    public static function getAllShortcodeIds(int $idShop): array
    {
        return static::getProvider()->getAllShortcodeIds($idShop);
    }

    public static function getEverShortcode(string $shortcode, int $shopId, int $langId): string
    {
        return static::getProvider()->getEverShortcode($shortcode, $shopId, $langId);
    }
}
