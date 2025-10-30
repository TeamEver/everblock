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

use DateTimeImmutable;
use FilesystemIterator;
use RuntimeException;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LogService
{
    private const DEFAULT_RELATIVE_DIRECTORY = 'var/logs/everblock';
    private const MAX_LOG_AGE_DAYS = 7;

    /**
     * @var string
     */
    private $logDirectory;

    public function __construct(?string $logDirectory = null)
    {
        $baseDirectory = $logDirectory ?: $this->resolveDefaultDirectory();
        $this->logDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR);
    }

    public function getLogDirectory(): string
    {
        $this->ensureDirectory();

        return $this->logDirectory;
    }

    public function getDailyLogPath(string $prefix): string
    {
        $normalized = trim($prefix);
        if ($normalized === '') {
            throw new RuntimeException('Log prefix cannot be empty.');
        }

        $filename = sprintf('%s-%s.log', $normalized, date('Y-m-d'));

        return $this->getLogPath($filename);
    }

    public function appendToDailyLog(string $prefix, string $content): void
    {
        $path = $this->getDailyLogPath($prefix);
        file_put_contents($path, $content, FILE_APPEND);
    }

    public function writeLog(string $filename, string $content): void
    {
        $path = $this->getLogPath($filename);
        file_put_contents($path, $content);
    }

    public function readLog(string $filename): string
    {
        $path = $this->getLogPath($filename);
        if (!is_file($path)) {
            return '';
        }

        $contents = @file_get_contents($path);

        return $contents !== false ? (string) $contents : '';
    }

    public function deleteLog(string $filename): void
    {
        $path = $this->getLogPath($filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function listLogs(): array
    {
        $this->purgeOldLogs();

        if (!is_dir($this->logDirectory)) {
            return [];
        }

        $logs = [];

        try {
            $iterator = new FilesystemIterator($this->logDirectory, FilesystemIterator::SKIP_DOTS);
        } catch (RuntimeException $e) {
            return [];
        }

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $extension = Tools::strtolower((string) $file->getExtension());
            if ($extension !== 'log') {
                continue;
            }

            $content = @file_get_contents($file->getPathname());

            $logs[] = [
                'filename' => $file->getFilename(),
                'path' => $file->getPathname(),
                'modified_at' => $file->getMTime(),
                'size' => $file->getSize(),
                'content' => $content !== false ? (string) $content : '',
            ];
        }

        usort($logs, static function (array $a, array $b) {
            return $b['modified_at'] <=> $a['modified_at'];
        });

        return $logs;
    }

    public function purgeOldLogs(): void
    {
        $this->ensureDirectory();

        if (!is_dir($this->logDirectory)) {
            return;
        }

        $threshold = (new DateTimeImmutable('-' . self::MAX_LOG_AGE_DAYS . ' days'))->getTimestamp();

        try {
            $iterator = new FilesystemIterator($this->logDirectory, FilesystemIterator::SKIP_DOTS);
        } catch (RuntimeException $e) {
            return;
        }

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $extension = Tools::strtolower((string) $file->getExtension());
            if ($extension !== 'log') {
                continue;
            }

            if ($file->getMTime() < $threshold) {
                @unlink($file->getPathname());
            }
        }
    }

    public function getLogPath(string $filename): string
    {
        $this->ensureDirectory();
        $this->purgeOldLogs();

        $sanitized = trim($filename);
        if ($sanitized === '') {
            throw new RuntimeException('Log filename cannot be empty.');
        }

        $sanitized = basename($sanitized);

        return $this->logDirectory . DIRECTORY_SEPARATOR . $sanitized;
    }

    private function ensureDirectory(): void
    {
        if (is_dir($this->logDirectory)) {
            return;
        }

        @mkdir($this->logDirectory, 0775, true);
    }

    private function resolveDefaultDirectory(): string
    {
        if (defined('_PS_ROOT_DIR_')) {
            return rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::DEFAULT_RELATIVE_DIRECTORY;
        }

        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::DEFAULT_RELATIVE_DIRECTORY;
    }
}
