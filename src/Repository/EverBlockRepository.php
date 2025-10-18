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

class EverBlockRepository
{
    private const CACHE_TAG = 'everblock';
    private const BOOTSTRAP_CACHE_TAG = 'everblock_bootstrap';
    private const CACHE_TTL = 86400;

    public function __construct(
        private readonly Connection $connection,
        private readonly TagAwareCacheInterface $cache,
        private readonly ?string $tablePrefix = null
    ) {
    }

    public function getAllBlocks(int $languageId, int $shopId): array
    {
        $cacheKey = sprintf('everblock.repository.all.%d.%d', $languageId, $shopId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($languageId, $shopId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag([
                self::CACHE_TAG,
                sprintf('everblock_lang_%d', $languageId),
                sprintf('everblock_shop_%d', $shopId),
            ]);

            $qb = $this->createBaseQueryBuilder()
                ->where('ebl.id_lang = :id_lang')
                ->andWhere('eb.id_shop = :id_shop')
                ->setParameter('id_lang', $languageId, ParameterType::INTEGER)
                ->setParameter('id_shop', $shopId, ParameterType::INTEGER)
                ->orderBy('eb.position', 'ASC');

            return $qb->executeQuery()->fetchAllAssociative();
        });
    }

    public function getBlocks(int $hookId, int $languageId, int $shopId): array
    {
        $cacheKey = sprintf('everblock.repository.blocks.%d.%d.%d', $hookId, $languageId, $shopId);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($hookId, $languageId, $shopId) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag([
                self::CACHE_TAG,
                sprintf('everblock_hook_%d', $hookId),
                sprintf('everblock_lang_%d', $languageId),
                sprintf('everblock_shop_%d', $shopId),
            ]);

            $qb = $this->createBaseQueryBuilder()
                ->where('eb.id_hook = :id_hook')
                ->andWhere('ebl.id_lang = :id_lang')
                ->andWhere('eb.id_shop = :id_shop')
                ->andWhere('eb.active = 1')
                ->setParameter('id_hook', $hookId, ParameterType::INTEGER)
                ->setParameter('id_lang', $languageId, ParameterType::INTEGER)
                ->setParameter('id_shop', $shopId, ParameterType::INTEGER)
                ->orderBy('eb.position', 'ASC');

            $blocks = $qb->executeQuery()->fetchAllAssociative();

            foreach ($blocks as &$block) {
                $bootstrapValue = isset($block['bootstrap_class']) ? (int) $block['bootstrap_class'] : 0;
                $block['bootstrap_class'] = $this->getBootstrapColClass($bootstrapValue);
            }

            return $blocks;
        });
    }

    public function getBootstrapColClass(int $colNumber): string
    {
        $cacheKey = sprintf('everblock.repository.bootstrap.%d', $colNumber);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($colNumber) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag([self::BOOTSTRAP_CACHE_TAG]);

            return $this->resolveBootstrapClass($colNumber);
        });
    }

    public function clearCache(): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG, self::BOOTSTRAP_CACHE_TAG]);
    }

    public function clearCacheForHook(int $hookId): void
    {
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_hook_%d', $hookId),
        ]);
    }

    public function clearCacheForLanguageAndShop(int $languageId, int $shopId): void
    {
        $this->cache->invalidateTags([
            self::CACHE_TAG,
            sprintf('everblock_lang_%d', $languageId),
            sprintf('everblock_shop_%d', $shopId),
        ]);
    }

    private function resolveBootstrapClass(int $colNumber): string
    {
        $class = 'col-';
        switch ($colNumber) {
            case 0:
                $class = '';
                break;
            case 1:
                $class .= '12';
                break;
            case 2:
                $class .= '6';
                break;
            case 3:
                $class .= '4';
                break;
            case 4:
                $class .= '3';
                break;
            case 6:
                $class .= '2';
                break;
            default:
                $class .= '12';
                break;
        }
        if ($class === '') {
            return '';
        }

        $class .= ' col-md-';
        switch ($colNumber) {
            case 0:
                $class = '';
                break;
            case 1:
                $class .= '12';
                break;
            case 2:
                $class .= '6';
                break;
            case 3:
                $class .= '4';
                break;
            case 4:
                $class .= '3';
                break;
            case 6:
                $class .= '2';
                break;
            default:
                $class .= '12';
                break;
        }

        return $class;
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('eb.*', 'ebl.id_lang', 'ebl.content', 'ebl.custom_code')
            ->from($this->getTableName('everblock'), 'eb')
            ->leftJoin('eb', $this->getTableName('everblock_lang'), 'ebl', 'eb.id_everblock = ebl.id_everblock');

        return $qb;
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
