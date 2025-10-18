<?php

namespace Everblock\Tools\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Everblock\Tools\Repository\EverBlockRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class EverBlockRepositoryTest extends TestCase
{
    private Connection $connection;
    private TagAwareAdapter $cache;
    private EverBlockRepository $repository;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $this->cache = new TagAwareAdapter(new ArrayAdapter());
        $this->repository = new EverBlockRepository($this->connection, $this->cache, 'ps_');

        $this->createSchema();
    }

    public function testGetAllBlocksReturnsOrderedBlocks(): void
    {
        $this->createBlock(1, 10, 1, 2, 1, 2);
        $this->createTranslation(1, 1, 'first block', '<div>code 1</div>');
        $this->createBlock(2, 10, 1, 1, 1, 3);
        $this->createTranslation(2, 1, 'second block', '<div>code 2</div>');

        $blocks = $this->repository->getAllBlocks(1, 1);

        $this->assertCount(2, $blocks);
        $this->assertSame(2, (int) $blocks[0]['id_everblock']);
        $this->assertSame('second block', $blocks[0]['content']);
        $this->assertSame(1, (int) $blocks[0]['id_lang']);
        $this->assertSame(1, (int) $blocks[0]['id_shop']);
        $this->assertSame('first block', $blocks[1]['content']);
    }

    public function testGetBlocksFiltersActiveBlocksAndFormatsBootstrapClass(): void
    {
        $this->createBlock(5, 15, 1, 1, 1, 2);
        $this->createTranslation(5, 1, 'active block', '<p>active</p>');
        $this->createBlock(6, 15, 1, 2, 0, 4);
        $this->createTranslation(6, 1, 'inactive block', '<p>inactive</p>');

        $blocks = $this->repository->getBlocks(15, 1, 1);

        $this->assertCount(1, $blocks);
        $this->assertSame('active block', $blocks[0]['content']);
        $this->assertSame('col-6 col-md-6', $blocks[0]['bootstrap_class']);
    }

    public function testCacheInvalidationPerHook(): void
    {
        $this->createBlock(20, 30, 1, 1, 1, 2);
        $this->createTranslation(20, 1, 'cached block', '<p>cached</p>');

        $initial = $this->repository->getBlocks(30, 1, 1);
        $this->assertSame('col-6 col-md-6', $initial[0]['bootstrap_class']);

        $this->connection->update('ps_everblock', ['bootstrap_class' => '4'], ['id_everblock' => 20]);

        $cached = $this->repository->getBlocks(30, 1, 1);
        $this->assertSame('col-6 col-md-6', $cached[0]['bootstrap_class']);

        $this->repository->clearCacheForHook(30);

        $fresh = $this->repository->getBlocks(30, 1, 1);
        $this->assertSame('col-3 col-md-3', $fresh[0]['bootstrap_class']);
    }

    private function createSchema(): void
    {
        $this->connection->executeStatement(
            'CREATE TABLE ps_everblock (
                id_everblock INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                id_hook INTEGER NOT NULL,
                only_home INTEGER DEFAULT 0,
                only_category INTEGER DEFAULT 0,
                only_category_product INTEGER DEFAULT 0,
                only_manufacturer INTEGER DEFAULT 0,
                only_supplier INTEGER DEFAULT 0,
                only_cms_category INTEGER DEFAULT 0,
                obfuscate_link INTEGER DEFAULT 0,
                add_container INTEGER DEFAULT 0,
                lazyload INTEGER DEFAULT 0,
                device INTEGER DEFAULT 0,
                id_shop INTEGER NOT NULL,
                position INTEGER DEFAULT 0,
                categories TEXT NULL,
                manufacturers TEXT NULL,
                suppliers TEXT NULL,
                cms_categories TEXT NULL,
                groups TEXT NULL,
                background TEXT NULL,
                css_class TEXT NULL,
                data_attribute TEXT NULL,
                bootstrap_class TEXT NULL,
                modal INTEGER DEFAULT 0,
                delay INTEGER DEFAULT 0,
                timeout INTEGER DEFAULT 0,
                date_start TEXT NULL,
                date_end TEXT NULL,
                active INTEGER DEFAULT 0
            )'
        );

        $this->connection->executeStatement(
            'CREATE TABLE ps_everblock_lang (
                id_everblock INTEGER NOT NULL,
                id_lang INTEGER NOT NULL,
                content TEXT NULL,
                custom_code TEXT NULL,
                PRIMARY KEY (id_everblock, id_lang)
            )'
        );
    }

    private function createBlock(
        int $id,
        int $hookId,
        int $shopId,
        int $position,
        int $active,
        int $bootstrapClass
    ): void {
        $this->connection->insert('ps_everblock', [
            'id_everblock' => $id,
            'name' => 'Block ' . $id,
            'id_hook' => $hookId,
            'only_home' => 0,
            'only_category' => 0,
            'only_category_product' => 0,
            'only_manufacturer' => 0,
            'only_supplier' => 0,
            'only_cms_category' => 0,
            'obfuscate_link' => 0,
            'add_container' => 0,
            'lazyload' => 0,
            'device' => 0,
            'id_shop' => $shopId,
            'position' => $position,
            'categories' => null,
            'manufacturers' => null,
            'suppliers' => null,
            'cms_categories' => null,
            'groups' => null,
            'background' => null,
            'css_class' => null,
            'data_attribute' => null,
            'bootstrap_class' => (string) $bootstrapClass,
            'modal' => 0,
            'delay' => 0,
            'timeout' => 0,
            'date_start' => null,
            'date_end' => null,
            'active' => $active,
        ]);
    }

    private function createTranslation(int $blockId, int $langId, string $content, string $customCode): void
    {
        $this->connection->insert('ps_everblock_lang', [
            'id_everblock' => $blockId,
            'id_lang' => $langId,
            'content' => $content,
            'custom_code' => $customCode,
        ]);
    }
}
