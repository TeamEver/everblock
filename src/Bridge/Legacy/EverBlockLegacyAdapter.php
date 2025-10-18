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

namespace Everblock\Tools\Bridge\Legacy;

use DateTimeImmutable;
use Everblock\Tools\Service\EverBlockProvider;

class EverBlockLegacyAdapter
{
    public function __construct(private readonly EverBlockProvider $provider)
    {
    }

    public function getAllBlocks(int $languageId, int $shopId): array
    {
        return $this->provider->getAllBlocks($languageId, $shopId);
    }

    public function cleanBlocksCacheOnDate(int $languageId, int $shopId): void
    {
        $blocks = $this->provider->getAllBlocks($languageId, $shopId);
        $now = new DateTimeImmutable();

        foreach ($blocks as $block) {
            $cacheShouldBeFlushed = false;
            $dateStart = $block['date_start'] ?? null;
            $dateEnd = $block['date_end'] ?? null;
            $formattedNow = $now->format('Y-m-d H:i:s');

            if (!empty($dateStart) && $dateStart !== '0000-00-00 00:00:00' && $dateStart > $formattedNow) {
                $cacheShouldBeFlushed = true;
            }

            if (!empty($dateEnd) && $dateEnd !== '0000-00-00 00:00:00' && $dateEnd < $formattedNow) {
                $cacheShouldBeFlushed = true;
            }

            if ($cacheShouldBeFlushed && isset($block['id_hook'])) {
                $this->provider->clearCacheForHook((int) $block['id_hook']);
            }
        }
    }

    public function getBlocks(int $hookId, int $languageId, int $shopId): array
    {
        return $this->provider->getBlocks($hookId, $languageId, $shopId);
    }

    public function getBootstrapColClass(int $colNumber): string
    {
        return $this->provider->getBootstrapColClass($colNumber);
    }

    public function clearCache(): void
    {
        $this->provider->clearCache();
    }

    public function clearCacheForHook(int $hookId): void
    {
        $this->provider->clearCacheForHook($hookId);
    }

    public function clearCacheForLanguageAndShop(int $languageId, int $shopId): void
    {
        $this->provider->clearCacheForLanguageAndShop($languageId, $shopId);
    }
}
