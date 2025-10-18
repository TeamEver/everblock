<?php

declare(strict_types=1);

namespace Everblock\Tools\Tests\Infrastructure\Repository;

use Doctrine\DBAL\DriverManager;
use Everblock\Tools\Dto\Product\ProductTagFilters;
use Everblock\Tools\Infrastructure\Repository\ProductTagRepository;
use PHPUnit\Framework\TestCase;

final class ProductTagRepositoryTest extends TestCase
{
    public function testFindProductIdsByTagName(): void
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->createSchema($connection);

        $connection->executeStatement("INSERT INTO ps_product (id_product, date_add) VALUES (1, '2024-01-01'), (2, '2024-01-02')");
        $connection->executeStatement("INSERT INTO ps_product_shop (id_product, id_shop, active, visibility, price, position) VALUES (1, 1, 1, 'both', 10, 1), (2, 1, 1, 'catalog', 20, 2)");
        $connection->executeStatement("INSERT INTO ps_product_lang (id_product, id_lang, id_shop, name) VALUES (1, 1, 1, 'First'), (2, 1, 1, 'Second')");
        $connection->executeStatement("INSERT INTO ps_tag (id_tag, id_lang, name) VALUES (10, 1, 'featured'), (11, 1, 'summer')");
        $connection->executeStatement("INSERT INTO ps_product_tag (id_product, id_tag) VALUES (1, 10), (1, 11), (2, 11)");

        $repository = new ProductTagRepository($connection);

        $filters = new ProductTagFilters(
            shopId: 1,
            languageId: 1,
            tagNames: ['summer'],
            tagIds: [],
            match: ProductTagFilters::MATCH_ANY,
            offset: 0,
            limit: 10,
            orderBy: 'position',
            orderDirection: 'asc',
            visibilities: ['both', 'catalog']
        );

        $result = $repository->findProductIds($filters);

        self::assertSame([1, 2], $result->toArray());
    }

    public function testFindProductIdsWithMatchAll(): void
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->createSchema($connection);

        $connection->executeStatement("INSERT INTO ps_product (id_product, date_add) VALUES (1, '2024-01-01'), (2, '2024-01-02')");
        $connection->executeStatement("INSERT INTO ps_product_shop (id_product, id_shop, active, visibility, price, position) VALUES (1, 1, 1, 'both', 10, 1), (2, 1, 1, 'catalog', 20, 2)");
        $connection->executeStatement("INSERT INTO ps_product_lang (id_product, id_lang, id_shop, name) VALUES (1, 1, 1, 'First'), (2, 1, 1, 'Second')");
        $connection->executeStatement("INSERT INTO ps_tag (id_tag, id_lang, name) VALUES (10, 1, 'featured'), (11, 1, 'summer')");
        $connection->executeStatement("INSERT INTO ps_product_tag (id_product, id_tag) VALUES (1, 10), (1, 11), (2, 11)");

        $repository = new ProductTagRepository($connection);

        $filters = new ProductTagFilters(
            shopId: 1,
            languageId: 1,
            tagNames: ['featured', 'summer'],
            tagIds: [],
            match: ProductTagFilters::MATCH_ALL,
            offset: 0,
            limit: 10,
            orderBy: 'position',
            orderDirection: 'asc',
            visibilities: ['both', 'catalog']
        );

        $result = $repository->findProductIds($filters);

        self::assertSame([1], $result->toArray());
    }

    private function createSchema($connection): void
    {
        $connection->executeStatement('CREATE TABLE ps_product (id_product INTEGER PRIMARY KEY, date_add TEXT)');
        $connection->executeStatement('CREATE TABLE ps_product_shop (id_product INTEGER, id_shop INTEGER, active INTEGER, visibility TEXT, price REAL, position INTEGER)');
        $connection->executeStatement('CREATE TABLE ps_product_lang (id_product INTEGER, id_lang INTEGER, id_shop INTEGER, name TEXT)');
        $connection->executeStatement('CREATE TABLE ps_product_tag (id_product INTEGER, id_tag INTEGER)');
        $connection->executeStatement('CREATE TABLE ps_tag (id_tag INTEGER PRIMARY KEY, id_lang INTEGER, name TEXT)');
    }
}
