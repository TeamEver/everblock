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

use Everblock\Tools\Service\EverblockTools;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'everblock/src/Service/EverblockTools.php';

function upgrade_module_8_0_7($module)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everblock_faq_product` (
        `id_everblock_faq_product` int(10) unsigned NOT NULL auto_increment,
        `id_everblock_faq` int(10) unsigned NOT NULL,
        `id_product` int(10) unsigned NOT NULL,
        `id_shop` int(10) unsigned NOT NULL,
        `position` int(10) unsigned NOT NULL DEFAULT 0,
        PRIMARY KEY (`id_everblock_faq_product`),
        UNIQUE KEY `everblock_faq_product_unique` (`id_everblock_faq`, `id_product`, `id_shop`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    EverblockTools::checkAndFixDatabase();
    $module->checkHooks();

    return true;
}
