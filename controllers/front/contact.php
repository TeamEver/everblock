<?php

/**
 * 2019-2023 Team Ever
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
        $this->ajax = true;
        return $this->formProcess();
    }

    protected function formProcess()
    {
        // ✅ Token CSRF sécurisé (lié à la session)
        $validToken = Tools::getToken(); // équivalent de Tools::getAdminTokenLite sans dépendre de l'admin
        $submittedToken = Tools::getValue('token');

        if (!$submittedToken || $submittedToken !== $validToken) {
            // Tu peux faire un redirect ou un die avec message custom
            die($this->module->l('Invalid security token.'));
        }

        // ➕ Récupération du formulaire
        $formData = Tools::getAllValues();

        // ➕ Si vide ou si erreurs
        if (empty($formData) || sizeof($this->context->controller->errors)) {
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
        }

        // ➕ Hook avant traitement
        Hook::exec('hookActionContactFormSubmitBefore', ['formData' => &$formData]);

        // ➕ Si erreurs existantes
        if (empty($formData) || sizeof($this->context->controller->errors)) {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
            die($response);
        }

        // ➕ Contenu du message HTML
        $messageContent = '';
        $excludedKeys = ['token', 'everHide', 'submit', 'action'];

        foreach ($formData as $key => $value) {
            if (in_array($key, $excludedKeys)) {
                continue;
            }

            $key = htmlspecialchars(str_replace('_', ' ', $key));
            if (is_array($value)) {
                $messageContent .= '<p><strong>' . $key . ':</strong><br>';
                foreach ($value as $item) {
                    $messageContent .= '  - ' . htmlspecialchars($item) . '<br>';
                }
                $messageContent .= '</p>';
            } else {
                $messageContent .= '<p><strong>' . $key . ':</strong> ' . htmlspecialchars($value) . '</p>';
            }
        }

        // ✅ Si le contenu est vide (pas de message à envoyer)
        if (trim(strip_tags($messageContent)) === '') {
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
        }

        // ➕ Infos client
        $messageContent .= '<p><strong>' . $this->module->l('Client IP:') . '</strong> ' . Tools::getRemoteAddr() . '</p>';
        $messageContent .= '<p><strong>' . $this->module->l('Client browser:') . '</strong> ' . Tools::getUserBrowser() . '</p>';
        $messageContent .= '<p><strong>' . $this->module->l('Client platform:') . '</strong> ' . Tools::getUserPlatform() . '</p>';

        // ➕ Pièces jointes
        $attachments = [];
        foreach ($_FILES as $fileKey => $fileData) {
            if (!empty($fileData['name']) && is_uploaded_file($fileData['tmp_name'])) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $fileData['tmp_name']);
                finfo_close($finfo);

                $attachments[] = [
                    'content' => Tools::file_get_contents($fileData['tmp_name']),
                    'name' => $fileData['name'],
                    'mime' => $mime,
                ];
            }
        }

        // ➕ Destinataires
        $mailSubject = $this->module->l('New form submitted');
        $mailRecipient = Configuration::get('PS_SHOP_EMAIL');

        if ($everHideValue = Tools::getValue('everHide')) {
            $formMail = base64_decode($everHideValue);
            $mailList = array_map('trim', explode(',', $formMail));
            $validMails = array_filter($mailList, function($email) {
                return Validate::isEmail($email);
            });

            if (!empty($validMails)) {
                $mailRecipient = $validMails;
            }
        }

        $mailSender = Configuration::get('PS_SHOP_EMAIL');
        $templateVars = [
            '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
            '{shop_logo}' => _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, (int) $this->context->shop->id),
            '{message}' => $messageContent,
        ];
        $mailFolder = _PS_MODULE_DIR_ . $this->module->name . '/mails/';
        $mailTemplateName = 'evercontact';

        try {
            $allSent = true;
            if (is_array($mailRecipient)) {
                foreach ($mailRecipient as $recipient) {
                    if (!Mail::send(
                        (int) $this->context->language->id,
                        $mailTemplateName,
                        $mailSubject,
                        $templateVars,
                        $recipient,
                        null,
                        $mailSender,
                        null,
                        !empty($attachments) ? $attachments : null,
                        null,
                        $mailFolder
                    )) {
                        $allSent = false;
                        break;
                    }
                }
            } else {
                $allSent = Mail::send(
                    (int) $this->context->language->id,
                    $mailTemplateName,
                    $mailSubject,
                    $templateVars,
                    $mailRecipient,
                    null,
                    $mailSender,
                    null,
                    !empty($attachments) ? $attachments : null,
                    null,
                    $mailFolder
                );
            }

            $template = $allSent ? 'success.tpl' : 'error.tpl';
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage());
            $template = 'error.tpl';
        }

        die($this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/' . $template));
    }
}
