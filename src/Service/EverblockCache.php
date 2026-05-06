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

use Cache;
use Configuration;
use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockCache
{
    /** @var array<string, mixed> */
    private static array $runtimeCache = [];
    /** @var array<string, true> */
    private static array $runtimeStored = [];

    private const MODULE_CACHE_PREFIXES = [
        'everblock',
        'Everblock',
        'EverBlock',
        'fetchInstagramImages',
        'generateLoremIpsum_',
        'getAccessoriesShortcode_',
        'getBestSalesShortcode_',
        'getCrossSellingShortcode_',
        'getLinkedProductsShortcode_',
        'store_coordinates_',
    ];

    public static function getModuleConfiguration(string $key): string
    {
        $context = Context::getContext();
        $controllerType = is_object($context->controller ?? null) && isset($context->controller->controller_type)
            ? (string) $context->controller->controller_type
            : '';

        if ($controllerType === 'admin' || $controllerType === 'moduleadmin') {
            return '';
        }

        if (!self::isCacheStored($key)) {
            self::cacheStore($key, Configuration::get($key));
        }

        return (string) self::cacheRetrieve($key);
    }

    public static function isCacheStored(string $cacheKey): bool
    {
        if (isset(self::$runtimeStored[$cacheKey])) {
            return true;
        }

        if (!Cache::isStored($cacheKey)) {
            return false;
        }

        self::$runtimeStored[$cacheKey] = true;

        return true;
    }

    public static function cacheStore(string $cacheKey, $cacheValue): void
    {
        self::$runtimeCache[$cacheKey] = $cacheValue;
        self::$runtimeStored[$cacheKey] = true;
        Cache::store($cacheKey, $cacheValue);
    }

    public static function cacheRetrieve(string $cacheKey)
    {
        if (array_key_exists($cacheKey, self::$runtimeCache)) {
            return self::$runtimeCache[$cacheKey];
        }

        if (!Cache::isStored($cacheKey)) {
            return '';
        }

        $value = Cache::retrieve($cacheKey);
        self::$runtimeCache[$cacheKey] = $value;
        self::$runtimeStored[$cacheKey] = true;

        return $value;
    }

    public static function cacheDrop(string $cacheKey): void
    {
        unset(self::$runtimeCache[$cacheKey], self::$runtimeStored[$cacheKey]);
        Cache::clean($cacheKey);
    }

    public static function cacheDropByPattern(string $cacheKeyStart): void
    {
        $prefix = rtrim($cacheKeyStart, '*');

        foreach (array_keys(self::$runtimeStored) as $cacheKey) {
            if ($prefix === '' || str_starts_with($cacheKey, $prefix)) {
                unset(self::$runtimeStored[$cacheKey]);
            }
        }
        foreach (array_keys(self::$runtimeCache) as $cacheKey) {
            if ($prefix === '' || str_starts_with($cacheKey, $prefix)) {
                unset(self::$runtimeCache[$cacheKey]);
            }
        }

        Cache::clean($cacheKeyStart);
        Cache::clean($prefix . '*');
    }

    public static function getObjectCacheVersion(string $objectType, int $objectId): int
    {
        if ($objectId <= 0) {
            return 1;
        }

        $cacheKey = self::buildObjectVersionCacheKey($objectType, $objectId);
        if (!self::isCacheStored($cacheKey)) {
            self::cacheStore($cacheKey, 1);

            return 1;
        }

        return max(1, (int) self::cacheRetrieve($cacheKey));
    }

    public static function refreshObjectCacheVersion(string $objectType, int $objectId): void
    {
        if ($objectId <= 0) {
            return;
        }

        $cacheKey = self::buildObjectVersionCacheKey($objectType, $objectId);
        $version = self::isCacheStored($cacheKey) ? (int) self::cacheRetrieve($cacheKey) : 0;
        self::cacheStore($cacheKey, $version + 1);
    }

    private static function buildObjectVersionCacheKey(string $objectType, int $objectId): string
    {
        return 'everblock-version-' . preg_replace('/[^A-Za-z0-9_]/', '_', $objectType) . '-' . $objectId;
    }

    public static function clearAllModuleCache(): void
    {
        foreach (self::MODULE_CACHE_PREFIXES as $prefix) {
            self::cacheDropByPattern($prefix);
        }
    }
}
