<?php

/**
 * 2019-2022 Team Ever
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
 *  @copyright 2019-2022 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Everblock\Tools\Service\EverblockBackOfficeGuard;
use Everblock\Tools\Service\EverblockCustomerLoginToken;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockEverloginModuleFrontController extends ModuleFrontController
{
    /**
     * Back office tabs from which "log in as this customer" is offered.
     */
    const ALLOWED_TABS = ['AdminCustomers', 'AdminOrders'];

    /**
     * PrestaShop's default front-office cookie lifetime: 480 hours (20 days).
     */
    const DEFAULT_FRONT_COOKIE_LIFETIME_HOURS = 480;

    public function initContent()
    {
        $this->sendNoStoreHeaders();

        if (!$this->module instanceof Everblock) {
            Tools::redirect('index.php');
            return;
        }

        if (!Module::isInstalled($this->module->name)) {
            Tools::redirect('index.php');
        }

        // 🔐 1. Signed proof minted by the back office.
        //
        // The proof cannot be obtained without going through
        // EverblockAdminController::customerLoginAction(), which runs inside the PrestaShop admin
        // firewall: an authenticated employee, the AdminCustomers ACL and the native CSRF token
        // are all required to get one. It is valid for EverblockCustomerLoginToken::TTL seconds.
        //
        // Reading the psAdmin cookie here is NOT used as the gate any more: PrestaShop 9 only
        // writes that cookie on login success (EmployeeSessionSubscriber::updateLegacyCookie is
        // called with $write = false on every other request), so its content cannot be relied on
        // from the front office. It is still used as a reinforcement below when available.
        $idCustomer = (int) Tools::getValue(EverblockCustomerLoginToken::PARAM_CUSTOMER);
        $idEmployee = (int) Tools::getValue(EverblockCustomerLoginToken::PARAM_EMPLOYEE);
        $expires = (int) Tools::getValue(EverblockCustomerLoginToken::PARAM_EXPIRES);
        $nonce = (string) Tools::getValue(EverblockCustomerLoginToken::PARAM_NONCE);
        $providedToken = (string) Tools::getValue(EverblockCustomerLoginToken::PARAM_TOKEN);

        if ($idCustomer <= 0
            || $idEmployee <= 0
            || !EverblockCustomerLoginToken::isFresh($expires)
            || !EverblockCustomerLoginToken::verify($idCustomer, $idEmployee, $expires, $nonce, $providedToken)
        ) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Everblock everlogin: invalid or expired proof (customer #%d, employee #%d, %s)',
                    $idCustomer,
                    $idEmployee,
                    $expires > 0 && $expires <= time() ? 'expired' : 'signature mismatch'
                ),
                2
            );
            Tools::redirect('index.php');
        }

        $employee = new Employee($idEmployee);
        if (!Validate::isLoadedObject($employee) || !$employee->active) {
            Tools::redirect('index.php');
        }

        // 🔐 2. Native ACL of the employee the back office vouched for.
        if (!EverblockBackOfficeGuard::isGrantedOnAnyTab($employee, self::ALLOWED_TABS, 'READ')) {
            Tools::redirect('index.php');
        }

        // 🔐 3. Reinforcement, best effort: when the psAdmin cookie does reach the front office,
        // it must designate the very employee the proof was issued for. When it cannot be read,
        // the signed proof above stands on its own.
        $cookieEmployee = EverblockBackOfficeGuard::getLoggedEmployee();
        if ($cookieEmployee instanceof Employee && (int) $cookieEmployee->id !== $idEmployee) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Everblock everlogin: proof issued for employee #%d but the back office cookie designates #%d',
                    $idEmployee,
                    (int) $cookieEmployee->id
                ),
                3
            );
            Tools::redirect('index.php');
        }

        $customer = new Customer($idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php');
        }

        // 🔐 4. Multistore: the employee must be allowed on the customer shop.
        if (method_exists($employee, 'hasAuthOnShop')
            && (int) $customer->id_shop > 0
            && !$employee->hasAuthOnShop((int) $customer->id_shop)
        ) {
            Tools::redirect('index.php');
        }

        // 🧾 Piste d'audit : une usurpation de compte client doit être traçable.
        PrestaShopLogger::addLog(
            sprintf(
                'Everblock: employee #%d logged in as customer #%d',
                (int) $employee->id,
                (int) $customer->id
            ),
            2,
            null,
            'Customer',
            (int) $customer->id,
            true,
            (int) $employee->id
        );

        try {
            // Deconnexion propre si un autre client etait deja connecte.
            if ($this->context->customer instanceof Customer && $this->context->customer->isLogged()) {
                $this->context->customer->logout();
            }

            // Connexion client legacy + session customer_session Prestashop 8/9.
            $this->context->customer = $customer;
            $this->context->updateCustomer($customer);

            // Panier propre dedie a l'impersonation BO, sans reutiliser un panier invite.
            $cart = new Cart();
            $cart->id_customer = (int) $customer->id;
            $cart->id_currency = (int) $this->context->currency->id;
            $cart->id_lang = (int) $this->context->language->id;
            $cart->id_shop = (int) $this->context->shop->id;
            $cart->secure_key = $customer->secure_key;
            $cart->add();

            $this->context->cart = $cart;
            $this->context->cookie->id_cart = (int) $cart->id;

            // Synchronisation complete du cookie FO. PrestaShop 8/9 valide a la fois les champs
            // legacy et le couple session_id/session_token cree par registerSession().
            $this->context->cookie->id_customer = (int) $customer->id;
            $this->context->cookie->customer_lastname = $customer->lastname;
            $this->context->cookie->customer_firstname = $customer->firstname;
            $this->context->cookie->email = $customer->email;
            $this->context->cookie->passwd = $customer->passwd;
            $this->context->cookie->logged = 1;
            $this->context->cookie->is_guest = (int) $customer->isGuest();
            $this->context->cookie->secure_key = $customer->secure_key;

            // Flag interne (utile pour debug / hooks).
            $this->context->cookie->__set('everlogin', true);

            // Context::updateCustomer() cree deja la session client sur PS 8/9, mais elle est
            // enregistree apres son premier write(). L'ecriture finale ci-dessous est donc
            // indispensable pour envoyer session_id/session_token et le panier propre au navigateur.
            $this->refreshFrontOfficeCookieLifetime();
            $this->context->cookie->write();
        } catch (Throwable $exception) {
            PrestaShopLogger::addLog(
                'Everblock everlogin: unable to persist customer login for customer #'
                . (int) $customer->id . ': ' . $exception->getMessage(),
                3,
                null,
                'Customer',
                (int) $customer->id
            );

            Tools::redirect('index.php');
        }

        Tools::redirect($this->getEverloginRedirectUrl());
    }

    private function sendNoStoreHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Surrogate-Control: no-store');
        header('X-Accel-Expires: 0');
    }

    private function refreshFrontOfficeCookieLifetime(): void
    {
        if (!method_exists($this->context->cookie, 'setExpire')) {
            return;
        }

        $lifetimeHours = (int) Configuration::get('PS_COOKIE_LIFETIME_FO');
        if ($lifetimeHours <= 0) {
            $lifetimeHours = self::DEFAULT_FRONT_COOKIE_LIFETIME_HOURS;
        }

        $this->context->cookie->setExpire(time() + ($lifetimeHours * 3600));
    }

    private function getEverloginRedirectUrl(): string
    {
        $params = [
            'from' => 'everlogin',
            'everlogin_nocache' => time(),
        ];

        if ($this->context->link instanceof Link) {
            return (string) $this->context->link->getPageLink('my-account', true, null, $params);
        }

        return 'index.php?controller=my-account&from=everlogin&everlogin_nocache=' . (int) $params['everlogin_nocache'];
    }
}
