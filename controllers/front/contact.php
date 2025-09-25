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

use Everblock\Tools\Service\RecaptchaValidator;

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
            return $this->terminateWithResponse($this->module->l('Invalid security token.'));
        }

        // ➕ Récupération du formulaire
        $formData = Tools::getAllValues();

        // ➕ Si vide ou si erreurs
        if (empty($formData) || sizeof($this->context->controller->errors)) {
            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
        }

        if (!$this->validateRecaptchaToken()) {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');

            return $this->terminateWithResponse($response);
        }

        // ➕ Hook avant traitement
        Hook::exec('hookActionContactFormSubmitBefore', ['formData' => &$formData]);

        // ➕ Si erreurs existantes
        if (empty($formData) || sizeof($this->context->controller->errors)) {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
            return $this->terminateWithResponse($response);
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
        $maxFileSize = (int) Configuration::get('EVERBLOCK_CONTACT_MAX_UPLOAD_SIZE');
        $allowedExtensions = $this->getConfigurationList('EVERBLOCK_CONTACT_ALLOWED_EXTENSIONS');
        $allowedMimeTypes = $this->getConfigurationList('EVERBLOCK_CONTACT_ALLOWED_MIME_TYPES', false);
        $uploadError = false;

        foreach ($_FILES as $fileKey => $fileData) {
            if (empty($fileData['name']) || empty($fileData['tmp_name'])) {
                continue;
            }

            if (!$this->isUploadedFile($fileData['tmp_name'])) {
                continue;
            }

            $fileSize = isset($fileData['size']) ? (int) $fileData['size'] : 0;
            if ($maxFileSize > 0 && $fileSize > $maxFileSize) {
                $uploadError = true;
                $this->context->controller->errors[] = $this->module->l('The uploaded file exceeds the allowed size.');
                PrestaShopLogger::addLog(
                    sprintf(
                        'Everblock contact: rejected "%s" because it exceeds the size limit (%d bytes > %d bytes).',
                        $fileData['name'],
                        $fileSize,
                        $maxFileSize
                    ),
                    3
                );
                break;
            }

            $extension = Tools::strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
            if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                $uploadError = true;
                $this->context->controller->errors[] = $this->module->l('The uploaded file type is not allowed.');
                PrestaShopLogger::addLog(
                    sprintf(
                        'Everblock contact: rejected "%s" because the extension "%s" is not allowed.',
                        $fileData['name'],
                        $extension ?: 'none'
                    ),
                    3
                );
                break;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_file($finfo, $fileData['tmp_name']) : null;
            if ($finfo) {
                finfo_close($finfo);
            }

            if (!empty($allowedMimeTypes) && (!is_string($mime) || !in_array($mime, $allowedMimeTypes))) {
                $uploadError = true;
                $this->context->controller->errors[] = $this->module->l('The uploaded file type is not allowed.');
                PrestaShopLogger::addLog(
                    sprintf(
                        'Everblock contact: rejected "%s" because the MIME type "%s" is not allowed.',
                        $fileData['name'],
                        $mime ?: 'unknown'
                    ),
                    3
                );
                break;
            }

            $attachments[] = [
                'content' => Tools::file_get_contents($fileData['tmp_name']),
                'name' => $fileData['name'],
                'mime' => $mime ?: (isset($fileData['type']) ? $fileData['type'] : 'application/octet-stream'),
            ];
        }

        if ($uploadError) {
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/error.tpl');
            return $this->terminateWithResponse($response);
        }

        // ➕ Destinataires
        $mailSubject = $this->module->l('New form submitted');
        $mailRecipient = Configuration::get('PS_SHOP_EMAIL');

        if ($everHideValue = Tools::getValue('everHide')) {
            $mailList = [];

            if (strpos($everHideValue, '::') !== false) {
                list($encodedRecipients, $signature) = explode('::', $everHideValue, 2);
                $decodedRecipients = base64_decode($encodedRecipients, true);

                if ($decodedRecipients !== false && $signature !== '') {
                    $expectedSignature = Tools::encrypt($decodedRecipients . '|' . (int) $this->context->shop->id);

                    if ($expectedSignature
                        && Tools::strlen($expectedSignature) === Tools::strlen($signature)
                        && hash_equals($expectedSignature, $signature)
                    ) {
                        $mailList = array_filter(array_map('trim', explode(',', $decodedRecipients)));
                    }
                }
            }

            $validMails = array_values(array_unique(array_filter($mailList, function ($email) {
                return Validate::isEmail($email);
            })));

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
                    if (!$this->sendMail(
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
                $allSent = $this->sendMail(
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

        return $this->terminateWithResponse($this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/' . $template));
    }

    protected function getConfigurationList($key, $lowercase = true)
    {
        $rawValue = Configuration::get($key);
        if (empty($rawValue)) {
            return [];
        }

        $decoded = json_decode($rawValue, true);
        if (is_array($decoded)) {
            $list = $decoded;
        } else {
            $list = array_map('trim', explode(',', (string) $rawValue));
        }

        $list = array_filter($list, function ($value) {
            return $value !== '' && $value !== null;
        });

        if ($lowercase) {
            $list = array_map('strtolower', $list);
        }

        return array_values($list);
    }

    protected function terminateWithResponse($response)
    {
        die($response);
    }

    protected function validateRecaptchaToken()
    {
        if (!RecaptchaValidator::shouldProtectContext(RecaptchaValidator::CONTEXT_EVERBLOCK_CONTACT)) {
            return true;
        }

        $result = RecaptchaValidator::validateRequest(RecaptchaValidator::CONTEXT_EVERBLOCK_CONTACT);
        if (!empty($result['success'])) {
            return true;
        }

        $message = RecaptchaValidator::getErrorMessage((int) $this->context->language->id);
        $this->context->controller->errors[] = $message;
        $this->context->smarty->assign('everblock_error_message', $message);

        RecaptchaValidator::logFailure(
            RecaptchaValidator::CONTEXT_EVERBLOCK_CONTACT,
            $result,
            ['ip' => Tools::getRemoteAddr()]
        );

        return false;
    }

    protected function sendMail($idLanguage, $template, $subject, $templateVars, $to, $toName = null, $from = null, $fromName = null, $attachments = null, $modeSMTP = null, $templatePath = _PS_MAIL_DIR_, $die = false, $idShop = null)
    {
        return Mail::send(
            $idLanguage,
            $template,
            $subject,
            $templateVars,
            $to,
            $toName,
            $from,
            $fromName,
            $attachments,
            $modeSMTP,
            $templatePath,
            $die,
            $idShop
        );
    }

    protected function isUploadedFile($tmpName)
    {
        return is_uploaded_file($tmpName);
    }
}
