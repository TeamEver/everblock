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

    #[ORM\Column(type: 'text')]
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
}
