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

use Everblock\Tools\Service\Legacy\EverblockToolsService;

class EverblockLookbookModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        // Front-office AJAX calls use the static token available in Smarty as
        // {$static_token}.  Tools::getToken() may generate a different value
        // depending on context, which caused legitimate requests to be rejected
        // with a 400 error.  Use the same static token to validate the request.
        $token = Tools::getValue('token');
        if (!$token || $token !== Tools::getToken(false)) {
            http_response_code(400);
            exit;
        }
        $idProduct = (int) Tools::getValue('id_product');
        if (!$idProduct) {
            http_response_code(400);
            exit;
        }
        $toolsService = $this->module instanceof Everblock
            ? $this->module->getLegacyToolsService()
            : null;

        $presented = $toolsService instanceof EverblockToolsService
            ? $toolsService->everPresentProducts([$idProduct], $this->context)
            : [];
        if (empty($presented)) {
            http_response_code(404);
            exit;
        }
        $product = reset($presented);
        $this->context->smarty->assign([
            'product' => $product,
        ]);
        $html = $this->context->smarty->fetch(
            _PS_THEME_DIR_ . 'templates/catalog/_partials/miniatures/product.tpl'
        );
        exit($html);
    }
}
