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

class EverblockAdventModuleFrontController extends ModuleFrontController
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
                'message' => $this->module->l('Invalid token', 'advent'),
            ]);
        }

        if (!$this->context->customer->isLogged()) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('You must be logged in to open this window.', 'advent'),
            ]);
        }

        $idBlock = (int) Tools::getValue('id_block');
        $requestedDay = (int) Tools::getValue('day');
        if ($idBlock <= 0 || $requestedDay < 1 || $requestedDay > 24) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('Invalid calendar configuration.', 'advent'),
            ]);
        }

        $idCustomer = (int) $this->context->customer->id;
        $idShop = (int) $this->context->shop->id;
        $idLang = (int) $this->context->language->id;

        $row = Db::getInstance()->getRow(
            'SELECT config, state FROM ' . _DB_PREFIX_ . 'prettyblocks WHERE id_prettyblocks = ' . (int) $idBlock
            . ' AND id_shop = ' . (int) $idShop . ' AND id_lang = ' . (int) $idLang
        );

        if (!$row) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('Configuration not found', 'advent'),
            ]);
        }

        $settings = json_decode($row['config'], true);
        if (!is_array($settings)) {
            $settings = [];
        }

        $rawWindows = json_decode($row['state'], true);
        if (!is_array($rawWindows)) {
            $rawWindows = [];
        }

        $calendarSettings = $this->resolveCalendarSettings($settings);
        $window = $this->findWindowForDay($rawWindows, $requestedDay, $idLang);

        if (!$window) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('This window is not configured yet.', 'advent'),
            ]);
        }

        $sanitizedWindow = $this->sanitizeWindowPayload($window);

        $today = $this->getNowDate();
        $startDate = $calendarSettings['start_date'];
        if (!$startDate) {
            $startDate = new DateTime('first day of December ' . (int) $today->format('Y'));
        }

        $windowDate = clone $startDate;
        $windowDate->modify('+' . ($requestedDay - 1) . ' days');

        if ($today < $windowDate) {
            $this->renderJson([
                'status' => false,
                'message' => sprintf(
                    $this->module->l('This window will open on %s.', 'advent'),
                    Tools::displayDate($windowDate->format('Y-m-d'), null, false)
                ),
                'reason' => 'too_early',
                'available_on' => $windowDate->format('Y-m-d'),
            ]);
        }

        if ($calendarSettings['restrict_to_current_day'] && $today->format('Y-m-d') !== $windowDate->format('Y-m-d')) {
            $this->renderJson([
                'status' => false,
                'message' => $this->module->l('You can only open today\'s window.', 'advent'),
                'reason' => 'not_today',
                'available_on' => $windowDate->format('Y-m-d'),
            ]);
        }

        $ipAddress = Tools::getRemoteAddr();
        if (!is_string($ipAddress)) {
            $ipAddress = '';
        } else {
            $ipAddress = Tools::substr($ipAddress, 0, 45);
        }

        if (!$this->isAdmin()) {
            $alreadyCustomer = (bool) Db::getInstance()->getValue(
                'SELECT id_everblock_game_play FROM ' . _DB_PREFIX_ . 'everblock_game_play WHERE id_prettyblocks = ' . (int) $idBlock
                . ' AND id_customer = ' . (int) $idCustomer
                . " AND result = '" . pSQL('day-' . $requestedDay) . "'"
            );

            $alreadyIp = false;
            if ($ipAddress !== '') {
                $alreadyIp = (bool) Db::getInstance()->getValue(
                    'SELECT id_everblock_game_play FROM ' . _DB_PREFIX_ . 'everblock_game_play WHERE id_prettyblocks = ' . (int) $idBlock
                    . " AND ip_address = '" . pSQL($ipAddress) . "'"
                    . " AND result = '" . pSQL('day-' . $requestedDay) . "'"
                );
            }

            if ($alreadyCustomer || $alreadyIp) {
                $this->renderJson([
                    'status' => 'already_opened',
                    'day' => $requestedDay,
                    'message' => $this->module->l('You have already opened this window.', 'advent'),
                    'reason' => 'already_opened',
                    'window' => $sanitizedWindow,
                ]);
            }
        }

        Db::getInstance()->insert('everblock_game_play', [
            'id_prettyblocks' => (int) $idBlock,
            'id_customer' => (int) $idCustomer,
            'ip_address' => pSQL($ipAddress),
            'result' => pSQL('day-' . $requestedDay),
            'is_winner' => 0,
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        $this->renderJson([
            'status' => true,
            'day' => $requestedDay,
            'message' => $this->module->l('Window unlocked!', 'advent'),
            'window' => $sanitizedWindow,
        ]);
    }

    private function renderJson(array $payload)
    {
        die(json_encode($payload));
    }

    private function resolveCalendarSettings(array $settings)
    {
        $restrict = true;
        if (array_key_exists('restrict_to_current_day', $settings)) {
            $restrictValue = $this->resolveConfigValue($settings['restrict_to_current_day']);
            if ($restrictValue !== null) {
                $restrict = (bool) $restrictValue;
            }
        }

        $startDate = null;
        if (array_key_exists('start_date', $settings)) {
            $startRaw = $this->resolveConfigValue($settings['start_date']);
            if (is_string($startRaw) && trim($startRaw) !== '') {
                $parsed = $this->createDateTime($startRaw);
                if ($parsed) {
                    $startDate = $parsed;
                }
            }
        }

        return [
            'restrict_to_current_day' => $restrict,
            'start_date' => $startDate,
        ];
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

            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }

        return null;
    }

    private function findWindowForDay(array $windows, $day, $idLang)
    {
        foreach ($windows as $window) {
            if (!is_array($window)) {
                continue;
            }
            $normalized = $this->normalizeWindow($window, $idLang);
            if ((int) ($normalized['day_number'] ?? 0) === (int) $day) {
                return $normalized;
            }
        }

        return null;
    }

    private function normalizeWindow(array $window, $idLang)
    {
        $normalized = [];
        foreach ($window as $key => $value) {
            $normalized[$key] = $this->normalizeWindowValue($value, $idLang);
        }

        return $normalized;
    }

    private function normalizeWindowValue($value, $idLang)
    {
        if (is_array($value)) {
            if (array_key_exists('value', $value)) {
                return $this->normalizeWindowValue($value['value'], $idLang);
            }
            if (array_key_exists($idLang, $value)) {
                return $this->normalizeWindowValue($value[$idLang], $idLang);
            }
            foreach ($value as $subKey => $subValue) {
                $value[$subKey] = $this->normalizeWindowValue($subValue, $idLang);
            }
        }

        return $value;
    }

    private function createDateTime($value)
    {
        try {
            return new DateTime($value);
        } catch (Exception $e) {
            return null;
        }
    }

    private function getNowDate()
    {
        try {
            $timezone = new DateTimeZone(@date_default_timezone_get());
        } catch (Exception $e) {
            $timezone = null;
        }

        if ($timezone) {
            return new DateTime('now', $timezone);
        }

        return new DateTime();
    }

    private function isAdmin()
    {
        return !empty((new Cookie('psAdmin'))->id_employee);
    }

    private function sanitizeWindowPayload(array $window)
    {
        $payload = [];

        $payload['day_number'] = (int) ($window['day_number'] ?? 0);

        $title = $this->sanitizePlainText($window['window_title'] ?? '');
        if ($title !== '') {
            $payload['window_title'] = $title;
        }

        $subtitle = $this->sanitizePlainText($window['window_subtitle'] ?? '');
        if ($subtitle !== '') {
            $payload['window_subtitle'] = $subtitle;
        }

        $content = $this->sanitizeHtmlContent($window['content'] ?? '');
        if ($content !== '') {
            $payload['content'] = $content;
        }

        if (!empty($window['promo_code'])) {
            $promo = $this->sanitizePlainText($window['promo_code']);
            if ($promo !== '') {
                $payload['promo_code'] = $promo;
            }
        }

        if (!empty($window['button_label'])) {
            $label = $this->sanitizePlainText($window['button_label']);
            if ($label !== '') {
                $payload['button_label'] = $label;
            }
        }

        if (!empty($window['button_url'])) {
            $url = $this->sanitizeUrl($window['button_url']);
            if ($url !== null) {
                $payload['button_url'] = $url;
            }
        }

        if (!empty($window['image']) && is_array($window['image'])) {
            $imageUrl = $this->sanitizeUrl($window['image']['url'] ?? '');
            if ($imageUrl !== null) {
                $payload['image'] = ['url' => $imageUrl];
            }
        } elseif (!empty($window['image']) && is_string($window['image'])) {
            $imageUrl = $this->sanitizeUrl($window['image']);
            if ($imageUrl !== null) {
                $payload['image'] = ['url' => $imageUrl];
            }
        }

        $background = $this->sanitizeColor($window['background_color'] ?? '');
        if ($background !== null) {
            $payload['background_color'] = $background;
        }

        $textColor = $this->sanitizeColor($window['text_color'] ?? '');
        if ($textColor !== null) {
            $payload['text_color'] = $textColor;
        }

        return $payload;
    }

    private function sanitizePlainText($value)
    {
        if (!is_scalar($value)) {
            return '';
        }

        $clean = trim((string) $value);
        if ($clean === '') {
            return '';
        }

        $clean = strip_tags($clean);

        return Tools::substr($clean, 0, 512);
    }

    private function sanitizeHtmlContent($value)
    {
        if (!is_string($value)) {
            return '';
        }

        $content = trim($value);
        if ($content === '') {
            return '';
        }

        if (method_exists('Tools', 'purifyHTML')) {
            return Tools::purifyHTML($content, true);
        }

        return strip_tags($content, '<p><br><strong><em><ul><ol><li><span><div><a>');
    }

    protected function sanitizeUrl($value)
    {
        if (!is_scalar($value)) {
            return null;
        }

        $url = trim((string) $value);
        if ($url === '') {
            return null;
        }

        if (stripos($url, 'javascript:') === 0) {
            return null;
        }

        if (preg_match('/^(mailto|tel):[^\s]+$/i', $url)) {
            return $url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (strpos($url, '//') === 0 && filter_var('https:' . $url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (in_array($url[0], ['/', '#', '?'], true)) {
            return $url;
        }

        return null;
    }

    private function sanitizeColor($value)
    {
        if (!is_scalar($value)) {
            return null;
        }

        $color = trim((string) $value);
        if ($color === '') {
            return null;
        }

        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color)) {
            return strtolower($color);
        }

        return null;
    }
}
