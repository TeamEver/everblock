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
        $segments = Tools::getValue('segments');
        if (is_string($segments)) {
            $segments = json_decode($segments, true);
        }
        if (!is_array($segments) || empty($segments)) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('No segments available', 'wheel'),
            ]));
        }
        $prefix = Tools::getValue('coupon_prefix', 'WHEEL');
        $validity = max(1, (int) Tools::getValue('coupon_validity', 30));
        $discountType = Tools::getValue('coupon_type', 'percent');
        $couponName = Tools::getValue('coupon_name', 'Wheel reward');
        $total = 0;
        foreach ($segments as $segment) {
            $total += isset($segment['probability']) ? (float) $segment['probability'] : 1;
        }
        $rand = (float) mt_rand() / (float) mt_getrandmax() * $total;
        $acc = 0;
        $result = $segments[0];
        foreach ($segments as $segment) {
            $prob = isset($segment['probability']) ? (float) $segment['probability'] : 1;
            $acc += $prob;
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
        Db::getInstance()->insert('everblock_wheel_play', [
            'id_customer' => $idCustomer,
            'result' => pSQL($result['label'] ?? ''),
            'date_add' => date('Y-m-d H:i:s'),
        ]);
        die(json_encode([
            'status' => true,
            'result' => $result,
            'code' => $code,
            'message' => $this->module->l('You won:', 'wheel') . ' ' . ($result['label'] ?? '') . ' - ' . $this->module->l('Your code:', 'wheel') . ' ' . $code,
        ]));
    }
}
