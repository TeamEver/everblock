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
        $idBlock = (int) Tools::getValue('id_block');
        if (!$idBlock) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('Invalid configuration', 'wheel'),
            ]));
        }
        $already = Db::getInstance()->getValue('SELECT id_everblock_game_play FROM ' . _DB_PREFIX_ . 'everblock_game_play WHERE id_customer = ' . $idCustomer . ' AND id_prettyblocks = ' . $idBlock);
        $checkOnly = (bool) Tools::getValue('check');
        if ($checkOnly) {
            if ($already) {
                die(json_encode([
                    'status' => false,
                    'played' => true,
                    'message' => $this->module->l('You have already played', 'wheel'),
                ]));
            }
            die(json_encode([
                'status' => true,
                'played' => false,
            ]));
        }
        if ($already) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('You have already played', 'wheel'),
            ]));
        }
        $idShop = (int) $this->context->shop->id;
        $idLang = (int) $this->context->language->id;
        $row = Db::getInstance()->getRow('SELECT config, state FROM ' . _DB_PREFIX_ . 'prettyblocks WHERE id_prettyblocks = ' . $idBlock . ' AND id_shop = ' . $idShop . ' AND id_lang = ' . $idLang);
        if (!$row) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('Configuration not found', 'wheel'),
            ]));
        }
        $settings = json_decode($row['config'], true);
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
        $segments = $this->normalizeSegments($segments);
        if (empty($segments)) {
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
        $maxWinners = (int) ($settings['max_winners'] ?? 0);
        if ($maxWinners < 0) {
            $maxWinners = 0;
        }
        $total = 0;
        foreach ($segments as &$segment) {
            $prob = isset($segment['probability']) ? (float) $segment['probability'] : 1;
            $segment['probability'] = $prob;
            $segment['discount'] = isset($segment['discount']) ? (float) $segment['discount'] : 0;
            $segment['id_categories'] = array_map('intval', (array) ($segment['id_categories'] ?? []));
            $segment['isWinning'] = filter_var($segment['isWinning'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $total += $prob;
        }
        unset($segment);
        if ($total <= 0) {
            die(json_encode([
                'status' => false,
                'message' => $this->module->l('Invalid probabilities', 'wheel'),
            ]));
        }
        $rand = (float) mt_rand() / (float) mt_getrandmax() * $total;
        $acc = 0;
        $result = reset($segments);
        $index = 0;
        foreach ($segments as $i => $segment) {
            $acc += $segment['probability'];
            if ($rand <= $acc) {
                $result = $segment;
                $index = (int) $i;
                break;
            }
        }

        $resultLabel = '';
        if (isset($result['label'])) {
            if (is_array($result['label'])) {
                $labels = $result['label'];
                $resultLabel = (string) ($labels[$idLang] ?? reset($labels));
            } else {
                $resultLabel = (string) $result['label'];
            }
        }
        $isWinning = filter_var($result['isWinning'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $rewardsDepleted = false;
        if ($isWinning && $maxWinners > 0) {
            $totalWinners = (int) Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'everblock_game_play '
                . 'WHERE id_prettyblocks = ' . (int) $idBlock . ' AND is_winner = 1'
            );
            if ($totalWinners >= $maxWinners) {
                $isWinning = false;
                $rewardsDepleted = true;
            }
        }
        $result['isWinning'] = $isWinning;
        $code = null;
        $categoryNames = [];
        if ($isWinning) {
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
            $idCategories = array_map('intval', (array) ($result['id_categories'] ?? []));
            $categoryNames = [];
            if (!empty($idCategories)) {
                $validCategoryIds = [];
                foreach ($idCategories as $idCategory) {
                    $category = new Category($idCategory, $idLang, $idShop);
                    if (Validate::isLoadedObject($category) && $category->active && $category->isAssociatedToShop($idShop)) {
                        $validCategoryIds[] = $idCategory;
                        $categoryNames[] = $category->name;
                    }
                }
                if (!empty($validCategoryIds)) {
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
                    foreach ($validCategoryIds as $idCategory) {
                        Db::getInstance()->insert('cart_rule_product_rule_value', [
                            'id_product_rule' => $idRule,
                            'id_item' => $idCategory,
                        ]);
                    }
                }
            }
        }
        Db::getInstance()->insert('everblock_game_play', [
            'id_prettyblocks' => $idBlock,
            'id_customer' => $idCustomer,
            'result' => pSQL($resultLabel),
            'is_winner' => $isWinning ? 1 : 0,
            'date_add' => date('Y-m-d H:i:s'),
        ]);
        if ($rewardsDepleted) {
            $message = $this->module->l('All rewards have already been distributed.', 'wheel');
        } else {
            $message = $isWinning
                ? $this->module->l('You won:', 'wheel') . ' ' . htmlspecialchars($resultLabel, ENT_QUOTES, 'UTF-8') . ' - ' . $this->module->l('Your code:', 'wheel') . ' ' . $code
                : $this->module->l('You lost:', 'wheel') . ' ' . htmlspecialchars($resultLabel, ENT_QUOTES, 'UTF-8');
        }
        $categoriesMessage = '';
        if (!empty($categoryNames)) {
            $categoriesMessage = $this->module->l('Valid for categories:', 'wheel') . ' ' . implode(', ', $categoryNames);
        }
        die(json_encode([
            'status' => true,
            'result' => $result,
            'index' => $index,
            'code' => $code,
            'message' => $message,
            'categories_message' => $categoriesMessage,
        ]));
    }

    private function normalizeSegments(array $segments)
    {
        foreach ($segments as $key => $segment) {
            if (is_array($segment)) {
                $segments[$key] = $this->normalizeSegmentValues($segment);
            } else {
                unset($segments[$key]);
            }
        }

        return $segments;
    }

    private function normalizeSegmentValues(array $segment)
    {
        foreach ($segment as $key => $value) {
            $segment[$key] = $this->extractSegmentValue($value);
        }

        return $segment;
    }

    private function extractSegmentValue($value)
    {
        if (is_array($value)) {
            if (array_key_exists('value', $value)) {
                return $this->extractSegmentValue($value['value']);
            }

            foreach ($value as $subKey => $subValue) {
                $value[$subKey] = $this->extractSegmentValue($subValue);
            }
        }

        return $value;
    }
}
