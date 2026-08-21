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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Generic signed token primitive shared by every back office link the module has to hand over
 * to a front office controller.
 *
 * Deliberately does NOT rely on Tools::getAdminTokenLite(): that helper mixes the tab id and
 * Context::getContext()->employee->id, so its value depends on runtime context that differs
 * between the Symfony admin request that generates a link and the front office request that
 * verifies it. Here both sides compute the same HMAC over an explicit payload.
 *
 * Domain separation is provided by the $purpose argument: a token minted for "preview" can never
 * be replayed as an "everlogin" token, even though both use the same server secret.
 */
final class EverblockSignedToken
{
    /**
     * Server secret. The configuration key name is historical (it was introduced for the
     * everlogin feature) and is kept as is so existing values stay valid.
     */
    const CONFIG_SECRET = 'EVERBLOCK_EVERLOGIN_SECRET';

    /** Tolerance on the expiry upper bound, to absorb clock drift. */
    const CLOCK_SKEW = 60;

    /**
     * Signs an explicit payload.
     *
     * @param string $purpose Domain separator, e.g. 'everlogin' or 'preview'
     * @param array<int|string, int|string> $payload Values covered by the signature
     * @param string $extraKeyMaterial Appended to the HMAC key, never to the URL
     *
     * @return string Empty string when no secret or no nonce could be produced
     */
    public static function sign(string $purpose, array $payload, int $expires, string $nonce, string $extraKeyMaterial = ''): string
    {
        $secret = self::getSecret();
        if ($secret === '' || $purpose === '' || $nonce === '') {
            return '';
        }

        $parts = [$purpose];
        foreach ($payload as $key => $value) {
            $parts[] = $key . '=' . (is_int($value) ? (string) $value : (string) $value);
        }
        $parts[] = (string) $expires;
        $parts[] = $nonce;

        return hash_hmac('sha256', implode('|', $parts), $secret . '|' . $extraKeyMaterial);
    }

    /**
     * Constant time verification.
     *
     * @param array<int|string, int|string> $payload
     */
    public static function verify(
        string $purpose,
        array $payload,
        int $expires,
        string $nonce,
        string $providedToken,
        string $extraKeyMaterial = ''
    ): bool {
        if ($providedToken === '' || !self::isValidNonce($nonce)) {
            return false;
        }

        $expected = self::sign($purpose, $payload, $expires, $nonce, $extraKeyMaterial);

        return $expected !== '' && hash_equals($expected, $providedToken);
    }

    /**
     * True when the expiry is neither in the past nor further away than the allowed lifetime.
     */
    public static function isFresh(int $expires, int $ttl, ?int $now = null): bool
    {
        $now = $now === null ? time() : (int) $now;

        return $expires > $now && $expires <= $now + $ttl + self::CLOCK_SKEW;
    }

    public static function isValidNonce(string $nonce): bool
    {
        $length = strlen($nonce);

        return $length >= 16 && $length <= 64 && ctype_xdigit($nonce);
    }

    public static function generateNonce(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable $exception) {
            // random_bytes only fails when no CSPRNG is available; refuse to emit a weak nonce.
            return '';
        }
    }

    /**
     * Dedicated random secret, created on first use so existing shops are covered without
     * waiting for an upgrade script.
     */
    public static function getSecret(): string
    {
        $secret = (string) Configuration::getGlobalValue(self::CONFIG_SECRET);

        if (strlen($secret) >= 32) {
            return $secret;
        }

        try {
            $secret = bin2hex(random_bytes(32));
        } catch (\Throwable $exception) {
            return '';
        }

        Configuration::updateGlobalValue(self::CONFIG_SECRET, $secret);

        return $secret;
    }
}
