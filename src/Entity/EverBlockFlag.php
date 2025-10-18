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
#[ORM\Table(name: 'everblock_flags')]
class EverBlockFlag
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_everblock_flags', type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: 'id_product', type: 'integer')]
    private int $productId = 0;

    #[ORM\Column(name: 'id_shop', type: 'integer')]
    private int $shopId = 0;

    #[ORM\Column(name: 'id_flag', type: 'integer')]
    private int $flagId = 0;

    /**
     * @var Collection<int, EverBlockFlagTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'flag', targetEntity: EverBlockFlagTranslation::class, cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    public function getFlagId(): int
    {
        return $this->flagId;
    }

    public function setFlagId(int $flagId): void
    {
        $this->flagId = $flagId;
    }

    /**
     * @return Collection<int, EverBlockFlagTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(EverBlockFlagTranslation $translation): void
    {
        foreach ($this->translations as $existingTranslation) {
            if ($existingTranslation->getLanguageId() === $translation->getLanguageId()) {
                $this->translations->removeElement($existingTranslation);

                break;
            }
        }

        $this->translations->add($translation);
    }

    public function getTranslation(int $languageId): ?EverBlockFlagTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLanguageId() === $languageId) {
                return $translation;
            }
        }

        return null;
    }
}
