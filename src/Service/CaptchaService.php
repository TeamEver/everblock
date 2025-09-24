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

namespace Everblock\Tools\Service;

use Configuration;
use Context;
use Exception;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CaptchaService
{
    private const TOKEN_SEPARATOR = '.';
    private const TOKEN_TTL = 900; // 15 minutes

    /**
     * Render the captcha block for a contact form.
     */
    public static function renderCaptchaField(Context $context, \Everblock $module): string
    {
        try {
            $challenge = static::createChallengePayload();
        } catch (Exception $e) {
            return '';
        }

        $question = sprintf(
            $module->l('What is %d + %d?', 'captchaservice'),
            $challenge['a'],
            $challenge['b']
        );
        $fieldId = 'evercaptcha_' . $challenge['nonce'];
        $token = static::buildToken($challenge);

        $context->smarty->assign([
            'captcha' => [
                'label' => $module->l('Security question', 'captchaservice'),
                'helper' => $module->l('Please answer the security question to validate your request.', 'captchaservice'),
                'question' => $question,
                'field_id' => $fieldId,
                'token' => $token,
            ],
        ]);

        $templatePath = \EverblockTools::getTemplatePath('hook/contact_captcha.tpl', $module);

        return $context->smarty->fetch($templatePath);
    }

    /**
     * Validate the captcha answer received from the form submission.
     */
    public static function validateResponse(?string $token, ?string $answer): bool
    {
        if (!$token || !$answer) {
            return false;
        }

        $payload = static::decodeToken($token);
        if (empty($payload)) {
            return false;
        }

        if (!isset($payload['a'], $payload['b'], $payload['ts']) || !is_numeric($payload['a']) || !is_numeric($payload['b'])) {
            return false;
        }

        if (!is_int($payload['ts']) || (time() - $payload['ts']) > self::TOKEN_TTL) {
            return false;
        }

        $expected = (int) ($payload['a'] + $payload['b']);
        $cleanAnswer = trim((string) $answer);

        if ($cleanAnswer === '' || !ctype_digit($cleanAnswer)) {
            return false;
        }

        return (int) $cleanAnswer === $expected;
    }

    private static function createChallengePayload(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $nonce = bin2hex(random_bytes(6));

        return [
            'a' => $a,
            'b' => $b,
            'nonce' => $nonce,
            'ts' => time(),
        ];
    }

    private static function buildToken(array $payload): string
    {
        $json = json_encode($payload);
        if ($json === false) {
            throw new Exception('Unable to encode captcha payload.');
        }

        $signature = hash_hmac('sha256', $json, static::getSecret());

        return base64_encode($json) . self::TOKEN_SEPARATOR . $signature;
    }

    private static function decodeToken(string $token): array
    {
        $parts = explode(self::TOKEN_SEPARATOR, $token, 2);
        if (count($parts) !== 2) {
            return [];
        }

        [$encoded, $signature] = $parts;
        $json = base64_decode($encoded, true);
        if ($json === false) {
            return [];
        }

        $expectedSignature = hash_hmac('sha256', $json, static::getSecret());
        if (!hash_equals($expectedSignature, $signature)) {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        $data['ts'] = isset($data['ts']) ? (int) $data['ts'] : 0;

        return $data;
    }

    private static function getSecret(): string
    {
        if (defined('_COOKIE_KEY_')) {
            return (string) _COOKIE_KEY_;
        }

        $fallback = Configuration::get('PS_SHOP_EMAIL');

        return Tools::hash($fallback ?: 'everblock');
    }
}
