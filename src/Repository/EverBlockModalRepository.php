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

namespace Everblock\Tools\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class EverBlockModalRepository
{
    private const CACHE_TAG = 'everblock_modal';
    private const CACHE_TTL = 86400;

    public function __construct(
        private readonly Connection $connection,
        private readonly TagAwareCacheInterface $cache,
        private readonly ?string $tablePrefix = null
    ) {
    }

    public function findModalIdByProduct(int $productId, int $shopId): ?int
    {
        $cacheKey = sprintf('everblock.modal.id.%d.%d', $productId, $shopId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($productId, $shopId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag($this->buildTags($shopId));

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->select('modal.id_everblock_modal')
                ->from($this->getTableName('everblock_modal'), 'modal')
                ->where('modal.id_product = :productId')
                ->andWhere('modal.id_shop = :shopId')
                ->setParameter('productId', $productId, ParameterType::INTEGER)
                ->setParameter('shopId', $shopId, ParameterType::INTEGER)
                ->setMaxResults(1);

            $result = $queryBuilder->executeQuery()->fetchOne();

            if (false === $result) {
                return null;
            }

            return (int) $result;
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getModalForProduct(int $productId, int $shopId, int $languageId): ?array
    {
        $cacheKey = sprintf('everblock.modal.full.%d.%d.%d', $productId, $shopId, $languageId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($productId, $shopId, $languageId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag(array_merge(
                $this->buildTags($shopId),
                [sprintf('everblock_modal_lang_%d', $languageId)]
            ));

            $queryBuilder = $this->createBaseQueryBuilder($shopId)
                ->leftJoin(
                    'modal',
                    $this->getTableName('everblock_modal_lang'),
                    'modall',
                    'modal.id_everblock_modal = modall.id_everblock_modal AND modall.id_lang = :languageId'
                )
                ->andWhere('modal.id_product = :productId')
                ->setParameter('productId', $productId, ParameterType::INTEGER)
                ->setParameter('languageId', $languageId, ParameterType::INTEGER)
                ->setMaxResults(1);

            $result = $queryBuilder->executeQuery()->fetchAssociative();

            if (false === $result) {
                return null;
            }

            if (isset($result['id_everblock_modal'])) {
                $item->tag(sprintf('everblock_modal_id_%d', (int) $result['id_everblock_modal']));
            }

            return $result;
        });
    }

    public function clearCache(): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_modal_shop_%d', $shopId),
        ]);
    }

    public function clearCacheForModal(int $modalId, int $shopId): void
    {
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_modal_shop_%d', $shopId),
            sprintf('everblock_modal_id_%d', $modalId),
        ]);
    }

    private function createBaseQueryBuilder(int $shopId): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                'modal.id_everblock_modal',
                'modal.id_product',
                'modal.id_shop',
                'modal.file',
                'modall.content'
            )
            ->from($this->getTableName('everblock_modal'), 'modal')
            ->where('modal.id_shop = :shopId')
            ->setParameter('shopId', $shopId, ParameterType::INTEGER);

        return $queryBuilder;
    }

    /**
     * @return string[]
     */
    private function buildTags(int $shopId): array
    {
        return [
            self::CACHE_TAG,
            sprintf('everblock_modal_shop_%d', $shopId),
        ];
    }

    private function getTableName(string $table): string
    {
        if (null !== $this->tablePrefix && '' !== $this->tablePrefix) {
            return $this->tablePrefix . $table;
        }

        if (defined('_DB_PREFIX_')) {
            return _DB_PREFIX_ . $table;
        }

        return $table;
    }
}
