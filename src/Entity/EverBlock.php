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

namespace Everblock\Tools\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'everblock')]
class EverBlock
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_everblock', type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(name: 'id_hook', type: 'integer')]
    private int $hookId = 0;

    #[ORM\Column(name: 'only_home', type: 'boolean', options: ['default' => false])]
    private bool $onlyHome = false;

    #[ORM\Column(name: 'only_category', type: 'boolean', options: ['default' => false])]
    private bool $onlyCategory = false;

    #[ORM\Column(name: 'only_category_product', type: 'boolean', options: ['default' => false])]
    private bool $onlyCategoryProduct = false;

    #[ORM\Column(name: 'only_manufacturer', type: 'boolean', options: ['default' => false])]
    private bool $onlyManufacturer = false;

    #[ORM\Column(name: 'only_supplier', type: 'boolean', options: ['default' => false])]
    private bool $onlySupplier = false;

    #[ORM\Column(name: 'only_cms_category', type: 'boolean', options: ['default' => false])]
    private bool $onlyCmsCategory = false;

    #[ORM\Column(name: 'obfuscate_link', type: 'boolean', options: ['default' => false])]
    private bool $obfuscateLink = false;

    #[ORM\Column(name: 'add_container', type: 'boolean', options: ['default' => false])]
    private bool $addContainer = false;

    #[ORM\Column(name: 'lazyload', type: 'boolean', options: ['default' => false])]
    private bool $lazyload = false;

    #[ORM\Column(name: 'device', type: 'integer', options: ['default' => 0])]
    private int $device = 0;

    #[ORM\Column(name: 'id_shop', type: 'integer', options: ['default' => 1])]
    private int $shopId = 1;

    #[ORM\Column(name: 'position', type: 'integer', options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $categories = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $manufacturers = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $suppliers = null;

    #[ORM\Column(name: 'cms_categories', type: 'text', nullable: true)]
    private ?string $cmsCategories = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $groups = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $background = null;

    #[ORM\Column(name: 'css_class', type: 'string', length: 255, nullable: true)]
    private ?string $cssClass = null;

    #[ORM\Column(name: 'data_attribute', type: 'string', length: 255, nullable: true)]
    private ?string $dataAttribute = null;

    #[ORM\Column(name: 'bootstrap_class', type: 'string', length: 255, nullable: true)]
    private ?string $bootstrapClass = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $modal = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $delay = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $timeout = 0;

    #[ORM\Column(name: 'date_start', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(name: 'date_end', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $active = false;

    /**
     * @var Collection<int, EverBlockTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'block', targetEntity: EverBlockTranslation::class, cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, EverBlockTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getHookId(): int
    {
        return $this->hookId;
    }

    public function setHookId(int $hookId): void
    {
        $this->hookId = $hookId;
    }

    public function getOnlyHome(): bool
    {
        return $this->onlyHome;
    }

    public function setOnlyHome(bool $onlyHome): void
    {
        $this->onlyHome = $onlyHome;
    }

    public function getOnlyCategory(): bool
    {
        return $this->onlyCategory;
    }

    public function setOnlyCategory(bool $onlyCategory): void
    {
        $this->onlyCategory = $onlyCategory;
    }

    public function getOnlyCategoryProduct(): bool
    {
        return $this->onlyCategoryProduct;
    }

    public function setOnlyCategoryProduct(bool $onlyCategoryProduct): void
    {
        $this->onlyCategoryProduct = $onlyCategoryProduct;
    }

    public function getOnlyManufacturer(): bool
    {
        return $this->onlyManufacturer;
    }

    public function setOnlyManufacturer(bool $onlyManufacturer): void
    {
        $this->onlyManufacturer = $onlyManufacturer;
    }

    public function getOnlySupplier(): bool
    {
        return $this->onlySupplier;
    }

    public function setOnlySupplier(bool $onlySupplier): void
    {
        $this->onlySupplier = $onlySupplier;
    }

    public function getOnlyCmsCategory(): bool
    {
        return $this->onlyCmsCategory;
    }

    public function setOnlyCmsCategory(bool $onlyCmsCategory): void
    {
        $this->onlyCmsCategory = $onlyCmsCategory;
    }

    public function getObfuscateLink(): bool
    {
        return $this->obfuscateLink;
    }

    public function setObfuscateLink(bool $obfuscateLink): void
    {
        $this->obfuscateLink = $obfuscateLink;
    }

    public function getAddContainer(): bool
    {
        return $this->addContainer;
    }

    public function setAddContainer(bool $addContainer): void
    {
        $this->addContainer = $addContainer;
    }

    public function getLazyload(): bool
    {
        return $this->lazyload;
    }

    public function setLazyload(bool $lazyload): void
    {
        $this->lazyload = $lazyload;
    }

    public function getDevice(): int
    {
        return $this->device;
    }

    public function setDevice(int $device): void
    {
        $this->device = $device;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getCategories(): ?string
    {
        return $this->categories;
    }

    public function setCategories(?string $categories): void
    {
        $this->categories = $categories;
    }

    public function getManufacturers(): ?string
    {
        return $this->manufacturers;
    }

    public function setManufacturers(?string $manufacturers): void
    {
        $this->manufacturers = $manufacturers;
    }

    public function getSuppliers(): ?string
    {
        return $this->suppliers;
    }

    public function setSuppliers(?string $suppliers): void
    {
        $this->suppliers = $suppliers;
    }

    public function getCmsCategories(): ?string
    {
        return $this->cmsCategories;
    }

    public function setCmsCategories(?string $cmsCategories): void
    {
        $this->cmsCategories = $cmsCategories;
    }

    public function getGroups(): ?string
    {
        return $this->groups;
    }

    public function setGroups(?string $groups): void
    {
        $this->groups = $groups;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background): void
    {
        $this->background = $background;
    }

    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    public function setCssClass(?string $cssClass): void
    {
        $this->cssClass = $cssClass;
    }

    public function getDataAttribute(): ?string
    {
        return $this->dataAttribute;
    }

    public function setDataAttribute(?string $dataAttribute): void
    {
        $this->dataAttribute = $dataAttribute;
    }

    public function getBootstrapClass(): ?string
    {
        return $this->bootstrapClass;
    }

    public function setBootstrapClass(?string $bootstrapClass): void
    {
        $this->bootstrapClass = $bootstrapClass;
    }

    public function isModal(): bool
    {
        return $this->modal;
    }

    public function setModal(bool $modal): void
    {
        $this->modal = $modal;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $dateStart): void
    {
        $this->dateStart = $dateStart;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeInterface $dateEnd): void
    {
        $this->dateEnd = $dateEnd;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function addTranslation(EverBlockTranslation $translation): void
    {
        foreach ($this->translations as $existingTranslation) {
            if ($existingTranslation->getLanguageId() === $translation->getLanguageId()) {
                $this->translations->removeElement($existingTranslation);
                break;
            }
        }

        $this->translations->add($translation);
    }

    public function getTranslation(int $languageId): ?EverBlockTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLanguageId() === $languageId) {
                return $translation;
            }
        }

        return null;
    }
}
