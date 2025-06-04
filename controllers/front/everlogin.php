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
        $secureToken = EverblockTools::getSecureToken('login');
        if (!Tools::getIsset('evertoken')
            || $secureToken != Tools::getValue('evertoken')
            || !Module::isInstalled($this->module->name)
        ) {
            Tools::redirect('index.php');
        }
        if (!Tools::getValue('id_ever_customer')) {
            Tools::redirect('index.php');
        }
        $updatedVersion = Tools::version_compare(_PS_VERSION_, '1.7.6.6', '>=') ? true : false;
        $customer = new Customer(
            (int) Tools::getValue('id_ever_customer')
        );
        if (Validate::isLoadedObject($customer)) {
            if ($this->context->customer->isLogged()) {
                $this->context->customer->logout();
            }
            $customer->logged = 1;
            $this->context->cookie->id_customer = (int) $customer->id;
            $this->context->cookie->customer_lastname = $customer->lastname;
            $this->context->cookie->customer_firstname = $customer->firstname;
            $this->context->cookie->passwd = $customer->passwd;
            $this->context->cookie->logged = 1;
            $this->context->cookie->email = $customer->email;
            $this->context->cookie->secure_key = $customer->secure_key;
            $this->context->cookie->is_guest = $customer->isGuest();
            $this->context->cart->secure_key = $customer->secure_key;
            // Use this cookie to see if user have been logged from admin
            $this->context->cookie->__set(
                'everlogin',
                true
            );
            if (Tools::getValue('ever_id_cart')
                && Validate::isInt(Tools::getValue('ever_id_cart'))
            ) {
                $cart = new Cart(
                    (int) Tools::getValue('ever_id_cart')
                );
                if (Validate::isLoadedObject($cart)) {
                    $this->context->cart = $cart;
                    $this->context->cookie->id_cart = $cart->id;
                }
            }
            if ((bool) $updatedVersion === true) {
                $this->context->cookie->registerSession(new CustomerSession());
            }
            Tools::redirect('index.php?controller=my-account');
        }
        parent::initContent();
    }
}
