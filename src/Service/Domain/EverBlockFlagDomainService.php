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

namespace Everblock\Tools\Service\Domain;

use Everblock\Tools\Entity\EverBlockFlag;
use Everblock\Tools\Entity\EverBlockFlagTranslation;
use Everblock\Tools\Repository\EverBlockFlagRepository;

class EverBlockFlagDomainService
{
    public function __construct(private readonly EverBlockFlagRepository $repository)
    {
    }

    /**
     * @return EverBlockFlag[]
     */
    public function getFlagsForAdmin(int $productId, int $shopId): array
    {
        $rows = $this->repository->getFlagsForAdmin($productId, $shopId);

        return array_map(function (array $row) use ($shopId) {
            return $this->hydrateFlag($row, $shopId);
        }, $rows);
    }

    public function getFlags(int $productId, int $shopId, int $languageId): array
    {
        return $this->repository->getFlags($productId, $shopId, $languageId);
    }

    /**
     * @param array<int, array{title: string|null, content: string|null}> $translations
     */
    public function save(EverBlockFlag $flag, array $translations): EverBlockFlag
    {
        $titles = [];
        $contents = [];
        foreach ($translations as $languageId => $data) {
            $titles[(int) $languageId] = $data['title'] ?? null;
            $contents[(int) $languageId] = $data['content'] ?? null;
        }

        $flagId = $this->repository->saveFlag(
            $flag->getProductId(),
            $flag->getShopId(),
            $flag->getFlagId(),
            $titles,
            $contents
        );

        $flag->setId($flagId);

        return $flag;
    }

    public function deleteByProduct(int $productId, int $shopId): void
    {
        $this->repository->deleteFlagsByProduct($productId, $shopId);
    }

    public function hasFlagsForShop(int $shopId): bool
    {
        return $this->repository->hasFlagsForShop($shopId);
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    private function hydrateFlag(array $row, int $shopId): EverBlockFlag
    {
        $flag = new EverBlockFlag();
        $flag->setId((int) ($row['id_everblock_flags'] ?? 0));
        $flag->setProductId((int) ($row['id_product'] ?? 0));
        $flag->setShopId((int) ($row['id_shop'] ?? $shopId));
        $flag->setFlagId((int) ($row['id_flag'] ?? 0));

        if (isset($row['title']) && is_array($row['title'])) {
            foreach ($row['title'] as $languageId => $title) {
                $translation = new EverBlockFlagTranslation($flag, (int) $languageId, $flag->getShopId());
                $translation->setTitle($title);
                $translation->setContent($row['content'][$languageId] ?? null);
                $flag->addTranslation($translation);
            }
        }

        return $flag;
    }
}

