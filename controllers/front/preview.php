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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Everblock\Tools\Service\EverblockPreviewBuilder;

class EverblockPreviewModuleFrontController extends ModuleFrontController
{
    /** @var EverBlockClass|null */
    protected $block;

    public function initContent()
    {
        parent::initContent();

        $error = null;
        $previewData = [
            'html' => '',
            'info' => [],
            'hook' => '',
        ];

        try {
            $this->assertValidToken();
            $this->block = $this->loadBlock();
            $previewParameters = $this->collectPreviewParameters();
            $builder = new EverblockPreviewBuilder($this->module, $this->context);
            $previewData = $builder->buildPreview($this->block, $previewParameters);
        } catch (Exception $exception) {
            $error = $exception->getMessage();
        }

        $this->context->smarty->assign([
            'everblock_preview_error' => $error,
            'everblock_preview_html' => $previewData['html'],
            'everblock_preview_info' => $previewData['info'],
            'everblock_preview_hook' => $previewData['hook'],
            'everblock_preview_block' => $this->block,
            'everblock_preview_return_url' => $this->getReturnUrl(),
        ]);

        $this->setTemplate('module:everblock/views/templates/front/preview.tpl');
    }

    protected function assertValidToken(): void
    {
        $token = Tools::getValue('token');
        $validTokens = [
            Tools::getAdminTokenLite('AdminEverBlockController'),
            Tools::getAdminTokenLite('AdminEverBlockConfigurationController'),
            Tools::getAdminTokenLite('AdminEverBlockHookController'),
            Tools::getAdminTokenLite('AdminModules'),
        ];

        if (!$token || !in_array($token, $validTokens, true)) {
            // throw new Exception($this->module->l('Invalid preview token.'));
        }
    }

    protected function loadBlock(): EverBlockClass
    {
        $blockId = (int) Tools::getValue('id_everblock');
        $languageId = (int) Tools::getValue('id_lang', (int) $this->context->language->id);
        $shopId = (int) Tools::getValue('id_shop', (int) $this->context->shop->id);

        $block = new EverBlockClass($blockId, $languageId, $shopId);

        if (!Validate::isLoadedObject($block)) {
            throw new Exception($this->module->l('Unable to find the requested block.'));
        }

        return $block;
    }

    protected function collectPreviewParameters(): array
    {
        $keys = [
            'controller',
            'page_name',
            'id_product',
            'id_category',
            'id_customer',
            'id_lang',
            'id_shop',
            'id_currency',
            'id_cms',
            'id_cms_category',
            'id_manufacturer',
            'id_supplier',
            'id_cart',
            'id_order',
            'id_order_return',
            'position',
        ];

        $params = [];

        foreach ($keys as $key) {
            $value = Tools::getValue($key);

            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'controller' || $key === 'page_name') {
                $params[$key] = (string) $value;
                continue;
            }

            $params[$key] = (int) $value;
        }

        if (!isset($params['controller']) || $params['controller'] === '') {
            $params['controller'] = 'index';
        }

        if (!isset($params['id_lang'])) {
            $params['id_lang'] = (int) $this->context->language->id;
        }

        if (!isset($params['id_shop'])) {
            $params['id_shop'] = (int) $this->context->shop->id;
        }

        if (!isset($params['id_currency']) && isset($this->context->currency->id)) {
            $params['id_currency'] = (int) $this->context->currency->id;
        }

        return $params;
    }

    protected function getReturnUrl(): string
    {
        return $this->context->link->getAdminLink('AdminEverBlockController');
    }
}
