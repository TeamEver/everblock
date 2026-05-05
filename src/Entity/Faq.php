<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

use Context;
use Customer;
use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\FaqRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Service\EverblockCache;
use Language;

/**
 * @ORM\Table(name="everblock_faq")
 * @ORM\Entity(repositoryClass="Everblock\Tools\Repository\FaqRepository")
 */
class Faq
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_everblock_faq", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;
    public ?int $id_everblock_faq = null;

    /** @ORM\Column(name="id_shop", type="integer") */
    public int $id_shop = 1;
    /** @ORM\Column(name="tag_name", type="text", nullable=true) */
    public string $tag_name = '';
    /** @ORM\Column(name="position", type="integer") */
    public int $position = 0;
    /** @ORM\Column(name="active", type="boolean") */
    public bool $active = true;
    /** @ORM\Column(name="date_add", type="datetime", nullable=true) */
    public ?string $date_add = null;
    /** @ORM\Column(name="date_upd", type="datetime", nullable=true) */
    public ?string $date_upd = null;

    /** @var array<int, string>|string */
    public $title = [];
    /** @var array<int, string>|string */
    public $content = [];
    public ?string $tag_link = null;

    public function __construct(?int $id = null, ?int $idLang = null, ?int $idShop = null)
    {
        if ($id !== null && $id > 0) {
            $loaded = self::repository()->find($id, $idShop, $idLang);
            if ($loaded instanceof self) {
                foreach (get_object_vars($loaded) as $property => $value) {
                    $this->{$property} = $value;
                }
            }
        }
    }

    public static function repository(): FaqRepository
    {
        /** @var FaqRepository $repository */
        $repository = RepositoryProvider::get('everblock.repository.faq');

        return $repository;
    }

    public static function fromDatabase(array $row, array $langRows = [], ?int $singleLangId = null): self
    {
        $faq = new self();
        $faq->id = isset($row['id_everblock_faq']) ? (int) $row['id_everblock_faq'] : null;
        $faq->id_everblock_faq = $faq->id;
        $faq->id_shop = (int) ($row['id_shop'] ?? 1);
        $faq->tag_name = (string) ($row['tag_name'] ?? '');
        $faq->position = (int) ($row['position'] ?? 0);
        $faq->active = (bool) ($row['active'] ?? false);
        $faq->date_add = $row['date_add'] ?? null;
        $faq->date_upd = $row['date_upd'] ?? null;

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

        if ($singleLangId !== null && $singleLangId > 0) {
            $faq->title = $titles[$singleLangId] ?? '';
            $faq->content = $contents[$singleLangId] ?? '';
        } else {
            $faq->title = $titles;
            $faq->content = $contents;
        }

        return $faq;
    }

    public function save(): bool
    {
        $this->id = self::repository()->save($this, Language::getLanguages(false));
        $this->id_everblock_faq = $this->id;

        return $this->id > 0;
    }

    public function delete(): bool
    {
        if (!$this->id) {
            return true;
        }

        return self::repository()->delete($this->id, $this->id_shop);
    }

    public static function getAllFaq(int $shopId, int $langId): array
    {
        $cacheId = 'EverblockFaq_getAllFaq_' . $shopId . '_' . $langId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $faqs = self::repository()->findAllActive($shopId, $langId);
            EverblockCache::cacheStore($cacheId, $faqs);

            return $faqs;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getFaqByTagName(int $shopId, int $langId, string $tagName): array
    {
        $cacheId = 'EverblockFaq_getFaqByTagName_' . $shopId . '_' . $langId . '_' . trim($tagName);
        if (!EverblockCache::isCacheStored($cacheId)) {
            $faqs = self::repository()->findByTagName($shopId, $langId, $tagName);
            EverblockCache::cacheStore($cacheId, $faqs);

            return $faqs;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function countActiveByTagName(int $shopId, string $tagName): int
    {
        return self::repository()->countActiveByTagName($shopId, $tagName);
    }

    public static function countAllActive(int $shopId): int
    {
        return self::repository()->countAllActive($shopId);
    }

    public static function countAll(int $shopId): int
    {
        return self::repository()->countAll($shopId);
    }

    public static function getFirstActiveTagName(?int $shopId = null): ?string
    {
        $shopId = self::resolveShopId($shopId);
        $cacheId = 'EverblockFaq_getFirstActiveTagName_' . $shopId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $tagName = self::repository()->getFirstActiveTagName($shopId);
            EverblockCache::cacheStore($cacheId, $tagName);

            return $tagName;
        }

        $tagName = EverblockCache::cacheRetrieve($cacheId);

        return is_string($tagName) && $tagName !== '' ? $tagName : null;
    }

    public static function getFaqByTagNamePaginated(int $shopId, int $langId, string $tagName, int $page, int $limit): array
    {
        return self::repository()->findByTagNamePaginated($shopId, $langId, $tagName, $page, $limit);
    }

    public static function getAllActivePaginated(int $shopId, int $langId, int $page, int $limit): array
    {
        return self::repository()->findAllActivePaginated($shopId, $langId, $page, $limit);
    }

    public static function getByIds(array $faqIds, int $langId, ?int $shopId = null, bool $onlyActive = true): array
    {
        $shopId = self::resolveShopId($shopId);
        $faqIds = array_values(array_unique(array_filter(array_map('intval', $faqIds))));
        if (empty($faqIds)) {
            return [];
        }

        $cacheId = 'EverblockFaq_getByIds_' . $shopId . '_' . $langId . '_' . (int) $onlyActive . '_' . implode('-', $faqIds);
        if (!EverblockCache::isCacheStored($cacheId)) {
            $faqs = self::repository()->findByIds($faqIds, $langId, $shopId, $onlyActive);
            EverblockCache::cacheStore($cacheId, $faqs);

            return $faqs;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getFaqIdsByProduct(int $productId, ?int $shopId = null): array
    {
        $shopId = self::resolveShopId($shopId);
        $cacheId = 'EverblockFaq_getFaqIdsByProduct_' . $shopId . '_' . $productId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $ids = self::repository()->findIdsByProduct($productId, $shopId);
            EverblockCache::cacheStore($cacheId, $ids);

            return $ids;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getProductsByFaq(int $faqId, ?int $shopId = null): array
    {
        $shopId = self::resolveShopId($shopId);
        $cacheId = 'EverblockFaq_getProductsByFaq_' . $shopId . '_' . $faqId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $products = self::repository()->findProductsByFaq($faqId, $shopId);
            EverblockCache::cacheStore($cacheId, $products);

            return $products;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function linkToProduct(int $faqId, int $productId, ?int $shopId = null, ?int $position = null): bool
    {
        $shopId = self::resolveShopId($shopId);
        $result = self::repository()->linkToProduct($faqId, $productId, $shopId, $position);
        self::clearRelationCaches($shopId, [$productId], [$faqId]);

        return $result;
    }

    public static function unlinkProductFaqs(int $productId, ?int $shopId = null, ?array $faqIds = null): bool
    {
        $shopId = self::resolveShopId($shopId);
        $existingFaqIds = $faqIds ?? self::getFaqIdsByProduct($productId, $shopId);
        $result = self::repository()->unlinkProductFaqs($productId, $shopId, $faqIds);
        self::clearRelationCaches($shopId, [$productId], $existingFaqIds);

        return $result;
    }

    public static function searchFaqOptions(int $shopId, int $langId, string $query = '', int $page = 1, int $limit = 20): array
    {
        return self::repository()->searchOptions($shopId, $langId, $query, $page, $limit);
    }

    public static function getFaqOptionsByIds(array $faqIds, int $shopId, int $langId): array
    {
        return self::repository()->findOptionsByIds($faqIds, $shopId, $langId);
    }

    public static function invalidateRelationsForFaq(int $faqId, ?int $shopId = null): void
    {
        $shopId = self::resolveShopId($shopId);
        foreach (self::getProductsByFaq($faqId, $shopId) as $product) {
            EverblockCache::cacheDrop('EverblockFaq_getFaqIdsByProduct_' . $shopId . '_' . (int) $product['id_product']);
        }
        EverblockCache::cacheDrop('EverblockFaq_getProductsByFaq_' . $shopId . '_' . $faqId);
    }

    public static function resolveShopId(?int $shopId = null): int
    {
        if ($shopId !== null && $shopId > 0) {
            return $shopId;
        }

        return (int) Context::getContext()->shop->id;
    }

    private static function clearRelationCaches(int $shopId, array $productIds = [], array $faqIds = []): void
    {
        foreach (array_unique(array_filter(array_map('intval', $productIds))) as $productId) {
            EverblockCache::cacheDrop('EverblockFaq_getFaqIdsByProduct_' . $shopId . '_' . $productId);
        }
        foreach (array_unique(array_filter(array_map('intval', $faqIds))) as $faqId) {
            EverblockCache::cacheDrop('EverblockFaq_getProductsByFaq_' . $shopId . '_' . $faqId);
        }
        EverblockCache::cacheDropByPattern('EverblockFaq_getByIds_');
    }
}
