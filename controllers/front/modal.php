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
        // Tools::getToken() is also published to the front office through
        // Media::addJsDef(['everblock_token' => ...]) in Everblock::hookDisplayHeader(). It is a
        // CSRF / session token, NOT an authorisation: every object loaded below therefore has to
        // be checked against its own visibility rules, exactly as the page displaying it does.
        $validToken = (string) Tools::getToken();
        $submittedToken = (string) Tools::getValue('token');
        if ($submittedToken === '' || $validToken === '' || !hash_equals($validToken, $submittedToken)) {
            Tools::redirect('index.php');
        }
        $blockId = (int) Tools::getValue('id_everblock');
        $cmsId = (int) Tools::getValue('id_cms');
        $productModalId = (int) Tools::getValue('id_everblock_modal');
        if (!$this->module instanceof Everblock) {
            die();
        }
        $module = $this->module;

        if ($cmsId && !$blockId && !$productModalId) {
            $cms = new CMS($cmsId, $this->context->language->id, $this->context->shop->id);
            // Same guard as the native CMSController: loaded, active and associated to this shop.
            if (!Validate::isLoadedObject($cms)
                || !(bool) $cms->active
                || !$cms->isAssociatedToShop((int) $this->context->shop->id)
            ) {
                die();
            }
            $cmsContent = EverblockTools::renderShortcodes(
                $cms->content,
                $this->context,
                $module
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
            // The repository already restricts the lookup to the current shop. What was missing is
            // the visibility of the PRODUCT the modal belongs to: without it, incrementing
            // id_everblock_modal exposed the modal content of disabled or group restricted
            // products.
            if (!Validate::isLoadedObject($modal) || !$this->isProductModalVisible($modal)) {
                die();
            }
            $content = $modal->getContent((int) $this->context->language->id);
            $content = $module->renderQcdBuilderTargetField(
                'everblock_product_modal',
                (int) $modal->id_product,
                'content',
                (string) $content,
                (int) $this->context->shop->id,
                (int) $this->context->language->id
            );
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
                        $module
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
        // new EverBlockClass() loads by id and shop only: unlike EverBlockClass::getBlocks(), it
        // filters neither on active nor on the publication window nor on the customer groups.
        // Without the guard below, incrementing id_everblock rendered any block, including
        // disabled, expired and group restricted ones.
        if (!Validate::isLoadedObject($block) || !$this->isBlockModalVisible($block)) {
            die();
        }
        $modalDelay = (int) $block->delay;
        $showModal = false;
        $cookieName = $module->encrypt(
            $module->name
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
            $idLang = (int) $this->context->language->id;
            $blockContent = $block->getContent($idLang);
            $blockContent = $module->renderQcdBuilderTargetField(
                'everblock',
                (int) $block->id,
                'content',
                $blockContent,
                (int) $this->context->shop->id,
                (int) $this->context->language->id
            );
            // Hooks not allowed here
            if (strpos($blockContent, '{hook h=') !== false) {
                $pattern = '/\{hook h=[^}]*\}/';
                $blockContent = preg_replace($pattern, '', $blockContent);
            }
            // Store locator not allowed here
            if (strpos($blockContent, '[storelocator]') !== false) {
                $blockContent = str_replace('[storelocator]', '', $blockContent);
            }
            $blockContent = EverBlockTools::renderShortcodes(
                $blockContent,
                $this->context,
                $module
            );
            $this->context->smarty->assign([
                'everblock_modal' => (object) [
                    'content' => $blockContent,
                    'background' => $block->background,
                ],
            ]);
            $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/modal.tpl');
            die($response);
        }
        die();
    }

    /**
     * Visibility rules a block must satisfy to be rendered through this controller.
     *
     * Reuses the very methods Everblock::everHook() applies when rendering the same block in a
     * hook, so the endpoint can never be more permissive than the page.
     *
     * Two families of flags are deliberately NOT re-applied here, because they are display
     * settings rather than authorisation:
     *  - page placement (only_home, only_category, only_manufacturer, only_supplier,
     *    only_cms_category, device): already evaluated on the page that opened the modal, and
     *    this controller has no page context to evaluate them against;
     *  - the "modal" flag itself: the README documents opening ANY block in a modal with
     *    <button class="everblock-modal-button" data-everclickmodal="12">, so requiring
     *    modal = 1 would break a documented feature.
     *
     * @param EverBlockClass $block
     */
    protected function isBlockModalVisible($block): bool
    {
        if (!(bool) $block->active) {
            return false;
        }

        if (!EverBlockClass::isWithinPublicationDates($block->date_start, $block->date_end)) {
            return false;
        }

        return EverBlockClass::isAllowedForGroups(
            $block->groups,
            EverBlockClass::resolveCustomerGroups($this->context)
        );
    }

    /**
     * A product modal is only readable when its product is readable: active, associated to the
     * current shop and allowed for the visitor groups. Product::checkAccess() is the native
     * PrestaShop catalogue access check.
     *
     * @param EverblockModal $modal
     */
    protected function isProductModalVisible($modal): bool
    {
        $productId = (int) $modal->id_product;
        if ($productId <= 0) {
            return false;
        }

        $product = new Product(
            $productId,
            false,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );

        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        if (!(bool) $product->active
            || !$product->isAssociatedToShop((int) $this->context->shop->id)
        ) {
            return false;
        }

        $customerId = isset($this->context->customer->id) ? (int) $this->context->customer->id : 0;

        return (bool) $product->checkAccess($customerId);
    }
}
