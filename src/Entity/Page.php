<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

use Configuration;
use Context;
use Customer;
use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\PageRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Service\EverblockCache;
use Language;
use Tools;

/**
 * @ORM\Table(name="everblock_page")
 * @ORM\Entity(repositoryClass="Everblock\Tools\Repository\PageRepository")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_everblock_page", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;
    public ?int $id_everblock_page = null;

    /** @ORM\Column(name="id_shop", type="integer") */
    public int $id_shop = 1;
    /** @ORM\Column(name="groups", type="text", nullable=true) */
    public ?string $groups = null;
    /** @ORM\Column(name="cover_image", type="string", length=255, nullable=true) */
    public ?string $cover_image = null;
    /** @ORM\Column(name="active", type="boolean") */
    public bool $active = true;
    /** @ORM\Column(name="position", type="integer") */
    public int $position = 0;
    /** @ORM\Column(name="date_add", type="datetime", nullable=true) */
    public ?string $date_add = null;
    /** @ORM\Column(name="date_upd", type="datetime", nullable=true) */
    public ?string $date_upd = null;

    /** @var array<int, string>|string */
    public $name = [];
    /** @var array<int, string>|string */
    public $title = [];
    /** @var array<int, string>|string */
    public $meta_description = [];
    /** @var array<int, string>|string */
    public $short_description = [];
    /** @var array<int, string>|string */
    public $link_rewrite = [];
    /** @var array<int, string>|string */
    public $content = [];
    public array $cover_image_data = [];

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

    public static function repository(): PageRepository
    {
        /** @var PageRepository $repository */
        $repository = RepositoryProvider::get('everblock.repository.page');

        return $repository;
    }

    public static function fromDatabase(array $row, array $langRows = [], ?int $singleLangId = null): self
    {
        $page = new self();
        $page->id = isset($row['id_everblock_page']) ? (int) $row['id_everblock_page'] : null;
        $page->id_everblock_page = $page->id;
        $page->id_shop = (int) ($row['id_shop'] ?? 1);
        $page->groups = $row['groups'] ?? null;
        $page->cover_image = $row['cover_image'] ?? null;
        $page->active = (bool) ($row['active'] ?? true);
        $page->position = (int) ($row['position'] ?? 0);
        $page->date_add = $row['date_add'] ?? null;
        $page->date_upd = $row['date_upd'] ?? null;

        if (isset($row['id_lang'])) {
            $langRows[] = $row;
        }

        $fields = [
            'name' => [],
            'title' => [],
            'meta_description' => [],
            'short_description' => [],
            'link_rewrite' => [],
            'content' => [],
        ];
        foreach ($langRows as $langRow) {
            $langId = (int) ($langRow['id_lang'] ?? 0);
            if ($langId <= 0) {
                continue;
            }
            foreach ($fields as $field => $values) {
                $fields[$field][$langId] = (string) ($langRow[$field] ?? '');
            }
        }

        foreach ($fields as $field => $values) {
            $page->{$field} = $singleLangId !== null && $singleLangId > 0 ? ($values[$singleLangId] ?? '') : $values;
        }

        return $page;
    }

    public function getAllowedGroups(): array
    {
        if (!$this->groups) {
            return [];
        }

        $decoded = json_decode($this->groups, true);

        return is_array($decoded) ? array_values(array_unique(array_map('intval', $decoded))) : [];
    }

    public function getCoverImageData(Context $context): array
    {
        $alt = is_string($this->title) && trim($this->title) !== ''
            ? $this->title
            : (string) Configuration::get('PS_SHOP_NAME');

        if (!$this->cover_image) {
            return [
                'url' => '',
                'width' => 0,
                'height' => 0,
                'alt' => $alt,
            ];
        }

        $imagePath = _PS_IMG_DIR_ . 'pages/' . $this->cover_image;
        $width = 1200;
        $height = 675;
        if (is_file($imagePath)) {
            $size = @getimagesize($imagePath);
            if ($size) {
                $width = (int) $size[0];
                $height = (int) $size[1];
            }
        }

        return [
            'url' => $context->link->getMediaLink(_PS_IMG_ . 'pages/' . $this->cover_image),
            'width' => $width,
            'height' => $height,
            'alt' => $alt,
        ];
    }

    public function save(): bool
    {
        if ($this->position <= 0) {
            $this->position = self::getNextPosition($this->id_shop);
        }
        $this->sanitizeLinkRewrite();
        $this->id = self::repository()->save($this, Language::getLanguages(false));
        $this->id_everblock_page = $this->id;

        return $this->id > 0;
    }

    public function delete(): bool
    {
        if (!$this->id) {
            return true;
        }

        return self::repository()->delete($this->id, $this->id_shop);
    }

    public static function getPages(int $langId, ?int $shopId = null, bool $onlyActive = true, array $allowedGroups = [], int $page = 1, ?int $perPage = null): array
    {
        $shopId = self::resolveShopId($shopId);
        $cacheId = 'EverblockPage_getPages_' . $langId . '_' . $shopId . '_' . (int) $onlyActive . '_' . md5(json_encode($allowedGroups)) . '_' . $page . '_' . (int) $perPage;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $pages = array_values(array_filter(
                self::repository()->findPages($langId, $shopId, $onlyActive, $page, $perPage),
                static fn (self $page): bool => self::isGroupAllowed($page, $allowedGroups)
            ));
            EverblockCache::cacheStore($cacheId, $pages);

            return $pages;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function countPages(int $langId, ?int $shopId = null, bool $onlyActive = true, array $allowedGroups = []): int
    {
        $shopId = self::resolveShopId($shopId);
        $cacheId = 'EverblockPage_countPages_' . $langId . '_' . $shopId . '_' . (int) $onlyActive . '_' . md5(json_encode($allowedGroups));
        if (!EverblockCache::isCacheStored($cacheId)) {
            $count = count(array_filter(
                self::repository()->findPages($langId, $shopId, $onlyActive),
                static fn (self $page): bool => self::isGroupAllowed($page, $allowedGroups)
            ));
            EverblockCache::cacheStore($cacheId, $count);

            return $count;
        }

        return (int) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getById(int $pageId, int $langId, ?int $shopId = null): ?self
    {
        $shopId = self::resolveShopId($shopId);
        $cacheId = 'EverblockPage_getById_' . $pageId . '_' . $langId . '_' . $shopId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $page = self::repository()->find($pageId, $shopId, $langId);
            EverblockCache::cacheStore($cacheId, $page);

            return $page;
        }

        $page = EverblockCache::cacheRetrieve($cacheId);

        return $page instanceof self ? $page : null;
    }

    public static function isGroupAllowed(self $page, array $customerGroups = []): bool
    {
        $allowed = $page->getAllowedGroups();

        return empty($allowed) || !empty(array_intersect($allowed, $customerGroups));
    }

    public static function getNextPosition(int $shopId): int
    {
        return self::repository()->getNextPosition(self::resolveShopId($shopId));
    }

    public static function getCustomerGroups(Context $context): array
    {
        if ($context->customer && $context->customer->id) {
            return Customer::getGroupsStatic((int) $context->customer->id);
        }

        return array_values(array_unique(array_filter([
            (int) Configuration::get('PS_UNIDENTIFIED_GROUP'),
            (int) Configuration::get('PS_GUEST_GROUP'),
            (int) Configuration::get('PS_CUSTOMER_GROUP'),
        ])));
    }

    private static function resolveShopId(?int $shopId = null): int
    {
        return $shopId !== null && $shopId > 0 ? $shopId : (int) Context::getContext()->shop->id;
    }

    private function sanitizeLinkRewrite(): void
    {
        if (!is_array($this->link_rewrite)) {
            return;
        }

        foreach (Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $rewrite = $this->link_rewrite[$langId] ?? '';
            if (!$rewrite && is_array($this->name)) {
                $rewrite = $this->name[$langId] ?? '';
            }
            $this->link_rewrite[$langId] = Tools::link_rewrite((string) $rewrite);
        }
    }
}
