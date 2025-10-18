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
use Everblock\Tools\Repository\EverBlockFlagRepository;

class EverBlockFlagProvider
{
    public function __construct(private readonly EverBlockFlagRepository $repository)
    {
        if (class_exists(\EverblockFlagsClass::class) && method_exists(\EverblockFlagsClass::class, 'setProvider')) {
            \EverblockFlagsClass::setProvider($this);
        }
    }

    /**
     * @return ArrayObject<int, mixed>[]
     */
    public function getFlagsForAdmin(int $productId, int $shopId): array
    {
        return $this->hydrateAdmin($this->repository->getFlagsForAdmin($productId, $shopId));
    }

    /**
     * @return ArrayObject<int, mixed>[]
     */
    public function getFlags(int $productId, int $shopId, int $languageId): array
    {
        return $this->hydrateFront($this->repository->getFlags($productId, $shopId, $languageId));
    }

    /**
     * @param array<int, array<string, string|null>> $translations
     */
    public function saveFlag(int $productId, int $shopId, int $flagId, array $translations): int
    {
        $titles = [];
        $contents = [];

        foreach ($translations as $languageId => $data) {
            $langId = (int) $languageId;
            $title = $data['title'] ?? null;
            $content = $data['content'] ?? null;

            $titles[$langId] = null === $title ? null : (string) $title;
            $contents[$langId] = null === $content ? null : (string) $content;
        }

        return $this->repository->saveFlag($productId, $shopId, $flagId, $titles, $contents);
    }

    public function deleteFlagsByProduct(int $productId, int $shopId): void
    {
        $this->repository->deleteFlagsByProduct($productId, $shopId);
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    public function clearCacheForProduct(int $productId, int $shopId): void
    {
        $this->repository->clearCacheForProduct($productId, $shopId);
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->repository->clearCacheForShop($shopId);
    }

    public function hasFlagsForShop(int $shopId): bool
    {
        return $this->repository->hasFlagsForShop($shopId);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return ArrayObject<int, mixed>[]
     */
    private function hydrateAdmin(array $rows): array
    {
        $flags = [];
        foreach ($rows as $row) {
            $flags[] = new ArrayObject([
                'id_everblock_flags' => isset($row['id_everblock_flags']) ? (int) $row['id_everblock_flags'] : 0,
                'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : 0,
                'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : 0,
                'id_flag' => isset($row['id_flag']) ? (int) $row['id_flag'] : 0,
                'title' => $row['title'] ?? [],
                'content' => $row['content'] ?? [],
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        return $flags;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return ArrayObject<int, mixed>[]
     */
    private function hydrateFront(array $rows): array
    {
        $flags = [];
        foreach ($rows as $row) {
            $flags[] = new ArrayObject([
                'id_everblock_flags' => isset($row['id_everblock_flags']) ? (int) $row['id_everblock_flags'] : 0,
                'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : 0,
                'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : 0,
                'id_flag' => isset($row['id_flag']) ? (int) $row['id_flag'] : 0,
                'title' => isset($row['title']) ? (string) $row['title'] : '',
                'content' => $row['content'] ?? '',
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        return $flags;
    }
}
