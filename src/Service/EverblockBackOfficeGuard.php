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
use Exception;
use Tab;
use Tools;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Identifies and authorises a real back office employee from a front office request.
 *
 * PrestaShop never populates $context->employee on the front office: config/config.inc.php
 * only builds it when _PS_ADMIN_DIR_ is defined, and classes/controller/FrontController.php
 * does not reference employees at all (checked on 8.0, 8.2 and 9.2).
 *
 * Employee::isLoggedBack() cannot be used from the front office either: on PS 8 it reads
 * Context::getContext()->cookie, which is the *customer* cookie, and on PS 9 it delegates to
 * the admin Symfony firewall (prestashop.user_provider), unreachable outside the back office.
 *
 * We therefore read the native `psAdmin` cookie — exactly what the core itself does in
 * Tools::isAllowedToBypassMaintenance() — and replay the checks performed by isLoggedBack().
 */
class EverblockBackOfficeGuard
{
    const ROLE_PREFIX = 'ROLE_MOD_TAB_';

    /** @var Cookie|null */
    private static $adminCookie;

    /** @var bool */
    private static $adminCookieResolved = false;

    /** @var Employee|null */
    private static $employee;

    /** @var bool */
    private static $employeeResolved = false;

    /**
     * Read-only access to the back office cookie, from either side of the shop.
     *
     * @return Cookie|null
     */
    public static function getAdminCookie()
    {
        if (self::$adminCookieResolved) {
            return self::$adminCookie;
        }

        self::$adminCookieResolved = true;
        self::$adminCookie = null;

        // Inside the back office the current cookie already *is* the psAdmin cookie.
        if (defined('_PS_ADMIN_DIR_')) {
            $context = Context::getContext();
            if ($context && $context->cookie instanceof Cookie) {
                self::$adminCookie = $context->cookie;
            }

            return self::$adminCookie;
        }

        try {
            $cookie = new Cookie('psAdmin');
        } catch (Exception $exception) {
            return null;
        }

        // Never rewrite the back office cookie from a front office request.
        $cookie->disallowWriting();
        self::$adminCookie = $cookie;

        return self::$adminCookie;
    }

    /**
     * Token of the current employee back office session, or an empty string.
     *
     * Useful as an extra HMAC ingredient: any signature bound to it dies when the employee
     * logs out of the back office, without any server side storage.
     */
    public static function getEmployeeSessionToken(): string
    {
        $cookie = self::getAdminCookie();
        if (!$cookie) {
            return '';
        }

        return isset($cookie->session_token) ? (string) $cookie->session_token : '';
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

        $cookie = self::getAdminCookie();
        if (!$cookie) {
            return null;
        }

        $employeeId = (int) $cookie->id_employee;
        if ($employeeId <= 0) {
            return null;
        }

        // The employee session must still exist in ps_employee_session.
        if (!method_exists($cookie, 'isSessionAlive') || !$cookie->isSessionAlive()) {
            return null;
        }

        // The password hash carried by the cookie must still match the database one.
        $passwd = isset($cookie->passwd) ? (string) $cookie->passwd : '';
        if ($passwd === '' || !Employee::checkPassword($employeeId, $passwd)) {
            return null;
        }

        if (Configuration::get('PS_COOKIE_CHECKIP')
            && isset($cookie->remote_addr)
            && (int) $cookie->remote_addr !== (int) ip2long(Tools::getRemoteAddr())
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
     * Native PrestaShop ACL check against a back office tab.
     *
     * @param Employee $employee
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
        } catch (Exception $exception) {
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
     * Test helper: forget the memoized employee/cookie.
     */
    public static function reset(): void
    {
        self::$adminCookie = null;
        self::$adminCookieResolved = false;
        self::$employee = null;
        self::$employeeResolved = false;
    }
}
