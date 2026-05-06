<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\ProductContentRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Language;

/**
 * @ORM\Table(name="everblock_tabs")
 * @ORM\Entity(repositoryClass="Everblock\Tools\Repository\ProductContentRepository")
 */
class ProductTab
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_everblock_tabs", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;
    public ?int $id_everblock_tabs = null;

    /** @ORM\Column(name="id_product", type="integer") */
    public int $id_product = 0;
    /** @ORM\Column(name="id_shop", type="integer", nullable=true) */
    public int $id_shop = 0;
    /** @ORM\Column(name="id_tab", type="integer", nullable=true) */
    public int $id_tab = 0;

    /** @var array<int, string>|string */
    public $title = [];
    /** @var array<int, string>|string */
    public $content = [];

    public function __construct(?int $id = null, ?int $idLang = null, ?int $idShop = null)
    {
        unset($idShop);
        if ($id !== null && $id > 0) {
            $loaded = self::repository()->findTab($id, $idLang);
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

    public static function fromDatabase(array $row, array $langRows = [], ?int $singleLangId = null): self
    {
        $tab = new self();
        $tab->id = isset($row['id_everblock_tabs']) ? (int) $row['id_everblock_tabs'] : null;
        $tab->id_everblock_tabs = $tab->id;
        $tab->id_product = (int) ($row['id_product'] ?? 0);
        $tab->id_shop = (int) ($row['id_shop'] ?? 0);
        $tab->id_tab = (int) ($row['id_tab'] ?? 0);
        if (isset($row['id_lang'])) {
            $langRows[] = $row;
        }
        $titles = [];
        $contents = [];
        foreach ($langRows as $langRow) {
            $langId = (int) ($langRow['id_lang'] ?? 0);
            if ($langId <= 0) {
                continue;
            }
            $titles[$langId] = (string) ($langRow['title'] ?? '');
            $contents[$langId] = (string) ($langRow['content'] ?? '');
        }
        $tab->title = $singleLangId ? ($titles[$singleLangId] ?? '') : $titles;
        $tab->content = $singleLangId ? ($contents[$singleLangId] ?? '') : $contents;

        return $tab;
    }

    public function save(): bool
    {
        $this->id = self::repository()->saveTab($this, Language::getLanguages(false));
        $this->id_everblock_tabs = $this->id;

        return $this->id > 0;
    }

    public function delete(): bool
    {
        return !$this->id || self::repository()->deleteTab($this->id);
    }

    public static function getByIdProductInAdmin(int $productId, int $shopId): array
    {
        return self::repository()->findTabsByProduct($productId, $shopId, null);
    }

    public static function getByIdProductIdTab(int $productId, int $shopId, int $tabId): self
    {
        return self::repository()->findTabByProductAndSlot($productId, $shopId, $tabId) ?? new self();
    }

    public static function getByIdProduct(int $productId, int $shopId, int $langId): array
    {
        return self::repository()->findTabsByProduct($productId, $shopId, $langId);
    }

    public static function createTabForAllProducts(int $idShop, array $titles, array $contents, bool $drop = false): void
    {
        self::repository()->createTabForAllProducts($idShop, $titles, $contents, $drop);
    }
}
