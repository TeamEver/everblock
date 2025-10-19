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
 */

namespace Everblock\Tools\Grid\Data;

use Doctrine\DBAL\Query\QueryBuilder;
use Everblock\Tools\Grid\Filters\EverBlockFilters;
use Everblock\Tools\Grid\Query\EverBlockQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Search\Filters;

class EverBlockGridDataFactory implements GridDataFactoryInterface
{
    /** @var EverBlockQueryBuilder */
    private $queryBuilder;

    public function __construct(EverBlockQueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getData(Filters $filters)
    {
        if (!$filters instanceof EverBlockFilters) {
            $filters = new EverBlockFilters($filters->all());
        }

        $searchQuery = $this->queryBuilder->getSearchQueryBuilder($filters);
        $records = $this->fetchAll($searchQuery);

        $countQuery = $this->queryBuilder->getCountQueryBuilder($filters);
        $totalRecords = (int) $this->fetchCount($countQuery);

        return new GridData($records, $totalRecords);
    }

    private function fetchAll(QueryBuilder $queryBuilder)
    {
        $statement = method_exists($queryBuilder, 'executeQuery')
            ? $queryBuilder->executeQuery()
            : $queryBuilder->execute();

        if (method_exists($statement, 'fetchAllAssociative')) {
            return $statement->fetchAllAssociative();
        }

        return $statement->fetchAll();
    }

    private function fetchCount(QueryBuilder $queryBuilder)
    {
        $statement = method_exists($queryBuilder, 'executeQuery')
            ? $queryBuilder->executeQuery()
            : $queryBuilder->execute();

        if (method_exists($statement, 'fetchOne')) {
            return $statement->fetchOne();
        }

        if (method_exists($statement, 'fetchColumn')) {
            return $statement->fetchColumn();
        }

        $result = $statement->fetch();
        if (is_array($result)) {
            return array_values($result)[0];
        }

        return 0;
    }
}
