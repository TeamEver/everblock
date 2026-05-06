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

function upgrade_module_9_0_0($module)
{
    if (class_exists('Everblock\\Tools\\Service\\EverblockTools')) {
        Everblock\Tools\Service\EverblockTools::checkAndFixDatabase();
    }

    if (method_exists($module, 'checkHooks')) {
        $module->checkHooks();
    }

    return true;
}
