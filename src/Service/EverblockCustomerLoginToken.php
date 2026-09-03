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
 * The signature covers the target customer, the requesting employee and an expiry, so a link:
 *  - only works for the customer it was issued for;
 *  - carries the identity of the employee the back office vouched for.
 *
 * It no longer dies after a delay: see the TTL constant below.
 *
 * The link can only be obtained through EverblockAdminController::customerLoginAction(), which
 * runs inside the PrestaShop admin firewall: obtaining one therefore requires an authenticated
 * employee holding the AdminCustomers ACL, with the native CSRF token.
 */
class EverblockCustomerLoginToken
{
    /** Domain separator of this token family. */
    const PURPOSE = 'everlogin';

    /** Configuration key holding the module HMAC secret (shop independent). */
    const CONFIG_SECRET = EverblockSignedToken::CONFIG_SECRET;

    /**
     * Lifetime of a generated link, in seconds. 0 disables expiry entirely.
     *
     * Set to 0 on purpose: the link is minted at click time by a back office route PrestaShop
     * itself has authenticated, and the feature is used often enough that an expiring link is
     * felt as a malfunction rather than as a safeguard.
     *
     * What still gates the link: the HMAC signature covers the customer, the employee and the
     * expiry, so none of the three can be tampered with; and everlogin.php re-checks, on every
     * hit, that the employee still exists, is still active, still holds the AdminCustomers or
     * AdminOrders read permission and still has access to the customer shop. Deactivating the
     * employee or removing their permission therefore revokes every link they ever minted.
     *
     * What is no longer gated: a URL recovered from a browser history, a server access log, a
     * proxy log or a Referer header opens that customer account for as long as the employee
     * stays active. Putting a positive number back here restores expiry with no other change.
     */
    const TTL = 0;

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
        // TTL 0 means "no expiry". The value still travels inside the signature, so it cannot be
        // tampered with, and flipping the constant back is enough to re-enable expiring links.
        $expires = self::TTL > 0 ? $now + self::TTL : 0;
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
     * True when the link may still be used.
     *
     * With TTL 0 every link is usable, including those minted while expiry was still enforced.
     */
    public static function isFresh(int $expires, ?int $now = null): bool
    {
        if (self::TTL <= 0) {
            return true;
        }

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
        return EverblockSignedToken::sign(
            self::PURPOSE,
            [
                'id_customer' => $idCustomer,
                'id_employee' => $idEmployee,
            ],
            $expires,
            $nonce
        );
    }
}
