<?php

namespace Everblock\Tools\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Everblock\Tools\Dto\Sales\SalesAggregation;
use DateTimeInterface;

final class SalesRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function createSoldQuantityAggregation(?DateTimeInterface $dateLimit, bool $byCombination, string $aliasPrefix = 'sales'): SalesAggregation
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('od.product_id AS product_id')
            ->from(_DB_PREFIX_ . 'order_detail', 'od')
            ->innerJoin('od', _DB_PREFIX_ . 'orders', 'o', 'o.id_order = od.id_order')
            ->where('o.valid = 1')
            ->groupBy('od.product_id');

        if ($byCombination) {
            $qb->addSelect('od.product_attribute_id AS product_attribute_id');
            $qb->addGroupBy('od.product_attribute_id');
        }

        $qb->addSelect('SUM(od.product_quantity) AS sold_qty');

        $parameters = [];

        if ($dateLimit instanceof DateTimeInterface) {
            $paramName = $aliasPrefix . '_date_limit';
            $qb->andWhere('o.date_add >= :' . $paramName);
            $qb->setParameter($paramName, $dateLimit->format('Y-m-d H:i:s'), Types::DATETIME_MUTABLE);
            $parameters[$paramName] = $dateLimit->format('Y-m-d H:i:s');
        }

        return new SalesAggregation($qb->getSQL(), $parameters);
    }
}
