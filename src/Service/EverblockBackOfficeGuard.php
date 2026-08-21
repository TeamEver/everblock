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

use Access;
use Configuration;
use Context;
use Cookie;
use Employee;
use EmployeeSession;
use PhpEncryption;
use Tab;
use Tools;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Identifies and authorises a real back office employee from a front office request.
 *
 * PrestaShop never populates $context->employee on the front office: config/config.inc.php only
 * builds it when _PS_ADMIN_DIR_ is defined, and classes/controller/FrontController.php does not
 * reference employees at all (checked on 8.0, 8.2 and 9.2).
 *
 * Employee::isLoggedBack() cannot be used from the front office either: on PS 8 it reads
 * Context::getContext()->cookie, which is the *customer* cookie, and on PS 9 it delegates to the
 * admin Symfony firewall, unreachable outside the back office.
 *
 * IMPORTANT — why this class does NOT instantiate Cookie('psAdmin') on the front office.
 * Cookie::__construct() calls update(), and update() calls logout() as soon as the stored
 * checksum does not match. Cookie::logout() then calls deleteSession() (which DELETES the
 * ps_employee_session row) and encryptAndSetCookie() (which sends a Set-Cookie clearing the
 * cookie) — and encryptAndSetCookie() does not honour disallowWriting(). Merely *reading* the
 * back office cookie that way could therefore log the employee out of the back office. The
 * parsing below is strictly read only: it never writes a cookie, never touches a session row.
 *
 * It is also name agnostic: rather than recomputing 'PrestaShop-' . md5(_PS_VERSION_ . 'psAdmin'
 * . domain), it scans the cookies actually sent by the browser. A host that differs between the
 * back office and the front office (preprod aliases, reverse proxies, shop domain mismatches)
 * therefore no longer breaks the lookup.
 */
class EverblockBackOfficeGuard
{
    const ROLE_PREFIX = 'ROLE_MOD_TAB_';

    /** Prefix of every PrestaShop cookie name. */
    const COOKIE_NAME_PREFIX = 'PrestaShop-';

    /** @var array<string, string>|null Decoded content of the back office cookie */
    private static $adminCookieContent;

    /** @var Employee|null */
    private static $employee;

    /** @var bool */
    private static $employeeResolved = false;

    /**
     * Decoded back office cookie content, as a plain key => value map.
     *
     * @return array<string, string>
     */
    public static function getAdminCookieContent(): array
    {
        if (self::$adminCookieContent !== null) {
            return self::$adminCookieContent;
        }

        self::$adminCookieContent = [];

        // Inside the back office the legacy context already holds the decoded psAdmin cookie,
        // so there is nothing to parse and nothing that could be written.
        if (defined('_PS_ADMIN_DIR_')) {
            $context = Context::getContext();
            if ($context && $context->cookie instanceof Cookie) {
                foreach (['id_employee', 'passwd', 'remote_addr', 'session_id', 'session_token'] as $key) {
                    if (isset($context->cookie->{$key})) {
                        self::$adminCookieContent[$key] = (string) $context->cookie->{$key};
                    }
                }
            }

            return self::$adminCookieContent;
        }

        if (!is_array($_COOKIE) || !class_exists('PhpEncryption') || !defined('_NEW_COOKIE_KEY_')) {
            return self::$adminCookieContent;
        }

        foreach ($_COOKIE as $name => $value) {
            if (!is_string($name) || !is_string($value) || strpos($name, self::COOKIE_NAME_PREFIX) !== 0) {
                continue;
            }

            $content = self::decodeCookieValue($value);
            if ($content === [] || (int) ($content['id_employee'] ?? 0) <= 0) {
                continue;
            }

            self::$adminCookieContent = $content;
            break;
        }

        return self::$adminCookieContent;
    }

    /**
     * Read only reimplementation of Cookie::update() decoding, restricted to what this guard
     * needs. Returns an empty array when the value cannot be decrypted or fails its checksum.
     *
     * @return array<string, string>
     */
    private static function decodeCookieValue(string $value): array
    {
        try {
            $cipher = new PhpEncryption(_NEW_COOKIE_KEY_);
            $decrypted = $cipher->decrypt($value);
        } catch (\Throwable $exception) {
            return [];
        }

        if (!is_string($decrypted) || $decrypted === '' || strpos($decrypted, 'id_employee|') === false) {
            return [];
        }

        $parts = explode('¤', $decrypted);
        array_pop($parts);
        $contentForChecksum = implode('¤', $parts) . '¤';

        $content = [];
        foreach (explode('¤', $decrypted) as $pair) {
            $keyAndValue = explode('|', $pair);
            if (count($keyAndValue) === 2) {
                $content[$keyAndValue[0]] = $keyAndValue[1];
            }
        }

        // Same checksum as Cookie::update() for a non standalone cookie: salt is _COOKIE_IV_.
        if (!defined('_COOKIE_IV_')
            || !isset($content['checksum'])
            || !hash_equals(hash('sha256', _COOKIE_IV_ . $contentForChecksum), (string) $content['checksum'])
        ) {
            return [];
        }

        return $content;
    }

