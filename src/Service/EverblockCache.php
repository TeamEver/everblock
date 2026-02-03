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
use Exception;
use PrestaShopLogger;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockCache
{
    protected const TTL = 86400; // 24 hours
    protected static function useNativeCache(): bool
    {
        return Configuration::get('EVERBLOCK_CACHE') !== '1';
    }

    public static function getModuleConfiguration(string $key): string
    {
        $context = Context::getContext();

        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return '';
        }

        if (static::useNativeCache()) {
            if (!Cache::isStored($key)) {
                $value = Configuration::get($key);
                Cache::store($key, $value);
            }
            return (string) Cache::retrieve($key);
        }

        static::createCacheDir();

        if (!static::isCacheStored($key)) {
            $value = Configuration::get($key);
            static::cacheStore($key, $value);
        }
        return (string) static::cacheRetrieve($key);
    }

    public static function isCacheStored(string $cacheKey): bool
    {
        if (static::useNativeCache()) {
            return Cache::isStored($cacheKey);
        }

        static::createCacheDir();
        $cacheFilePath = _PS_CACHE_DIR_ . 'everblock/' . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            if (time() - filemtime($cacheFilePath) > self::TTL) {
                unlink($cacheFilePath);
                return false;
            }
            return true;
        }
        return false;
    }

    public static function cacheStore(string $cacheKey, $cacheValue)
    {
        if (static::useNativeCache()) {
            Cache::store($cacheKey, $cacheValue);
            return;
        }

        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return;
        }

        static::createCacheDir();
        $cacheFilePath = _PS_CACHE_DIR_ . 'everblock/' . $cacheKey . '.cache';
        file_put_contents($cacheFilePath, serialize($cacheValue));
    }

    public static function cacheRetrieve(string $cacheKey)
    {
        if (static::useNativeCache()) {
            return Cache::retrieve($cacheKey);
        }

        static::createCacheDir();
        $cacheFilePath = _PS_CACHE_DIR_ . 'everblock/' . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            if (time() - filemtime($cacheFilePath) > self::TTL) {
                unlink($cacheFilePath);
                return '';
            }
            $cachedData = Tools::file_get_contents($cacheFilePath);
            return unserialize($cachedData);
        }
        return '';
    }

    public static function cacheDrop(string $cacheKey)
    {
        if (static::useNativeCache()) {
            Cache::clean($cacheKey);
            return;
        }

        static::createCacheDir();
        $cacheFilePath = _PS_CACHE_DIR_ . 'everblock/' . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            unlink($cacheFilePath);
        }
    }

    public static function cacheDropByPattern(string $cacheKeyStart)
    {
        if (static::useNativeCache()) {
            // Pas de gestion native par motif → on pourrait itérer manuellement sur les clés si nécessaire
            return;
        }

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
            static::cleanOldFiles($cacheDir);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Unable to create Everblock cache dir');
        }
    }

    protected static function cleanOldFiles(string $cacheDir)
    {
        $files = glob($cacheDir . '/*.cache');
        if ($files !== false) {
            foreach ($files as $file) {
                if (time() - filemtime($file) > self::TTL) {
                    unlink($file);
                }
            }
        }
    }
}
