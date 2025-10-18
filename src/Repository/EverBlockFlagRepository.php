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

class EverBlockFlagRepository
{
    private const CACHE_TAG = 'everblock_flag';
    private const CACHE_TTL = 86400;

    public function __construct(
        private readonly Connection $connection,
        private readonly TagAwareCacheInterface $cache,
        private readonly ?string $tablePrefix = null
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFlagsForAdmin(int $productId, int $shopId): array
    {
        $queryBuilder = $this->createAdminQueryBuilder()
            ->where('f.id_product = :productId')
            ->andWhere('f.id_shop = :shopId')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->orderBy('f.id_flag', 'ASC');

        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

        return $this->groupTranslations($rows, 'id_everblock_flags');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFlags(int $productId, int $shopId, int $languageId): array
    {
        $cacheKey = sprintf('everblock.flags.%d.%d.%d', $productId, $shopId, $languageId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($productId, $shopId, $languageId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag($this->buildTags($productId, $shopId, $languageId));

            $queryBuilder = $this->createFrontQueryBuilder()
                ->where('f.id_product = :productId')
                ->andWhere('f.id_shop = :shopId')
                ->andWhere('fl.id_lang = :languageId')
                ->setParameter('productId', $productId, ParameterType::INTEGER)
                ->setParameter('shopId', $shopId, ParameterType::INTEGER)
                ->setParameter('languageId', $languageId, ParameterType::INTEGER)
                ->orderBy('f.id_flag', 'ASC');

            $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

            $flags = [];
            foreach ($rows as $row) {
                $flags[] = [
                    'id_everblock_flags' => isset($row['id_everblock_flags']) ? (int) $row['id_everblock_flags'] : 0,
                    'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : $productId,
                    'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : $shopId,
                    'id_flag' => isset($row['id_flag']) ? (int) $row['id_flag'] : 0,
                    'title' => isset($row['title']) ? (string) $row['title'] : '',
                    'content' => $row['content'] ?? '',
                ];
            }

            return $flags;
        });
    }

    /**
     * @param array<int, string|null> $titles
     * @param array<int, string|null> $contents
     */
    public function saveFlag(int $productId, int $shopId, int $flagId, array $titles, array $contents): int
    {
        $flagPrimaryId = $this->getFlagPrimaryId($productId, $shopId, $flagId);

        if (null === $flagPrimaryId) {
            $this->connection->insert(
                $this->getTableName('everblock_flags'),
                [
                    'id_product' => $productId,
                    'id_shop' => $shopId,
                    'id_flag' => $flagId,
                ],
                [
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                ]
            );

            $flagPrimaryId = (int) $this->connection->lastInsertId();
        } else {
            $this->connection->update(
                $this->getTableName('everblock_flags'),
                [
                    'id_product' => $productId,
                    'id_shop' => $shopId,
                    'id_flag' => $flagId,
                ],
                ['id_everblock_flags' => $flagPrimaryId],
                [
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                ]
            );
        }

        $this->saveTranslations($flagPrimaryId, $shopId, $titles, $contents);
        $this->clearCacheForProduct($productId, $shopId);

        return $flagPrimaryId;
    }

    public function deleteFlagsByProduct(int $productId, int $shopId): void
    {
        $ids = $this->connection->createQueryBuilder()
            ->select('id_everblock_flags')
            ->from($this->getTableName('everblock_flags'))
            ->where('id_product = :productId')
            ->andWhere('id_shop = :shopId')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->executeQuery()
            ->fetchFirstColumn();

        if (!empty($ids)) {
            $this->connection->createQueryBuilder()
                ->delete($this->getTableName('everblock_flags_lang'))
                ->where('id_everblock_flags IN (:ids)')
                ->setParameter('ids', array_map('intval', $ids), Connection::PARAM_INT_ARRAY)
                ->executeStatement();

            $this->connection->createQueryBuilder()
                ->delete($this->getTableName('everblock_flags'))
                ->where('id_everblock_flags IN (:ids)')
                ->setParameter('ids', array_map('intval', $ids), Connection::PARAM_INT_ARRAY)
                ->executeStatement();
        }

        $this->clearCacheForProduct($productId, $shopId);
    }

    public function clearCache(): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);
    }

