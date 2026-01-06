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

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockEverloginModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        // 🔐 Sécurité du token
        if (
            !Tools::getIsset('evertoken')
            || Tools::encrypt($this->module->name . '/everlogin') !== Tools::getValue('evertoken')
            || !Module::isInstalled($this->module->name)
        ) {
            Tools::redirect('index.php');
        }

        $idCustomer = (int) Tools::getValue('id_ever_customer');
        if ($idCustomer <= 0) {
            Tools::redirect('index.php');
        }

        $customer = new Customer($idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php');
        }

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
