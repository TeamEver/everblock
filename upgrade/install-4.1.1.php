<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_1_1()
{
    $result = true;
    $moduleColumns = [
        'name' => [
            'type' => 'text NOT NULL',
            'after' => 'id_everblock',
        ],
        'id_hook' => [
            'type' => 'int(10) unsigned NOT NULL',
            'after' => 'name',
        ],
        'only_home' => [
            'type' => '`int(10) unsigned DEFAULT 0',
            'after' => 'id_hook',
        ],
        'only_category' => [
            'type' => 'int(10) unsigned DEFAULT 0',
            'after' => 'only_home',
        ],
        'device' => [
            'type' => 'int(10) unsigned DEFAULT 0',
            'after' => 'only_category',
        ],
        'id_shop' => [
            'type' => 'int(10) unsigned NOT NULL',
            'after' => 'device',
        ],
        'position' => [
            'type' => 'int(10) unsigned DEFAULT 0',
            'after' => 'id_shop',
        ],
        'categories' => [
            'type' => 'text DEFAULT NULL',
            'after' => 'position',
        ],
        'groups' => [
            'type' => 'text DEFAULT NULL',
            'after' => 'categories',
        ],
        'background' => [
            'type' => 'varchar(255) DEFAULT NULL',
            'after' => 'groups',
        ],
        'css_class' => [
            'type' => 'varchar(255) DEFAULT NULL',
            'after' => 'background',
        ],
        'date_start' => [
            'type' => 'DATETIME DEFAULT NULL',
            'after' => 'css_class',
        ],
        'date_end' => [
            'type' => 'DATETIME DEFAULT NULL',
            'after' => 'date_start',
        ],
        'active' => [
            'type' => 'int(10) unsigned NOT NULL',
            'after' => 'date_end',
        ],
    ];
    $currentColumns = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'everblock`');
    $sql = [];
    $alterTable = true;
    foreach ($moduleColumns as $columnName => $columnType) {
        foreach ($currentColumns as $currentColumn) {
            if ((string) $columnName == (string) $currentColumn['Field']) {
                $alterTable = false;
            }
        }
        if ((bool) $alterTable === true) {
            $sql[] =
                'ALTER TABLE ' . _DB_PREFIX_ . 'everblock
                ADD COLUMN `' . pSQL($columnName) . '` ' . pSQL($columnType['type']) . '
                AFTER `' . pSQL($columnType['after']) . '`
            ';
        }
    }
    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }
    return $result;
}
