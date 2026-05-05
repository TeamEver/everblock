<?php

declare(strict_types=1);

namespace Everblock\Tools\Repository;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use RuntimeException;

final class RepositoryProvider
{
    public static function get(string $serviceId)
    {
        $container = SymfonyContainer::getInstance();
        if ($container === null) {
            throw new RuntimeException('Symfony container is not available.');
        }

        if ($container->has($serviceId)) {
            return $container->get($serviceId);
        }

        throw new RuntimeException(sprintf('Everblock repository service "%s" is not available.', $serviceId));
    }

    public static function connection(): Connection
    {
        return self::get('doctrine.dbal.default_connection');
    }

    public static function databasePrefix(): string
    {
        $container = SymfonyContainer::getInstance();
        if ($container === null) {
            throw new RuntimeException('Symfony container is not available.');
        }

        return (string) $container->getParameter('database_prefix');
    }
}
