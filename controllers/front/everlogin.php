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

    public function initContent()
    {
        if (!$this->module instanceof Everblock) {
            Tools::redirect('index.php');
            return;
        }

        if (!Module::isInstalled($this->module->name)) {
            Tools::redirect('index.php');
        }

        // 🔐 1. A real, live back office employee session is mandatory: a leaked URL replayed
        // from a machine without a back office session must never impersonate a customer.
        $employee = EverblockBackOfficeGuard::getLoggedEmployee();
        if (!$employee instanceof Employee) {
            Tools::redirect('index.php');
            return;
        }

        // 🔐 2. Native ACL: the employee must be allowed to browse customers or orders.
        if (!EverblockBackOfficeGuard::isGrantedOnAnyTab($employee, self::ALLOWED_TABS, 'READ')) {
            Tools::redirect('index.php');
        }

        // 🔐 3. Signed token, scoped to one customer, one employee, with an expiry.
        $idCustomer = (int) Tools::getValue(EverblockCustomerLoginToken::PARAM_CUSTOMER);
        $idEmployee = (int) Tools::getValue(EverblockCustomerLoginToken::PARAM_EMPLOYEE);
        $expires = (int) Tools::getValue(EverblockCustomerLoginToken::PARAM_EXPIRES);
        $nonce = (string) Tools::getValue(EverblockCustomerLoginToken::PARAM_NONCE);
        $providedToken = (string) Tools::getValue(EverblockCustomerLoginToken::PARAM_TOKEN);

        if ($idCustomer <= 0
            || $idEmployee <= 0
            || $idEmployee !== (int) $employee->id
            || !EverblockCustomerLoginToken::isFresh($expires)
            || !EverblockCustomerLoginToken::verify($idCustomer, $idEmployee, $expires, $nonce, $providedToken)
        ) {
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

        // 🔄 Déconnexion propre si déjà loggué
        if ($this->context->customer->isLogged()) {
            $this->context->customer->logout();
        }

        /**
         * ✅ CONNEXION CLIENT PROPRE
         */
        $this->context->customer = $customer;
        $this->context->updateCustomer($customer);

        /**
         * 🛒 CRÉATION D'UN PANIER PROPRE (IMPORTANT)
         * ❌ ne jamais réutiliser un panier invité
         */
        $cart = new Cart();
        $cart->id_customer = (int) $customer->id;
        $cart->id_currency = (int) $this->context->currency->id;
        $cart->id_lang = (int) $this->context->language->id;
        $cart->id_shop = (int) $this->context->shop->id;
        $cart->secure_key = $customer->secure_key;
        $cart->add();

        $this->context->cart = $cart;
        $this->context->cookie->id_cart = (int) $cart->id;

        /**
         * 🍪 Synchronisation complète du cookie
         */
        $this->context->cookie->id_customer = (int) $customer->id;
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->email = $customer->email;
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->logged = 1;
        $this->context->cookie->is_guest = (int) $customer->isGuest();
        $this->context->cookie->secure_key = $customer->secure_key;

        // 🧠 Flag interne (utile pour debug / hooks)
        $this->context->cookie->__set('everlogin', true);

        /**
         * 🧠 Session PS 1.7.6+ / PS 8
         */
        if (method_exists($this->context->cookie, 'registerSession')) {
            $this->context->cookie->registerSession(new CustomerSession());
        }

        /**
         * 🔁 REDIRECTION AVEC FLAG
         * => permet de forcer un reload JS propre
         */
        Tools::redirect('index.php?controller=my-account&from=everlogin');
    }
}
