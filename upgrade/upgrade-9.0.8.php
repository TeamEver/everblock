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

/**
 * 9.0.8 : retrait de la FAQ (remplacée par qcdfaq), des jeux (roue, grattage, boîtes mystère,
 * calendrier de l'Avent, machine à sous) et de l'intégration PrettyBlocks ; greffe MCP.
 *
 * Supprime les tables everblock_faq* et everblock_game_play, l'onglet BO « FAQ », les réglages EVERBLOCK_FAQ_* et les
 * hooks d'objet de la FAQ. Les shortcodes [everfaq ...] restés dans des contenus sont effacés au
 * rendu (EverblockTools::stripRemovedFaqShortcodes) et signalés par l'outil MCP everblock_audit.
 */
function upgrade_module_9_0_8($module)
{
    $db = Db::getInstance();
    foreach (['everblock_faq_product', 'everblock_faq_lang', 'everblock_faq', 'everblock_game_play'] as $table) {
        if (!$db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL($table) . '`')) {
            return false;
        }
    }

    // Onglets et flags vides créés à chaque enregistrement de fiche par les versions précédentes :
    // ils grossissaient les tables et gardaient le hook des flags actif sans flag réel.
    foreach ([['everblock_tabs', 'id_everblock_tabs'], ['everblock_flags', 'id_everblock_flags']] as [$table, $key]) {
        $db->execute(
            'DELETE FROM `' . _DB_PREFIX_ . $table . '` WHERE `' . $key . '` NOT IN (
                SELECT `' . $key . '` FROM `' . _DB_PREFIX_ . $table . '_lang`
                WHERE TRIM(COALESCE(`title`, "")) <> "" OR TRIM(COALESCE(`content`, "")) <> ""
            )'
        );
        $db->execute(
            'DELETE l FROM `' . _DB_PREFIX_ . $table . '_lang` l
             LEFT JOIN `' . _DB_PREFIX_ . $table . '` t ON t.`' . $key . '` = l.`' . $key . '`
             WHERE t.`' . $key . '` IS NULL'
        );
    }
    // Contrôles d'environnement relancés une fois avec la nouvelle version.
    Configuration::deleteByName('EVERBLOCK_ENV_CHECKED');

    Configuration::deleteByName('EVERBLOCK_FAQ_BASE_URL');
    Configuration::deleteByName('EVERBLOCK_FAQ_PER_PAGE');

    $tabId = (int) Tab::getIdFromClassName('AdminEverBlockFaq');
    if ($tabId > 0) {
        $tab = new Tab($tabId);
        if (Validate::isLoadedObject($tab) && !$tab->delete()) {
            return false;
        }
    }

    // Hooks de la FAQ retirée, de l'intégration PrettyBlocks et de l'étape de commande dans les e-mails.
    foreach (['actionObjectEverblockFaqUpdateAfter', 'actionObjectEverblockFaqDeleteAfter', 'actionRegisterBlock', 'actionEmailSendBefore', 'displayOrderDetail'] as $hookName) {
        if ($module->isRegisteredInHook($hookName)) {
            $module->unregisterHook($hookName);
        }
    }

    if (class_exists('\Everblock\Tools\Service\EverblockCache')) {
        \Everblock\Tools\Service\EverblockCache::cacheDropByPattern('EverblockFaq_');
    }
    // Images de l'intégration PrettyBlocks retirée (dossier livré par les versions précédentes).
    $prettyblocksDir = _PS_MODULE_DIR_ . $module->name . '/views/img/prettyblocks';
    if (is_dir($prettyblocksDir)) {
        Tools::deleteDirectory($prettyblocksDir);
    }
    Tools::clearSf2Cache();

    return true;
}
