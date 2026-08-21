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
 * Deliberately NOT bound to the employee: the preview controller already requires a live back
 * office session (EverblockBackOfficeGuard), read access on the Ever Block tab, and authorisation
 * on the requested shop. Binding the signature to an employee id would only reintroduce the
 * context dependency this class exists to remove.
 *
 * The signature covers the block, the shop and the language, so those parameters cannot be
 * tampered with, and it guarantees the URL was issued by the back office.
 */
final class EverblockPreviewToken
{
    /** Domain separator: a preview token can never be replayed as an everlogin token. */
    const PURPOSE = 'preview';

    /**
     * Lifetime, in seconds. Generous on purpose: a back office list can legitimately stay open
     * for hours, and the real gate is the live employee session, not this expiry.
     */
    const TTL = 86400;

    const PARAM_BLOCK = 'id_everblock';
    const PARAM_SHOP = 'id_shop';
    const PARAM_LANG = 'id_lang';
    const PARAM_EXPIRES = 'ever_expires';
    const PARAM_NONCE = 'ever_nonce';
    const PARAM_TOKEN = 'token';

    /**
     * Query parameters of a fresh preview link.
     *
     * @return array<string, int|string>
     */
    public static function buildLinkParameters(int $idBlock, int $idShop, int $idLang, ?int $now = null): array
    {
        $now = $now === null ? time() : (int) $now;
        $expires = $now + self::TTL;
        $nonce = EverblockSignedToken::generateNonce();

        return [
            self::PARAM_BLOCK => $idBlock,
            self::PARAM_LANG => $idLang,
            self::PARAM_SHOP => $idShop,
            self::PARAM_EXPIRES => $expires,
            self::PARAM_NONCE => $nonce,
            self::PARAM_TOKEN => self::sign($idBlock, $idShop, $idLang, $expires, $nonce),
        ];
    }

    /**
     * Constant time verification of a provided signature.
     */
    public static function verify(
        int $idBlock,
        int $idShop,
        int $idLang,
        int $expires,
        string $nonce,
        string $providedToken
    ): bool {
        return EverblockSignedToken::verify(
            self::PURPOSE,
            self::payload($idBlock, $idShop, $idLang),
            $expires,
            $nonce,
            $providedToken
        );
    }

    public static function isFresh(int $expires, ?int $now = null): bool
    {
        return EverblockSignedToken::isFresh($expires, self::TTL, $now);
    }

    private static function sign(int $idBlock, int $idShop, int $idLang, int $expires, string $nonce): string
    {
        return EverblockSignedToken::sign(
            self::PURPOSE,
            self::payload($idBlock, $idShop, $idLang),
            $expires,
            $nonce
        );
    }

    /**
     * @return array<string, int>
     */
    private static function payload(int $idBlock, int $idShop, int $idLang): array
    {
        return [
            'id_everblock' => $idBlock,
            'id_shop' => $idShop,
            'id_lang' => $idLang,
        ];
    }
}
