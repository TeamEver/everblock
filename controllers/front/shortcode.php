<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockshortcodeModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        $this->processShortcodes();
    }

    protected function processShortcodes()
    {
        $validToken = Tools::getToken();
        $token = Tools::getValue('token');
        if (!$token || $token !== $validToken) {
            die('');
        }

        $html = Tools::getValue('html');
        if (!$html) {
            die('');
        }

        $html = html_entity_decode($html);
        $html = EverblockTools::renderShortcodes($html, $this->context, $this->module);
        die($html);
    }
}
