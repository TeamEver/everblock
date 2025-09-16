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
        $ipAddress = Tools::getRemoteAddr();
        if (!is_string($ipAddress)) {
            $ipAddress = '';
        } else {
            $ipAddress = Tools::substr($ipAddress, 0, 45);
        }
        $already = Db::getInstance()->getValue(
            'SELECT id_everblock_game_play FROM ' . _DB_PREFIX_ . 'everblock_game_play WHERE id_customer = ' . $idCustomer
            . ' AND id_prettyblocks = ' . $idBlock
        );
        $ipAlready = false;
        if ($ipAddress !== '') {
            $ipAlready = Db::getInstance()->getValue(
                'SELECT id_everblock_game_play FROM ' . _DB_PREFIX_ . 'everblock_game_play WHERE id_prettyblocks = '
                . $idBlock . " AND ip_address = '" . pSQL($ipAddress) . "'"
            );
        }
        $refusalMessage = $this->module->l(
            'You have already played. The game can only be played once per household.',
            'wheel'
        );
        $checkOnly = (bool) Tools::getValue('check');
        if ($checkOnly) {
            if ($already || $ipAlready) {
                die(json_encode([
                    'status' => false,
                    'played' => true,
                    'message' => $refusalMessage,
                ]));
            }
            die(json_encode([
                'status' => true,
                'played' => false,
            ]));
        }
        if ($already || $ipAlready) {
            die(json_encode([
                'status' => false,
                'message' => $refusalMessage,
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
        $defaultCouponName = 'Wheel reward';
        if (array_key_exists('coupon_name', $settings)) {
            $defaultCouponNameValue = $this->extractSegmentValue($settings['coupon_name']);
            if (is_array($defaultCouponNameValue)) {
                $defaultCouponNameValue = reset($defaultCouponNameValue);
            }
            if ($defaultCouponNameValue !== null) {
                $defaultCouponName = (string) $defaultCouponNameValue;
            } else {
                $defaultCouponName = '';
            }
        }
        $defaultPrefix = 'WHEEL';
        if (array_key_exists('coupon_prefix', $settings)) {
            $defaultPrefixValue = $this->extractSegmentValue($settings['coupon_prefix']);
            if (is_array($defaultPrefixValue)) {
                $defaultPrefixValue = reset($defaultPrefixValue);
            }
            $defaultPrefix = preg_replace('/[^A-Z0-9]/', '', Tools::strtoupper((string) $defaultPrefixValue));
            if ($defaultPrefix === null) {
                $defaultPrefix = '';
            }
        }
        $defaultValidity = 30;
        if (array_key_exists('coupon_validity', $settings)) {
            $rawDefaultValidity = $this->extractSegmentValue($settings['coupon_validity']);
            if (is_array($rawDefaultValidity)) {
                $rawDefaultValidity = reset($rawDefaultValidity);
            }
            if ($rawDefaultValidity !== null) {
                $defaultValidity = (int) $rawDefaultValidity;
            }
        }
        $defaultValidity = max(1, min($defaultValidity, 365));
        $defaultDiscountType = 'percent';
        if (array_key_exists('coupon_type', $settings)) {
            $rawDefaultDiscountType = $this->extractSegmentValue($settings['coupon_type']);
            if (is_array($rawDefaultDiscountType)) {
                $rawDefaultDiscountType = reset($rawDefaultDiscountType);
            }
            if (in_array($rawDefaultDiscountType, ['amount', 'percent'])) {
                $defaultDiscountType = $rawDefaultDiscountType;
            }
        }
        $defaultMaxWinners = 0;
        if (array_key_exists('max_winners', $settings)) {
            $rawDefaultMaxWinners = $this->extractSegmentValue($settings['max_winners']);
            if (is_array($rawDefaultMaxWinners)) {
                $rawDefaultMaxWinners = reset($rawDefaultMaxWinners);
            }
            if ($rawDefaultMaxWinners !== null) {
                $defaultMaxWinners = (int) $rawDefaultMaxWinners;
            }
        }
        if ($defaultMaxWinners < 0) {
            $defaultMaxWinners = 0;
        }
        $total = 0;
        foreach ($segments as &$segment) {
            $prob = isset($segment['probability']) ? (float) $segment['probability'] : 1;
            $segment['probability'] = $prob;
            $segment['discount'] = isset($segment['discount']) ? (float) $segment['discount'] : 0;
            $segment['minimum_purchase'] = isset($segment['minimum_purchase']) ? (float) $segment['minimum_purchase'] : 0;
            if ($segment['minimum_purchase'] < 0) {
                $segment['minimum_purchase'] = 0;
            }
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
        $segmentCouponName = $defaultCouponName;
        if (array_key_exists('coupon_name', $result)) {
            $segmentCouponNameValue = $this->extractSegmentValue($result['coupon_name']);
            if (is_array($segmentCouponNameValue)) {
                $segmentCouponNameValue = reset($segmentCouponNameValue);
            }
            if ($segmentCouponNameValue !== null) {
                $segmentCouponName = $segmentCouponNameValue;
            }
        }
        $segmentCouponName = (string) $segmentCouponName;
        $segmentPrefix = $defaultPrefix;
        if (array_key_exists('coupon_prefix', $result)) {
            $segmentPrefixValue = $this->extractSegmentValue($result['coupon_prefix']);
            if (is_array($segmentPrefixValue)) {
                $segmentPrefixValue = reset($segmentPrefixValue);
            }
            $segmentPrefix = preg_replace('/[^A-Z0-9]/', '', Tools::strtoupper((string) $segmentPrefixValue));
            if ($segmentPrefix === null) {
                $segmentPrefix = '';
            }
        }
        $segmentValidity = $defaultValidity;
        if (array_key_exists('coupon_validity', $result)) {
            $segmentValidityValue = $this->extractSegmentValue($result['coupon_validity']);
            if (is_array($segmentValidityValue)) {
                $segmentValidityValue = reset($segmentValidityValue);
            }
            if ($segmentValidityValue !== null) {
                $segmentValidity = (int) $segmentValidityValue;
            }
        }
        $segmentValidity = max(1, min($segmentValidity, 365));
        $segmentDiscountType = $defaultDiscountType;
        if (array_key_exists('coupon_type', $result)) {
            $segmentDiscountTypeValue = $this->extractSegmentValue($result['coupon_type']);
            if (is_array($segmentDiscountTypeValue)) {
                $segmentDiscountTypeValue = reset($segmentDiscountTypeValue);
            }
            if (in_array($segmentDiscountTypeValue, ['amount', 'percent'])) {
                $segmentDiscountType = $segmentDiscountTypeValue;
            }
        }
        $segmentMaxWinners = null;
        if (array_key_exists('max_winners', $result)) {
            $segmentMaxWinnersValue = $this->extractSegmentValue($result['max_winners']);
            if (is_array($segmentMaxWinnersValue)) {
                $segmentMaxWinnersValue = reset($segmentMaxWinnersValue);
            }
            if ($segmentMaxWinnersValue !== null) {
                $segmentMaxWinners = (int) $segmentMaxWinnersValue;
            } else {
                $segmentMaxWinners = 0;
            }
            if ($segmentMaxWinners < 0) {
                $segmentMaxWinners = 0;
            }
        }
        $segmentMinimumPurchase = isset($result['minimum_purchase']) ? (float) $result['minimum_purchase'] : 0;
        if ($segmentMinimumPurchase < 0) {
            $segmentMinimumPurchase = 0;
        }
        $segmentMinimumPurchase = (float) Tools::ps_round($segmentMinimumPurchase, 2);
        $useSegmentLimit = $segmentMaxWinners !== null;
        $maxWinners = $useSegmentLimit ? $segmentMaxWinners : $defaultMaxWinners;
        if ($maxWinners < 0) {
            $maxWinners = 0;
        }
        $couponName = (string) $segmentCouponName;
        $prefix = (string) $segmentPrefix;
        $validity = $segmentValidity;
        $discountType = $segmentDiscountType;
        $isWinning = filter_var($result['isWinning'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $rewardsDepleted = false;
        if ($isWinning && $maxWinners > 0) {
            if ($useSegmentLimit) {
                $totalWinners = (int) Db::getInstance()->getValue(
                    'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'everblock_game_play '
                    . 'WHERE id_prettyblocks = ' . (int) $idBlock
                    . " AND is_winner = 1 AND result = '" . pSQL($resultLabel) . "'"
                );
            } else {
                $totalWinners = (int) Db::getInstance()->getValue(
                    'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'everblock_game_play '
                    . 'WHERE id_prettyblocks = ' . (int) $idBlock . ' AND is_winner = 1'
                );
            }
            if ($totalWinners >= $maxWinners) {
                $isWinning = false;
                $rewardsDepleted = true;
            }
        }
        $currency = $this->context->currency;
        if (!Validate::isLoadedObject($currency)) {
            $defaultCurrencyId = (int) Configuration::get('PS_CURRENCY_DEFAULT', null, null, $idShop);
            if (!$defaultCurrencyId) {
                $defaultCurrencyId = (int) Configuration::get('PS_CURRENCY_DEFAULT');
            }
            if ($defaultCurrencyId) {
                $currency = new Currency($defaultCurrencyId);
            }
        }
        $isCurrencyValid = Validate::isLoadedObject($currency);
        $currencyId = $isCurrencyValid ? (int) $currency->id : 0;
        $priceCurrency = $isCurrencyValid ? $currency : null;
        $result['isWinning'] = $isWinning;
        $code = null;
        $displayCategoryNames = [];
        if ($isWinning) {
            $code = $prefix . Tools::strtoupper(Tools::passwdGen(8));
            $voucher = new CartRule();
            foreach (Language::getIDs(false) as $langId) {
                $voucher->name[(int) $langId] = $couponName;
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
            $voucher->minimum_amount = $segmentMinimumPurchase;
            $voucher->minimum_amount_tax = $segmentMinimumPurchase > 0 ? 1 : 0;
            $voucher->minimum_amount_currency = $currencyId;
            $voucher->minimum_amount_shipping = 0;
            $voucher->active = 1;
            $voucher->add();
            $idCategories = array_map('intval', (array) ($result['id_categories'] ?? []));
            if (!empty($idCategories)) {
                $validCategoryIds = [];
                $rootCategoryId = (int) Configuration::get('PS_ROOT_CATEGORY', null, null, $idShop);
                if (!$rootCategoryId) {
                    $rootCategoryId = 1;
                }
                $groupRestrictionsActive = method_exists('Group', 'isFeatureActive') ? Group::isFeatureActive() : false;
                $customerGroupIds = [];
                if ($groupRestrictionsActive && method_exists('Group', 'getCustomerGroups')) {
                    $customerGroupIds = Group::getCustomerGroups($idCustomer);
                    if (!is_array($customerGroupIds)) {
                        $customerGroupIds = [];
                    }
                }
                $groupIdList = implode(',', array_map('intval', $customerGroupIds));
                foreach ($idCategories as $idCategory) {
                    $category = new Category($idCategory, $idLang, $idShop);
                    if (Validate::isLoadedObject($category) && $category->active && $category->isAssociatedToShop($idShop)) {
                        $validCategoryIds[] = $idCategory;
                        $isRootCategory = (int) $category->id_parent === 0 || (int) $category->id === $rootCategoryId;
                        if (!$isRootCategory) {
                            $hasAccess = true;
                            if ($groupRestrictionsActive) {
                                if (method_exists($category, 'checkAccess')) {
                                    $hasAccess = (bool) $category->checkAccess($idCustomer);
                                } elseif (!empty($groupIdList)) {
                                    $hasAccess = (bool) Db::getInstance()->getValue(
                                        'SELECT 1 FROM ' . _DB_PREFIX_ . 'category_group WHERE id_category = ' . (int) $idCategory
                                        . ' AND id_group IN (' . $groupIdList . ')'
                                    );
                                }
                            }
                            if ($hasAccess) {
                                $displayCategoryNames[] = $category->name;
                            }
                        }
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
            'ip_address' => pSQL($ipAddress),
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
        if (!empty($displayCategoryNames)) {
            $uniqueCategoryNames = array_values(array_unique($displayCategoryNames));
            $categoriesMessage = $this->module->l('Valid for categories:', 'wheel') . ' ' . implode(', ', $uniqueCategoryNames);
        }
        $minimumPurchaseMessage = '';
        if ($segmentMinimumPurchase > 0) {
            $minimumPurchaseMessage = $this->module->l('Minimum purchase (tax incl.):', 'wheel') . ' '
                . Tools::displayPrice($segmentMinimumPurchase, $priceCurrency);
        }
        die(json_encode([
            'status' => true,
            'result' => $result,
            'index' => $index,
            'code' => $code,
            'message' => $message,
            'categories_message' => $categoriesMessage,
            'minimum_purchase_message' => $minimumPurchaseMessage,
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
