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

$sql = [];
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_shortcode`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_shortcode_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_faq`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_faq_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_tabs`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_tabs_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_flags`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'everblock_flags_lang`;';

foreach ($sql as $s) {
    if (!Db::getInstance()->execute($s)) {
        return false;
    }
}
