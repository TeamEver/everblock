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

use Everblock\Tools\Entity\EverBlockModal;
use Everblock\Tools\Entity\EverBlockModalTranslation;
use Everblock\Tools\Repository\EverBlockModalRepository;

class EverBlockModalDomainService
{
    public function __construct(private readonly EverBlockModalRepository $repository)
    {
    }

    public function findByProduct(int $productId, int $shopId, int $languageId): ?array
    {
        return $this->repository->getModalForProduct($productId, $shopId, $languageId);
    }

    public function find(int $modalId, int $shopId): ?EverBlockModal
    {
        return $this->repository->findById($modalId, $shopId);
    }

    public function findEntityByProduct(int $productId, int $shopId): ?EverBlockModal
    {
        $modalId = $this->repository->findModalIdByProduct($productId, $shopId);

        if (null === $modalId) {
            return null;
        }

        return $this->repository->findById($modalId, $shopId);
    }

    public function getOrCreateForProduct(int $productId, int $shopId): EverBlockModal
    {
        $modal = $this->findEntityByProduct($productId, $shopId);

        if ($modal instanceof EverBlockModal) {
            return $modal;
        }

        $modal = new EverBlockModal();
        $modal->setProductId($productId);
        $modal->setShopId($shopId);

        return $modal;
    }

    /**
     * @param array<int, array{content: string|null}> $translations
     */
    public function save(EverBlockModal $modal, array $translations): EverBlockModal
    {
        return $this->repository->save($modal, $translations);
    }

    public function delete(int $modalId, int $shopId): void
    {
        $this->repository->delete($modalId, $shopId);
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    /**
     * @param array<int, string|null> $contents
     */
    public function buildTranslations(EverBlockModal $modal, array $contents): array
    {
        $translations = [];
        foreach ($contents as $languageId => $content) {
            $translation = new EverBlockModalTranslation($modal, (int) $languageId);
            $translation->setContent($content);
            $modal->addTranslation($translation);
            $translations[(int) $languageId] = ['content' => $content];
        }

        return $translations;
    }
}

