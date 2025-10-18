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

namespace Everblock\Tools\Application\Command\EverBlock;

use DateTimeInterface;

/**
 * Command that carries all data required to create or update an Ever Block.
 */
final class UpsertEverBlockCommand
{
    /**
     * @param array<int, EverBlockTranslationCommand> $translations
     * @param array<int> $groups
     * @param array<int> $categories
     * @param array<int> $manufacturers
     * @param array<int> $suppliers
     * @param array<int> $cmsCategories
     */
    public function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly int $hookId,
        private readonly int $shopId,
        private readonly bool $onlyHome,
        private readonly bool $onlyCategory,
        private readonly bool $onlyCategoryProduct,
        private readonly bool $onlyManufacturer,
        private readonly bool $onlySupplier,
        private readonly bool $onlyCmsCategory,
        private readonly bool $obfuscateLink,
        private readonly bool $addContainer,
        private readonly bool $lazyload,
        private readonly array $groups,
        private readonly array $categories,
        private readonly array $manufacturers,
        private readonly array $suppliers,
        private readonly array $cmsCategories,
        private readonly string $background,
        private readonly string $cssClass,
        private readonly string $dataAttribute,
        private readonly string $bootstrapClass,
        private readonly ?int $position,
        private readonly int $device,
        private readonly ?int $delay,
        private readonly ?int $timeout,
        private readonly bool $modal,
        private readonly ?DateTimeInterface $dateStart,
        private readonly ?DateTimeInterface $dateEnd,
        private readonly bool $active,
        private readonly array $translations,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHookId(): int
    {
        return $this->hookId;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function onlyHome(): bool
    {
        return $this->onlyHome;
    }

    public function onlyCategory(): bool
    {
        return $this->onlyCategory;
    }

    public function onlyCategoryProduct(): bool
    {
        return $this->onlyCategoryProduct;
    }

    public function onlyManufacturer(): bool
    {
        return $this->onlyManufacturer;
    }

    public function onlySupplier(): bool
    {
        return $this->onlySupplier;
    }

    public function onlyCmsCategory(): bool
    {
        return $this->onlyCmsCategory;
    }

    public function shouldObfuscateLink(): bool
    {
        return $this->obfuscateLink;
    }

    public function shouldAddContainer(): bool
    {
        return $this->addContainer;
    }

    public function shouldLazyload(): bool
    {
        return $this->lazyload;
    }

    /**
     * @return array<int>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return array<int>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @return array<int>
     */
    public function getManufacturers(): array
    {
        return $this->manufacturers;
    }

    /**
     * @return array<int>
     */
    public function getSuppliers(): array
    {
        return $this->suppliers;
    }

    /**
     * @return array<int>
     */
    public function getCmsCategories(): array
    {
        return $this->cmsCategories;
    }

    public function getBackground(): string
    {
        return $this->background;
    }

    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    public function getDataAttribute(): string
    {
        return $this->dataAttribute;
    }

    public function getBootstrapClass(): string
    {
        return $this->bootstrapClass;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getDevice(): int
    {
        return $this->device;
    }

    public function getDelay(): ?int
    {
        return $this->delay;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function isModal(): bool
    {
        return $this->modal;
    }

    public function getDateStart(): ?DateTimeInterface
    {
        return $this->dateStart;
    }

    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return array<int, EverBlockTranslationCommand>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
