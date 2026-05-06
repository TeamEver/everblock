<?php

declare(strict_types=1);

namespace Everblock\Tools\Entity;

use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\RepositoryProvider;
use Everblock\Tools\Repository\ShortcodeRepository;
use Everblock\Tools\Service\EverblockCache;
use Language;

/**
 * @ORM\Table(name="everblock_shortcode")
 * @ORM\Entity(repositoryClass="Everblock\Tools\Repository\ShortcodeRepository")
 */
class Shortcode
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_everblock_shortcode", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public ?int $id = null;
    public ?int $id_everblock_shortcode = null;

    /** @ORM\Column(name="shortcode", type="text", nullable=true) */
    public string $shortcode = '';
    /** @ORM\Column(name="id_shop", type="integer") */
    public int $id_shop = 1;

    /** @var array<int, string>|string */
    public $title = [];
    /** @var array<int, string>|string */
    public $content = [];

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

    public static function repository(): ShortcodeRepository
    {
        /** @var ShortcodeRepository $repository */
        $repository = RepositoryProvider::get('everblock.repository.shortcode');

        return $repository;
    }

    public static function fromDatabase(array $row, array $langRows = [], ?int $singleLangId = null): self
    {
        $shortcode = new self();
        $shortcode->id = isset($row['id_everblock_shortcode']) ? (int) $row['id_everblock_shortcode'] : null;
        $shortcode->id_everblock_shortcode = $shortcode->id;
        $shortcode->shortcode = (string) ($row['shortcode'] ?? '');
        $shortcode->id_shop = (int) ($row['id_shop'] ?? 1);

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
            $shortcode->title = $titles[$singleLangId] ?? '';
            $shortcode->content = $contents[$singleLangId] ?? '';
        } else {
            $shortcode->title = $titles;
            $shortcode->content = $contents;
        }

        return $shortcode;
    }

    public function save(): bool
    {
        $this->id = self::repository()->save($this, Language::getLanguages(false));
        $this->id_everblock_shortcode = $this->id;

        return $this->id > 0;
    }

    public function delete(): bool
    {
        if (!$this->id) {
            return true;
        }

        return self::repository()->delete($this->id, $this->id_shop);
    }

    public static function getAllShortcodes(int $idShop, int $langId): array
    {
        $cacheId = 'EverblockShortcode_getAllShortcodes_' . $idShop . '_' . $langId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $shortcodes = array_map(
                static fn (array $row): self => self::fromDatabase($row, [], $langId),
                self::findAllLegacy($idShop, $langId)
            );
            EverblockCache::cacheStore($cacheId, $shortcodes);

            return $shortcodes;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getAllShortcodeIds(int $idShop): array
    {
        $cacheId = 'EverblockShortcode_getAllShortcodeIds_' . $idShop;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $ids = (array) \Db::getInstance()->executeS(
                'SELECT id_everblock_shortcode
                FROM `' . _DB_PREFIX_ . 'everblock_shortcode`
                WHERE id_shop = ' . (int) $idShop
            );
            EverblockCache::cacheStore($cacheId, $ids);

            return $ids;
        }

        return (array) EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getEverShortcode(string $shortcode, int $shopId, int $langId): string
    {
        $cacheId = 'EverblockShortcode_getEverShortcode_' . trim($shortcode) . '_' . $shopId . '_' . $langId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $content = (string) \Db::getInstance()->getValue(
                'SELECT sl.content
                FROM `' . _DB_PREFIX_ . 'everblock_shortcode` s
                INNER JOIN `' . _DB_PREFIX_ . 'everblock_shortcode_lang` sl
                    ON s.id_everblock_shortcode = sl.id_everblock_shortcode
                WHERE s.shortcode = "' . pSQL($shortcode) . '"
                  AND s.id_shop = ' . (int) $shopId . '
                  AND sl.id_lang = ' . (int) $langId
            );
            EverblockCache::cacheStore($cacheId, $content);

            return $content;
        }

        return (string) EverblockCache::cacheRetrieve($cacheId);
    }

    private static function findAllLegacy(int $idShop, int $langId): array
    {
        return (array) \Db::getInstance()->executeS(
            'SELECT s.*, sl.title, sl.content, sl.id_lang
            FROM `' . _DB_PREFIX_ . 'everblock_shortcode` s
            INNER JOIN `' . _DB_PREFIX_ . 'everblock_shortcode_lang` sl
                ON s.id_everblock_shortcode = sl.id_everblock_shortcode
               AND sl.id_lang = ' . (int) $langId . '
            WHERE s.id_shop = ' . (int) $idShop . '
            ORDER BY s.shortcode ASC'
        );
    }
}
