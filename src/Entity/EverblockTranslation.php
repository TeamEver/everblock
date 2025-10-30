<?php

namespace Everblock\Tools\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'everblock_lang')]
class EverblockTranslation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Everblock::class, inversedBy: 'translationEntities')]
    #[ORM\JoinColumn(name: 'id_everblock', referencedColumnName: 'id_everblock', nullable: false, onDelete: 'CASCADE')]
    private Everblock $everblock;

    #[ORM\Id]
    #[ORM\Column(name: 'id_lang', type: 'integer')]
    private int $languageId;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(name: 'custom_code', type: 'text', nullable: true)]
    private ?string $customCode = null;

    public function __construct(Everblock $everblock, int $languageId)
    {
        $this->everblock = $everblock;
        $this->languageId = $languageId;
    }

    public function getEverblock(): Everblock
    {
        return $this->everblock;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): void
    {
        $this->languageId = $languageId;
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
