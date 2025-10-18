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
#[ORM\Table(name: 'everblock_shortcode')]
class EverBlockShortcode
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_everblock_shortcode', type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $shortcode = null;

    #[ORM\Column(name: 'id_shop', type: 'integer')]
    private int $shopId = 0;

    /**
     * @var Collection<int, EverBlockShortcodeTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'shortcode', targetEntity: EverBlockShortcodeTranslation::class, cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortcode(): ?string
    {
        return $this->shortcode;
    }

    public function setShortcode(?string $shortcode): void
    {
        $this->shortcode = $shortcode;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @return Collection<int, EverBlockShortcodeTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
