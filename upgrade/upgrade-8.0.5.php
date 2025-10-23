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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_8_0_5($module)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everblock_faq_product` (
            `id_everblock_faq_product` int(10) unsigned NOT NULL auto_increment,
            `id_product` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
            `id_everblock_faq` int(10) unsigned NOT NULL,
            `date_add` DATETIME DEFAULT NULL,
            `date_upd` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id_everblock_faq_product`),
            UNIQUE KEY `idx_everblock_faq_product_unique` (`id_product`, `id_shop`, `id_everblock_faq`),
            KEY `idx_everblock_faq_product_product` (`id_product`),
            KEY `idx_everblock_faq_product_faq` (`id_everblock_faq`),
            KEY `idx_everblock_faq_product_shop` (`id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

    return Db::getInstance()->execute($sql);
}
