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

class EverBlockShortcodeRepository
{
    private const CACHE_TAG = 'everblock_shortcode';
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
    public function getAllShortcodes(int $shopId, int $languageId): array
    {
        $cacheKey = sprintf('everblock.shortcode.all.%d.%d', $shopId, $languageId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($shopId, $languageId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag($this->buildTags($shopId, $languageId));

            $queryBuilder = $this->createBaseQueryBuilder($shopId)
                ->andWhere('shortcode_lang.id_lang = :languageId')
                ->setParameter('languageId', $languageId, ParameterType::INTEGER)
                ->orderBy('shortcode.id_everblock_shortcode', 'ASC');

            return $queryBuilder->executeQuery()->fetchAllAssociative();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllShortcodeIds(int $shopId): array
    {
        $cacheKey = sprintf('everblock.shortcode.ids.%d', $shopId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($shopId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag($this->buildTags($shopId));

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->select('shortcode.id_everblock_shortcode')
                ->from($this->getTableName('everblock_shortcode'), 'shortcode')
                ->where('shortcode.id_shop = :shopId')
                ->setParameter('shopId', $shopId, ParameterType::INTEGER)
                ->orderBy('shortcode.id_everblock_shortcode', 'ASC');

            return $queryBuilder->executeQuery()->fetchAllAssociative();
        });
    }

    public function getEverShortcode(string $shortcode, int $shopId, int $languageId): string
    {
        $normalizedShortcode = $this->normalizeShortcode($shortcode);
        $cacheKey = sprintf(
            'everblock.shortcode.content.%d.%d.%s',
            $shopId,
            $languageId,
            $normalizedShortcode
        );

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($shortcode, $shopId, $languageId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag(array_merge(
                $this->buildTags($shopId, $languageId),
                [sprintf('everblock_shortcode_code_%s', $this->normalizeShortcode($shortcode))]
            ));

            $queryBuilder = $this->createBaseQueryBuilder($shopId)
                ->select('shortcode_lang.content')
                ->andWhere('shortcode_lang.id_lang = :languageId')
                ->andWhere('shortcode.shortcode = :code')
                ->setParameter('languageId', $languageId, ParameterType::INTEGER)
                ->setParameter('code', trim($shortcode), ParameterType::STRING)
                ->setMaxResults(1);

            $content = $queryBuilder->executeQuery()->fetchOne();

            if (false === $content) {
                return '';
            }

            return (string) $content;
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getShortcodeForForm(int $shortcodeId, int $shopId): ?array
    {
        $cacheKey = sprintf('everblock.shortcode.form.%d.%d', $shortcodeId, $shopId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($shortcodeId, $shopId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag([
                self::CACHE_TAG,
                sprintf('everblock_shortcode_shop_%d', $shopId),
                sprintf('everblock_shortcode_id_%d', $shortcodeId),
            ]);

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->select(
                    'shortcode.id_everblock_shortcode',
                    'shortcode.id_shop',
                    'shortcode.shortcode',
                    'shortcode_lang.id_lang',
                    'shortcode_lang.title',
                    'shortcode_lang.content'
                )
                ->from($this->getTableName('everblock_shortcode'), 'shortcode')
                ->leftJoin(
                    'shortcode',
                    $this->getTableName('everblock_shortcode_lang'),
                    'shortcode_lang',
                    'shortcode.id_everblock_shortcode = shortcode_lang.id_everblock_shortcode'
                )
                ->where('shortcode.id_everblock_shortcode = :shortcodeId')
                ->andWhere('shortcode.id_shop = :shopId')
                ->setParameter('shortcodeId', $shortcodeId, ParameterType::INTEGER)
                ->setParameter('shopId', $shopId, ParameterType::INTEGER)
                ->orderBy('shortcode_lang.id_lang', 'ASC');

            $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

            if ([] === $rows) {
                return null;
            }

            $baseRow = $rows[0];
            $translations = [];
            foreach ($rows as $row) {
                if (isset($row['id_lang'])) {
                    $translations[(int) $row['id_lang']] = [
                        'title' => $row['title'] ?? '',
                        'content' => $row['content'] ?? '',
                    ];
                }
            }

            return [
                'id_everblock_shortcode' => (int) $baseRow['id_everblock_shortcode'],
                'id_shop' => (int) $baseRow['id_shop'],
                'shortcode' => (string) $baseRow['shortcode'],
                'translations' => $translations,
            ];
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
            sprintf('everblock_shortcode_shop_%d', $shopId),
        ]);
    }

    public function clearCacheForShortcode(string $shortcode, int $shopId): void
    {
        $normalized = $this->normalizeShortcode($shortcode);
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_shortcode_shop_%d', $shopId),
            sprintf('everblock_shortcode_code_%s', $normalized),
        ]);
    }

    private function createBaseQueryBuilder(int $shopId): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                'shortcode.id_everblock_shortcode',
                'shortcode.id_shop',
                'shortcode.shortcode',
                'shortcode_lang.id_lang',
                'shortcode_lang.title',
                'shortcode_lang.content'
            )
            ->from($this->getTableName('everblock_shortcode'), 'shortcode')
            ->leftJoin(
                'shortcode',
                $this->getTableName('everblock_shortcode_lang'),
                'shortcode_lang',
                'shortcode.id_everblock_shortcode = shortcode_lang.id_everblock_shortcode'
            )
            ->where('shortcode.id_shop = :shopId')
            ->setParameter('shopId', $shopId, ParameterType::INTEGER);

        return $queryBuilder;
    }

    /**
     * @return string[]
     */
    private function buildTags(int $shopId, ?int $languageId = null): array
    {
        $tags = [
            self::CACHE_TAG,
            sprintf('everblock_shortcode_shop_%d', $shopId),
        ];

        if (null !== $languageId) {
            $tags[] = sprintf('everblock_shortcode_lang_%d', $languageId);
        }

        return $tags;
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

    private function normalizeShortcode(string $shortcode): string
    {
        return preg_replace('/[^a-z0-9_\-]/i', '_', strtolower(trim($shortcode))) ?: 'default';
    }
}
