<?php

declare(strict_types=1);

namespace Everblock\Tools\Tests\Infrastructure\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\DriverManager;
use Everblock\Tools\Infrastructure\Repository\SalesRepository;
use PHPUnit\Framework\TestCase;

final class SalesRepositoryTest extends TestCase
{
    public function testCreateSoldQuantityAggregationWithoutDateLimit(): void
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->createSchema($connection);

        $connection->executeStatement("INSERT INTO ps_orders (id_order, valid, date_add) VALUES (1, 1, '2024-01-01'), (2, 0, '2024-01-02')");
        $connection->executeStatement("INSERT INTO ps_order_detail (id_order, product_id, product_attribute_id, product_quantity) VALUES (1, 10, 0, 3), (1, 10, 0, 2), (2, 10, 0, 5)");

        $repository = new SalesRepository($connection);
        $aggregation = $repository->createSoldQuantityAggregation(null, false);

        $rows = $connection->executeQuery('SELECT * FROM (' . $aggregation->getSql() . ') sales')->fetchAllAssociative();

        self::assertCount(1, $rows);
        self::assertSame(10, (int) $rows[0]['product_id']);
        self::assertSame(5, (int) $rows[0]['sold_qty']);
    }

    public function testCreateSoldQuantityAggregationWithDateLimitAndCombination(): void
    {
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->createSchema($connection);

        $connection->executeStatement("INSERT INTO ps_orders (id_order, valid, date_add) VALUES (1, 1, '2024-01-05'), (2, 1, '2023-12-01')");
        $connection->executeStatement("INSERT INTO ps_order_detail (id_order, product_id, product_attribute_id, product_quantity) VALUES (1, 20, 5, 4), (2, 20, 5, 10)");

        $repository = new SalesRepository($connection);
        $aggregation = $repository->createSoldQuantityAggregation(new DateTimeImmutable('2024-01-01'), true);

        $rows = $connection->executeQuery('SELECT * FROM (' . $aggregation->getSql() . ') sales', $aggregation->getParameters())->fetchAllAssociative();

        self::assertCount(1, $rows);
        self::assertSame(20, (int) $rows[0]['product_id']);
        self::assertSame(5, (int) $rows[0]['product_attribute_id']);
        self::assertSame(4, (int) $rows[0]['sold_qty']);
    }

    private function createSchema($connection): void
    {
        $connection->executeStatement('CREATE TABLE ps_orders (id_order INTEGER PRIMARY KEY, valid INTEGER, date_add TEXT)');
        $connection->executeStatement('CREATE TABLE ps_order_detail (id_order INTEGER, product_id INTEGER, product_attribute_id INTEGER, product_quantity INTEGER)');
    }
}
