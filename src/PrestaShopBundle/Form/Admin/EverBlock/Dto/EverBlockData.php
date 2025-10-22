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

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock\Dto;

class EverBlockData
{
    private $id;

    private $shopId;

    private $name;

    private $hookId;

    private $content;

    private $customCode;

    private $active;

    private $device;

    private $groupIds;

    private $onlyHome;

    private $onlyCategory;

    private $onlyCategoryProduct;

    private $categoryIds;

    private $onlyManufacturer;

    private $manufacturerIds;

    private $onlySupplier;

    private $supplierIds;

    private $onlyCmsCategory;

    private $cmsCategoryIds;

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

    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int) $data['id'] : null;
        $instance->shopId = (int) $data['id_shop'];
        $instance->name = (string) $data['name'];
        $instance->hookId = (int) $data['hook_id'];
        $instance->content = $data['content'];
        $instance->customCode = $data['custom_code'];
        $instance->active = (bool) $data['active'];
        $instance->device = (int) $data['device'];
        $instance->groupIds = $data['group_ids'];
        $instance->onlyHome = (bool) $data['only_home'];
        $instance->onlyCategory = (bool) $data['only_category'];
        $instance->onlyCategoryProduct = (bool) $data['only_category_product'];
        $instance->categoryIds = $data['category_ids'];
        $instance->onlyManufacturer = (bool) $data['only_manufacturer'];
        $instance->manufacturerIds = $data['manufacturer_ids'];
        $instance->onlySupplier = (bool) $data['only_supplier'];
        $instance->supplierIds = $data['supplier_ids'];
        $instance->onlyCmsCategory = (bool) $data['only_cms_category'];
        $instance->cmsCategoryIds = $data['cms_category_ids'];
        $instance->obfuscateLink = (bool) $data['obfuscate_link'];
        $instance->addContainer = (bool) $data['add_container'];
        $instance->lazyload = (bool) $data['lazyload'];
        $instance->background = $data['background'];
        $instance->cssClass = $data['css_class'];
        $instance->dataAttribute = $data['data_attribute'];
        $instance->bootstrapClass = (int) $data['bootstrap_class'];
        $instance->position = (int) $data['position'];
        $instance->modal = (bool) $data['modal'];
        $instance->delay = $data['delay'];
        $instance->timeout = $data['timeout'];
        $instance->dateStart = $data['date_start'];
        $instance->dateEnd = $data['date_end'];

        return $instance;
    }

    public function withContent(array $content): self
    {
        $clone = clone $this;
        $clone->content = $content;

        return $clone;
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

    public function getGroupIds(): array
    {
        return $this->groupIds;
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

    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    public function isOnlyManufacturer(): bool
    {
        return $this->onlyManufacturer;
    }

    public function getManufacturerIds(): array
    {
        return $this->manufacturerIds;
    }

    public function isOnlySupplier(): bool
    {
        return $this->onlySupplier;
    }

    public function getSupplierIds(): array
    {
        return $this->supplierIds;
    }

    public function isOnlyCmsCategory(): bool
    {
        return $this->onlyCmsCategory;
    }

    public function getCmsCategoryIds(): array
    {
        return $this->cmsCategoryIds;
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

    public function getBackground()
    {
        return $this->background;
    }

    public function getCssClass()
    {
        return $this->cssClass;
    }

    public function getDataAttribute()
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

    public function getDelay()
    {
        return $this->delay;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getDateStart()
    {
        return $this->dateStart;
    }

    public function getDateEnd()
    {
        return $this->dateEnd;
    }
}
