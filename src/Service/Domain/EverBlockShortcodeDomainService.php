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

namespace Everblock\Tools\Service\Domain;

use Everblock\Tools\Service\EverBlockShortcodeProvider;

class EverBlockShortcodeDomainService
{
    public function __construct(private readonly EverBlockShortcodeProvider $provider)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getShortcodeForForm(int $shortcodeId, int $shopId): array
    {
        return $this->provider->getShortcodeForForm($shortcodeId, $shopId);
    }

    /**
     * @param array<int, array{title: string, content: string}> $translations
     */
    public function save(?int $shortcodeId, int $shopId, string $shortcode, array $translations): int
    {
        if (null === $shortcodeId) {
            return $this->provider->createShortcode($shortcode, $shopId, $translations);
        }

        $this->provider->updateShortcode($shortcodeId, $shortcode, $shopId, $translations);

        return $shortcodeId;
    }

    public function delete(int $shortcodeId, int $shopId): void
    {
        $this->provider->deleteShortcode($shortcodeId, $shopId);
    }

    /**
     * @param array<int, int|string> $ids
     */
    public function bulkDelete(array $ids, int $shopId): int
    {
        $deleted = 0;

        foreach ($ids as $id) {
            try {
                $this->delete((int) $id, $shopId);
                ++$deleted;
            } catch (\RuntimeException) {
                continue;
            }
        }

        return $deleted;
    }

    public function duplicate(int $shortcodeId, int $shopId, string $newShortcode): int
    {
        return $this->provider->duplicateShortcode($shortcodeId, $shopId, $newShortcode);
    }
}
