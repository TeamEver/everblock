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

use DbQuery;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class HookQueryBuilder
{
    /**
     * @param array<string, mixed> $filters
     */
    public function buildQuery(array $filters = []): DbQuery
    {
        $query = new DbQuery();
        $query->select('h.id_hook, h.name, h.title, h.description, h.active');
        $query->from('hook', 'h');
        $query->where('INSTR(h.name, "action") = 0');
        $query->where('INSTR(h.name, "filter") = 0');

        foreach ($filters as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            switch ($key) {
                case 'id_hook':
                    $query->where('h.id_hook = ' . (int) $value);
                    break;
                case 'name':
                case 'title':
                case 'description':
                    $query->where(sprintf('h.%s LIKE "%%%s%%"', pSQL($key), pSQL((string) $value)));
                    break;
                case 'active':
                    $query->where('h.active = ' . (int) $value);
                    break;
            }
        }

        $query->orderBy('h.id_hook ASC');

        return $query;
    }
}
