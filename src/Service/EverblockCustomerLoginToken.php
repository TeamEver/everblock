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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Signed, scoped and short lived token for the "log in as this customer" feature.
 *
 * The historical token was Tools::hash('everblock/everlogin'): shop wide, permanent, and
 * unrelated to the customer being impersonated — leaking a single URL was enough to open any
 * customer account by changing id_ever_customer.
 *
 * The signature now covers the target customer, the requesting employee and an expiry, and the
 * HMAC key embeds the employee back office session token, so a link:
 *  - only works for the customer it was issued for;
 *  - only works for the employee who requested it;
 *  - dies when that employee logs out of the back office;
 *  - dies after TTL seconds.
 *
 * The front controller additionally requires a live back office session, see
 * EverblockBackOfficeGuard.
 */
class EverblockCustomerLoginToken
{
    /** Domain separator of this token family. */
    const PURPOSE = 'everlogin';

    /** Configuration key holding the module HMAC secret (shop independent). */
    const CONFIG_SECRET = EverblockSignedToken::CONFIG_SECRET;

    /** Lifetime of a generated link, in seconds. */
    const TTL = 900;

    /** Tolerance on the expiry upper bound, to absorb clock drift. */
    const CLOCK_SKEW = 60;

    const PARAM_CUSTOMER = 'id_ever_customer';
    const PARAM_EMPLOYEE = 'ever_id_employee';
    const PARAM_EXPIRES = 'ever_expires';
    const PARAM_NONCE = 'ever_nonce';
    const PARAM_TOKEN = 'evertoken';

    /**
     * Query parameters of a fresh "log in as customer" link.
     *
     * @return array<string, int|string>
     */
    public static function buildLinkParameters(int $idCustomer, int $idEmployee, ?int $now = null): array
    {
        $now = $now === null ? time() : (int) $now;
        $expires = $now + self::TTL;
        $nonce = self::generateNonce();

        return [
            self::PARAM_CUSTOMER => $idCustomer,
            self::PARAM_EMPLOYEE => $idEmployee,
            self::PARAM_EXPIRES => $expires,
            self::PARAM_NONCE => $nonce,
            self::PARAM_TOKEN => self::sign($idCustomer, $idEmployee, $expires, $nonce),
        ];
    }

    /**
     * Constant time verification of a provided signature.
     */
    public static function verify(
        int $idCustomer,
        int $idEmployee,
        int $expires,
        string $nonce,
        string $providedToken
    ): bool {
        if ($providedToken === '' || !self::isValidNonce($nonce)) {
            return false;
        }

        $expected = self::sign($idCustomer, $idEmployee, $expires, $nonce);

        return $expected !== '' && hash_equals($expected, $providedToken);
    }

    /**
     * True when the expiry is neither in the past nor absurdly far in the future.
     */
    public static function isFresh(int $expires, ?int $now = null): bool
    {
        return EverblockSignedToken::isFresh($expires, self::TTL, $now);
    }

    public static function isValidNonce(string $nonce): bool
    {
        return EverblockSignedToken::isValidNonce($nonce);
    }

    public static function generateNonce(): string
    {
        return EverblockSignedToken::generateNonce();
    }

    private static function sign(int $idCustomer, int $idEmployee, int $expires, string $nonce): string
    {
        // Delegates to the shared primitive so everlogin and the block preview cannot drift apart.
        // The employee back office session token is part of the HMAC key, never of the URL.
        return EverblockSignedToken::sign(
            self::PURPOSE,
            [
                'id_customer' => $idCustomer,
                'id_employee' => $idEmployee,
            ],
            $expires,
            $nonce,
            EverblockBackOfficeGuard::getEmployeeSessionToken()
        );
    }
}
