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

use Configuration;
use PrestaShopLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GithubReleaseChecker
{
    private const API_URL = 'https://api.github.com/repos/TeamEver/everblock/releases/latest';
    private const CACHE_KEY = 'QCD_EVERBLOCK_LATEST_RELEASE';
    private const CACHE_TTL = 86400;

    /**
     * @var string
     */
    private $moduleVersion;

    /**
     * @var array|null
     */
    private $latestRelease;

    /**
     * @var bool
     */
    private $latestReleaseLoaded = false;

    public function __construct(string $moduleVersion)
    {
        $this->moduleVersion = $moduleVersion;
    }

    public function getLatestEverblockRelease(): ?array
    {
        if ($this->latestReleaseLoaded) {
            return $this->latestRelease;
        }

        $cachedPayload = $this->getCachedPayload();
        if ($cachedPayload && !$this->isCacheExpired($cachedPayload)) {
            $this->latestReleaseLoaded = true;
            $this->latestRelease = $cachedPayload['release'];
            return $this->latestRelease;
        }

        $release = $this->fetchLatestRelease();
        if ($release === null) {
            if ($cachedPayload) {
                $this->latestReleaseLoaded = true;
                $this->latestRelease = $cachedPayload['release'];
                return $this->latestRelease;
            }

            $this->latestReleaseLoaded = true;
            return null;
        }

        $this->storeCache($release);

        $this->latestReleaseLoaded = true;
        $this->latestRelease = $release;
        return $release;
    }

    public function isEverblockUpdateAvailable(): bool
    {
        $release = $this->getLatestEverblockRelease();
        if (!$release || empty($release['tag_name'])) {
            return false;
        }

        $latestVersion = $this->normalizeVersion((string) $release['tag_name']);
        $currentVersion = $this->normalizeVersion((string) $this->moduleVersion);

        if ($latestVersion === '' || $currentVersion === '') {
            return false;
        }

        return version_compare($latestVersion, $currentVersion, '>');
    }

    private function normalizeVersion(string $version): string
    {
        $version = trim($version);
        if ($version === '') {
            return '';
        }

        return ltrim($version, 'vV');
    }

    private function getCachedPayload(): ?array
    {
        $cachedValue = Configuration::get(self::CACHE_KEY);
        if (!$cachedValue) {
            return null;
        }

        $payload = json_decode((string) $cachedValue, true);
        if (!is_array($payload)) {
            return null;
        }

        if (!isset($payload['release']) || !is_array($payload['release'])) {
            return null;
        }

        $release = $payload['release'];
        if (empty($release['tag_name'])) {
            return null;
        }

        if (!isset($payload['fetched_at'])) {
            return null;
        }

        $payload['release'] = [
            'tag_name' => (string) ($release['tag_name'] ?? ''),
            'published_at' => (string) ($release['published_at'] ?? ''),
            'body' => (string) ($release['body'] ?? ''),
        ];

        $payload['fetched_at'] = (int) $payload['fetched_at'];

        return $payload;
    }

    private function isCacheExpired(array $payload): bool
    {
        if (!isset($payload['fetched_at'])) {
            return true;
        }

        return (time() - (int) $payload['fetched_at']) > self::CACHE_TTL;
    }

    private function fetchLatestRelease(): ?array
    {
        $headers = [
            'User-Agent: EverblockModule/' . $this->moduleVersion,
            'Accept: application/vnd.github+json',
        ];
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'header' => implode("\r\n", $headers),
            ],
        ]);

        $response = @file_get_contents(self::API_URL, false, $context);
        if ($response === false) {
            PrestaShopLogger::addLog('Everblock: unable to reach GitHub API for latest release.');
            return null;
        }

        $payload = json_decode($response, true);
        if (!is_array($payload)) {
            PrestaShopLogger::addLog('Everblock: invalid GitHub response for latest release.');
            return null;
        }

        $tagName = (string) ($payload['tag_name'] ?? '');
        if ($tagName === '') {
            return null;
        }

        return [
            'tag_name' => $tagName,
            'published_at' => (string) ($payload['published_at'] ?? ''),
            'body' => (string) ($payload['body'] ?? ''),
        ];
    }

    private function storeCache(array $release): void
    {
        $payload = [
            'fetched_at' => time(),
            'release' => [
                'tag_name' => (string) ($release['tag_name'] ?? ''),
                'published_at' => (string) ($release['published_at'] ?? ''),
                'body' => (string) ($release['body'] ?? ''),
            ],
        ];

        Configuration::updateValue(self::CACHE_KEY, json_encode($payload));
    }
}
