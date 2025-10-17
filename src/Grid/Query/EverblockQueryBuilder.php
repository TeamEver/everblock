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

namespace Everblock\Tools\Grid\Query;

use Context;
use DbQuery;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockQueryBuilder
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function buildQuery(array $filters = []): DbQuery
    {
        $query = new DbQuery();
        $query->select('eb.*, h.title AS hook_name, CONCAT(h.title, LPAD(eb.position, 10, "0")) AS sort_key');
        $query->from('everblock', 'eb');
        $query->leftJoin('hook', 'h', 'h.id_hook = eb.id_hook');
        $query->where('eb.id_shop = ' . (int) $this->context->shop->id);

        foreach ($filters as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            switch ($key) {
                case 'id_everblock':
                case 'position':
                    $query->where(sprintf('eb.%s = %d', pSQL($key), (int) $value));
                    break;
                case 'name':
                    $query->where('eb.name LIKE "%' . pSQL($value) . '%"');
                    break;
                case 'hook_name':
                    $query->where('h.title LIKE "%' . pSQL($value) . '%"');
                    break;
                case 'date_start':
                case 'date_end':
                    $query->where(sprintf('eb.%s LIKE "%%%s%%"', pSQL($key), pSQL($value)));
                    break;
                case 'only_home':
                case 'only_category':
                case 'only_manufacturer':
                case 'only_supplier':
                case 'only_cms_category':
                case 'modal':
                case 'active':
                    $query->where(sprintf('eb.%s = %d', pSQL($key), (int) $value));
                    break;
            }
        }

        $query->orderBy('sort_key ASC');

        return $query;
    }
}
