<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\ProductContentRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Service\EverblockCache;
use Language;

/**
 * @ORM\Table(name="everblock_flags")
 * @ORM\Entity(repositoryClass="Everblock\Tools\Repository\ProductContentRepository")
 */
class ProductFlag
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_everblock_flags", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;
    public ?int $id_everblock_flags = null;

    /** @ORM\Column(name="id_product", type="integer") */
    public int $id_product = 0;
    /** @ORM\Column(name="id_shop", type="integer", nullable=true) */
    public int $id_shop = 0;
    /** @ORM\Column(name="id_flag", type="integer", nullable=true) */
    public int $id_flag = 0;

    /** @var array<int, string>|string */
    public $title = [];
    /** @var array<int, string>|string */
    public $content = [];

    public function __construct(?int $id = null, ?int $idLang = null, ?int $idShop = null)
    {
        if ($id !== null && $id > 0) {
            $loaded = self::repository()->findFlag($id, $idLang);
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
        $flag = new self();
        $flag->id = isset($row['id_everblock_flags']) ? (int) $row['id_everblock_flags'] : null;
        $flag->id_everblock_flags = $flag->id;
        $flag->id_product = (int) ($row['id_product'] ?? 0);
        $flag->id_shop = (int) ($row['id_shop'] ?? 0);
        $flag->id_flag = (int) ($row['id_flag'] ?? 0);
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
        $flag->title = $singleLangId ? ($titles[$singleLangId] ?? '') : $titles;
        $flag->content = $singleLangId ? ($contents[$singleLangId] ?? '') : $contents;

        return $flag;
    }

    public function save(): bool
    {
        $this->id = self::repository()->saveFlag($this, Language::getLanguages(false));
        $this->id_everblock_flags = $this->id;

        return $this->id > 0;
    }

    public function delete(): bool
    {
        return !$this->id || self::repository()->deleteFlag($this->id);
    }

    public static function getByIdProductInAdmin(int $productId, int $shopId): array
    {
        return self::repository()->findFlagsByProduct($productId, $shopId, null);
    }

    public static function getByIdProductIdFlag(int $productId, int $shopId, int $flagId): self
    {
        return self::repository()->findFlagByProductAndSlot($productId, $shopId, $flagId) ?? new self();
    }

    public static function getByIdProduct(int $productId, int $shopId, int $langId): array
    {
        $cacheId = 'EverblockFlagsClass_getByIdProduct_' . $productId . '_' . $shopId . '_' . $langId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $flags = self::repository()->findFlagsByProduct($productId, $shopId, $langId);
            EverblockCache::cacheStore($cacheId, $flags);

            return $flags;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }
}
