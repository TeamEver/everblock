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

namespace Everblock\Tools\Application;

use Configuration;
use Everblock\Tools\Application\Command\EverBlock\EverBlockTranslationCommand;
use Everblock\Tools\Application\Command\EverBlock\UpsertEverBlockCommand;
use Everblock\Tools\Entity\EverBlock;
use Everblock\Tools\Entity\EverBlockTranslation;
use Everblock\Tools\Repository\EverBlockRepository;
use EverblockTools;
use Hook;
use Module;
use Tools;

class EverBlockApplicationService
{
    public function __construct(private readonly EverBlockRepository $repository)
    {
    }

    public function save(UpsertEverBlockCommand $command): EverBlock
    {
        $block = $this->hydrateBlockFromCommand($command);
        $translations = $this->buildTranslationsPayload($command->getTranslations());

        $block = $this->repository->save($block, $translations);

        $this->registerHook($block->getHookId());
        $this->clearCacheIfNeeded();

        return $block;
    }

    public function duplicate(int $blockId, int $shopId, bool $activate = false): EverBlock
    {
        $source = $this->repository->findById($blockId, $shopId);
        if (!$source instanceof EverBlock) {
            throw new \RuntimeException(sprintf('EverBlock %d not found for shop %d.', $blockId, $shopId));
        }

        $duplicate = new EverBlock();
        $duplicate->setShopId($source->getShopId());
        $duplicate->setHookId($source->getHookId());
        $duplicate->setName($source->getName());
        $duplicate->setOnlyHome($source->getOnlyHome());
        $duplicate->setOnlyCategory($source->getOnlyCategory());
        $duplicate->setOnlyCategoryProduct($source->getOnlyCategoryProduct());
        $duplicate->setOnlyManufacturer($source->getOnlyManufacturer());
        $duplicate->setOnlySupplier($source->getOnlySupplier());
        $duplicate->setOnlyCmsCategory($source->getOnlyCmsCategory());
        $duplicate->setObfuscateLink($source->getObfuscateLink());
        $duplicate->setAddContainer($source->getAddContainer());
        $duplicate->setLazyload($source->getLazyload());
        $duplicate->setCategories($source->getCategories());
        $duplicate->setManufacturers($source->getManufacturers());
        $duplicate->setSuppliers($source->getSuppliers());
        $duplicate->setCmsCategories($source->getCmsCategories());
        $duplicate->setGroups($source->getGroups());
        $duplicate->setBackground((string) $source->getBackground());
        $duplicate->setCssClass((string) $source->getCssClass());
        $duplicate->setDataAttribute((string) $source->getDataAttribute());
        $duplicate->setBootstrapClass((string) $source->getBootstrapClass());
        $duplicate->setDelay($source->getDelay());
        $duplicate->setTimeout($source->getTimeout());
        $duplicate->setModal($source->isModal());
        $duplicate->setDevice($source->getDevice());
        $duplicate->setDateStart($source->getDateStart());
        $duplicate->setDateEnd($source->getDateEnd());
        $duplicate->setActive($activate);
        $duplicate->setPosition($this->repository->getNextPosition($source->getHookId(), $source->getShopId()));

        $translations = [];
        foreach ($source->getTranslations() as $translation) {
            if (!$translation instanceof EverBlockTranslation) {
                continue;
            }
            $translations[$translation->getLanguageId()] = [
                'content' => $translation->getContent(),
                'custom_code' => $translation->getCustomCode(),
            ];
        }

        $duplicate = $this->repository->save($duplicate, $translations);

        $this->registerHook($duplicate->getHookId());
        $this->clearCacheIfNeeded();

        return $duplicate;
    }

    public function delete(int $blockId, int $shopId): void
    {
        $this->repository->delete($blockId, $shopId);
        $this->clearCacheIfNeeded();
    }

