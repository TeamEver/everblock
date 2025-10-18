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

use Everblock\Tools\Dto\ModalDto;
use Everblock\Tools\Entity\EverBlock;
use Everblock\Tools\Entity\EverBlockModal;
use Everblock\Tools\Entity\EverBlockTranslation;
use Everblock\Tools\Repository\EverBlockRepository;
use Everblock\Tools\Service\Domain\EverBlockModalDomainService;

class EverblockmodalModuleFrontController extends ModuleFrontController
{
    private ?EverBlockRepository $blockRepository;
    private ?EverBlockModalDomainService $modalDomainService;

    public function __construct(
        ?EverBlockRepository $blockRepository = null,
        ?EverBlockModalDomainService $modalDomainService = null
    ) {
        $this->blockRepository = $blockRepository;
        $this->modalDomainService = $modalDomainService;
        parent::__construct();
    }

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

            $this->renderModal($this->createModalDto((string) $cms->content));
        }

        if ($productModalId && !$blockId && !$cmsId) {
            $modalDomainService = $this->getModalDomainService();
            if (!$modalDomainService instanceof EverBlockModalDomainService) {
                die();
            }

            $modalEntity = $modalDomainService->find(
                $productModalId,
                (int) $this->context->shop->id
            );

            if (!$modalEntity instanceof EverBlockModal) {
                die();
            }

            $translation = $modalEntity->getTranslation((int) $this->context->language->id);
            $content = $translation ? $translation->getContent() : '';

            $this->renderModal($this->createModalDto(
                (string) $content,
                $modalEntity->getFile()
            ));
        }
        $block = $this->findBlock($blockId);
        if (null === $block) {
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
            $this->renderModal($this->createModalDto(
                $block->content,
                null,
                $block->background ?? null
            ));
        }
        die();
    }

    private function findBlock(int $blockId): ?\stdClass
    {
        $repository = $this->getBlockRepository();
        if (!$repository instanceof EverBlockRepository) {
            return null;
        }

        $block = $repository->findById($blockId, (int) $this->context->shop->id);
        if (!$block instanceof EverBlock) {
            return null;
        }

        $translation = $this->resolveTranslation($block, (int) $this->context->language->id);
        $content = $translation ? (string) $translation->getContent() : '';

        return (object) [
            'id' => $block->getId(),
            'delay' => $block->getDelay(),
            'content' => $content,
            'background' => $block->getBackground(),
        ];
    }

    private function resolveTranslation(EverBlock $block, int $languageId): ?EverBlockTranslation
    {
        foreach ($block->getTranslations() as $translation) {
            if ($translation instanceof EverBlockTranslation && $translation->getLanguageId() === $languageId) {
                return $translation;
            }
        }

        return null;
    }

    private function getBlockRepository(): ?EverBlockRepository
    {
        if ($this->blockRepository instanceof EverBlockRepository) {
            return $this->blockRepository;
        }

        if ($this->module instanceof Everblock) {
            $this->blockRepository = $this->module->getEverBlockRepository();
        }

        return $this->blockRepository instanceof EverBlockRepository ? $this->blockRepository : null;
    }

    private function getModalDomainService(): ?EverBlockModalDomainService
    {
        if ($this->modalDomainService instanceof EverBlockModalDomainService) {
            return $this->modalDomainService;
        }

        if ($this->module instanceof Everblock) {
            try {
                $this->modalDomainService = $this->module->getEverBlockModalDomainService();
            } catch (\RuntimeException $exception) {
                $this->modalDomainService = null;
            }
        }

        return $this->modalDomainService instanceof EverBlockModalDomainService
            ? $this->modalDomainService
            : null;
    }

    private function renderModal(ModalDto $modal): void
    {
        $this->context->smarty->assign([
            'everblock_modal' => $modal,
        ]);

        $response = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everblock/views/templates/front/modal.tpl');
        die($response);
    }

    private function createModalDto(string $content, ?string $file = null, ?string $background = null): ModalDto
    {
        $fileUrl = null;
        $fileExtension = null;
        $fileRenderType = null;

        if (!empty($file)) {
            $fileUrl = $this->context->link->getBaseLink() . 'img/cms/' . ltrim($file, '/');
            $fileExtension = Tools::strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $fileRenderType = $this->resolveFileRenderType($fileExtension);
        }

        $renderedContent = EverblockTools::renderShortcodes(
            $content,
            $this->context,
            $this->module
        );

        return new ModalDto(
            $renderedContent,
            $fileUrl,
            $fileRenderType,
            $fileExtension,
            $background
        );
    }

    private function resolveFileRenderType(?string $fileExtension): ?string
    {
        if (!$fileExtension) {
            return null;
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'];
        $videoExtensions = ['mp4', 'webm', 'ogg', 'ogv'];

        if (in_array($fileExtension, $imageExtensions, true)) {
            return 'image';
        }

        if (in_array($fileExtension, $videoExtensions, true)) {
            return 'video';
        }

        return 'iframe';
    }
}
