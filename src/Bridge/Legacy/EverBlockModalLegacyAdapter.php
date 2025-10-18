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

namespace Everblock\Tools\Bridge\Legacy;

use ArrayObject;
use Everblock\Tools\Service\EverBlockModalProvider;

class EverBlockModalLegacyAdapter
{
    public function __construct(private readonly EverBlockModalProvider $provider)
    {
    }

    public function findModalIdByProduct(int $productId, int $shopId): ?int
    {
        return $this->provider->findModalIdByProduct($productId, $shopId);
    }

    public function getModalForProduct(int $productId, int $shopId, int $languageId): ?ArrayObject
    {
        return $this->provider->getModalForProduct($productId, $shopId, $languageId);
    }

    public function clearCache(): void
    {
        $this->provider->clearCache();
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->provider->clearCacheForShop($shopId);
    }

    public function clearCacheForModal(int $modalId, int $shopId): void
    {
        $this->provider->clearCacheForModal($modalId, $shopId);
    }
}
