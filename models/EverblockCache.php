<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockCache extends ObjectModel
{
    /**
     * Get module configuration from cache, store it if not stored on cache
     * Do NOT store confidential informations
     * @param configuration key
     * @return configuration value
    */
    public static function getModuleConfiguration(string $key): string
    {
        static::createCacheDir();
        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return '';
        }
        if (!static::isCacheStored($key)) {
            $value = Configuration::get($key);
            static::cacheStore($key, $value);
        }
        return static::cacheRetrieve($key);
    }

    /**
     * Check if cache exists based on unique key
     * No cache on admin, no cache if PS cache is disabled
     * @return bool
    */
    public static function isCacheStored(string $cacheKey): bool
    {
        static::createCacheDir();
        $context = Context::getContext();
        $cacheFilePath = _PS_CACHE_DIR_ . 'everblock/' . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            return true;
        }
        return false;
    }

    /**
     * Store data on file, on Prestashop cache folder
     * Do NOT store confidential informations
     * @param cacheKey, must be unique
     * @param value to store
    */
    public static function cacheStore(string $cacheKey, $cacheValue)
    {
        static::createCacheDir();
        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return;
        }
        $cacheFilePath = _PS_CACHE_DIR_ .'everblock/' . $cacheKey . '.cache';
        return file_put_contents($cacheFilePath, serialize($cacheValue));
    }

    /**
     * Retrieve value from cache
     * @param cache key
     * @return cache content
    */
    public static function cacheRetrieve(string $cacheKey)
    {
        static::createCacheDir();
        $cacheFilePath = _PS_CACHE_DIR_ . 'everblock/' . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            $cachedData = Tools::file_get_contents($cacheFilePath);
            return unserialize($cachedData);
        }
        return '';
    }

    public static function cacheDrop(string $cacheKey)
    {
        static::createCacheDir();
        $cacheFilePath = _PS_CACHE_DIR_ . 'everblock/' . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            unlink($cacheFilePath);
        }
    }

    public static function cacheDropByPattern(string $cacheKeyStart)
    {
        static::createCacheDir();
        $cacheDir = _PS_CACHE_DIR_ . 'everblock/';
        $pattern = $cacheDir . $cacheKeyStart . '*.cache';
        $matchingFiles = glob($pattern);
        if (!empty($matchingFiles)) {
            foreach ($matchingFiles as $file) {
                unlink($file);
            }
        }
    }

    protected static function createCacheDir()
    {
        try {
            $cacheDir = _PS_CACHE_DIR_ . 'everblock';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Unable to create Everblock cache dir');
        }
    }
}
