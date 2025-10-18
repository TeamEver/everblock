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

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'everblock_faq_lang')]
class EverBlockFaqTranslation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: EverBlockFaq::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'id_everblock_faq', referencedColumnName: 'id_everblock_faq', nullable: false, onDelete: 'CASCADE')]
    private EverBlockFaq $faq;

    #[ORM\Id]
    #[ORM\Column(name: 'id_lang', type: 'integer')]
    private int $languageId;

    #[ORM\Id]
    #[ORM\Column(name: 'id_shop', type: 'integer')]
    private int $shopId;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    public function __construct(EverBlockFaq $faq, int $languageId, int $shopId)
    {
        $this->faq = $faq;
        $this->languageId = $languageId;
        $this->shopId = $shopId;
    }

    public function getFaq(): EverBlockFaq
    {
        return $this->faq;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
