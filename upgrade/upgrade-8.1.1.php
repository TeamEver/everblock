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

function upgrade_module_8_1_1($module)
{
    $db = Db::getInstance();
    $columnExists = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'everblock_page_lang` LIKE "short_description"');

    if (!$columnExists) {
        $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_page_lang` ADD `short_description` TEXT DEFAULT NULL';
        if (!$db->execute($sql)) {
            return false;
        }
    }

    EverblockTools::checkAndFixDatabase();
    $module->checkHooks();

    return true;
}
