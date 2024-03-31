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
        $validToken = Tools::encrypt($this->module->name . '/token');
        if (!Tools::getValue('token') || Tools::getValue('token') != $validToken) {
            Tools::redirect('index.php');
        }
        $formData = Tools::getAllValues();
        // Use this for recaptcha validation
        Hook::exec(
            'hookActionContactFormSubmitBefore',
            [
                'formData' => &$formData,
            ]
        );
        if (empty($formData)) {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
            die($response);
        }
        $messageContent = '';
        foreach ($formData as $key => $value) { 
            $key = str_replace('_', ' ', $key);
            if (is_array($value)) {
                $messageContent .= $key . ': ' . "\n";
                foreach ($value as $item) {
                    $messageContent .= '  - ' . $item . "\n";
                }
            } else {
                $key = str_replace('_', ' ', $key);
                $messageContent .= $key . ': ' . $value . "\n";
            }
        }
        $clientIP = Tools::getRemoteAddr();
        $clientPlatform = Tools::getUserPlatform();
        $clientBrowser = Tools::getUserBrowser();
        $messageContent .= $this->module->l('Client IP:') . ' ' . $clientIP . "\n";
        $messageContent .= $this->module->l('Client browser:') . ' ' . $clientBrowser . "\n";
        $messageContent .= $this->module->l('Client platform:') . ' ' . $clientPlatform . "\n";
        $attachments = [];
        foreach ($_FILES as $fileKey => $fileData) {
            if (!empty($fileData['name'])
                && is_uploaded_file($fileData['tmp_name'])
            ) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $fileData['tmp_name']);
                finfo_close($finfo);
                $attachment = [
                    'content' => Tools::file_get_contents($fileData['tmp_name']),
                    'name' => $fileData['name'],
                    'mime' => $mime,
                ];
                $attachments[] = $attachment;
            }
        }
        $mailSubject = $this->module->l('New form submitted');
        if (Tools::getValue('everHide')
            && !empty(Tools::getValue('everHide'))
        ) {
            $mailRecipient = base64_decode(Tools::getValue('everHide'));
            if (Validate::isEmail($mailRecipient)) {
                $mailRecipient = base64_decode(Tools::getValue('everHide'));
            } else {
                $mailRecipient = Configuration::get('PS_SHOP_EMAIL');
            }
        } else {
            $mailRecipient = Configuration::get('PS_SHOP_EMAIL');
        }
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
        if ($sent) {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/success.tpl');
        } else {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
        }
        die($response);
    }
}
