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

namespace Everblock\Tools\Service\Admin;

use Context;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class StatisticsProvider
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(?Context $context = null)
    {
        $this->context = $context ?? Context::getContext();
    }

    /**
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        $defaults = [
            'blocks_total' => 0,
            'blocks_active' => 0,
            'shortcodes' => 0,
            'faqs' => 0,
            'tabs' => 0,
            'flags' => 0,
            'modals' => 0,
            'game_sessions' => 0,
        ];

        if (!class_exists('Db') || !defined('_DB_PREFIX_') || !isset($this->context->shop)) {
            return $defaults;
        }

        $idShop = (int) $this->context->shop->id;

        $defaults['blocks_total'] = $this->countTableRecords('everblock', 'id_shop = ' . $idShop);
        $defaults['blocks_active'] = $this->countTableRecords('everblock', 'id_shop = ' . $idShop . ' AND active = 1');
        $defaults['shortcodes'] = $this->countTableRecords('everblock_shortcode', 'id_shop = ' . $idShop);
        $defaults['faqs'] = $this->countTableRecords('everblock_faq', 'id_shop = ' . $idShop);
        $defaults['tabs'] = $this->countTableRecords('everblock_tabs', 'id_shop = ' . $idShop);
        $defaults['flags'] = $this->countTableRecords('everblock_flags', 'id_shop = ' . $idShop);
        $defaults['modals'] = $this->countTableRecords('everblock_modal', 'id_shop = ' . $idShop);
        $defaults['game_sessions'] = $this->countTableRecords('everblock_game_play');

        return $defaults;
    }

    private function countTableRecords(string $table, ?string $whereClause = null): int
    {
        if (!method_exists('Db', 'getInstance')) {
            return 0;
        }

        $tableName = defined('_DB_PREFIX_') ? _DB_PREFIX_ . $table : $table;
        $useSlave = defined('_PS_USE_SQL_SLAVE_') ? _PS_USE_SQL_SLAVE_ : false;
        $db = \Db::getInstance($useSlave);
        $sql = sprintf('SELECT COUNT(*) FROM `%s`', $this->escapeSqlName($tableName));

        if ($whereClause) {
            $sql .= ' WHERE ' . $whereClause;
        }

        return (int) $db->getValue($sql);
    }

    private function escapeSqlName(string $value): string
    {
        if (function_exists('bqSQL')) {
            return bqSQL($value);
        }

        return str_replace('`', '\\`', $value);
    }
}
