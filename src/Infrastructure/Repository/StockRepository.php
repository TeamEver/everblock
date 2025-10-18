<?php

namespace Everblock\Tools\Infrastructure\Repository;

use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Everblock\Tools\Dto\Product\LowStockFilters;
use Everblock\Tools\Dto\Product\LowStockProduct;
use Everblock\Tools\Dto\Product\LowStockProductCollection;

final class StockRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SalesRepository $salesRepository
    ) {
    }

    public function findLowStockProducts(LowStockFilters $filters): LowStockProductCollection
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('p.id_product', 'sa.quantity AS quantity')
            ->from(_DB_PREFIX_ . 'product', 'p')
            ->innerJoin('p', _DB_PREFIX_ . 'product_shop', 'ps', 'ps.id_product = p.id_product AND ps.id_shop = :shopId')
            ->innerJoin('p', _DB_PREFIX_ . 'product_lang', 'pl', 'pl.id_product = p.id_product AND pl.id_shop = :shopId AND pl.id_lang = :langId')
            ->innerJoin(
                'p',
                _DB_PREFIX_ . 'stock_available',
                'sa',
                'sa.id_product = p.id_product'
                . ' AND (sa.id_shop = :shopId OR (sa.id_shop IS NULL AND sa.id_shop_group = :shopGroupId))'
            )
            ->where('ps.active = 1')
            ->andWhere('sa.quantity ' . $filters->comparisonOperator . ' :threshold')
            ->setParameter('shopId', $filters->shopId, ParameterType::INTEGER)
            ->setParameter('shopGroupId', $filters->shopGroupId, ParameterType::INTEGER)
            ->setParameter('langId', $filters->languageId, ParameterType::INTEGER)
            ->setParameter('threshold', $filters->threshold, ParameterType::INTEGER);

        if ($filters->isCombinationLevel()) {
            $qb->andWhere('sa.id_product_attribute > 0');
            $qb->addSelect('sa.id_product_attribute');
        } else {
            $qb->andWhere('sa.id_product_attribute = 0');
        }

        if ($filters->visibilities !== []) {
            $visibilityPlaceholders = [];
            foreach ($filters->visibilities as $index => $visibility) {
                $param = 'visibility_' . $index;
                $visibilityPlaceholders[] = ':' . $param;
                $qb->setParameter($param, $visibility);
            }
            $qb->andWhere('ps.visibility IN (' . implode(',', $visibilityPlaceholders) . ')');
        }

        if ($filters->availableOnly) {
            $qb->andWhere('ps.available_for_order = 1');
        }

        if ($filters->days > 0) {
            $dateLimit = (new DateTimeImmutable('now'))->sub(new DateInterval('P' . $filters->days . 'D'));
            $qb->andWhere('p.date_add >= :productDateLimit');
            $qb->setParameter('productDateLimit', $dateLimit->format('Y-m-d H:i:s'));
            $salesAggregation = $this->salesRepository->createSoldQuantityAggregation($dateLimit, $filters->isCombinationLevel(), 'lowstock');
        } else {
            $salesAggregation = $this->salesRepository->createSoldQuantityAggregation(null, $filters->isCombinationLevel(), 'lowstock');
        }

        if ($filters->categoryIds !== []) {
            $qb->leftJoin('p', _DB_PREFIX_ . 'category_product', 'cp', 'cp.id_product = p.id_product');
            $placeholders = [];
            foreach ($filters->categoryIds as $index => $categoryId) {
                $param = 'category_' . $index;
                $placeholders[] = ':' . $param;
                $qb->setParameter($param, $categoryId, ParameterType::INTEGER);
            }
            $qb->andWhere('cp.id_category IN (' . implode(',', $placeholders) . ')');
        }

        if ($filters->manufacturerIds !== []) {
            $placeholders = [];
            foreach ($filters->manufacturerIds as $index => $manufacturerId) {
                $param = 'manufacturer_' . $index;
                $placeholders[] = ':' . $param;
                $qb->setParameter($param, $manufacturerId, ParameterType::INTEGER);
            }
            $qb->andWhere('p.id_manufacturer IN (' . implode(',', $placeholders) . ')');
        }

        if ($filters->orderBySales()) {
            $qb->addSelect('COALESCE(s.sold_qty, 0) AS sold_qty');
            $qb->leftJoin(
                'p',
                '(' . $salesAggregation->getSql() . ')',
                's',
                $filters->isCombinationLevel()
                    ? 's.product_id = p.id_product AND s.product_attribute_id = sa.id_product_attribute'
                    : 's.product_id = p.id_product'
            );
        }

        foreach ($salesAggregation->getParameters() as $name => $value) {
            $qb->setParameter($name, $value);
        }

        $qb->groupBy('p.id_product');
        if ($filters->isCombinationLevel()) {
            $qb->addGroupBy('sa.id_product_attribute');
        }

        if ($filters->orderRandomly()) {
            $qb->add('orderBy', 'RAND()');
        } else {
            $orderMap = [
                'qty' => 'sa.quantity',
                'date_add' => 'p.date_add',
                'name' => 'pl.name',
                'price' => 'ps.price',
                'sales' => 'sold_qty',
            ];
            $orderBy = $orderMap[$filters->orderBy] ?? 'sa.quantity';
            $direction = strtoupper($filters->orderDirection) === 'DESC' ? 'DESC' : 'ASC';
            $qb->orderBy($orderBy, $direction);
        }

        $qb->setFirstResult($filters->offset)
            ->setMaxResults($filters->limit);

        $rows = $qb->executeQuery()->fetchAllAssociative();
        $products = [];

        foreach ($rows as $row) {
            $products[] = new LowStockProduct(
                (int) $row['id_product'],
                isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                (int) $row['quantity'],
                array_key_exists('sold_qty', $row) ? (int) $row['sold_qty'] : null
            );
        }

        return new LowStockProductCollection($products);
    }
}
