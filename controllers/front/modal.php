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

use Everblock\Tools\Service\EverblockTools;

class EverblockmodalModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
        return $this->getModal();
    }

    protected function getModal()
    {
        $validToken = Tools::getToken();
        if (!Tools::getValue('token') || Tools::getValue('token') != $validToken) {
            Tools::redirect('index.php');
        }
        $blockId = (int) Tools::getValue('id_everblock');
        $cmsId = (int) Tools::getValue('id_cms');
        $productModalId = (int) Tools::getValue('id_everblock_modal');

        if ($cmsId && !$blockId && !$productModalId) {
            $cms = new CMS($cmsId, $this->context->language->id, $this->context->shop->id);
            if (!Validate::isLoadedObject($cms) || !(bool) $cms->active) {
                die();
            }
            $cmsContent = EverblockTools::renderShortcodes(
                $cms->content,
                $this->context,
                $this->module
            );
            $this->context->smarty->assign([
                'everblock_modal' => (object) ['content' => $cmsContent],
            ]);
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/modal.tpl');
            die($response);
        }
        if ($productModalId && !$blockId && !$cmsId) {
            $modal = new EverblockModal(
                $productModalId,
                $this->context->language->id,
                $this->context->shop->id
            );
            if (!Validate::isLoadedObject($modal)) {
                die();
            }
            $content = isset($modal->content[$this->context->language->id])
                ? $modal->content[$this->context->language->id]
                : '';
            $fileUrl = '';
            $fileRenderType = '';
            $fileExtension = '';
            if (!empty($modal->file)) {
                $fileUrl = $this->context->link->getBaseLink() . 'img/cms/' . $modal->file;
                $fileExtension = Tools::strtolower(pathinfo($modal->file, PATHINFO_EXTENSION));
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'];
                $videoExtensions = ['mp4', 'webm', 'ogg', 'ogv'];
                if (in_array($fileExtension, $imageExtensions, true)) {
                    $fileRenderType = 'image';
                } elseif (in_array($fileExtension, $videoExtensions, true)) {
                    $fileRenderType = 'video';
                } else {
                    $fileRenderType = 'iframe';
                }
            }
            $this->context->smarty->assign([
                'everblock_modal' => (object) [
                    'content' => EverblockTools::renderShortcodes(
                        $content,
                        $this->context,
                        $this->module
                    ),
                    'file' => $fileUrl,
                    'file_render_type' => $fileRenderType,
                    'file_extension' => $fileExtension,
                ],
            ]);
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/modal.tpl');
            die($response);
        }
        $block = new EverBlockClass(
            $blockId,
            $this->context->language->id,
            $this->context->shop->id

        );
        if (!Validate::isLoadedObject($block)) {
            die();
        }
        $modalDelay = (int) $block->delay;
        $showModal = false;
        $cookieName = $this->module->encrypt(
            $this->module->name
            . $this->context->shop->id
            . Configuration::get('PS_SHOP_NAME')
        );
        if ($modalDelay > 0 && (bool) Tools::getValue('force') != true) {
            if (!isset($_COOKIE[$cookieName])) {
                $showModal = true;
                $expiration = time() + ($modalDelay * 24 * 60 * 60);
                setcookie($cookieName, 'true', $expiration, '/');
            }
        } else {
            $showModal = true;
        }
        if ($showModal) {
            // Hooks not allowed here
            if (strpos($block->content, '{hook h=') !== false) {
                $pattern = '/\{hook h=[^}]*\}/';
                $block->content = preg_replace($pattern, '', $block->content);
            }
            // Store locator not allowed here
            if (strpos($block->content, '[storelocator]') !== false) {
                $block->content = str_replace('[storelocator]', '', $block->content);
            }
            $block->content = EverBlockTools::renderShortcodes(
                $block->content,
                $this->context,
                $this->module
            );
            $this->context->smarty->assign([
                'everblock_modal' => $block,
            ]);
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/modal.tpl');
            die($response);
        }
        die();
    }
}
