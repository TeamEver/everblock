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

use DateTimeImmutable;
use Everblock\Tools\Entity\EverBlock;
use Everblock\Tools\Repository\EverBlockRepository;

class EverBlockDomainService
{
    public function __construct(private readonly EverBlockRepository $repository)
    {
    }

    public function getAllBlocks(int $languageId, int $shopId): array
    {
        return $this->repository->getAllBlocks($languageId, $shopId);
    }

    public function getBlocks(int $hookId, int $languageId, int $shopId): array
    {
        return $this->repository->getBlocks($hookId, $languageId, $shopId);
    }

    public function getBootstrapColClass(int $colNumber): string
    {
        return $this->repository->getBootstrapColClass($colNumber);
    }

    public function cleanBlocksCacheOnDate(int $languageId, int $shopId): void
    {
        $blocks = $this->repository->getAllBlocks($languageId, $shopId);

        $now = new DateTimeImmutable('now');
        $formattedNow = $now->format('Y-m-d H:i:s');

        foreach ($blocks as $block) {
            $needsFlush = false;

            if (!empty($block['date_start']) && $block['date_start'] !== '0000-00-00 00:00:00' && $block['date_start'] > $formattedNow) {
                $needsFlush = true;
            }

            if (!empty($block['date_end']) && $block['date_end'] !== '0000-00-00 00:00:00' && $block['date_end'] < $formattedNow) {
                $needsFlush = true;
            }

            if (true === $needsFlush) {
                $this->repository->clearCacheForHook((int) $block['id_hook']);
            }
        }
    }

    public function find(int $blockId, int $shopId): ?EverBlock
    {
        return $this->repository->findById($blockId, $shopId);
    }

    /**
     * @param array<int, array{content: string|null, custom_code: string|null}> $translations
     */
    public function save(EverBlock $block, array $translations): EverBlock
    {
        return $this->repository->save($block, $translations);
    }

    public function delete(int $blockId, int $shopId): void
    {
        $this->repository->delete($blockId, $shopId);
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }
}