    public function clearCacheForProduct(int $productId, int $shopId): void
    {
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_flag_product_%d', $productId),
            sprintf('everblock_flag_shop_%d', $shopId),
        ]);
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_flag_shop_%d', $shopId),
        ]);
    }

    public function hasFlagsForShop(int $shopId): bool
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('1')
            ->from($this->getTableName('everblock_flags'), 'f')
            ->where('f.id_shop = :shopId')
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->setMaxResults(1);

        return (bool) $queryBuilder->executeQuery()->fetchOne();
    }

    private function getFlagPrimaryId(int $productId, int $shopId, int $flagId): ?int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('id_everblock_flags')
            ->from($this->getTableName('everblock_flags'))
            ->where('id_product = :productId')
            ->andWhere('id_shop = :shopId')
            ->andWhere('id_flag = :flagId')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->setParameter('flagId', $flagId, ParameterType::INTEGER)
            ->setMaxResults(1);

        $result = $queryBuilder->executeQuery()->fetchOne();

        return $result === false ? null : (int) $result;
    }

    /**
     * @param array<int, string|null> $titles
     * @param array<int, string|null> $contents
     */
    private function saveTranslations(int $flagPrimaryId, int $shopId, array $titles, array $contents): void
    {
        $this->connection->createQueryBuilder()
            ->delete($this->getTableName('everblock_flags_lang'))
            ->where('id_everblock_flags = :id')
            ->setParameter('id', $flagPrimaryId, ParameterType::INTEGER)
            ->executeStatement();

        $languageIds = array_unique(array_merge(array_keys($titles), array_keys($contents)));

        foreach ($languageIds as $languageId) {
            $title = $titles[$languageId] ?? null;
            $content = $contents[$languageId] ?? null;

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->insert($this->getTableName('everblock_flags_lang'))
                ->values([
                    'id_everblock_flags' => ':flagId',
                    'id_lang' => ':langId',
                    'id_shop' => ':shopId',
                    'title' => ':title',
                    'content' => ':content',
                ])
                ->setParameter('flagId', $flagPrimaryId, ParameterType::INTEGER)
                ->setParameter('langId', (int) $languageId, ParameterType::INTEGER)
                ->setParameter('shopId', $shopId, ParameterType::INTEGER)
                ->setParameter('title', $title, null === $title ? ParameterType::NULL : ParameterType::STRING)
                ->setParameter('content', $content, null === $content ? ParameterType::NULL : ParameterType::STRING)
                ->executeStatement();
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param string $identifierKey
     *
     * @return array<int, array<string, mixed>>
     */
    private function groupTranslations(array $rows, string $identifierKey): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            if (!isset($row[$identifierKey])) {
                continue;
            }

            $id = (int) $row[$identifierKey];
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    $identifierKey => $id,
                    'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : 0,
                    'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : 0,
                    'id_flag' => isset($row['id_flag']) ? (int) $row['id_flag'] : 0,
                    'title' => [],
                    'content' => [],
                ];
            }

            if (isset($row['id_lang'])) {
                $languageId = (int) $row['id_lang'];
                $grouped[$id]['title'][$languageId] = isset($row['title']) ? (string) $row['title'] : '';
                $grouped[$id]['content'][$languageId] = $row['content'] ?? '';
            }
        }

        return array_values($grouped);
    }

    /**
     * @return string[]
     */
    private function buildTags(int $productId, int $shopId, int $languageId): array
    {
        return [
            self::CACHE_TAG,
            sprintf('everblock_flag_product_%d', $productId),
            sprintf('everblock_flag_shop_%d', $shopId),
            sprintf('everblock_flag_lang_%d', $languageId),
        ];
    }

    private function createAdminQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                'f.id_everblock_flags',
                'f.id_product',
                'f.id_shop',
                'f.id_flag',
                'fl.id_lang',
                'fl.title',
                'fl.content'
            )
            ->from($this->getTableName('everblock_flags'), 'f')
            ->leftJoin(
                'f',
                $this->getTableName('everblock_flags_lang'),
                'fl',
                'f.id_everblock_flags = fl.id_everblock_flags'
            );

        return $queryBuilder;
    }

    private function createFrontQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                'f.id_everblock_flags',
                'f.id_product',
                'f.id_shop',
                'f.id_flag',
                'fl.title',
                'fl.content'
            )
            ->from($this->getTableName('everblock_flags'), 'f')
            ->leftJoin(
                'f',
                $this->getTableName('everblock_flags_lang'),
                'fl',
                'f.id_everblock_flags = fl.id_everblock_flags'
            );

        return $queryBuilder;
    }

    private function getTableName(string $table): string
    {
        if (null !== $this->tablePrefix && $this->tablePrefix !== '') {
            return $this->tablePrefix . $table;
        }

        if (defined('_DB_PREFIX_')) {
            return _DB_PREFIX_ . $table;
        }

        return $table;
    }
}