    public function toggle(int $blockId, int $shopId): void
    {
        $block = $this->repository->findById($blockId, $shopId);
        if (!$block instanceof EverBlock) {
            throw new \RuntimeException(sprintf('EverBlock %d not found for shop %d.', $blockId, $shopId));
        }

        $block->setActive(!$block->isActive());

        $translations = [];
        foreach ($block->getTranslations() as $translation) {
            if (!$translation instanceof EverBlockTranslation) {
                continue;
            }
            $translations[$translation->getLanguageId()] = [
                'content' => $translation->getContent(),
                'custom_code' => $translation->getCustomCode(),
            ];
        }

        $this->repository->save($block, $translations);
        $this->clearCacheIfNeeded();
    }

    private function hydrateBlockFromCommand(UpsertEverBlockCommand $command): EverBlock
    {
        $block = null;
        if (null !== $command->getId()) {
            $block = $this->repository->findById($command->getId(), $command->getShopId());
        }

        if (!$block instanceof EverBlock) {
            $block = new EverBlock();
            $block->setShopId($command->getShopId());
            $block->setPosition(
                $command->getPosition() ?? $this->repository->getNextPosition($command->getHookId(), $command->getShopId())
            );
        } else {
            $block->setPosition(
                $command->getPosition() ?? $block->getPosition()
            );
        }

        $block->setName($command->getName());
        $block->setHookId($command->getHookId());
        $block->setOnlyHome($command->onlyHome());
        $block->setOnlyCategory($command->onlyCategory());
        $block->setOnlyCategoryProduct($command->onlyCategoryProduct());
        $block->setOnlyManufacturer($command->onlyManufacturer());
        $block->setOnlySupplier($command->onlySupplier());
        $block->setOnlyCmsCategory($command->onlyCmsCategory());
        $block->setObfuscateLink($command->shouldObfuscateLink());
        $block->setAddContainer($command->shouldAddContainer());
        $block->setLazyload($command->shouldLazyload());
        $block->setGroups($this->encodeArray($command->getGroups()));
        $block->setCategories($this->encodeArray($command->getCategories()));
        $block->setManufacturers($this->encodeArray($command->getManufacturers()));
        $block->setSuppliers($this->encodeArray($command->getSuppliers()));
        $block->setCmsCategories($this->encodeArray($command->getCmsCategories()));
        $block->setBackground($command->getBackground() !== '' ? $command->getBackground() : null);
        $block->setCssClass($command->getCssClass() !== '' ? $command->getCssClass() : null);
        $block->setDataAttribute($command->getDataAttribute() !== '' ? $command->getDataAttribute() : null);
        $block->setBootstrapClass($command->getBootstrapClass() !== '' ? $command->getBootstrapClass() : null);
        $block->setDevice($command->getDevice());
        $block->setDelay($command->getDelay() ?? 0);
        $block->setTimeout($command->getTimeout() ?? 0);
        $block->setModal($command->isModal());
        $block->setDateStart($command->getDateStart());
        $block->setDateEnd($command->getDateEnd());
        $block->setActive($command->isActive());

        return $block;
    }

    /**
     * @param array<int, EverBlockTranslationCommand> $translations
     *
     * @return array<int, array<string, string|null>>
     */
    private function buildTranslationsPayload(array $translations): array
    {
        $payload = [];
        foreach ($translations as $translationCommand) {
            if (!$translationCommand instanceof EverBlockTranslationCommand) {
                continue;
            }

            $content = EverblockTools::convertImagesToWebP($translationCommand->getContent());
            $payload[$translationCommand->getLanguageId()] = [
                'content' => $content,
                'custom_code' => $translationCommand->getCustomCode(),
            ];
        }

        return $payload;
    }

    /**
     * @param array<int> $values
     */
    private function encodeArray(array $values): ?string
    {
        $filtered = array_values(array_filter($values, static fn ($value) => $value !== null));
        if (empty($filtered)) {
            return null;
        }

        return json_encode($filtered, JSON_THROW_ON_ERROR);
    }

    private function registerHook(int $hookId): void
    {
        $hookName = Hook::getNameById($hookId);
        $module = Module::getInstanceByName('everblock');

        if ($module && $hookName) {
            $module->registerHook($hookName);
        }
    }

    private function clearCacheIfNeeded(): void
    {
        if (Configuration::get('EVERPSCSS_CACHE')) {
            Tools::clearAllCache();
        }
    }
}
