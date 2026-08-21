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
 * Signed token for the block preview link.
 *
 * Replaces Tools::getAdminTokenLite('AdminEverBlock'), which could not work reliably here: that
 * helper hashes the tab id together with Context::getContext()->employee->id, so its value depends
 * on runtime context that is not identical between the Symfony admin request that builds the link
 * and the front office request that verifies it. Both sides now compute the same HMAC over an
 * explicit payload.
 *
 * The signature covers the block, the shop, the language and the employee the back office
 * vouched for, so none of those parameters can be tampered with, and it guarantees the URL was
 * issued by EverblockAdminController::previewRedirectAction() — a route that runs inside the
 * PrestaShop admin firewall, with the Ever Block ACL and the native CSRF token.
 */
final class EverblockPreviewToken
{
    /** Domain separator: a preview token can never be replayed as an everlogin token. */
    const PURPOSE = 'preview';

    /**
     * Lifetime, in seconds. Short on purpose: the link is minted when the employee clicks, by a
     * back office route that PrestaShop itself has authenticated.
     */
    const TTL = 120;

    const PARAM_BLOCK = 'id_everblock';
    const PARAM_SHOP = 'id_shop';
    const PARAM_LANG = 'id_lang';
    const PARAM_EMPLOYEE = 'ever_id_employee';
    const PARAM_EXPIRES = 'ever_expires';
    const PARAM_NONCE = 'ever_nonce';
    const PARAM_TOKEN = 'token';

    /**
     * Query parameters of a fresh preview link.
     *
     * @return array<string, int|string>
     */
    public static function buildLinkParameters(int $idBlock, int $idShop, int $idLang, int $idEmployee, ?int $now = null): array
    {
        $now = $now === null ? time() : (int) $now;
        $expires = $now + self::TTL;
        $nonce = EverblockSignedToken::generateNonce();

        return [
            self::PARAM_BLOCK => $idBlock,
            self::PARAM_LANG => $idLang,
            self::PARAM_SHOP => $idShop,
            self::PARAM_EMPLOYEE => $idEmployee,
            self::PARAM_EXPIRES => $expires,
            self::PARAM_NONCE => $nonce,
            self::PARAM_TOKEN => self::sign($idBlock, $idShop, $idLang, $idEmployee, $expires, $nonce),
        ];
    }

    /**
     * Constant time verification of a provided signature.
     */
    public static function verify(
        int $idBlock,
        int $idShop,
        int $idLang,
        int $idEmployee,
        int $expires,
        string $nonce,
        string $providedToken
    ): bool {
        return EverblockSignedToken::verify(
            self::PURPOSE,
            self::payload($idBlock, $idShop, $idLang, $idEmployee),
            $expires,
            $nonce,
            $providedToken
        );
    }

    public static function isFresh(int $expires, ?int $now = null): bool
    {
        return EverblockSignedToken::isFresh($expires, self::TTL, $now);
    }

    private static function sign(int $idBlock, int $idShop, int $idLang, int $idEmployee, int $expires, string $nonce): string
    {
        return EverblockSignedToken::sign(
            self::PURPOSE,
            self::payload($idBlock, $idShop, $idLang, $idEmployee),
            $expires,
            $nonce
        );
    }

    /**
     * @return array<string, int>
     */
    private static function payload(int $idBlock, int $idShop, int $idLang, int $idEmployee): array
    {
        return [
            'id_everblock' => $idBlock,
            'id_shop' => $idShop,
            'id_lang' => $idLang,
            'id_employee' => $idEmployee,
        ];
    }
}
