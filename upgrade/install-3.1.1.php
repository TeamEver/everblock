<?php
/**
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_1()
{
    $result = false;
    $sql = [];
    $sql[] =
        'ALTER TABLE ' . _DB_PREFIX_ . 'everblock
        ADD COLUMN `categories` text DEFAULT NULL
        AFTER `position`
    ';
    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }
    // Need to update all existing id_categories, to set into json_encode to categories column
    $sql = new DbQuery;
    $sql->select('*');
    $sql->from('everblock');
    $blocks = Db::getInstance()->executeS($sql);
    foreach ($blocks as $bloc) {
        $bloc_categories = json_encode($bloc['id_category']);
        Db::getInstance()->update(
            'everblock',
            [
                'categories' => $bloc_categories,
            ],
            'id_everblock = '.(int) $bloc['id_everblock']
        );
    }
    return $result;
}
