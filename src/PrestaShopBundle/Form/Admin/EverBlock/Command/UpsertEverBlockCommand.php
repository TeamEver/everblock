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
 */

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock\Command;

class UpsertEverBlockCommand
{
    private $id;
    private $shopId;
    private $name;
    private $hookId;
    private $content;
    private $customCode;
    private $active;
    private $device;
    private $groupsJson;
    private $onlyHome;
    private $onlyCategory;
    private $onlyCategoryProduct;
    private $categoriesJson;
    private $onlyManufacturer;
    private $manufacturersJson;
    private $onlySupplier;
    private $suppliersJson;
    private $onlyCmsCategory;
    private $cmsCategoriesJson;
    private $obfuscateLink;
    private $addContainer;
    private $lazyload;
    private $background;
    private $cssClass;
    private $dataAttribute;
    private $bootstrapClass;
    private $position;
    private $modal;
    private $delay;
    private $timeout;
    private $dateStart;
    private $dateEnd;

    public function __construct(
        $id,
        int $shopId,
        string $name,
        int $hookId,
        array $content,
        array $customCode,
        bool $active,
        int $device,
        string $groupsJson,
        bool $onlyHome,
        bool $onlyCategory,
        bool $onlyCategoryProduct,
        string $categoriesJson,
        bool $onlyManufacturer,
        string $manufacturersJson,
        bool $onlySupplier,
        string $suppliersJson,
        bool $onlyCmsCategory,
        string $cmsCategoriesJson,
        bool $obfuscateLink,
        bool $addContainer,
        bool $lazyload,
        ?string $background,
        ?string $cssClass,
        ?string $dataAttribute,
        int $bootstrapClass,
        int $position,
        bool $modal,
        ?int $delay,
        ?int $timeout,
        ?string $dateStart,
        ?string $dateEnd
    ) {
        $this->id = $id;
        $this->shopId = $shopId;
        $this->name = $name;
        $this->hookId = $hookId;
        $this->content = $content;
        $this->customCode = $customCode;
        $this->active = $active;
        $this->device = $device;
        $this->groupsJson = $groupsJson;
        $this->onlyHome = $onlyHome;
        $this->onlyCategory = $onlyCategory;
        $this->onlyCategoryProduct = $onlyCategoryProduct;
        $this->categoriesJson = $categoriesJson;
        $this->onlyManufacturer = $onlyManufacturer;
        $this->manufacturersJson = $manufacturersJson;
        $this->onlySupplier = $onlySupplier;
        $this->suppliersJson = $suppliersJson;
        $this->onlyCmsCategory = $onlyCmsCategory;
        $this->cmsCategoriesJson = $cmsCategoriesJson;
        $this->obfuscateLink = $obfuscateLink;
        $this->addContainer = $addContainer;
        $this->lazyload = $lazyload;
        $this->background = $background;
        $this->cssClass = $cssClass;
        $this->dataAttribute = $dataAttribute;
        $this->bootstrapClass = $bootstrapClass;
        $this->position = $position;
        $this->modal = $modal;
        $this->delay = $delay;
        $this->timeout = $timeout;
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHookId(): int
    {
        return $this->hookId;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function getCustomCode(): array
    {
        return $this->customCode;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getDevice(): int
    {
        return $this->device;
    }

    public function getGroupsJson(): string
    {
        return $this->groupsJson;
    }

    public function isOnlyHome(): bool
    {
        return $this->onlyHome;
    }

    public function isOnlyCategory(): bool
    {
        return $this->onlyCategory;
    }

    public function isOnlyCategoryProduct(): bool
    {
        return $this->onlyCategoryProduct;
    }

    public function getCategoriesJson(): string
    {
        return $this->categoriesJson;
    }

    public function isOnlyManufacturer(): bool
    {
        return $this->onlyManufacturer;
    }

    public function getManufacturersJson(): string
    {
        return $this->manufacturersJson;
    }

    public function isOnlySupplier(): bool
    {
        return $this->onlySupplier;
    }

    public function getSuppliersJson(): string
    {
        return $this->suppliersJson;
    }

    public function isOnlyCmsCategory(): bool
    {
        return $this->onlyCmsCategory;
    }

    public function getCmsCategoriesJson(): string
    {
        return $this->cmsCategoriesJson;
    }

    public function isObfuscateLink(): bool
    {
        return $this->obfuscateLink;
    }

    public function isAddContainer(): bool
    {
        return $this->addContainer;
    }

    public function isLazyload(): bool
    {
        return $this->lazyload;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    public function getDataAttribute(): ?string
    {
        return $this->dataAttribute;
    }

    public function getBootstrapClass(): int
    {
        return $this->bootstrapClass;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isModal(): bool
    {
        return $this->modal;
    }

    public function getDelay(): ?int
    {
        return $this->delay;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function getDateStart(): ?string
    {
        return $this->dateStart;
    }

    public function getDateEnd(): ?string
    {
        return $this->dateEnd;
    }
}
