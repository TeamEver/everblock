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
use Everblock\Tools\Repository\EverBlockTabRepository;

class EverBlockTabProvider
{
    public function __construct(private readonly EverBlockTabRepository $repository)
    {
        if (class_exists(\EverblockTabsClass::class) && method_exists(\EverblockTabsClass::class, 'setProvider')) {
            \EverblockTabsClass::setProvider($this);
        }
    }

    /**
     * @return ArrayObject<int, mixed>[]
     */
    public function getTabsForAdmin(int $productId, int $shopId): array
    {
        return $this->hydrateAdmin($this->repository->getTabsForAdmin($productId, $shopId));
    }

    /**
     * @return ArrayObject<int, mixed>[]
     */
    public function getTabs(int $productId, int $shopId, int $languageId): array
    {
        return $this->hydrateFront($this->repository->getTabs($productId, $shopId, $languageId));
    }

    /**
     * @param array<int, array<string, string|null>> $translations
     */
    public function saveTab(int $productId, int $shopId, int $tabId, array $translations): int
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

        return $this->repository->saveTab($productId, $shopId, $tabId, $titles, $contents);
    }

    public function deleteTabsByProduct(int $productId, int $shopId): void
    {
        $this->repository->deleteTabsByProduct($productId, $shopId);
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

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return ArrayObject<int, mixed>[]
     */
    private function hydrateAdmin(array $rows): array
    {
        $tabs = [];
        foreach ($rows as $row) {
            $tabs[] = new ArrayObject([
                'id_everblock_tabs' => isset($row['id_everblock_tabs']) ? (int) $row['id_everblock_tabs'] : 0,
                'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : 0,
                'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : 0,
                'id_tab' => isset($row['id_tab']) ? (int) $row['id_tab'] : 0,
                'title' => $row['title'] ?? [],
                'content' => $row['content'] ?? [],
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        return $tabs;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return ArrayObject<int, mixed>[]
     */
    private function hydrateFront(array $rows): array
    {
        $tabs = [];
        foreach ($rows as $row) {
            $tabs[] = new ArrayObject([
                'id_everblock_tabs' => isset($row['id_everblock_tabs']) ? (int) $row['id_everblock_tabs'] : 0,
                'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : 0,
                'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : 0,
                'id_tab' => isset($row['id_tab']) ? (int) $row['id_tab'] : 0,
                'title' => isset($row['title']) ? (string) $row['title'] : '',
                'content' => $row['content'] ?? '',
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        return $tabs;
    }
}
