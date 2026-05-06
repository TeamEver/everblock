<?php

declare(strict_types=1);

namespace Everblock\Tools\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use RuntimeException;

final class RepositoryProvider
{
    /**
     * Map of Symfony service IDs to their concrete repository class.
     *
     * Used as a fallback when the Symfony container is not available
     * (typically inside a front-office controller, where PrestaShop boots
     * through the legacy entry point and SymfonyContainer::getInstance()
     * returns null).
     *
     * @var array<string, class-string>
     */
    private const SERVICE_MAP = [
        'everblock.repository.block' => BlockRepository::class,
        'everblock.repository.shortcode' => ShortcodeRepository::class,
        'everblock.repository.faq' => FaqRepository::class,
        'everblock.repository.page' => PageRepository::class,
        'everblock.repository.product_content' => ProductContentRepository::class,
        'everblock.repository.hook' => HookRepository::class,
    ];

    /** @var Connection|null */
    private static $fallbackConnection;

    public static function get(string $serviceId)
    {
        $container = SymfonyContainer::getInstance();
        if ($container !== null) {
            if ($container->has($serviceId)) {
                return $container->get($serviceId);
            }

            throw new RuntimeException(sprintf('Everblock repository service "%s" is not available.', $serviceId));
        }

        return self::buildFallbackRepository($serviceId);
    }

    public static function connection(): Connection
    {
        $container = SymfonyContainer::getInstance();
        if ($container !== null) {
            /** @var Connection $connection */
            $connection = $container->get('doctrine.dbal.default_connection');

            return $connection;
        }

        return self::fallbackConnection();
    }

    public static function databasePrefix(): string
    {
        $container = SymfonyContainer::getInstance();
        if ($container !== null) {
            return (string) $container->getParameter('database_prefix');
        }

        return self::fallbackDatabasePrefix();
    }

    /**
     * Build a repository instance manually when the Symfony container is
     * not available.
     */
    private static function buildFallbackRepository(string $serviceId): object
    {
        if (!isset(self::SERVICE_MAP[$serviceId])) {
            throw new RuntimeException(sprintf('Everblock repository service "%s" is not available.', $serviceId));
        }

        $class = self::SERVICE_MAP[$serviceId];

        return new $class(self::fallbackConnection(), self::fallbackDatabasePrefix());
    }

    private static function fallbackConnection(): Connection
    {
        if (self::$fallbackConnection instanceof Connection) {
            return self::$fallbackConnection;
        }

        if (!class_exists(DriverManager::class)) {
            throw new RuntimeException('Doctrine\\DBAL\\DriverManager is not available; cannot build a fallback connection for Everblock repositories.');
        }

        if (!defined('_DB_SERVER_') || !defined('_DB_NAME_') || !defined('_DB_USER_')) {
            throw new RuntimeException('PrestaShop database constants are not defined; cannot build a fallback connection for Everblock repositories.');
        }

        $host = (string) constant('_DB_SERVER_');
        $port = null;
        if (strpos($host, ':') !== false) {
            [$host, $portString] = explode(':', $host, 2);
            $port = (int) $portString;
        }

        $params = [
            'driver' => 'pdo_mysql',
            'host' => $host,
            'dbname' => (string) constant('_DB_NAME_'),
            'user' => (string) constant('_DB_USER_'),
            'password' => defined('_DB_PASSWD_') ? (string) constant('_DB_PASSWD_') : '',
            'charset' => 'utf8mb4',
        ];

        if ($port !== null) {
            $params['port'] = $port;
        }

        self::$fallbackConnection = DriverManager::getConnection($params);

        return self::$fallbackConnection;
    }

    private static function fallbackDatabasePrefix(): string
    {
        if (defined('_DB_PREFIX_')) {
            return (string) constant('_DB_PREFIX_');
        }

        return '';
    }
}
