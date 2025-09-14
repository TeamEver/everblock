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

class EverblockWheelModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        header('Content-Type: application/json');
        $token = Tools::getValue('token');
        if (!$token || $token !== Tools::getToken(false)) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('Invalid token', 'wheel'),
            ]));
        }
        if (!$this->context->customer->isLogged()) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('You must be logged in to play', 'wheel'),
            ]));
        }
        $idCustomer = (int) $this->context->customer->id;
        $already = Db::getInstance()->getValue('SELECT id_everblock_wheel_play FROM ' . _DB_PREFIX_ . 'everblock_wheel_play WHERE id_customer = ' . $idCustomer);
        if ($already) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('You have already played', 'wheel'),
            ]));
        }
        $idBlock = (int) Tools::getValue('id_block');
        if (!$idBlock) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('Invalid configuration', 'wheel'),
            ]));
        }
        $row = Db::getInstance()->getRow('SELECT settings, state FROM ' . _DB_PREFIX_ . 'prettyblocks WHERE id_prettyblocks = ' . $idBlock);
        if (!$row) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('Configuration not found', 'wheel'),
            ]));
        }
        $settings = json_decode($row['settings'], true);
        if (!is_array($settings)) {
            $settings = [];
        }
        $segments = json_decode($row['state'], true);
        if (!is_array($segments) || empty($segments)) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('No segments available', 'wheel'),
            ]));
        }
        $prefix = isset($settings['coupon_prefix']) ? preg_replace('/[^A-Z0-9]/', '', Tools::strtoupper($settings['coupon_prefix'])) : 'WHEEL';
        $validity = (int) ($settings['coupon_validity'] ?? 30);
        $validity = max(1, min($validity, 365));
        $discountType = in_array($settings['coupon_type'] ?? '', ['amount', 'percent']) ? $settings['coupon_type'] : 'percent';
        $couponName = isset($settings['coupon_name']) ? $settings['coupon_name'] : 'Wheel reward';
        $total = 0;
        foreach ($segments as &$segment) {
            $prob = isset($segment['probability']) ? (float) $segment['probability'] : 1;
            $segment['probability'] = $prob;
            $segment['discount'] = isset($segment['discount']) ? (float) $segment['discount'] : 0;
            $segment['id_category'] = isset($segment['id_category']) ? (int) $segment['id_category'] : 0;
            $total += $prob;
        }
        unset($segment);
        $rand = (float) mt_rand() / (float) mt_getrandmax() * $total;
        $acc = 0;
        $result = $segments[0];
        foreach ($segments as $segment) {
            $acc += $segment['probability'];
            if ($rand <= $acc) {
                $result = $segment;
                break;
            }
        }
        $code = $prefix . Tools::strtoupper(Tools::passwdGen(8));
        $voucher = new CartRule();
        foreach (Language::getIDs(false) as $idLang) {
            $voucher->name[(int) $idLang] = $couponName;
        }
        $voucher->code = $code;
        $voucher->id_customer = (int) $this->context->customer->id;
        $voucher->date_from = date('Y-m-d H:i:s');
        $voucher->date_to = date('Y-m-d H:i:s', strtotime('+' . $validity . ' days'));
        $voucher->quantity = 1;
        $voucher->quantity_per_user = 1;
        if ($discountType === 'amount') {
            $voucher->reduction_amount = isset($result['discount']) ? (float) $result['discount'] : 0;
            $voucher->reduction_tax = 1;
        } else {
            $voucher->reduction_percent = isset($result['discount']) ? (float) $result['discount'] : 10;
        }
        $voucher->active = 1;
        $voucher->add();
        $idCategory = (int) ($result['id_category'] ?? 0);
        if ($idCategory > 0) {
            Db::getInstance()->insert('cart_rule_product_rule_group', [
                'id_cart_rule' => (int) $voucher->id,
                'quantity' => 1,
            ]);
            $idGroup = (int) Db::getInstance()->Insert_ID();
            Db::getInstance()->insert('cart_rule_product_rule', [
                'id_product_rule_group' => $idGroup,
                'type' => 'categories',
            ]);
            $idRule = (int) Db::getInstance()->Insert_ID();
            Db::getInstance()->insert('cart_rule_product_rule_value', [
                'id_product_rule' => $idRule,
                'id_item' => $idCategory,
            ]);
        }
        Db::getInstance()->insert('everblock_wheel_play', [
            'id_customer' => $idCustomer,
            'result' => pSQL($result['label'] ?? ''),
            'date_add' => date('Y-m-d H:i:s'),
        ]);
        die(json_encode([
            'status' => true,
            'result' => $result,
            'code' => $code,
            'message' => $this->module->l('You won:', 'wheel') . ' ' . htmlspecialchars($result['label'] ?? '', ENT_QUOTES, 'UTF-8') . ' - ' . $this->module->l('Your code:', 'wheel') . ' ' . $code,
        ]));
    }
}
