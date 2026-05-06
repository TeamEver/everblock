<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Service\EverblockCache;
use Language;

/**
 * @ORM\Table(name="everblock")
 * @ORM\Entity(repositoryClass="Everblock\Tools\Repository\BlockRepository")
 */
class Block
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_everblock", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;
    public ?int $id_everblock = null;

    /** @ORM\Column(name="name", type="text") */
    public string $name = '';
    /** @ORM\Column(name="id_hook", type="integer") */
    public int $id_hook = 0;
    /** @ORM\Column(name="only_home", type="boolean", nullable=true) */
    public bool $only_home = false;
    /** @ORM\Column(name="only_category", type="boolean", nullable=true) */
    public bool $only_category = false;
    /** @ORM\Column(name="only_category_product", type="boolean", nullable=true) */
    public bool $only_category_product = false;
    /** @ORM\Column(name="only_manufacturer", type="boolean", nullable=true) */
    public bool $only_manufacturer = false;
    /** @ORM\Column(name="only_supplier", type="boolean", nullable=true) */
    public bool $only_supplier = false;
    /** @ORM\Column(name="only_cms_category", type="boolean", nullable=true) */
    public bool $only_cms_category = false;
    /** @ORM\Column(name="obfuscate_link", type="boolean", nullable=true) */
    public bool $obfuscate_link = false;
    /** @ORM\Column(name="add_container", type="boolean", nullable=true) */
    public bool $add_container = false;
    /** @ORM\Column(name="lazyload", type="boolean", nullable=true) */
    public bool $lazyload = false;
    /** @ORM\Column(name="device", type="integer") */
    public int $device = 0;
    /** @ORM\Column(name="id_shop", type="integer") */
    public int $id_shop = 1;
    /** @ORM\Column(name="position", type="integer", nullable=true) */
    public int $position = 0;
    /** @ORM\Column(name="categories", type="text", nullable=true) */
    public ?string $categories = null;
    /** @ORM\Column(name="manufacturers", type="text", nullable=true) */
    public ?string $manufacturers = null;
    /** @ORM\Column(name="suppliers", type="text", nullable=true) */
    public ?string $suppliers = null;
    /** @ORM\Column(name="cms_categories", type="text", nullable=true) */
    public ?string $cms_categories = null;
    /** @ORM\Column(name="groups", type="text", nullable=true) */
    public ?string $groups = null;
    /** @ORM\Column(name="background", type="string", length=255, nullable=true) */
    public ?string $background = null;
    /** @ORM\Column(name="css_class", type="string", length=255, nullable=true) */
    public ?string $css_class = null;
    /** @ORM\Column(name="data_attribute", type="string", length=255, nullable=true) */
    public ?string $data_attribute = null;
    /** @ORM\Column(name="bootstrap_class", type="string", length=255, nullable=true) */
    public ?string $bootstrap_class = null;
    /** @ORM\Column(name="modal", type="boolean") */
    public bool $modal = false;
    /** @ORM\Column(name="delay", type="integer") */
    public int $delay = 0;
    /** @ORM\Column(name="timeout", type="integer") */
    public int $timeout = 0;
    /** @ORM\Column(name="date_start", type="datetime", nullable=true) */
    public ?string $date_start = null;
    /** @ORM\Column(name="date_end", type="datetime", nullable=true) */
    public ?string $date_end = null;
    /** @ORM\Column(name="active", type="boolean") */
    public bool $active = false;

    /** @var array<int, string> */
    public array $content = [];
    /** @var array<int, string> */
    public array $custom_code = [];

    public function __construct(?int $id = null, ?int $idLang = null, ?int $idShop = null)
    {
        if ($id !== null && $id > 0) {
            try {
                $loaded = self::repository()->find($id, $idShop, $idLang);
            } catch (\RuntimeException $exception) {
                $loaded = self::findOneLegacy($id, $idShop, $idLang);
            }
            if ($loaded instanceof self) {
                $this->copyFrom($loaded);
            }
        }
    }

    public static function repository(): BlockRepository
    {
        /** @var BlockRepository $repository */
        $repository = RepositoryProvider::get('everblock.repository.block');

        return $repository;
    }

    public static function fromDatabase(array $row, array $langRows = []): self
    {
        $block = new self();
        $block->id = isset($row['id_everblock']) ? (int) $row['id_everblock'] : null;
        $block->id_everblock = $block->id;
        $block->name = (string) ($row['name'] ?? '');
        $block->id_hook = (int) ($row['id_hook'] ?? 0);
        $block->only_home = (bool) ($row['only_home'] ?? false);
        $block->only_category = (bool) ($row['only_category'] ?? false);
        $block->only_category_product = (bool) ($row['only_category_product'] ?? false);
        $block->only_manufacturer = (bool) ($row['only_manufacturer'] ?? false);
        $block->only_supplier = (bool) ($row['only_supplier'] ?? false);
        $block->only_cms_category = (bool) ($row['only_cms_category'] ?? false);
        $block->obfuscate_link = (bool) ($row['obfuscate_link'] ?? false);
        $block->add_container = (bool) ($row['add_container'] ?? false);
        $block->lazyload = (bool) ($row['lazyload'] ?? false);
        $block->device = (int) ($row['device'] ?? 0);
        $block->id_shop = (int) ($row['id_shop'] ?? 1);
        $block->position = (int) ($row['position'] ?? 0);
        $block->categories = self::nullableString($row['categories'] ?? null);
        $block->manufacturers = self::nullableString($row['manufacturers'] ?? null);
        $block->suppliers = self::nullableString($row['suppliers'] ?? null);
        $block->cms_categories = self::nullableString($row['cms_categories'] ?? null);
        $block->groups = self::nullableString($row['groups'] ?? null);
        $block->background = self::nullableString($row['background'] ?? null);
        $block->css_class = self::nullableString($row['css_class'] ?? null);
        $block->data_attribute = self::nullableString($row['data_attribute'] ?? null);
        $block->bootstrap_class = self::nullableString($row['bootstrap_class'] ?? null);
        $block->modal = (bool) ($row['modal'] ?? false);
        $block->delay = (int) ($row['delay'] ?? 0);
        $block->timeout = (int) ($row['timeout'] ?? 0);
        $block->date_start = self::nullableString($row['date_start'] ?? null);
        $block->date_end = self::nullableString($row['date_end'] ?? null);
        $block->active = (bool) ($row['active'] ?? false);

        if (isset($row['id_lang'])) {
            $langRows[] = $row;
        }

        foreach ($langRows as $langRow) {
            $langId = (int) ($langRow['id_lang'] ?? 0);
            if ($langId <= 0) {
                continue;
            }
            $block->content[$langId] = (string) ($langRow['content'] ?? '');
            $block->custom_code[$langId] = (string) ($langRow['custom_code'] ?? '');
        }

        return $block;
    }

    public function save(): bool
    {
        $previous = $this->id ? self::repository()->find((int) $this->id, $this->id_shop) : null;
        $this->id = self::repository()->save($this, Language::getLanguages(false));
        $this->id_everblock = $this->id;

        if ($this->id > 0) {
            $hookIds = [(int) $this->id_hook];
            if ($previous instanceof self) {
                $hookIds[] = (int) $previous->id_hook;
            }
            self::clearCache((int) $this->id, (int) $this->id_shop, Language::getLanguages(false), $hookIds);
        }

        return $this->id > 0;
    }

    public function delete(): bool
    {
        if (!$this->id) {
            return true;
        }

        $blockId = (int) $this->id;
        $hookIds = [(int) $this->id_hook];
        $deleted = self::repository()->delete($blockId, $this->id_shop);
        if ($deleted) {
            self::clearCache($blockId, (int) $this->id_shop, Language::getLanguages(false), $hookIds);
        }

        return $deleted;
    }

    public static function clearCache(?int $blockId, int $shopId, array $languages = [], array $hookIds = []): void
    {
        $languages = $languages ?: Language::getLanguages(false);
        $hookIds = array_values(array_unique(array_filter(array_map('intval', $hookIds))));

        foreach ($languages as $language) {
            $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
            if ($langId <= 0) {
                continue;
            }

            EverblockCache::cacheDrop('EverBlockClass_getAllBlocks_' . $langId . '_' . $shopId);
            foreach ($hookIds as $hookId) {
                EverblockCache::cacheDrop('EverBlockClass_getBlocks_' . $hookId . '_' . $langId . '_' . $shopId);
            }
        }

        if ($blockId !== null && $blockId > 0) {
            EverblockCache::refreshObjectCacheVersion('block', $blockId);
            EverblockCache::cacheDropByPattern('everblock-block-' . $blockId . '-');
        }

        foreach ($hookIds as $hookId) {
            EverblockCache::cacheDropByPattern('everblock-id_hook-' . $hookId);
        }
    }

    public static function getAllBlocks(int $idLang, int $idShop): array
    {
        $cacheId = 'EverBlockClass_getAllBlocks_' . $idLang . '_' . $idShop;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $blocks = self::findAllForShopLegacy($idLang, $idShop);
            EverblockCache::cacheStore($cacheId, $blocks);

            return $blocks;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function cleanBlocksCacheOnDate(int $idLang, int $idShop): void
    {
        // Block visibility dates are evaluated at render time, so no hook-wide
        // rendered cache has to be flushed on each front-office request.
    }

    public static function getBlocks(int $idHook, int $idLang, int $idShop): array
    {
        $cacheId = 'EverBlockClass_getBlocks_' . $idHook . '_' . $idLang . '_' . $idShop;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $blocks = self::findActiveForHookLegacy($idHook, $idLang, $idShop);
            foreach ($blocks as &$block) {
                $block['bootstrap_class'] = self::getBootstrapColClass((int) ($block['bootstrap_class'] ?? 0));
            }
            unset($block);
            EverblockCache::cacheStore($cacheId, $blocks);

            return $blocks;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    private static function findAllForShopLegacy(int $idLang, int $idShop): array
    {
        return (array) \Db::getInstance()->executeS(
            'SELECT b.*, bl.content, bl.custom_code
            FROM `' . _DB_PREFIX_ . 'everblock` b
            LEFT JOIN `' . _DB_PREFIX_ . 'everblock_lang` bl ON b.id_everblock = bl.id_everblock
            WHERE bl.id_lang = ' . (int) $idLang . '
              AND b.id_shop = ' . (int) $idShop . '
            ORDER BY b.position ASC'
        );
    }

    private static function findOneLegacy(int $id, ?int $idShop = null, ?int $idLang = null): ?self
    {
        $where = 'b.id_everblock = ' . (int) $id;
        if ($idShop !== null && $idShop > 0) {
            $where .= ' AND b.id_shop = ' . (int) $idShop;
        }

        $row = \Db::getInstance()->getRow(
            'SELECT b.*
            FROM `' . _DB_PREFIX_ . 'everblock` b
            WHERE ' . $where
        );
        if (!$row) {
            return null;
        }

        $langWhere = 'id_everblock = ' . (int) $id;
        if ($idLang !== null && $idLang > 0) {
            $langWhere .= ' AND id_lang = ' . (int) $idLang;
        }

        $langRows = (array) \Db::getInstance()->executeS(
            'SELECT *
            FROM `' . _DB_PREFIX_ . 'everblock_lang`
            WHERE ' . $langWhere
        );

        return self::fromDatabase($row, $langRows);
    }

    private static function findActiveForHookLegacy(int $idHook, int $idLang, int $idShop): array
    {
        return (array) \Db::getInstance()->executeS(
            'SELECT b.*, bl.content, bl.custom_code
            FROM `' . _DB_PREFIX_ . 'everblock` b
            LEFT JOIN `' . _DB_PREFIX_ . 'everblock_lang` bl ON b.id_everblock = bl.id_everblock
            WHERE b.id_hook = ' . (int) $idHook . '
              AND bl.id_lang = ' . (int) $idLang . '
              AND b.id_shop = ' . (int) $idShop . '
              AND b.active = 1
            ORDER BY b.position ASC'
        );
    }

    public static function getBootstrapColClass(int $colNumber): string
    {
        $cacheId = 'EverBlockClass_getBootstrapColClass_' . $colNumber;
        if (EverblockCache::isCacheStored($cacheId)) {
            return (string) EverblockCache::cacheRetrieve($cacheId);
        }

        $map = [
            0 => '',
            1 => 'col-12 col-md-12',
            2 => 'col-6 col-md-6',
            3 => 'col-4 col-md-4',
            4 => 'col-3 col-md-3',
            6 => 'col-2 col-md-2',
        ];
        $class = $map[$colNumber] ?? 'col-12 col-md-12';
        EverblockCache::cacheStore($cacheId, $class);

        return $class;
    }

    private static function nullableString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function copyFrom(self $block): void
    {
        foreach (get_object_vars($block) as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
