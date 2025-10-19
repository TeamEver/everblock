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

namespace Everblock\Tools\Grid\Filters;

use PrestaShop\PrestaShop\Core\Search\Filters;

class EverBlockFilters extends Filters
{
    protected $filterId = 'ever_block';

    protected $defaultLimit = 20;

    protected $defaultOrderBy = 'sort_key';

    protected $defaultOrderWay = 'asc';
}
