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

namespace Everblock\PrestaShopBundle\Grid\Search\Filters;

use PrestaShop\PrestaShop\Core\Grid\Search\Filters;

class EverBlockFilters extends Filters
{
    public const FILTER_ID = 'ever_block';

    protected $filterId = self::FILTER_ID;

    /**
     * @var array<string, mixed>
     */
    protected $defaults = [
        'limit' => 20,
        'offset' => 0,
        'orderBy' => 'sort_key',
        'orderWay' => 'asc',
    ];

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $defaults
     */
    public function __construct(array $filters = [], array $defaults = [])
    {
        parent::__construct($filters, array_merge($this->defaults, $defaults));
    }
}
