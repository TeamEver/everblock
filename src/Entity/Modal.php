<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\ProductContentRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Language;

/**
 * @ORM\Table(name="everblock_modal")
 * @ORM\Entity(repositoryClass="Everblock\Tools\Repository\ProductContentRepository")
 */
class Modal
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_everblock_modal", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;
    public ?int $id_everblock_modal = null;

    /** @ORM\Column(name="id_product", type="integer") */
    public int $id_product = 0;
    /** @ORM\Column(name="id_shop", type="integer") */
    public int $id_shop = 0;
    /** @ORM\Column(name="file", type="string", length=255, nullable=true) */
    public ?string $file = null;
    /** @ORM\Column(name="button_file", type="string", length=255, nullable=true) */
    public ?string $button_file = null;

    /** @var array<int, string> */
    public array $content = [];
    /** @var array<int, string> */
    public array $button_label = [];

    public function __construct(?int $id = null, ?int $idLang = null, ?int $idShop = null)
    {
        if ($id !== null && $id > 0) {
            $loaded = self::repository()->findModal($id);
            if ($loaded instanceof self) {
                foreach (get_object_vars($loaded) as $property => $value) {
                    $this->{$property} = $value;
                }
            }
        }
    }

    public static function repository(): ProductContentRepository
    {
        /** @var ProductContentRepository $repository */
        $repository = RepositoryProvider::get('everblock.repository.product_content');

        return $repository;
    }

    public static function fromDatabase(array $row, array $langRows = []): self
    {
        $modal = new self();
        $modal->id = isset($row['id_everblock_modal']) ? (int) $row['id_everblock_modal'] : null;
        $modal->id_everblock_modal = $modal->id;
        $modal->id_product = (int) ($row['id_product'] ?? 0);
        $modal->id_shop = (int) ($row['id_shop'] ?? 0);
        $modal->file = $row['file'] ?? null;
        $modal->button_file = $row['button_file'] ?? null;
        foreach ($langRows as $langRow) {
            $langId = (int) ($langRow['id_lang'] ?? 0);
            if ($langId <= 0) {
                continue;
            }
            $modal->content[$langId] = (string) ($langRow['content'] ?? '');
            $modal->button_label[$langId] = (string) ($langRow['button_label'] ?? '');
        }

        return $modal;
    }

    public function save(): bool
    {
        $this->id = self::repository()->saveModal($this, Language::getLanguages(false));
        $this->id_everblock_modal = $this->id;

        return $this->id > 0;
    }

    public function delete(): bool
    {
        return !$this->id || self::repository()->deleteModal($this->id);
    }

    public static function getByProductId(int $idProduct, int $idShop): self
    {
        $modal = self::repository()->findModalByProduct($idProduct, $idShop);
        if ($modal instanceof self) {
            return $modal;
        }

        $modal = new self();
        $modal->id_product = $idProduct;
        $modal->id_shop = $idShop;

        return $modal;
    }
}
