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

class EverBlockFaqRepository
{
    private const CACHE_TAG = 'everblock_faq';
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
    public function getAllFaq(int $shopId, int $languageId): array
    {
        $cacheKey = sprintf('everblock.faq.all.%d.%d', $shopId, $languageId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($shopId, $languageId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag($this->buildTags($shopId, $languageId));

            $queryBuilder = $this->createBaseQueryBuilder($shopId, $languageId)
                ->andWhere('faq.active = 1')
                ->orderBy('faq.position', 'ASC');

            return $queryBuilder->executeQuery()->fetchAllAssociative();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFaqByTagName(int $shopId, int $languageId, string $tagName): array
    {
        $normalizedTag = $this->normalizeTagName($tagName);
        $cacheKey = sprintf('everblock.faq.tag.%d.%d.%s', $shopId, $languageId, $normalizedTag);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($shopId, $languageId, $tagName, $normalizedTag) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag(array_merge(
                $this->buildTags($shopId, $languageId),
                [sprintf('everblock_faq_tag_%s', $normalizedTag)]
            ));

            $queryBuilder = $this->createBaseQueryBuilder($shopId, $languageId)
                ->andWhere('faq.active = 1')
                ->andWhere('faq.tag_name = :tagName')
                ->setParameter('tagName', trim($tagName), ParameterType::STRING)
                ->orderBy('faq.position', 'ASC');

            return $queryBuilder->executeQuery()->fetchAllAssociative();
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
            sprintf('everblock_faq_shop_%d', $shopId),
        ]);
    }

    public function clearCacheForTag(int $shopId, string $tagName): void
    {
        $normalizedTag = $this->normalizeTagName($tagName);
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_faq_shop_%d', $shopId),
            sprintf('everblock_faq_tag_%s', $normalizedTag),
        ]);
    }

    private function createBaseQueryBuilder(int $shopId, int $languageId): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                'faq.id_everblock_faq',
                'faq.id_shop',
                'faq.tag_name',
                'faq.position',
                'faq.active',
                'faq.date_add',
                'faq.date_upd',
                'faql.title',
                'faql.content'
            )
            ->from($this->getTableName('everblock_faq'), 'faq')
            ->leftJoin(
                'faq',
                $this->getTableName('everblock_faq_lang'),
                'faql',
                'faq.id_everblock_faq = faql.id_everblock_faq AND faql.id_lang = :languageId AND faql.id_shop = :shopId'
            )
            ->where('faq.id_shop = :shopId')
            ->setParameter('languageId', $languageId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER);

        return $queryBuilder;
    }

    /**
     * @return string[]
     */
    private function buildTags(int $shopId, int $languageId): array
    {
        return [
            self::CACHE_TAG,
            sprintf('everblock_faq_shop_%d', $shopId),
            sprintf('everblock_faq_lang_%d', $languageId),
        ];
    }

    private function normalizeTagName(string $tagName): string
    {
        return preg_replace('/[^a-z0-9_]/', '_', strtolower(trim($tagName))) ?: 'default';
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
