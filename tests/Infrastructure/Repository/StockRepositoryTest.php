<?php

declare(strict_types=1);

namespace Everblock\Tools\Tests\Infrastructure\Repository;

use Doctrine\DBAL\DriverManager;
use Everblock\Tools\Dto\Product\LowStockFilters;
use Everblock\Tools\Infrastructure\Repository\SalesRepository;
use Everblock\Tools\Infrastructure\Repository\StockRepository;
use PHPUnit\Framework\TestCase;

final class StockRepositoryTest extends TestCase
{
    public function testFindLowStockProductsByProduct(): void
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->createSchema($connection);

        $connection->executeStatement("INSERT INTO ps_product (id_product, id_manufacturer, date_add) VALUES (1, 0, '2024-01-01'), (2, 0, '2024-01-02')");
        $connection->executeStatement("INSERT INTO ps_product_shop (id_product, id_shop, active, visibility, available_for_order, price, position) VALUES (1, 1, 1, 'both', 1, 10, 1), (2, 1, 1, 'catalog', 1, 12, 2)");
        $connection->executeStatement("INSERT INTO ps_product_lang (id_product, id_lang, id_shop, name) VALUES (1, 1, 1, 'First'), (2, 1, 1, 'Second')");
        $connection->executeStatement("INSERT INTO ps_stock_available (id_product, id_product_attribute, id_shop, id_shop_group, quantity) VALUES (1, 0, 1, 1, 2), (2, 0, 1, 1, 6)");

        $salesRepository = new SalesRepository($connection);
        $repository = new StockRepository($connection, $salesRepository);

        $filters = new LowStockFilters(
            shopId: 1,
            shopGroupId: 1,
            languageId: 1,
            threshold: 5,
            comparisonOperator: '<=',
            limit: 10,
            offset: 0,
            orderBy: 'qty',
            orderDirection: 'asc',
            days: 0,
            categoryIds: [],
            manufacturerIds: [],
            visibilities: ['both', 'catalog'],
            availableOnly: true,
            granularity: LowStockFilters::GRANULARITY_PRODUCT,
        );

        $result = $repository->findLowStockProducts($filters);
        $products = $result->toArray();

        self::assertCount(1, $products);
        self::assertSame(1, $products[0]->getProductId());
        self::assertNull($products[0]->getProductAttributeId());
        self::assertSame(2, $products[0]->getQuantity());
    }

    public function testFindLowStockProductsByCombinationOrderedBySales(): void
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->createSchema($connection);

        $connection->executeStatement("INSERT INTO ps_product (id_product, id_manufacturer, date_add) VALUES (10, 0, '2024-01-01')");
        $connection->executeStatement("INSERT INTO ps_product_shop (id_product, id_shop, active, visibility, available_for_order, price, position) VALUES (10, 1, 1, 'both', 1, 15, 1)");
        $connection->executeStatement("INSERT INTO ps_product_lang (id_product, id_lang, id_shop, name) VALUES (10, 1, 1, 'Variant')");
        $connection->executeStatement("INSERT INTO ps_stock_available (id_product, id_product_attribute, id_shop, id_shop_group, quantity) VALUES (10, 5, 1, 1, 3), (10, 6, 1, 1, 8)");
        $connection->executeStatement("INSERT INTO ps_orders (id_order, valid, date_add) VALUES (1, 1, '2024-01-01'), (2, 1, '2024-01-02')");
        $connection->executeStatement("INSERT INTO ps_order_detail (id_order, product_id, product_attribute_id, product_quantity) VALUES (1, 10, 5, 4), (2, 10, 6, 10)");

        $salesRepository = new SalesRepository($connection);
        $repository = new StockRepository($connection, $salesRepository);

        $filters = new LowStockFilters(
            shopId: 1,
            shopGroupId: 1,
            languageId: 1,
            threshold: 10,
            comparisonOperator: '<=',
            limit: 10,
            offset: 0,
            orderBy: 'sales',
            orderDirection: 'desc',
            days: 0,
            categoryIds: [],
            manufacturerIds: [],
            visibilities: ['both', 'catalog'],
            availableOnly: true,
            granularity: LowStockFilters::GRANULARITY_COMBINATION,
        );

        $result = $repository->findLowStockProducts($filters);
        $products = $result->toArray();

        self::assertCount(1, $products);
        self::assertSame(10, $products[0]->getProductId());
        self::assertSame(5, $products[0]->getProductAttributeId());
        self::assertSame(3, $products[0]->getQuantity());
        self::assertSame(4, $products[0]->getSoldQuantity());
    }

    private function createSchema($connection): void
    {
        $connection->executeStatement('CREATE TABLE ps_product (id_product INTEGER PRIMARY KEY, id_manufacturer INTEGER, date_add TEXT)');
        $connection->executeStatement('CREATE TABLE ps_product_shop (id_product INTEGER, id_shop INTEGER, active INTEGER, visibility TEXT, available_for_order INTEGER, price REAL, position INTEGER)');
        $connection->executeStatement('CREATE TABLE ps_product_lang (id_product INTEGER, id_lang INTEGER, id_shop INTEGER, name TEXT)');
        $connection->executeStatement('CREATE TABLE ps_stock_available (id_product INTEGER, id_product_attribute INTEGER, id_shop INTEGER, id_shop_group INTEGER, quantity INTEGER)');
        $connection->executeStatement('CREATE TABLE ps_category_product (id_category INTEGER, id_product INTEGER)');
        $connection->executeStatement('CREATE TABLE ps_orders (id_order INTEGER PRIMARY KEY, valid INTEGER, date_add TEXT)');
        $connection->executeStatement('CREATE TABLE ps_order_detail (id_order INTEGER, product_id INTEGER, product_attribute_id INTEGER, product_quantity INTEGER)');
    }
}
