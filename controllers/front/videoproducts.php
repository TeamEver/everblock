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

class EverblockVideoproductsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        $token = Tools::getValue('token');
        if (!$token || $token !== Tools::getToken(false)) {
            http_response_code(400);
            exit;
        }
        $productIds = Tools::getValue('product_ids');
        if (!$productIds) {
            http_response_code(400);
            exit;
        }
        $ids = array_map('intval', explode(',', $productIds));
        $toolsService = $this->module instanceof Everblock
            ? $this->module->getLegacyToolsService()
            : null;

        $products = $toolsService instanceof EverblockToolsService
            ? $toolsService->everPresentProducts($ids, $this->context)
            : [];
        if (empty($products)) {
            http_response_code(404);
            exit;
        }
        $this->context->smarty->assign([
            'everPresentProducts' => $products,
            'carousel' => false,
            'shortcodeClass' => 'video-products',
        ]);
        $html = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'everblock/views/templates/hook/ever_presented_products.tpl');
        exit($html);
    }
}
