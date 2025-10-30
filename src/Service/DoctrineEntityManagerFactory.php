<?php

namespace Everblock\Tools\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;

class DoctrineEntityManagerFactory
{
    public static function createForLegacyContext(): EntityManagerInterface
    {
        $paths = [__DIR__ . '/../Entity'];
        $isDevMode = defined('_PS_MODE_DEV_') ? (bool) _PS_MODE_DEV_ : false;
        $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);

        $connectionParams = [
            'dbname' => defined('_DB_NAME_') ? _DB_NAME_ : null,
            'user' => defined('_DB_USER_') ? _DB_USER_ : null,
            'password' => defined('_DB_PASSWD_') ? _DB_PASSWD_ : null,
            'host' => defined('_DB_SERVER_') ? _DB_SERVER_ : null,
            'port' => defined('_DB_PORT_') ? _DB_PORT_ : null,
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
        ];

        $connectionParams = array_filter(
            $connectionParams,
            static fn ($value): bool => null !== $value && $value !== ''
        );

        return EntityManager::create($connectionParams, $config);
    }
}
