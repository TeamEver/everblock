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
use Exception;
use PrestaShopLogger;
use Tools;

class RecaptchaValidator
{
    public const CONTEXT_EVERBLOCK_CONTACT = 'everblock_contact';

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var array<string, array<string, string>>
     */
    private const CONTEXT_CONFIGURATION = [
        self::CONTEXT_EVERBLOCK_CONTACT => [
            'enabled_key' => 'EVERBLOCK_RECAPTCHA_PROTECT_CONTACT',
            'score_key' => 'EVERBLOCK_RECAPTCHA_MIN_SCORE_CONTACT',
        ],
    ];

    public static function isEnabled(): bool
    {
        return (bool) Configuration::get('EVERBLOCK_RECAPTCHA_ENABLED')
            && (bool) self::getSecretKey();
    }

    public static function getSiteKey(): string
    {
        return trim((string) Configuration::get('EVERBLOCK_RECAPTCHA_SITE_KEY'));
    }

    public static function getSecretKey(): string
    {
        return trim((string) Configuration::get('EVERBLOCK_RECAPTCHA_SECRET_KEY'));
    }

    public static function shouldProtectContext(string $context): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        if (!isset(self::CONTEXT_CONFIGURATION[$context])) {
            return false;
        }

        $enabledKey = self::CONTEXT_CONFIGURATION[$context]['enabled_key'];

        return (bool) Configuration::get($enabledKey);
    }

    public static function getMinScore(string $context): float
    {
        if (!isset(self::CONTEXT_CONFIGURATION[$context])) {
            return 0.0;
        }

        $value = Configuration::get(self::CONTEXT_CONFIGURATION[$context]['score_key']);

        return (float) str_replace(',', '.', (string) $value ?: '0');
    }

    /**
     * @param array<string, mixed> $additional
     */
    public static function logFailure(string $context, array $result, array $additional = []): void
    {
        $score = isset($result['score']) ? (float) $result['score'] : null;
        $action = isset($result['action']) ? (string) $result['action'] : '';
        $errors = [];
        if (!empty($result['errorCodes']) && is_array($result['errorCodes'])) {
            $errors = $result['errorCodes'];
        }

        $payload = array_merge([
            'context' => $context,
            'score' => $score !== null ? number_format($score, 3) : 'n/a',
            'action' => $action,
            'errors' => implode(',', $errors),
        ], $additional);

        $message = sprintf(
            'Everblock reCAPTCHA rejected context "%s" (score: %s, action: %s, errors: %s)%s',
            $payload['context'],
            $payload['score'],
            $payload['action'],
            $payload['errors'] ?: 'none',
            isset($payload['ip']) ? ' - IP: ' . $payload['ip'] : ''
        );

        PrestaShopLogger::addLog($message, 2);
    }

    /**
     * @return array<string, mixed>
     */
    public static function validateRequest(string $context, ?string $token = null, ?string $clientIp = null): array
    {
        if (!self::shouldProtectContext($context)) {
            return [
                'success' => true,
                'score' => null,
                'action' => $context,
                'errorCodes' => [],
                'raw' => [],
            ];
        }

        $token = $token ?? Tools::getValue('g-recaptcha-response');
        $clientIp = $clientIp ?? Tools::getRemoteAddr();
        $minScore = self::getMinScore($context);

        return self::validateToken($token, $context, $minScore, $clientIp);
    }

    /**
     * @return array<string, mixed>
     */
    public static function validateToken(?string $token, string $expectedAction, float $minScore, ?string $clientIp = null): array
    {
        $response = [
            'success' => false,
            'score' => null,
            'action' => null,
            'errorCodes' => [],
            'raw' => [],
        ];

        $secret = self::getSecretKey();
        if ($secret === '') {
            $response['errorCodes'][] = 'missing-secret';

            return $response;
        }

        if ($token === null || trim($token) === '') {
            $response['errorCodes'][] = 'missing-input-response';

            return $response;
        }

        $payload = http_build_query([
            'secret' => $secret,
            'response' => trim($token),
            'remoteip' => $clientIp,
        ]);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 10,
            ],
        ];

        try {
            $raw = Tools::file_get_contents(self::VERIFY_URL, false, stream_context_create($options));
        } catch (Exception $exception) {
            $response['errorCodes'][] = 'connection-failed';
            $response['raw'] = ['exception' => $exception->getMessage()];

            return $response;
        }

        if ($raw === false) {
            $response['errorCodes'][] = 'connection-failed';

            return $response;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $response['errorCodes'][] = 'invalid-json';

            return $response;
        }

        $response['raw'] = $data;
        $response['action'] = isset($data['action']) ? (string) $data['action'] : null;
        $response['score'] = isset($data['score']) ? (float) $data['score'] : null;
        $response['errorCodes'] = isset($data['error-codes']) && is_array($data['error-codes'])
            ? $data['error-codes']
            : [];

        if (empty($data['success'])) {
            return $response;
        }

        if ($expectedAction !== '' && $response['action'] !== null && $response['action'] !== $expectedAction) {
            $response['errorCodes'][] = 'unexpected-action';

            return $response;
        }

        if ($response['score'] !== null && $response['score'] < $minScore) {
            $response['errorCodes'][] = 'score-too-low';

            return $response;
        }

        $response['success'] = true;

        return $response;
    }

    public static function getErrorMessage(int $idLang): string
    {
        $message = Configuration::get('EVERBLOCK_RECAPTCHA_ERROR_MESSAGE', $idLang);

        if (is_string($message) && trim($message) !== '') {
            return $message;
        }

        $default = Configuration::get('EVERBLOCK_RECAPTCHA_ERROR_MESSAGE', (int) Configuration::get('PS_LANG_DEFAULT'));

        if (is_string($default) && trim($default) !== '') {
            return $default;
        }

        return 'The anti-spam verification failed. Please try again.';
    }

    /**
     * @return array<string, mixed>
     */
    public static function getFrontendConfiguration(): array
    {
        $config = [
            'enabled' => self::isEnabled(),
            'siteKey' => self::getSiteKey(),
            'contexts' => [],
        ];

        foreach (self::CONTEXT_CONFIGURATION as $context => $settings) {
            $config['contexts'][$context] = [
                'enabled' => (bool) Configuration::get($settings['enabled_key']),
            ];
        }

        return $config;
    }
}
