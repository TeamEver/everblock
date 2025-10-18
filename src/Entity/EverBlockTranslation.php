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
#[ORM\Table(name: 'everblock_lang')]
class EverBlockTranslation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: EverBlock::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'id_everblock', referencedColumnName: 'id_everblock', nullable: false, onDelete: 'CASCADE')]
    private EverBlock $block;

    #[ORM\Id]
    #[ORM\Column(name: 'id_lang', type: 'integer')]
    private int $languageId;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(name: 'custom_code', type: 'text', nullable: true)]
    private ?string $customCode = null;

    public function __construct(EverBlock $block, int $languageId)
    {
        $this->block = $block;
        $this->languageId = $languageId;
    }

    public function getBlock(): EverBlock
    {
        return $this->block;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getCustomCode(): ?string
    {
        return $this->customCode;
    }

    public function setCustomCode(?string $customCode): void
    {
        $this->customCode = $customCode;
    }
}
