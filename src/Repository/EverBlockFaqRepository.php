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

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Everblock\Tools\Entity\EverBlockFaq;
use Everblock\Tools\Entity\EverBlockFaqTranslation;
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

    public function findById(int $faqId, int $shopId): ?EverBlockFaq
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from($this->getTableName('everblock_faq'))
            ->where('id_everblock_faq = :faqId')
            ->andWhere('id_shop = :shopId')
            ->setParameter('faqId', $faqId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->setMaxResults(1);

        $row = $queryBuilder->executeQuery()->fetchAssociative();

        if (false === $row) {
            return null;
        }

        $faq = $this->hydrateFaq($row);
        $translations = $this->fetchTranslations($faqId, $shopId, $faq);

        foreach ($translations as $translation) {
            $faq->addTranslation($translation);
        }

        return $faq;
    }

    /**
     * @param array<int, array{title: string|null, content: string|null}> $translations
     */
    public function save(EverBlockFaq $faq, array $translations): EverBlockFaq
    {
        if (null === $faq->getId()) {
            $faqId = $this->insertFaq($faq);
            $faq->setId($faqId);
        } else {
            $this->updateFaq($faq);
        }

        $this->saveTranslations((int) $faq->getId(), $faq->getShopId(), $translations);
        $this->clearCacheForShop($faq->getShopId());

        if (null !== $faq->getTagName()) {
            $this->clearCacheForTag($faq->getShopId(), $faq->getTagName());
        }

        return $faq;
    }

    public function delete(int $faqId, int $shopId): void
    {
        $this->connection->delete($this->getTableName('everblock_faq_lang'), [
            'id_everblock_faq' => $faqId,
            'id_shop' => $shopId,
        ]);
        $this->connection->delete($this->getTableName('everblock_faq'), [
            'id_everblock_faq' => $faqId,
            'id_shop' => $shopId,
        ]);

        $this->clearCacheForShop($shopId);
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

    private function hydrateFaq(array $row): EverBlockFaq
    {
        $faq = new EverBlockFaq();
        $faq->setShopId((int) $row['id_shop']);
        $faq->setTagName($row['tag_name']);
        $faq->setPosition((int) $row['position']);
        $faq->setActive((bool) $row['active']);

        if (!empty($row['date_add']) && $row['date_add'] !== '0000-00-00 00:00:00') {
            $faq->setDateAdd(new DateTimeImmutable((string) $row['date_add']));
        }
        if (!empty($row['date_upd']) && $row['date_upd'] !== '0000-00-00 00:00:00') {
            $faq->setDateUpdated(new DateTimeImmutable((string) $row['date_upd']));
        }

        $faq->setId((int) $row['id_everblock_faq']);

        return $faq;
    }

    private function fetchTranslations(int $faqId, int $shopId, EverBlockFaq $faq): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('id_lang', 'title', 'content')
            ->from($this->getTableName('everblock_faq_lang'))
            ->where('id_everblock_faq = :faqId')
            ->andWhere('id_shop = :shopId')
            ->setParameter('faqId', $faqId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->executeQuery()
            ->fetchAllAssociative();

        $translations = [];
        foreach ($rows as $row) {
            $translation = new EverBlockFaqTranslation($faq, (int) $row['id_lang'], $shopId);
            $translation->setTitle($row['title']);
            $translation->setContent($row['content']);
            $translations[] = $translation;
        }

        return $translations;
    }

    private function insertFaq(EverBlockFaq $faq): int
    {
        $this->connection->insert($this->getTableName('everblock_faq'), [
            'tag_name' => $faq->getTagName(),
            'id_shop' => $faq->getShopId(),
            'position' => $faq->getPosition(),
            'date_add' => $faq->getDateAdd()?->format('Y-m-d H:i:s'),
            'date_upd' => $faq->getDateUpdated()?->format('Y-m-d H:i:s'),
            'active' => $faq->isActive() ? 1 : 0,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function updateFaq(EverBlockFaq $faq): void
    {
        $this->connection->update(
            $this->getTableName('everblock_faq'),
            [
                'tag_name' => $faq->getTagName(),
                'position' => $faq->getPosition(),
                'date_add' => $faq->getDateAdd()?->format('Y-m-d H:i:s'),
                'date_upd' => $faq->getDateUpdated()?->format('Y-m-d H:i:s'),
                'active' => $faq->isActive() ? 1 : 0,
            ],
            [
                'id_everblock_faq' => $faq->getId(),
                'id_shop' => $faq->getShopId(),
            ]
        );
    }

    /**
     * @param array<int, array{title: string|null, content: string|null}> $translations
     */
    private function saveTranslations(int $faqId, int $shopId, array $translations): void
    {
        $this->connection->delete($this->getTableName('everblock_faq_lang'), [
            'id_everblock_faq' => $faqId,
            'id_shop' => $shopId,
        ]);

        foreach ($translations as $languageId => $data) {
            $this->connection->insert($this->getTableName('everblock_faq_lang'), [
                'id_everblock_faq' => $faqId,
                'id_lang' => $languageId,
                'id_shop' => $shopId,
                'title' => $data['title'] ?? null,
                'content' => $data['content'] ?? null,
            ]);
        }
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
