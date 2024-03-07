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

    public static function cleanThemeCache(): bool
    {
        // Issue #17
        return true;
        if (!defined(_PS_PARENT_THEME_DIR_) || empty(_PS_PARENT_THEME_DIR_)) {
            $themeDir = _PS_THEME_DIR_;
        } else {
            $themeDir = _PS_PARENT_THEME_DIR_ . 'assets/cache';
        }
        $cacheDir = $themeDir;
        try {
            if (file_exists($cacheDir) && is_dir($cacheDir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    // Ne supprime pas le fichier index.php
                    if ($fileinfo->getFilename() !== 'index.php') {
                        $todo($fileinfo->getRealPath());
                    }
                }

                return true;
            }

            return false;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('cleanThemeCache : ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Check if cache exists based on unique key
     * No cache on admin, no cache if PS cache is disabled
     * @return bool
    */
    public static function isCacheStored(string $cacheKey): bool
    {
        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return false;
        }
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_) {
            return false;
        }
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
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
        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return;
        }
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_) {
            return;
        }
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
        file_put_contents($cacheFilePath, serialize($cacheValue));
    }

    /**
     * Retrieve value from cache
     * @param cache key
     * @return cache content
    */
    public static function cacheRetrieve(string $cacheKey)
    {
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            $cachedData = Tools::file_get_contents($cacheFilePath);
            return unserialize($cachedData);
        }
        return '';
    }

    public static function cacheDrop(string $cacheKey)
    {
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            unlink($cacheFilePath);
        }
    }

    public static function cacheDropByPattern(string $cacheKeyStart)
    {
        $cacheDir = _PS_CACHE_DIR_;
        $pattern = $cacheDir . $cacheKeyStart . '*.cache';
        $matchingFiles = glob($pattern);
        if (!empty($matchingFiles)) {
            foreach ($matchingFiles as $file) {
                unlink($file);
            }
        }
    }
}
