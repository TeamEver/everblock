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

namespace Everblock\Tools\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\Grid\Query\QueryBuilderInterface;
use PrestaShop\PrestaShop\Core\Search\SearchCriteriaInterface;

class EverBlockQueryBuilder implements QueryBuilderInterface
{
    /** @var Connection */
    private $connection;

    /** @var LegacyContext */
    private $legacyContext;

    public function __construct(Connection $connection, LegacyContext $legacyContext)
    {
        $this->connection = $connection;
        $this->legacyContext = $legacyContext;
    }

    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getBaseQueryBuilder();

        $this->applyFilters($qb, $searchCriteria);
        $this->applySorting($qb, $searchCriteria);
        $this->applyPagination($qb, $searchCriteria);

        return $qb;
    }

    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('COUNT(eb.id_everblock)')
            ->from(_DB_PREFIX_ . 'everblock', 'eb')
            ->leftJoin('eb', _DB_PREFIX_ . 'hook', 'h', 'h.id_hook = eb.id_hook')
            ->where('eb.id_shop = :id_shop')
            ->setParameter('id_shop', (int) $this->getContext()->shop->id);

        $this->applyRawFilters($qb, $searchCriteria);

        return $qb;
    }

    private function getBaseQueryBuilder()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(
            'eb.id_everblock',
            'eb.name',
            'h.title as hname',
            'eb.position',
            'eb.only_home',
            'eb.only_category',
            'eb.only_manufacturer',
            'eb.only_supplier',
            'eb.only_cms_category',
            'eb.modal',
            'eb.date_start',
            'eb.date_end',
            'eb.active'
        )
            ->from(_DB_PREFIX_ . 'everblock', 'eb')
            ->leftJoin('eb', _DB_PREFIX_ . 'hook', 'h', 'h.id_hook = eb.id_hook')
            ->where('eb.id_shop = :id_shop')
            ->setParameter('id_shop', (int) $this->getContext()->shop->id);

        return $qb;
    }

    private function applyFilters(QueryBuilder $qb, SearchCriteriaInterface $searchCriteria)
    {
        $filters = $searchCriteria->getFilters();

        foreach ($filters as $filterName => $value) {
            if (null === $value || $value === '' || $value === []) {
                continue;
            }

            switch ($filterName) {
                case 'id_everblock':
                    $qb->andWhere('eb.id_everblock = :id_everblock');
                    $qb->setParameter('id_everblock', (int) $value, ParameterType::INTEGER);
                    break;
                case 'name':
                    $qb->andWhere('eb.name LIKE :name');
                    $qb->setParameter('name', '%' . pSQL($value) . '%');
                    break;
                case 'hname':
                    $qb->andWhere('h.title LIKE :hook_title');
                    $qb->setParameter('hook_title', '%' . pSQL($value) . '%');
                    break;
                case 'only_home':
                case 'only_category':
                case 'only_manufacturer':
                case 'only_supplier':
                case 'only_cms_category':
                case 'modal':
                case 'active':
                    $qb->andWhere(sprintf('eb.%s = :%s', $filterName, $filterName));
                    $qb->setParameter($filterName, (int) $value, ParameterType::INTEGER);
                    break;
                case 'date_start':
                case 'date_end':
                    $this->applyDateFilter($qb, $filterName, $value);
                    break;
                default:
                    break;
            }
        }
    }

    private function applyRawFilters(QueryBuilder $qb, SearchCriteriaInterface $searchCriteria)
    {
        $filters = $searchCriteria->getFilters();
        foreach ($filters as $filterName => $value) {
            if (null === $value || $value === '' || $value === []) {
                continue;
            }

            switch ($filterName) {
                case 'id_everblock':
                    $qb->andWhere('eb.id_everblock = :id_everblock');
                    $qb->setParameter('id_everblock', (int) $value, ParameterType::INTEGER);
                    break;
                case 'name':
                    $qb->andWhere('eb.name LIKE :name');
                    $qb->setParameter('name', '%' . pSQL($value) . '%');
                    break;
                case 'hname':
                    $qb->andWhere('h.title LIKE :hook_title');
                    $qb->setParameter('hook_title', '%' . pSQL($value) . '%');
                    break;
                case 'only_home':
                case 'only_category':
                case 'only_manufacturer':
                case 'only_supplier':
                case 'only_cms_category':
                case 'modal':
                case 'active':
                    $qb->andWhere(sprintf('eb.%s = :%s', $filterName, $filterName));
                    $qb->setParameter($filterName, (int) $value, ParameterType::INTEGER);
                    break;
                case 'date_start':
                case 'date_end':
                    $this->applyDateFilter($qb, $filterName, $value);
                    break;
                default:
                    break;
            }
        }
    }

    private function applySorting(QueryBuilder $qb, SearchCriteriaInterface $searchCriteria)
    {
        $orderBy = $searchCriteria->getOrderBy();
        if (!$orderBy) {
            $orderBy = 'hname';
        }

        $orderWay = $searchCriteria->getOrderWay();
        if (!$orderWay) {
            $orderWay = 'ASC';
        }

        switch ($orderBy) {
            case 'hname':
                $qb->orderBy('h.title', $orderWay);
                $qb->addOrderBy('eb.position', 'ASC');
                break;
            case 'position':
                $qb->orderBy('eb.position', $orderWay);
                break;
            default:
                $qb->orderBy('eb.' . pSQL($orderBy), $orderWay);
        }
    }

    private function applyPagination(QueryBuilder $qb, SearchCriteriaInterface $searchCriteria)
    {
        $offset = $searchCriteria->getOffset();
        if (null !== $offset) {
            $qb->setFirstResult((int) $offset);
        }

        $limit = $searchCriteria->getLimit();
        if (null !== $limit) {
            $qb->setMaxResults((int) $limit);
        }
    }

    private function applyDateFilter(QueryBuilder $qb, $field, $value)
    {
        if (!is_array($value)) {
            return;
        }

        if (!empty($value['from'])) {
            $qb->andWhere(sprintf('eb.%s >= :%s_from', $field, $field));
            $qb->setParameter($field . '_from', $value['from']);
        }

        if (!empty($value['to'])) {
            $qb->andWhere(sprintf('eb.%s <= :%s_to', $field, $field));
            $qb->setParameter($field . '_to', $value['to']);
        }
    }

    private function getContext()
    {
        return $this->legacyContext->getContext();
    }
}
