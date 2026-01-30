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

class EverblockSlotmachineModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        header('Content-Type: application/json');

        $token = Tools::getValue('token');
        if (!$token || $token !== Tools::getToken(false)) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('Invalid token', 'slotmachine'),
            ]);
        }

        $idBlock = (int) Tools::getValue('id_block');
        if (!$idBlock) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('Invalid configuration', 'slotmachine'),
            ]);
        }

        $checkOnly = (bool) Tools::getValue('check');
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $row = Db::getInstance()->getRow(
            'SELECT config, state FROM ' . _DB_PREFIX_ . 'prettyblocks WHERE id_prettyblocks = ' . (int) $idBlock
            . ' AND id_shop = ' . (int) $idShop . ' AND id_lang = ' . (int) $idLang
        );

        if (!$row) {
            $response = [
                'status' => false,
                'message' => $this->module->l('Configuration not found', 'slotmachine'),
            ];
            if ($checkOnly) {
                $response['played'] = false;
                $response['playable'] = false;
            }
            $this->renderJson($response);
        }

        $settings = json_decode($row['config'], true);
        if (!is_array($settings)) {
            $settings = [];
        }
        $rawSymbols = json_decode($row['state'], true);
        $requireLogin = $this->resolveBoolean($settings['require_login'] ?? true);
        $customerLogged = $this->context->customer->isLogged();
        $employeeLogged = $this->isAdmin();

        $idCustomer = $customerLogged ? (int) $this->context->customer->id : 0;
        $ipAddress = Tools::getRemoteAddr();
        if (!is_string($ipAddress)) {
            $ipAddress = '';
        } else {
            $ipAddress = Tools::substr($ipAddress, 0, 45);
        }

        $already = 0;
        if ($idCustomer > 0) {
            $already = (int) Db::getInstance()->getValue(
                'SELECT id_everblock_game_play FROM ' . _DB_PREFIX_ . 'everblock_game_play WHERE id_customer = ' . (int) $idCustomer
                . ' AND id_prettyblocks = ' . (int) $idBlock
            );
        }
        $ipAlready = false;
        if ($ipAddress !== '') {
            $ipAlready = Db::getInstance()->getValue(
                'SELECT id_everblock_game_play FROM ' . _DB_PREFIX_ . 'everblock_game_play WHERE id_prettyblocks = '
                . (int) $idBlock . " AND ip_address = '" . pSQL($ipAddress) . "'"
            );
        }

        if ($employeeLogged) {
            $already = 0;
            $ipAlready = false;
        }

        $startDateValue = $this->resolveConfigValue($settings['start_date'] ?? '');
        $endDateValue = $this->resolveConfigValue($settings['end_date'] ?? '');
        $preStartMessage = $this->resolveConfigValue($settings['pre_start_message'] ?? '');
        $postEndMessage = $this->resolveConfigValue($settings['post_end_message'] ?? '');
        $defaultPreStartMessage = $this->module->l('The game has not started yet.', 'slotmachine');
        $defaultPostEndMessage = $this->module->l('The game is over.', 'slotmachine');

        $now = time();
        $startTimestamp = $this->parseDateTime($startDateValue);
        $endTimestamp = $this->parseDateTime($endDateValue);
        $isBeforeStart = $startTimestamp !== null && $now < $startTimestamp;
        $isAfterEnd = $endTimestamp !== null && $now > $endTimestamp;

        if (!$employeeLogged) {
            if ($isBeforeStart) {
                $message = $preStartMessage !== '' ? $preStartMessage : $defaultPreStartMessage;
                $response = [
                    'status' => false,
                    'message' => $message,
                    'playable' => false,
                    'reason' => 'before_start',
                ];
                if ($checkOnly) {
                    $response['played'] = false;
                }
                if ($startTimestamp !== null) {
                    $response['start_timestamp'] = $startTimestamp;
                    $response['countdown'] = max(0, $startTimestamp - $now);
                }
                $this->renderJson($response);
            }
            if ($isAfterEnd) {
                $message = $postEndMessage !== '' ? $postEndMessage : $defaultPostEndMessage;
                $response = [
                    'status' => false,
                    'message' => $message,
                    'playable' => false,
                    'reason' => 'after_end',
                ];
                if ($checkOnly) {
                    $response['played'] = false;
                }
                if ($endTimestamp !== null) {
                    $response['end_timestamp'] = $endTimestamp;
                }
                $this->renderJson($response);
            }
        }

        if ($requireLogin && !$customerLogged && !$employeeLogged) {
            $response = [
                'status' => false,
                'message' => $this->module->l('You must be logged in to play', 'slotmachine'),
                'playable' => false,
                'reason' => 'login_required',
            ];
            if ($checkOnly) {
                $response['played'] = false;
            }
            $this->renderJson($response);
        }

        $refusalMessage = $this->module->l(
            'You have already played. The game can only be played once per household.',
            'slotmachine'
        );

        if ($checkOnly) {
            if (($already && $idCustomer) || $ipAlready) {
                $this->renderJson([
                    'status' => false,
                    'played' => true,
                    'playable' => false,
                    'message' => $refusalMessage,
                ]);
            }
            if (!is_array($rawSymbols) || empty($rawSymbols)) {
                $this->renderJson([
                    'status' => false,
                    'played' => false,
                    'playable' => false,
                    'message' => $this->module->l('No symbols available', 'slotmachine'),
                ]);
            }
            $this->renderJson([
                'status' => true,
                'played' => false,
                'playable' => true,
                'start_timestamp' => $startTimestamp,
                'end_timestamp' => $endTimestamp,
            ]);
        }

        if ((!$employeeLogged && $idCustomer && $already) || (!$employeeLogged && $ipAlready)) {
            $this->renderJson([
                'status' => false,
                'message' => $refusalMessage,
                'playable' => false,
            ]);
        }

        if (!is_array($rawSymbols) || empty($rawSymbols)) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('No symbols available', 'slotmachine'),
                'playable' => false,
            ]);
        }

        $symbols = $this->normalizeSegments($rawSymbols);
        $symbols = $this->normalizeSymbols($symbols, $idLang);
        if (empty($symbols)) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('No symbols available', 'slotmachine'),
                'playable' => false,
            ]);
        }

        $defaultCouponName = (string) $this->resolveConfigValue($settings['default_coupon_name'] ?? '');
        if ($defaultCouponName === '') {
            $defaultCouponName = 'Slot machine reward';
        }
        $defaultCouponPrefix = Tools::strtoupper((string) $this->resolveConfigValue($settings['default_coupon_prefix'] ?? 'SLOT'));
        $defaultCouponPrefix = preg_replace('/[^A-Z0-9]/', '', $defaultCouponPrefix);
        if ($defaultCouponPrefix === null) {
            $defaultCouponPrefix = '';
        }
        $defaultCouponValidity = (int) $this->resolveNumericValue($settings['default_coupon_validity'] ?? 30, 1, 365, 30);
        $defaultCouponTypeValue = $this->resolveConfigValue($settings['default_coupon_type'] ?? 'percent');
        $defaultCouponType = in_array($defaultCouponTypeValue, ['percent', 'amount']) ? $defaultCouponTypeValue : 'percent';
        $defaultMaxWinners = (int) $this->resolveNumericValue($settings['default_max_winners'] ?? 0, 0, PHP_INT_MAX, 0);

        $combinationRules = $this->parseWinningCombinations($settings['winning_combinations'] ?? []);
        $resultSymbols = [];
        for ($i = 0; $i < 3; $i++) {
            $resultSymbols[] = $this->pickWeightedSymbol($symbols, $idLang);
        }

        $patternKeys = array_map(static function ($symbol) {
            return $symbol['symbol_key'];
        }, $resultSymbols);

        $matchedCombination = $this->findMatchingCombination($combinationRules, $patternKeys, $idLang);
        $isWinning = false;
        $rewardsDepleted = false;
        $rewardCode = null;
        $rewardLabel = '';
        $resultMessage = '';
        $categoriesMessage = '';
        $minimumPurchaseMessage = '';
        $displayCategoryNames = [];
        $segmentMinimumPurchase = 0.0;

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

        if ($matchedCombination !== null) {
            $rewardLabel = (string) ($matchedCombination['label'] ?? '');
            $resultMessage = (string) ($matchedCombination['message'] ?? '');
            $isWinning = (bool) ($matchedCombination['isWinning'] ?? false);
            $segmentMinimumPurchase = isset($matchedCombination['minimum_purchase'])
                ? max(0, (float) $matchedCombination['minimum_purchase'])
                : 0;
            $segmentMinimumPurchase = (float) Tools::ps_round($segmentMinimumPurchase, 2);
            $segmentDiscountType = $matchedCombination['coupon_type'] ?? $defaultCouponType;
            if (!in_array($segmentDiscountType, ['percent', 'amount'])) {
                $segmentDiscountType = $defaultCouponType;
            }
            $segmentMaxWinners = $matchedCombination['max_winners'];
            if ($segmentMaxWinners === null) {
                $segmentMaxWinners = $defaultMaxWinners;
            }
            if ($segmentMaxWinners < 0) {
                $segmentMaxWinners = 0;
            }
            $segmentCouponName = (string) ($matchedCombination['coupon_name'] ?? $defaultCouponName);
            $segmentCouponName = trim($segmentCouponName);
            if ($segmentCouponName === '') {
                $segmentCouponName = $defaultCouponName;
            }
            $segmentCouponPrefix = (string) ($matchedCombination['coupon_prefix'] ?? $defaultCouponPrefix);
            $segmentCouponPrefix = preg_replace('/[^A-Z0-9]/', '', Tools::strtoupper($segmentCouponPrefix));
            if ($segmentCouponPrefix === null) {
                $segmentCouponPrefix = '';
            }
            $segmentCouponValidity = (int) ($matchedCombination['coupon_validity'] ?? $defaultCouponValidity);
            if ($segmentCouponValidity < 1) {
                $segmentCouponValidity = $defaultCouponValidity;
            }
            if ($segmentCouponValidity > 365) {
                $segmentCouponValidity = 365;
            }
            $segmentDiscountValue = isset($matchedCombination['discount']) ? (float) $matchedCombination['discount'] : 0;
            $idCategories = array_map('intval', (array) ($matchedCombination['id_categories'] ?? []));

            if ($isWinning && !$employeeLogged) {
                if ($segmentMaxWinners > 0) {
                    $totalWinners = (int) Db::getInstance()->getValue(
                        'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'everblock_game_play '
                        . 'WHERE id_prettyblocks = ' . (int) $idBlock . ' AND is_winner = 1'
                    );
                    if ($totalWinners >= $segmentMaxWinners) {
                        $isWinning = false;
                        $rewardsDepleted = true;
                    }
                }
            }

            if ($isWinning && (!$requireLogin || $customerLogged || $employeeLogged)) {
                $rewardCode = $segmentCouponPrefix . Tools::strtoupper(Tools::passwdGen(8));
                $voucher = new CartRule();
                foreach (Language::getIDs(false) as $langId) {
                    $voucher->name[(int) $langId] = $segmentCouponName;
                }
                $voucher->code = $rewardCode;
                $voucher->id_customer = $customerLogged ? (int) $this->context->customer->id : 0;
                $voucher->date_from = date('Y-m-d H:i:s');
                $voucher->date_to = date('Y-m-d H:i:s', strtotime('+' . (int) $segmentCouponValidity . ' days'));
                $voucher->quantity = 1;
                $voucher->quantity_per_user = 1;
                if ($segmentDiscountType === 'amount') {
                    $voucher->reduction_amount = $segmentDiscountValue;
                    $voucher->reduction_tax = 1;
                } else {
                    $voucher->reduction_percent = $segmentDiscountValue;
                }
                $voucher->minimum_amount = $segmentMinimumPurchase;
                $voucher->minimum_amount_tax = $segmentMinimumPurchase > 0 ? 1 : 0;
                $voucher->minimum_amount_currency = $currencyId;
                $voucher->minimum_amount_shipping = 0;
                $voucher->active = 1;
                $voucher->add();

                if (!empty($idCategories)) {
                    $validCategoryIds = [];
                    $rootCategoryId = (int) Configuration::get('PS_ROOT_CATEGORY', null, null, $idShop);
                    if (!$rootCategoryId) {
                        $rootCategoryId = 1;
                    }
                    $groupRestrictionsActive = method_exists('Group', 'isFeatureActive') ? Group::isFeatureActive() : false;
                    $customerGroupIds = [];
                    if ($customerLogged && $groupRestrictionsActive && method_exists('Group', 'getCustomerGroups')) {
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

                if (!empty($displayCategoryNames)) {
                    $displayCategoryNames = array_values(array_unique($displayCategoryNames));
                    $categoriesMessage = $this->module->l('Valid for categories:', 'slotmachine') . ' '
                        . implode(', ', $displayCategoryNames);
                }
            } elseif ($isWinning && !$customerLogged && !$employeeLogged) {
                $isWinning = false;
                $resultMessage = $this->module->l('Log in to receive your reward.', 'slotmachine');
            }
        }

        if ($segmentMinimumPurchase > 0) {
            $minimumPurchaseMessage = $this->module->l('Minimum purchase (tax incl.):', 'slotmachine') . ' '
                . Tools::displayPrice($segmentMinimumPurchase, $priceCurrency);
        }

        $resultLabel = $rewardLabel !== '' ? $rewardLabel : implode(' - ', array_map(static function ($symbol) use ($idLang) {
            return $symbol['label'];
        }, $resultSymbols));

        Db::getInstance()->insert('everblock_game_play', [
            'id_prettyblocks' => $idBlock,
            'id_customer' => $idCustomer,
            'ip_address' => pSQL($ipAddress),
            'result' => pSQL($resultLabel),
            'is_winner' => $isWinning ? 1 : 0,
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        if ($rewardsDepleted) {
            $finalMessage = $this->module->l('All rewards have already been distributed.', 'slotmachine');
        } else {
            if ($resultMessage !== '') {
                $finalMessage = $resultMessage;
            } elseif ($isWinning) {
                $finalMessage = $this->module->l('You won:', 'slotmachine') . ' ' . htmlspecialchars($resultLabel, ENT_QUOTES, 'UTF-8');
            } else {
                $finalMessage = $this->module->l('No win this time:', 'slotmachine') . ' ' . htmlspecialchars($resultLabel, ENT_QUOTES, 'UTF-8');
            }
        }

        $this->renderJson([
            'status' => true,
            'symbols' => $resultSymbols,
            'pattern' => $patternKeys,
            'message' => $finalMessage,
            'code' => $rewardCode,
            'categories_message' => $categoriesMessage,
            'minimum_purchase_message' => $minimumPurchaseMessage,
            'is_winner' => $isWinning,
        ]);
    }

    private function renderJson(array $payload)
    {
        die(json_encode($payload));
    }

    private function parseDateTime($value)
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return (int) $timestamp;
    }

    private function resolveConfigValue($value)
    {
        if (is_array($value)) {
            if (array_key_exists('value', $value)) {
                return $this->resolveConfigValue($value['value']);
            }
            if (array_key_exists($this->context->language->id, $value)) {
                return $this->resolveConfigValue($value[$this->context->language->id]);
            }
            $first = reset($value);
            if ($first !== false) {
                return $this->resolveConfigValue($first);
            }

            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    private function resolveNumericValue($value, $min, $max, $default)
    {
        if (is_array($value)) {
            $value = $this->resolveConfigValue($value);
        }
        if (!is_numeric($value)) {
            return $default;
        }
        $value = (float) $value;
        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    private function resolveBoolean($value)
    {
        if (is_array($value)) {
            $value = $this->resolveConfigValue($value);
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function isAdmin()
    {
        return !empty((new Cookie('psAdmin'))->id_employee);
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

    private function normalizeSymbols(array $symbols, $idLang)
    {
        $normalized = [];
        foreach ($symbols as $symbol) {
            $key = isset($symbol['symbol_key']) && $symbol['symbol_key'] !== ''
                ? (string) $symbol['symbol_key']
                : Tools::strtolower(
                    method_exists('Tools', 'str2url')
                        ? Tools::str2url((string) ($symbol['label'] ?? 'symbol'))
                        : Tools::link_rewrite((string) ($symbol['label'] ?? 'symbol'))
                );
            $label = '';
            if (isset($symbol['label'])) {
                if (is_array($symbol['label'])) {
                    $label = (string) ($symbol['label'][$idLang] ?? reset($symbol['label']));
                } else {
                    $label = (string) $symbol['label'];
                }
            }
            $probability = isset($symbol['probability']) ? (float) $symbol['probability'] : 1.0;
            if ($probability <= 0) {
                $probability = 0;
            }
            $imageUrl = '';
            if (!empty($symbol['image'])) {
                if (is_array($symbol['image']) && isset($symbol['image']['url'])) {
                    $imageUrl = (string) $symbol['image']['url'];
                } elseif (is_string($symbol['image'])) {
                    $imageUrl = (string) $symbol['image'];
                }
            }
            $altText = '';
            if (isset($symbol['alt_text'])) {
                if (is_array($symbol['alt_text'])) {
                    $altText = (string) ($symbol['alt_text'][$idLang] ?? reset($symbol['alt_text']));
                } else {
                    $altText = (string) $symbol['alt_text'];
                }
            }
            $description = '';
            if (isset($symbol['text'])) {
                if (is_array($symbol['text'])) {
                    $description = (string) ($symbol['text'][$idLang] ?? reset($symbol['text']));
                } else {
                    $description = (string) $symbol['text'];
                }
            }
            $normalized[] = [
                'symbol_key' => $key,
                'label' => $label,
                'probability' => $probability,
                'image' => $imageUrl,
                'alt_text' => $altText,
                'description' => $description,
            ];
        }

        return array_values(array_filter($normalized, static function ($symbol) {
            return $symbol['probability'] > 0;
        }));
    }

    private function pickWeightedSymbol(array $symbols, $idLang)
    {
        $total = array_reduce($symbols, static function ($carry, $symbol) {
            return $carry + (float) $symbol['probability'];
        }, 0.0);
        if ($total <= 0) {
            return reset($symbols);
        }
        $rand = (float) mt_rand() / (float) mt_getrandmax() * $total;
        $acc = 0.0;
        foreach ($symbols as $symbol) {
            $acc += (float) $symbol['probability'];
            if ($rand <= $acc) {
                return $symbol;
            }
        }

        return end($symbols);
    }

    private function parseWinningCombinations($raw)
    {
        if (is_array($raw) && array_key_exists('value', $raw)) {
            $raw = $raw['value'];
        }
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                $raw = [];
            }
        }
        if (!is_array($raw)) {
            return [];
        }
        $normalized = [];
        foreach ($raw as $combination) {
            if (!is_array($combination)) {
                continue;
            }
            $pattern = [];
            if (isset($combination['pattern'])) {
                $rawPattern = $combination['pattern'];
                if (is_string($rawPattern)) {
                    $pattern = array_map('trim', explode(',', $rawPattern));
                } elseif (is_array($rawPattern)) {
                    $pattern = array_map('trim', $rawPattern);
                }
            }
            if (empty($pattern)) {
                continue;
            }
            $label = '';
            if (isset($combination['label'])) {
                if (is_array($combination['label'])) {
                    $label = (string) ($combination['label'][$this->context->language->id] ?? reset($combination['label']));
                } else {
                    $label = (string) $combination['label'];
                }
            }
            $message = '';
            if (isset($combination['message'])) {
                if (is_array($combination['message'])) {
                    $message = (string) ($combination['message'][$this->context->language->id] ?? reset($combination['message']));
                } else {
                    $message = (string) $combination['message'];
                }
            }
            $normalized[] = [
                'pattern' => $pattern,
                'label' => $label,
                'message' => $message,
                'isWinning' => $this->resolveBoolean($combination['isWinning'] ?? false),
                'coupon_name' => $this->resolveConfigValue($combination['coupon_name'] ?? ''),
                'coupon_prefix' => $this->resolveConfigValue($combination['coupon_prefix'] ?? ''),
                'coupon_validity' => $this->resolveNumericValue($combination['coupon_validity'] ?? null, 1, 365, null),
                'coupon_type' => $this->resolveConfigValue($combination['coupon_type'] ?? ''),
                'discount' => isset($combination['discount']) ? (float) $combination['discount'] : null,
                'minimum_purchase' => isset($combination['minimum_purchase']) ? (float) $combination['minimum_purchase'] : 0,
                'id_categories' => isset($combination['id_categories']) ? (array) $combination['id_categories'] : [],
                'max_winners' => isset($combination['max_winners']) ? (int) $combination['max_winners'] : null,
            ];
        }

        return $normalized;
    }

    private function findMatchingCombination(array $combinations, array $patternKeys, $idLang)
    {
        foreach ($combinations as $combination) {
            $pattern = array_map(static function ($item) {
                return Tools::strtolower(trim($item));
            }, $combination['pattern']);
            $keys = array_map(static function ($item) {
                return Tools::strtolower(trim($item));
            }, $patternKeys);
            if ($pattern === $keys) {
                return $combination;
            }
        }

        return null;
    }
}
