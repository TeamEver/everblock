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

namespace Everblock\PrestaShopBundle\Grid\Data\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class EverBlockGridDataFactory implements GridDataFactoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LegacyContext
     */
    private $legacyContext;

    /**
     * @var string
     */
    private $dbPrefix;

    public function __construct(Connection $connection, LegacyContext $legacyContext, string $dbPrefix)
    {
        $this->connection = $connection;
        $this->legacyContext = $legacyContext;
        $this->dbPrefix = $dbPrefix;
    }

    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $baseQueryBuilder = $this->getBaseQueryBuilder();
        $this->applyFilters($baseQueryBuilder, $searchCriteria->getFilters());

        $orderedQueryBuilder = clone $baseQueryBuilder;

        $orderBy = $searchCriteria->getOrderBy();
        if ('hname' === $orderBy) {
            $orderBy = 'sort_key';
        }

        if (!$orderBy) {
            $orderBy = 'sort_key';
        }

        $orderWay = $searchCriteria->getOrderWay() ?: 'asc';
        $orderedQueryBuilder->orderBy($orderBy, $orderWay);

        $offset = $searchCriteria->getOffset();
        if (null !== $offset) {
            $orderedQueryBuilder->setFirstResult((int) $offset);
        }

        $limit = $searchCriteria->getLimit();
        if (null !== $limit) {
            $orderedQueryBuilder->setMaxResults((int) $limit);
        }

        $records = $this->fetchAll($orderedQueryBuilder);

        $countQueryBuilder = clone $baseQueryBuilder;
        $countQueryBuilder->select('COUNT(eb.id_everblock)');
        $filteredRecordsCount = (int) $this->fetchSingleColumn($countQueryBuilder);

        $totalQueryBuilder = $this->getBaseQueryBuilder();
        $totalRecords = (int) $this->fetchSingleColumn($totalQueryBuilder->select('COUNT(eb.id_everblock)'));

        return new GridData($records, $totalRecords, $filteredRecordsCount);
    }

    private function getBaseQueryBuilder(): QueryBuilder
    {
        $context = $this->legacyContext->getContext();

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->from($this->dbPrefix . 'everblock', 'eb')
            ->select('eb.*')
            ->addSelect('h.title AS hname')
            ->addSelect("CONCAT(IFNULL(h.title, ''), LPAD(eb.position, 10, '0')) AS sort_key")
            ->leftJoin('eb', $this->dbPrefix . 'hook', 'h', 'h.id_hook = eb.id_hook')
            ->andWhere('eb.id_shop = :id_shop')
            ->setParameter('id_shop', (int) $context->shop->id);

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array<string, mixed> $filters
     */
    private function applyFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        foreach ($filters as $filterName => $value) {
            if ('' === $value || null === $value) {
                continue;
            }

            switch ($filterName) {
                case 'id_everblock':
                case 'position':
                    $queryBuilder->andWhere(sprintf('eb.%s = :%s', $filterName, $filterName));
                    $queryBuilder->setParameter($filterName, (int) $value);
                    break;
                case 'hname':
                    $queryBuilder->andWhere('h.title LIKE :hname');
                    $queryBuilder->setParameter('hname', '%' . $value . '%');
                    break;
                case 'name':
                    $queryBuilder->andWhere('eb.name LIKE :name');
                    $queryBuilder->setParameter('name', '%' . $value . '%');
                    break;
                case 'date_start':
                case 'date_end':
                    $queryBuilder->andWhere(sprintf('eb.%s LIKE :%s', $filterName, $filterName));
                    $queryBuilder->setParameter($filterName, '%' . $value . '%');
                    break;
                case 'only_home':
                case 'only_category':
                case 'only_manufacturer':
                case 'only_supplier':
                case 'only_cms_category':
                case 'modal':
                case 'active':
                    $queryBuilder->andWhere(sprintf('eb.%s = :%s', $filterName, $filterName));
                    $queryBuilder->setParameter($filterName, (int) $value);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchAll(QueryBuilder $queryBuilder): array
    {
        if (method_exists($queryBuilder, 'executeQuery')) {
            $result = $queryBuilder->executeQuery();

            return method_exists($result, 'fetchAllAssociative')
                ? $result->fetchAllAssociative()
                : $result->fetchAll();
        }

        $statement = $queryBuilder->execute();

        if (method_exists($statement, 'fetchAllAssociative')) {
            return $statement->fetchAllAssociative();
        }

        return $statement->fetchAll();
    }

    private function fetchSingleColumn(QueryBuilder $queryBuilder): int
    {
        if (method_exists($queryBuilder, 'executeQuery')) {
            $result = $queryBuilder->executeQuery();

            if (method_exists($result, 'fetchOne')) {
                return (int) $result->fetchOne();
            }

            return (int) $result->fetchColumn();
        }

        $statement = $queryBuilder->execute();

        if (method_exists($statement, 'fetchOne')) {
            return (int) $statement->fetchOne();
        }

        return (int) $statement->fetchColumn();
    }
}
