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

namespace Everblock\Tools\Service;

use ArrayObject;
use Everblock\Tools\Repository\EverBlockModalRepository;

class EverBlockModalProvider
{
    public function __construct(private readonly EverBlockModalRepository $repository)
    {
        if (class_exists(\EverblockModal::class) && method_exists(\EverblockModal::class, 'setProvider')) {
            \EverblockModal::setProvider($this);
        }
    }

    public function findModalIdByProduct(int $productId, int $shopId): ?int
    {
        return $this->repository->findModalIdByProduct($productId, $shopId);
    }

    public function getModalForProduct(int $productId, int $shopId, int $languageId): ?ArrayObject
    {
        $row = $this->repository->getModalForProduct($productId, $shopId, $languageId);
        if (null === $row) {
            return null;
        }

        return new ArrayObject([
            'id_everblock_modal' => isset($row['id_everblock_modal']) ? (int) $row['id_everblock_modal'] : 0,
            'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : 0,
            'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : 0,
            'file' => $row['file'] ?? '',
            'content' => $row['content'] ?? '',
        ], ArrayObject::ARRAY_AS_PROPS);
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->repository->clearCacheForShop($shopId);
    }

    public function clearCacheForModal(int $modalId, int $shopId): void
    {
        $this->repository->clearCacheForModal($modalId, $shopId);
    }
}
