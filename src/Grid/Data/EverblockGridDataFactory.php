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

namespace Everblock\Tools\Grid\Data;

use Db;
use Everblock\Tools\Grid\Query\EverblockQueryBuilder;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockGridDataFactory
{
    /**
     * @var EverblockQueryBuilder
     */
    private $queryBuilder;

    public function __construct(EverblockQueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function getData(array $filters = []): array
    {
        $query = $this->queryBuilder->buildQuery($filters);
        $records = Db::getInstance()->executeS($query);

        return [
            'records' => $records ?: [],
            'records_total' => is_array($records) ? count($records) : 0,
        ];
    }
}
