<?php

namespace Everblock\Tools\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Everblock\Tools\Dto\Product\ProductIdCollection;
use Everblock\Tools\Dto\Product\ProductTagFilters;

final class ProductTagRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findProductIds(ProductTagFilters $filters): ProductIdCollection
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('pt.id_product')
            ->from(_DB_PREFIX_ . 'product_tag', 'pt')
            ->innerJoin('pt', _DB_PREFIX_ . 'product_shop', 'ps', 'ps.id_product = pt.id_product AND ps.id_shop = :shopId AND ps.active = 1')
            ->innerJoin('pt', _DB_PREFIX_ . 'product_lang', 'pl', 'pl.id_product = pt.id_product AND pl.id_shop = :shopId AND pl.id_lang = :langId')
            ->innerJoin('pt', _DB_PREFIX_ . 'product', 'p', 'p.id_product = pt.id_product')
            ->setParameter('shopId', $filters->shopId, ParameterType::INTEGER)
            ->setParameter('langId', $filters->languageId, ParameterType::INTEGER);

        if ($filters->tagNames !== []) {
            $qb->innerJoin('pt', _DB_PREFIX_ . 'tag', 't', 't.id_tag = pt.id_tag AND t.id_lang = :langId');
        }

        if ($filters->visibilities !== []) {
            $visibilityPlaceholders = [];
            foreach ($filters->visibilities as $index => $visibility) {
                $paramName = 'visibility_' . $index;
                $visibilityPlaceholders[] = ':' . $paramName;
                $qb->setParameter($paramName, $visibility);
            }
            $qb->andWhere('ps.visibility IN (' . implode(',', $visibilityPlaceholders) . ')');
        }

        $conditions = [];

        if ($filters->tagIds !== []) {
            $placeholders = [];
            foreach ($filters->tagIds as $index => $tagId) {
                $paramName = 'tagId_' . $index;
                $placeholders[] = ':' . $paramName;
                $qb->setParameter($paramName, $tagId, ParameterType::INTEGER);
            }
            $conditions[] = 'pt.id_tag IN (' . implode(',', $placeholders) . ')';
        }

        if ($filters->tagNames !== []) {
            $placeholders = [];
            foreach ($filters->tagNames as $index => $tagName) {
                $paramName = 'tagName_' . $index;
                $placeholders[] = ':' . $paramName;
                $qb->setParameter($paramName, $tagName);
            }
            $conditions[] = 't.name IN (' . implode(',', $placeholders) . ')';
        }

        if ($conditions !== []) {
            if (count($conditions) > 1) {
                $qb->andWhere('(' . implode(' OR ', $conditions) . ')');
            } else {
                $qb->andWhere($conditions[0]);
            }
        }

        $qb->groupBy('pt.id_product');

        if ($filters->match === ProductTagFilters::MATCH_ALL) {
            $tagCount = count(array_unique(array_merge($filters->tagIds, $filters->tagNames)));
            if ($tagCount > 1) {
                $qb->having('COUNT(DISTINCT pt.id_tag) = :tagCount');
                $qb->setParameter('tagCount', $tagCount, ParameterType::INTEGER);
            }
        }

        $orderByMap = [
            'position' => 'ps.position',
            'name' => 'pl.name',
            'price' => 'ps.price',
            'date_add' => 'p.date_add',
            'rand' => 'RAND()',
        ];

        $orderBy = $orderByMap[$filters->orderBy] ?? 'ps.position';

        if ($orderBy === 'RAND()') {
            $qb->add('orderBy', $orderBy);
        } else {
            $direction = strtoupper($filters->orderDirection) === 'DESC' ? 'DESC' : 'ASC';
            $qb->orderBy($orderBy, $direction);
        }

        $qb->setFirstResult($filters->offset)
            ->setMaxResults($filters->limit);

        $rows = $qb->executeQuery()->fetchFirstColumn();
        $productIds = array_map('intval', $rows ?: []);

        return new ProductIdCollection($productIds);
    }
}
