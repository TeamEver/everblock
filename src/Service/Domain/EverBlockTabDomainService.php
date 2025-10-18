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

use Everblock\Tools\Entity\EverBlockTab;
use Everblock\Tools\Entity\EverBlockTabTranslation;
use Everblock\Tools\Repository\EverBlockTabRepository;

class EverBlockTabDomainService
{
    public function __construct(private readonly EverBlockTabRepository $repository)
    {
    }

    /**
     * @return EverBlockTab[]
     */
    public function getTabsForAdmin(int $productId, int $shopId): array
    {
        $rows = $this->repository->getTabsForAdmin($productId, $shopId);

        return array_map(function (array $row) use ($shopId) {
            return $this->hydrateTab($row, $shopId);
        }, $rows);
    }

    public function getTabs(int $productId, int $shopId, int $languageId): array
    {
        return $this->repository->getTabs($productId, $shopId, $languageId);
    }

    /**
     * @param array<int, array{title: string|null, content: string|null}> $translations
     */
    public function save(EverBlockTab $tab, array $translations): EverBlockTab
    {
        $titles = [];
        $contents = [];
        foreach ($translations as $languageId => $data) {
            $titles[(int) $languageId] = $data['title'] ?? null;
            $contents[(int) $languageId] = $data['content'] ?? null;
        }

        $tabId = $this->repository->saveTab(
            $tab->getProductId(),
            $tab->getShopId(),
            $tab->getTabId(),
            $titles,
            $contents
        );

        $tab->setId($tabId);

        return $tab;
    }

    public function deleteByProduct(int $productId, int $shopId): void
    {
        $this->repository->deleteTabsByProduct($productId, $shopId);
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    private function hydrateTab(array $row, int $shopId): EverBlockTab
    {
        $tab = new EverBlockTab();
        $tab->setId((int) ($row['id_everblock_tabs'] ?? 0));
        $tab->setProductId((int) ($row['id_product'] ?? 0));
        $tab->setShopId((int) ($row['id_shop'] ?? $shopId));
        $tab->setTabId((int) ($row['id_tab'] ?? 0));

        if (isset($row['title']) && is_array($row['title'])) {
            foreach ($row['title'] as $languageId => $title) {
                $translation = new EverBlockTabTranslation($tab, (int) $languageId, $tab->getShopId());
                $translation->setTitle($title);
                $translation->setContent($row['content'][$languageId] ?? null);
                $tab->addTranslation($translation);
            }
        }

        return $tab;
    }
}

