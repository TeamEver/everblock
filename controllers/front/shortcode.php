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

class EverblockshortcodeModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        $this->processShortcodes();
    }

    protected function processShortcodes()
    {
        $validToken = Tools::getToken();
        $token = Tools::getValue('token');
        if (!$token || $token !== $validToken) {
            die('');
        }

        $html = Tools::getValue('html', '', true);
        if (!$html) {
            die('');
        }

        $html = html_entity_decode($html);
        $html = EverblockTools::renderShortcodes($html, $this->context, $this->module);
        die($html);
    }
}
