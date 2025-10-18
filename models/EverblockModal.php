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
use Everblock\Tools\Service\EverBlockModalProvider;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockModal extends ObjectModel
{
    /** @var EverBlockModalProvider|null */
    protected static $provider;

    private static function triggerLegacyDeprecation(string $method): void
    {
        @trigger_error(sprintf('%s::%s() is deprecated and will be removed in a future major version.', __CLASS__, $method), E_USER_DEPRECATED);
    }

    /** @var int */
    public $id_everblock_modal;

    /** @var int */
    public $id_product;

    /** @var int */
    public $id_shop;

    /** @var string */
    public $file;

    /** @var array */
    public $content;

    public static $definition = [
        'table' => 'everblock_modal',
        'primary' => 'id_everblock_modal',
        'multilang' => true,
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'file' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
        ],
    ];

    public static function setProvider(EverBlockModalProvider $provider): void
    {
        static::triggerLegacyDeprecation(__METHOD__);
        static::$provider = $provider;
    }

    protected static function getProvider(): EverBlockModalProvider
    {
        static::triggerLegacyDeprecation(__METHOD__);
        if (static::$provider instanceof EverBlockModalProvider) {
            return static::$provider;
        }

        if (class_exists(SymfonyContainer::class)) {
            $container = SymfonyContainer::getInstance();
            if (null !== $container && $container->has(EverBlockModalProvider::class)) {
                $provider = $container->get(EverBlockModalProvider::class);
                if ($provider instanceof EverBlockModalProvider) {
                    static::$provider = $provider;

                    return $provider;
                }
            }
        }

        throw new \RuntimeException('EverBlockModalProvider service is not available.');
    }

    public static function getByProductId(int $idProduct, int $idShop)
    {
        static::triggerLegacyDeprecation(__METHOD__);
        $provider = static::getProvider();
        $modalId = $provider->findModalIdByProduct($idProduct, $idShop);
        if (null !== $modalId) {
            return new self($modalId, null, $idShop);
        }

        $modal = new self();
        $modal->id_product = (int) $idProduct;
        $modal->id_shop = (int) $idShop;

        return $modal;
    }
}

