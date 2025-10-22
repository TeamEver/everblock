<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\Repository;

use EverBlockClass;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\CannotDeleteEverBlockException;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\CannotDuplicateEverBlockException;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\CannotToggleEverBlockStatusException;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\CannotUpdateEverBlockStatusException;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\EverBlockNotFoundException;
use Everblock\PrestaShopBundle\Service\EverBlockContentConverter;

class LegacyEverBlockRepository implements EverBlockRepositoryInterface
{
    /**
     * @var EverBlockContentConverter
     */
    private $contentConverter;

    public function __construct(EverBlockContentConverter $contentConverter)
    {
        $this->contentConverter = $contentConverter;
    }

    public function duplicate(int $everBlockId): int
    {
        $original = $this->getEverBlock($everBlockId);

        $duplicate = new EverBlockClass();
        $duplicate->name = $original->name;
        $duplicate->content = $this->contentConverter->convert($this->normalizeLocalizedData($original->content));
        $duplicate->custom_code = $this->normalizeLocalizedData($original->custom_code);
        $duplicate->only_home = (int) $original->only_home;
        $duplicate->only_category = (int) $original->only_category;
        $duplicate->only_category_product = (int) $original->only_category_product;
        $duplicate->only_manufacturer = (int) $original->only_manufacturer;
        $duplicate->only_supplier = (int) $original->only_supplier;
        $duplicate->only_cms_category = (int) $original->only_cms_category;
        $duplicate->obfuscate_link = (int) $original->obfuscate_link;
        $duplicate->add_container = (int) $original->add_container;
        $duplicate->lazyload = (int) $original->lazyload;
        $duplicate->id_hook = (int) $original->id_hook;
        $duplicate->device = (int) $original->device;
        $duplicate->groups = $original->groups;
        $duplicate->background = $original->background;
        $duplicate->css_class = $original->css_class;
        $duplicate->data_attribute = $original->data_attribute;
        $duplicate->bootstrap_class = $original->bootstrap_class;
        $duplicate->position = (int) $original->position;
        $duplicate->id_shop = (int) $original->id_shop;
        $duplicate->categories = $original->categories;
        $duplicate->manufacturers = $original->manufacturers;
        $duplicate->suppliers = $original->suppliers;
        $duplicate->cms_categories = $original->cms_categories;
        $duplicate->modal = (int) $original->modal;
        $duplicate->delay = (int) $original->delay;
        $duplicate->timeout = (int) $original->timeout;
        $duplicate->date_start = $original->date_start;
        $duplicate->date_end = $original->date_end;
        $duplicate->active = 0;

        if (!$duplicate->save()) {
            throw new CannotDuplicateEverBlockException('Unable to duplicate Ever Block "' . $original->name . '".');
        }

        return (int) $duplicate->id;
    }

    public function toggleStatus(int $everBlockId): bool
    {
        $block = $this->getEverBlock($everBlockId);
        $newStatus = !(bool) $block->active;
        $block->active = (int) $newStatus;

        if (!$block->save()) {
            throw new CannotToggleEverBlockStatusException('Unable to toggle block status.');
        }

        return $newStatus;
    }

    public function updateStatus(int $everBlockId, bool $enabled): void
    {
        $block = $this->getEverBlock($everBlockId);
        $block->active = (int) $enabled;

        if (!$block->save()) {
            throw new CannotUpdateEverBlockStatusException('Unable to update block status.');
        }
    }

    public function delete(int $everBlockId): void
    {
        $block = $this->getEverBlock($everBlockId);

        if (!$block->delete()) {
            throw new CannotDeleteEverBlockException('Unable to delete Ever Block.');
        }
    }

    /**
     * @param mixed $localizedData
     *
     * @return array<int, string>
     */
    private function normalizeLocalizedData($localizedData): array
    {
        if (!is_array($localizedData)) {
            return [];
        }

        return $localizedData;
    }

    private function getEverBlock(int $everBlockId): EverBlockClass
    {
        $everBlock = new EverBlockClass($everBlockId);
        if (!isset($everBlock->id) || !$everBlock->id) {
            throw new EverBlockNotFoundException('Ever Block #' . $everBlockId . ' was not found.');
        }

        return $everBlock;
    }
}