    /**
     * Token of the current employee back office session, or an empty string.
     *
     * Useful as an extra HMAC ingredient: any signature bound to it dies when the employee logs
     * out of the back office, without any server side storage.
     */
    public static function getEmployeeSessionToken(): string
    {
        $content = self::getAdminCookieContent();

        return isset($content['session_token']) ? (string) $content['session_token'] : '';
    }

    /**
     * @return Employee|null The employee owning a live back office session, null otherwise
     */
    public static function getLoggedEmployee()
    {
        if (self::$employeeResolved) {
            return self::$employee;
        }

        self::$employeeResolved = true;
        self::$employee = null;

        $content = self::getAdminCookieContent();
        if ($content === []) {
            return null;
        }

        $employeeId = (int) ($content['id_employee'] ?? 0);
        if ($employeeId <= 0) {
            return null;
        }

        // The password hash carried by the cookie must still match the database one.
        $passwd = (string) ($content['passwd'] ?? '');
        if ($passwd === '' || !Employee::checkPassword($employeeId, $passwd)) {
            return null;
        }

        if (!self::isSessionAlive($employeeId, $content)) {
            return null;
        }

        if (Configuration::get('PS_COOKIE_CHECKIP')
            && isset($content['remote_addr'])
            && (int) $content['remote_addr'] !== (int) ip2long(Tools::getRemoteAddr())
        ) {
            return null;
        }

        $employee = new Employee($employeeId);
        if (!Validate::isLoadedObject($employee) || !$employee->active) {
            return null;
        }

        self::$employee = $employee;

        return self::$employee;
    }

    /**
     * Same check as Cookie::isSessionAlive(), without going through a Cookie instance and
     * without the date_upd write that Cookie::getSession() performs.
     *
     * @param array<string, string> $content
     */
    private static function isSessionAlive(int $employeeId, array $content): bool
    {
        $sessionId = (int) ($content['session_id'] ?? 0);
        $sessionToken = (string) ($content['session_token'] ?? '');

        if ($sessionId <= 0 || $sessionToken === '') {
            return false;
        }

        if (!class_exists('EmployeeSession')) {
            return false;
        }

        $session = new EmployeeSession($sessionId);
        if (!Validate::isLoadedObject($session)) {
            return false;
        }

        return hash_equals((string) $session->getToken(), $sessionToken)
            && (int) $session->getUserId() === $employeeId;
    }

    /**
     * Native PrestaShop ACL check against a back office tab.
     *
     * @param string $tabClassName e.g. 'AdminEverBlock'
     * @param string $authorization 'CREATE'|'READ'|'UPDATE'|'DELETE'
     */
    public static function isGrantedOnTab(Employee $employee, string $tabClassName, string $authorization = 'READ'): bool
    {
        if (method_exists($employee, 'isSuperAdmin') && $employee->isSuperAdmin()) {
            return true;
        }

        if (!class_exists('Access')) {
            return true;
        }

        // A missing tab means the authorization role does not exist either: the ACL cannot be
        // evaluated, so do not lock employees out because of a partial tab installation.
        if ((int) Tab::getIdFromClassName($tabClassName) <= 0) {
            return true;
        }

        try {
            return (bool) Access::isGranted(
                self::ROLE_PREFIX . Tools::strtoupper($tabClassName) . '_' . Tools::strtoupper($authorization),
                (int) $employee->id_profile
            );
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * True as soon as one of the given tabs grants the authorization.
     *
     * @param string[] $tabClassNames
     */
    public static function isGrantedOnAnyTab(Employee $employee, array $tabClassNames, string $authorization = 'READ'): bool
    {
        foreach ($tabClassNames as $tabClassName) {
            if (self::isGrantedOnTab($employee, (string) $tabClassName, $authorization)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Diagnostic helper: why did the lookup fail? Never returns a secret.
     */
    public static function describeLookup(): string
    {
        $prestashopCookies = 0;
        if (is_array($_COOKIE)) {
            foreach (array_keys($_COOKIE) as $name) {
                if (is_string($name) && strpos($name, self::COOKIE_NAME_PREFIX) === 0) {
                    ++$prestashopCookies;
                }
            }
        }

        $content = self::getAdminCookieContent();

        return sprintf(
            'admin dir defined: %s, PrestaShop cookies received: %d, decoded employee cookie: %s, https: %s',
            defined('_PS_ADMIN_DIR_') ? 'yes' : 'no',
            $prestashopCookies,
            $content === [] ? 'none' : ('#' . (int) ($content['id_employee'] ?? 0)),
            Tools::usingSecureMode() ? 'yes' : 'no'
        );
    }

    /**
     * Test helper: forget the memoized employee/cookie.
     */
    public static function reset(): void
    {
        self::$adminCookieContent = null;
        self::$employee = null;
        self::$employeeResolved = false;
    }
}
