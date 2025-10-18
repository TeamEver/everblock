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
use Everblock\Tools\Entity\EverBlock;
use Everblock\Tools\Entity\EverBlockTranslation;
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

    public function findById(int $blockId, int $shopId): ?EverBlock
    {
        $qb = $this->createBaseQueryBuilder()
            ->where('eb.id_everblock = :blockId')
            ->andWhere('eb.id_shop = :shopId')
            ->setParameter('blockId', $blockId, ParameterType::INTEGER)
            ->setParameter('shopId', $shopId, ParameterType::INTEGER)
            ->setMaxResults(1);

        $row = $qb->executeQuery()->fetchAssociative();

        if (false === $row) {
            return null;
        }

        $block = $this->hydrateBlock($row);
        $translations = $this->fetchTranslations($blockId, $block);

        foreach ($translations as $translation) {
            $block->addTranslation($translation);
        }

        return $block;
    }

    /**
     * @param array<int, array<string, mixed>> $translations
     */
    public function save(EverBlock $block, array $translations): EverBlock
    {
        if (null === $block->getId()) {
            $blockId = $this->insertBlock($block);
            $blockId = (int) $blockId;
            $block->setId($blockId);
        } else {
            $this->updateBlock($block);
        }

        $this->saveTranslations((int) $block->getId(), $translations);
        $this->clearCacheForLanguageAndShopCollections($translations, $block->getShopId());

        return $block;
    }

    public function delete(int $blockId, int $shopId): void
    {
        $this->connection->delete($this->getTableName('everblock_lang'), ['id_everblock' => $blockId]);
        $this->connection->delete($this->getTableName('everblock'), [
            'id_everblock' => $blockId,
            'id_shop' => $shopId,
        ]);

        $this->clearCache();
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

    private function hydrateBlock(array $row): EverBlock
    {
        $block = new EverBlock();
        $block->setName((string) ($row['name'] ?? ''));
        $block->setHookId((int) $row['id_hook']);
        $block->setOnlyHome((bool) $row['only_home']);
        $block->setOnlyCategory((bool) $row['only_category']);
        $block->setOnlyCategoryProduct((bool) $row['only_category_product']);
        $block->setOnlyManufacturer((bool) $row['only_manufacturer']);
        $block->setOnlySupplier((bool) $row['only_supplier']);
        $block->setOnlyCmsCategory((bool) $row['only_cms_category']);
        $block->setObfuscateLink((bool) $row['obfuscate_link']);
        $block->setAddContainer((bool) $row['add_container']);
        $block->setLazyload((bool) $row['lazyload']);
        $block->setDevice((int) $row['device']);
        $block->setShopId((int) $row['id_shop']);
        $block->setPosition((int) $row['position']);
        $block->setCategories($row['categories']);
        $block->setManufacturers($row['manufacturers']);
        $block->setSuppliers($row['suppliers']);
        $block->setCmsCategories($row['cms_categories']);
        $block->setGroups($row['groups']);
        $block->setBackground($row['background']);
        $block->setCssClass($row['css_class']);
        $block->setDataAttribute($row['data_attribute']);
        $block->setBootstrapClass($row['bootstrap_class']);
        $block->setModal((bool) $row['modal']);
        $block->setDelay((int) $row['delay']);
        $block->setTimeout((int) $row['timeout']);
        $block->setActive((bool) $row['active']);

        if (!empty($row['date_start']) && $row['date_start'] !== '0000-00-00 00:00:00') {
            $block->setDateStart(new DateTimeImmutable((string) $row['date_start']));
        }
        if (!empty($row['date_end']) && $row['date_end'] !== '0000-00-00 00:00:00') {
            $block->setDateEnd(new DateTimeImmutable((string) $row['date_end']));
        }

        $block->setId((int) $row['id_everblock']);

        return $block;
    }

    private function fetchTranslations(int $blockId, EverBlock $block): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('id_lang', 'content', 'custom_code')
            ->from($this->getTableName('everblock_lang'))
            ->where('id_everblock = :blockId')
            ->setParameter('blockId', $blockId, ParameterType::INTEGER)
            ->executeQuery()
            ->fetchAllAssociative();

        $translations = [];
        foreach ($rows as $row) {
            $translation = new EverBlockTranslation($block, (int) $row['id_lang']);
            $translation->setContent($row['content']);
            $translation->setCustomCode($row['custom_code']);
            $translations[] = $translation;
        }

        return $translations;
    }

    private function insertBlock(EverBlock $block): int
    {
        $this->connection->insert($this->getTableName('everblock'), [
            'name' => $block->getName(),
            'id_hook' => $block->getHookId(),
            'only_home' => $block->getOnlyHome() ? 1 : 0,
            'only_category' => $block->getOnlyCategory() ? 1 : 0,
            'only_category_product' => $block->getOnlyCategoryProduct() ? 1 : 0,
            'only_manufacturer' => $block->getOnlyManufacturer() ? 1 : 0,
            'only_supplier' => $block->getOnlySupplier() ? 1 : 0,
            'only_cms_category' => $block->getOnlyCmsCategory() ? 1 : 0,
            'obfuscate_link' => $block->getObfuscateLink() ? 1 : 0,
            'add_container' => $block->getAddContainer() ? 1 : 0,
            'lazyload' => $block->getLazyload() ? 1 : 0,
            'device' => $block->getDevice(),
            'id_shop' => $block->getShopId(),
            'position' => $block->getPosition(),
            'categories' => $block->getCategories(),
            'manufacturers' => $block->getManufacturers(),
            'suppliers' => $block->getSuppliers(),
            'cms_categories' => $block->getCmsCategories(),
            'groups' => $block->getGroups(),
            'background' => $block->getBackground(),
            'css_class' => $block->getCssClass(),
            'data_attribute' => $block->getDataAttribute(),
            'bootstrap_class' => $block->getBootstrapClass(),
            'modal' => $block->isModal() ? 1 : 0,
            'delay' => $block->getDelay(),
            'timeout' => $block->getTimeout(),
            'date_start' => $block->getDateStart()?->format('Y-m-d H:i:s'),
            'date_end' => $block->getDateEnd()?->format('Y-m-d H:i:s'),
            'active' => $block->isActive() ? 1 : 0,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function updateBlock(EverBlock $block): void
    {
        $this->connection->update(
            $this->getTableName('everblock'),
            [
                'name' => $block->getName(),
                'id_hook' => $block->getHookId(),
                'only_home' => $block->getOnlyHome() ? 1 : 0,
                'only_category' => $block->getOnlyCategory() ? 1 : 0,
                'only_category_product' => $block->getOnlyCategoryProduct() ? 1 : 0,
                'only_manufacturer' => $block->getOnlyManufacturer() ? 1 : 0,
                'only_supplier' => $block->getOnlySupplier() ? 1 : 0,
                'only_cms_category' => $block->getOnlyCmsCategory() ? 1 : 0,
                'obfuscate_link' => $block->getObfuscateLink() ? 1 : 0,
                'add_container' => $block->getAddContainer() ? 1 : 0,
                'lazyload' => $block->getLazyload() ? 1 : 0,
                'device' => $block->getDevice(),
                'position' => $block->getPosition(),
                'categories' => $block->getCategories(),
                'manufacturers' => $block->getManufacturers(),
                'suppliers' => $block->getSuppliers(),
                'cms_categories' => $block->getCmsCategories(),
                'groups' => $block->getGroups(),
                'background' => $block->getBackground(),
                'css_class' => $block->getCssClass(),
                'data_attribute' => $block->getDataAttribute(),
                'bootstrap_class' => $block->getBootstrapClass(),
                'modal' => $block->isModal() ? 1 : 0,
                'delay' => $block->getDelay(),
                'timeout' => $block->getTimeout(),
                'date_start' => $block->getDateStart()?->format('Y-m-d H:i:s'),
                'date_end' => $block->getDateEnd()?->format('Y-m-d H:i:s'),
                'active' => $block->isActive() ? 1 : 0,
            ],
            [
                'id_everblock' => $block->getId(),
                'id_shop' => $block->getShopId(),
            ]
        );
    }

    /**
     * @param array<int, array<string, mixed>> $translations
     */
    private function saveTranslations(int $blockId, array $translations): void
    {
        $this->connection->delete($this->getTableName('everblock_lang'), ['id_everblock' => $blockId]);

        foreach ($translations as $languageId => $data) {
            $this->connection->insert($this->getTableName('everblock_lang'), [
                'id_everblock' => $blockId,
                'id_lang' => $languageId,
                'content' => $data['content'] ?? null,
                'custom_code' => $data['custom_code'] ?? null,
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $translations
     */
    private function clearCacheForLanguageAndShopCollections(array $translations, int $shopId): void
    {
        $languageIds = array_keys($translations);
        foreach ($languageIds as $languageId) {
            $this->clearCacheForLanguageAndShop((int) $languageId, $shopId);
        }
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
