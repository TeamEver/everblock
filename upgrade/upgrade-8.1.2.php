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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_8_1_2($module)
{
    $db = Db::getInstance();
    $table = _DB_PREFIX_ . 'everblock_page';

    $columnExists = $db->executeS('SHOW COLUMNS FROM `' . $table . '` LIKE "position"');
    if (!$columnExists) {
        $db->execute('ALTER TABLE `' . $table . '` ADD `position` int(10) unsigned NOT NULL DEFAULT 0 AFTER `active`');
    }

    $shops = Shop::getShops(false, null, true);
    foreach ($shops as $shopId) {
        $pages = $db->executeS(
            'SELECT `id_everblock_page` FROM `' . $table . '` WHERE `id_shop` = ' . (int) $shopId . ' ORDER BY `id_everblock_page` ASC'
        );

        $position = 0;
        foreach ($pages as $page) {
            $db->update(
                'everblock_page',
                ['position' => (int) $position],
                '`id_everblock_page` = ' . (int) $page['id_everblock_page'] . ' AND `id_shop` = ' . (int) $shopId
            );
            ++$position;
        }
    }

    return (bool) $module;
}
