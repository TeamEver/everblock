<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2023 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockcontactModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->ajax = true;
        parent::initContent();
        return $this->formProcess();
    }

    protected function formProcess()
    {
        $validToken = Tools::encrypt($this->module->name.'/token');
        
        // Vérifiez si le jeton est valide
        if (!Tools::getValue('token') || Tools::getValue('token') != $validToken) {
            Tools::redirect('index.php');
        }

        // Obtenez toutes les valeurs du formulaire
        $formData = Tools::getAllValues();

        // Construisez la chaîne de données
        $messageContent = '';
        foreach ($formData as $key => $value) { 
            $key = str_replace('_', ' ', $key);
            if (is_array($value)) {
                // Si la valeur est un tableau, bouclez à travers ses éléments
                $messageContent .= $key . ': ' . "\n";
                foreach ($value as $item) {
                    $messageContent .= '  - ' . $item . "\n";
                }
            } else {
                $key = str_replace('_', ' ', $key);
                $messageContent .= $key . ': ' . $value . "\n";
            }
        }

        $mailSubject = $this->module->l('New form submitted');
        $mailRecipient = Configuration::get('PS_SHOP_EMAIL');
        $mailSender = Configuration::get('PS_SHOP_EMAIL');
        $mailTemplateName = 'evercontact';
        $templateVars = [
            '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
            '{shop_logo}' => _PS_IMG_DIR_ . Configuration::get(
                'PS_LOGO',
                null,
                null,
                (int) $this->context->shop->id
            ),
            '{message}' => $messageContent,
        ];
        $mailFolder = _PS_MODULE_DIR_ . $this->module->name . '/mails/';
        $sent = Mail::send(
            $this->context->language->id,
            $mailTemplateName,
            $mailSubject,
            $templateVars,
            $mailRecipient,
            null,
            $mailSender,
            null,
            null,
            null,
            $mailFolder
        );
        if ($sent) {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/success.tpl');
        } else {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
        }
        die($response);
    }
}
