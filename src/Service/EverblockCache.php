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

/**
 * Cache d'Ever Block, sur deux niveaux.
 *
 * 1. Mémoire de la requête (toujours) : Cache::store() de PrestaShop ne vit que le temps de
 *    la requête.
 * 2. Cache persistant (Cache::getInstance(), donc Redis via qcdredis, Memcached...) quand le
 *    cache PrestaShop est activé, pour les seules clés de PERSISTENT_PREFIXES : des données
 *    qui ne dépendent ni du client ni de son groupe (lignes de blocs, HTML brut des blocs avant
 *    shortcodes, listes d'ID produits, avis Google, médias Instagram), et seulement si la valeur
 *    est faite de scalaires et de tableaux. Les produits présentés (prix du groupe client) et
 *    les objets restent en mémoire de requête.
 *
 * Invalidation : toute suppression (cacheDrop, cacheDropByPattern, clearAllModuleCache)
 * incrémente une génération globale qui préfixe les clés persistantes ; les anciennes entrées
 * expirent seules (PERSISTENT_TTL). Les suppressions ne viennent que du back-office.
 */
class EverblockCache
{
    /** Clés autorisées dans le cache persistant (préfixes). */
    private const PERSISTENT_PREFIXES = [
        'EverBlockClass_getBlocks_',
        'EverBlockClass_getAllBlocks_',
        'everblock-block-',
        'everblock-version-',
        'getCrossSellingShortcode_',
        'getBestSalesShortcode_',
        'getLinkedProductsShortcode_',
        'getAccessoriesShortcode_',
        'everblock_getProductsByFeature_',
        'everblock_getProductsByFeatureValue_',
        'everblock_bestSellingProductIds_',
        'everblock_google_reviews_',
        'everblockFlags_',
        'fetchInstagramImages',
    ];
    private const PERSISTENT_TTL = 3600;
    private const GENERATION_KEY = 'EVERBLOCK_CACHE_GENERATION';

    /** @var array<string, mixed> */
    private static array $runtimeCache = [];
    /** @var array<string, true> */
    private static array $runtimeStored = [];
    /** @var array<string, true> clés absentes du cache persistant (évite de redemander) */
    private static array $persistentMisses = [];
    private static ?int $generation = null;

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

        if (Cache::isStored($cacheKey)) {
            self::$runtimeStored[$cacheKey] = true;

            return true;
        }

        return self::loadPersistent($cacheKey);
    }

    public static function cacheStore(string $cacheKey, $cacheValue): void
    {
        self::$runtimeCache[$cacheKey] = $cacheValue;
        self::$runtimeStored[$cacheKey] = true;
        Cache::store($cacheKey, $cacheValue);

        if (self::isPersistable($cacheKey, $cacheValue)) {
            try {
                Cache::getInstance()->set(self::persistentKey($cacheKey), $cacheValue, self::PERSISTENT_TTL);
                unset(self::$persistentMisses[$cacheKey]);
            } catch (\Throwable) {
                // cache persistant indisponible : la mémoire de requête suffit
            }
        }
    }

    public static function cacheRetrieve(string $cacheKey)
    {
        if (array_key_exists($cacheKey, self::$runtimeCache)) {
            return self::$runtimeCache[$cacheKey];
        }

        if (!Cache::isStored($cacheKey)) {
            return self::loadPersistent($cacheKey) ? self::$runtimeCache[$cacheKey] : '';
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
        self::bumpGeneration();
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
        self::bumpGeneration();
    }

    private static function isPersistable(string $cacheKey, mixed $value): bool
    {
        if (!self::persistentEnabled()) {
            return false;
        }
        foreach (self::PERSISTENT_PREFIXES as $prefix) {
            if (str_starts_with($cacheKey, $prefix)) {
                return self::isPlain($value);
            }
        }

        return false;
    }

    private static function isPlain(mixed $value, int $depth = 0): bool
    {
        if ($value === null || is_scalar($value)) {
            return true;
        }
        if (!is_array($value) || $depth > 6) {
            return false;
        }
        foreach ($value as $item) {
            if (!self::isPlain($item, $depth + 1)) {
                return false;
            }
        }

        return true;
    }

    private static function loadPersistent(string $cacheKey): bool
    {
        if (isset(self::$persistentMisses[$cacheKey]) || !self::persistentEnabled() || !self::isPersistable($cacheKey, null)) {
            return false;
        }
        try {
            $value = Cache::getInstance()->get(self::persistentKey($cacheKey));
        } catch (\Throwable) {
            $value = false;
        }
        if ($value === false) {
            self::$persistentMisses[$cacheKey] = true;

            return false;
        }
        self::$runtimeCache[$cacheKey] = $value;
        self::$runtimeStored[$cacheKey] = true;
        Cache::store($cacheKey, $value);

        return true;
    }

    private static function persistentEnabled(): bool
    {
        return defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_;
    }

    private static function persistentKey(string $cacheKey): string
    {
        if (self::$generation === null) {
            self::$generation = max(1, (int) Configuration::getGlobalValue(self::GENERATION_KEY));
        }

        return 'everblock_' . self::$generation . '_' . md5($cacheKey);
    }

    private static function bumpGeneration(): void
    {
        self::$persistentMisses = [];
        if (!self::persistentEnabled()) {
            return;
        }
        $next = max(1, (int) Configuration::getGlobalValue(self::GENERATION_KEY)) + 1;
        Configuration::updateGlobalValue(self::GENERATION_KEY, $next);
        self::$generation = $next;
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
