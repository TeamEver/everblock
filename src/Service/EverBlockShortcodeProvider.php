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

namespace Everblock\Tools\Service;

use ArrayObject;
use Everblock\Tools\Repository\EverBlockShortcodeRepository;
use RuntimeException;

class EverBlockShortcodeProvider
{
    public function __construct(private readonly EverBlockShortcodeRepository $repository)
    {
    }

    /**
     * @return ArrayObject<int, mixed>[]
     */
    public function getAllShortcodes(int $shopId, int $languageId): array
    {
        $rows = $this->repository->getAllShortcodes($shopId, $languageId);
        $shortcodes = [];
        foreach ($rows as $row) {
            $shortcodes[] = new ArrayObject([
                'id_everblock_shortcode' => isset($row['id_everblock_shortcode']) ? (int) $row['id_everblock_shortcode'] : 0,
                'id_shop' => isset($row['id_shop']) ? (int) $row['id_shop'] : 0,
                'id_lang' => isset($row['id_lang']) ? (int) $row['id_lang'] : 0,
                'shortcode' => (string) ($row['shortcode'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'content' => $row['content'] ?? '',
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        return $shortcodes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllShortcodeIds(int $shopId): array
    {
        return $this->repository->getAllShortcodeIds($shopId);
    }

    public function getEverShortcode(string $shortcode, int $shopId, int $languageId): string
    {
        return $this->repository->getEverShortcode($shortcode, $shopId, $languageId);
    }

    /**
     * @return array<string, mixed>
     */
    public function getShortcodeForForm(int $shortcodeId, int $shopId): array
    {
        $row = $this->repository->getShortcodeForForm($shortcodeId, $shopId);
        if (null === $row) {
            throw new \RuntimeException('The requested shortcode cannot be found.');
        }

        $titles = [];
        $contents = [];
        foreach ($row['translations'] as $langId => $translation) {
            $titles[$langId] = (string) ($translation['title'] ?? '');
            $contents[$langId] = $translation['content'] ?? '';
        }

        return [
            'id_everblock_shortcode' => $row['id_everblock_shortcode'],
            'id_shop' => $row['id_shop'],
            'shortcode' => $row['shortcode'],
            'title' => $titles,
            'content' => $contents,
            'translations' => $row['translations'],
        ];
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->repository->clearCacheForShop($shopId);
    }

    public function clearCacheForShortcode(string $shortcode, int $shopId): void
    {
        $this->repository->clearCacheForShortcode($shortcode, $shopId);
    }

    /**
     * @param array<int, array{title: string, content: string}> $translations
     */
    public function createShortcode(string $shortcode, int $shopId, array $translations): int
    {
        $id = $this->repository->createShortcode($shortcode, $shopId, $translations);

        $this->clearCacheForShop($shopId);
        $this->clearCacheForShortcode($shortcode, $shopId);

        return $id;
    }

    /**
     * @param array<int, array{title: string, content: string}> $translations
     */
    public function updateShortcode(int $shortcodeId, string $shortcode, int $shopId, array $translations): void
    {
        $existing = $this->repository->findShortcode($shortcodeId, $shopId);
        if (null === $existing) {
            throw new RuntimeException('The requested shortcode cannot be found.');
        }

        $this->repository->updateShortcode($shortcodeId, $shortcode, $shopId, $translations);

        $this->clearCacheForShop($shopId);
        $this->clearCacheForShortcode($shortcode, $shopId);
        if ($existing['shortcode'] !== $shortcode) {
            $this->clearCacheForShortcode($existing['shortcode'], $shopId);
        }
    }

    public function deleteShortcode(int $shortcodeId, int $shopId): void
    {
        $existing = $this->repository->findShortcode($shortcodeId, $shopId);
        if (null === $existing) {
            throw new RuntimeException('The requested shortcode cannot be found.');
        }

        $this->repository->deleteShortcode($shortcodeId, $shopId);

        $this->clearCacheForShop($shopId);
        $this->clearCacheForShortcode($existing['shortcode'], $shopId);
    }

    public function duplicateShortcode(int $shortcodeId, int $shopId, string $newShortcode): int
    {
        $shortcode = $this->repository->getShortcodeForForm($shortcodeId, $shopId);
        if (null === $shortcode) {
            throw new RuntimeException('The requested shortcode cannot be found.');
        }

        $translations = [];
        foreach ($shortcode['translations'] as $languageId => $translation) {
            $translations[(int) $languageId] = [
                'title' => (string) ($translation['title'] ?? ''),
                'content' => $translation['content'] ?? '',
            ];
        }

        return $this->createShortcode($newShortcode, $shopId, $translations);
    }
}
