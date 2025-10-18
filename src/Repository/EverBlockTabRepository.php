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

class EverBlockTabRepository
{
    private const CACHE_TAG = 'everblock_tab';
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
    public function getTabsForAdmin(int $productId, int $shopId): array
    {
        $queryBuilder = $this->createAdminQueryBuilder()
            ->where('t.id_product = :productId')
            ->andWhere('t.id_shop = :shopId')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->orderBy('t.id_tab', 'ASC');

        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

        return $this->groupTranslations($rows, 'id_everblock_tabs');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTabs(int $productId, int $shopId, int $languageId): array
    {
        $cacheKey = sprintf('everblock.tabs.%d.%d.%d', $productId, $shopId, $languageId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($productId, $shopId, $languageId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag($this->buildTags($productId, $shopId, $languageId));

            $queryBuilder = $this->createFrontQueryBuilder()
                ->where('t.id_product = :productId')
                ->andWhere('t.id_shop = :shopId')
                ->andWhere('tl.id_lang = :languageId')
                ->setParameter('productId', $productId, ParameterType::INTEGER)
                ->setParameter('shopId', $shopId, ParameterType::INTEGER)
                ->setParameter('languageId', $languageId, ParameterType::INTEGER)
                ->orderBy('t.id_tab', 'ASC');

            $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

            $tabs = [];
            foreach ($rows as $row) {
                $tabs[] = [
                    'id_everblock_tabs' => isset($row['id_everblock_tabs']) ? (int) $row['id_everblock_tabs'] : 0,
                    'id_product' => isset($row['id_product']) ? (int) $row['id_product'] : $productId,
                    'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : $shopId,
                    'id_tab' => isset($row['id_tab']) ? (int) $row['id_tab'] : 0,
                    'title' => isset($row['title']) ? (string) $row['title'] : '',
                    'content' => $row['content'] ?? '',
                ];
            }

            return $tabs;
        });
    }

    /**
     * @param array<int, string|null> $titles
     * @param array<int, string|null> $contents
     */
    public function saveTab(int $productId, int $shopId, int $tabId, array $titles, array $contents): int
    {
        $tabPrimaryId = $this->getTabPrimaryId($productId, $shopId, $tabId);

        if (null === $tabPrimaryId) {
            $this->connection->insert(
                $this->getTableName('everblock_tabs'),
                [
                    'id_product' => $productId,
                    'id_shop' => $shopId,
                    'id_tab' => $tabId,
                ],
                [
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                ]
            );

            $tabPrimaryId = (int) $this->connection->lastInsertId();
        } else {
            $this->connection->update(
                $this->getTableName('everblock_tabs'),
                [
                    'id_product' => $productId,
                    'id_shop' => $shopId,
                    'id_tab' => $tabId,
                ],
                ['id_everblock_tabs' => $tabPrimaryId],
                [
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                    ParameterType::INTEGER,
                ]
            );
        }

        $this->saveTranslations($tabPrimaryId, $shopId, $titles, $contents);
        $this->clearCacheForProduct($productId, $shopId);

        return $tabPrimaryId;
    }

    public function deleteTabsByProduct(int $productId, int $shopId): void
    {
        $ids = $this->connection->createQueryBuilder()
            ->select('id_everblock_tabs')
            ->from($this->getTableName('everblock_tabs'))
            ->where('id_product = :productId')
            ->andWhere('id_shop = :shopId')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->executeQuery()
            ->fetchFirstColumn();

        if (!empty($ids)) {
            $this->connection->createQueryBuilder()
                ->delete($this->getTableName('everblock_tabs_lang'))
                ->where('id_everblock_tabs IN (:ids)')
                ->setParameter('ids', array_map('intval', $ids), Connection::PARAM_INT_ARRAY)
                ->executeStatement();

            $this->connection->createQueryBuilder()
                ->delete($this->getTableName('everblock_tabs'))
                ->where('id_everblock_tabs IN (:ids)')
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
            sprintf('everblock_tab_product_%d', $productId),
            sprintf('everblock_tab_shop_%d', $shopId),
        ]);
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_tab_shop_%d', $shopId),
        ]);
    }

    private function getTabPrimaryId(int $productId, int $shopId, int $tabId): ?int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('id_everblock_tabs')
            ->from($this->getTableName('everblock_tabs'))
            ->where('id_product = :productId')
            ->andWhere('id_shop = :shopId')
            ->andWhere('id_tab = :tabId')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->setParameter('tabId', $tabId, ParameterType::INTEGER)
            ->setMaxResults(1);

        $result = $queryBuilder->executeQuery()->fetchOne();

        return $result === false ? null : (int) $result;
    }

    /**
     * @param array<int, string|null> $titles
     * @param array<int, string|null> $contents
     */
    private function saveTranslations(int $tabPrimaryId, int $shopId, array $titles, array $contents): void
    {
        $this->connection->createQueryBuilder()
            ->delete($this->getTableName('everblock_tabs_lang'))
            ->where('id_everblock_tabs = :id')
            ->setParameter('id', $tabPrimaryId, ParameterType::INTEGER)
            ->executeStatement();

        $languageIds = array_unique(array_merge(array_keys($titles), array_keys($contents)));

        foreach ($languageIds as $languageId) {
            $title = $titles[$languageId] ?? null;
            $content = $contents[$languageId] ?? null;

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->insert($this->getTableName('everblock_tabs_lang'))
                ->values([
                    'id_everblock_tabs' => ':tabId',
                    'id_lang' => ':langId',
                    'id_shop' => ':shopId',
                    'title' => ':title',
                    'content' => ':content',
                ])
                ->setParameter('tabId', $tabPrimaryId, ParameterType::INTEGER)
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
                    'id_tab' => isset($row['id_tab']) ? (int) $row['id_tab'] : 0,
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
            sprintf('everblock_tab_product_%d', $productId),
            sprintf('everblock_tab_shop_%d', $shopId),
            sprintf('everblock_tab_lang_%d', $languageId),
        ];
    }

    private function createAdminQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                't.id_everblock_tabs',
                't.id_product',
                't.id_shop',
                't.id_tab',
                'tl.id_lang',
                'tl.title',
                'tl.content'
            )
            ->from($this->getTableName('everblock_tabs'), 't')
            ->leftJoin(
                't',
                $this->getTableName('everblock_tabs_lang'),
                'tl',
                't.id_everblock_tabs = tl.id_everblock_tabs'
            );

        return $queryBuilder;
    }

    private function createFrontQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                't.id_everblock_tabs',
                't.id_product',
                't.id_shop',
                't.id_tab',
                'tl.title',
                'tl.content'
            )
            ->from($this->getTableName('everblock_tabs'), 't')
            ->leftJoin(
                't',
                $this->getTableName('everblock_tabs_lang'),
                'tl',
                't.id_everblock_tabs = tl.id_everblock_tabs'
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
