<?php

namespace Everblock\Tools\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Everblock\Tools\Repository\EverBlockShortcodeRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class EverBlockShortcodeRepositoryTest extends TestCase
{
    private Connection $connection;
    private TagAwareAdapter $cache;
    private EverBlockShortcodeRepository $repository;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $this->cache = new TagAwareAdapter(new ArrayAdapter());
        $this->repository = new EverBlockShortcodeRepository($this->connection, $this->cache, 'ps_');

        $this->createSchema();
    }

    public function testGetAllShortcodesReturnsLocalizedData(): void
    {
        $this->createShortcode(1, '[code_one]', 1);
        $this->createTranslation(1, 1, 'Title EN', '<p>EN</p>');
        $this->createTranslation(1, 2, 'Titre FR', '<p>FR</p>');
        $this->createShortcode(2, '[code_two]', 2);
        $this->createTranslation(2, 1, 'Other shop EN', '<p>Other</p>');

        $shortcodes = $this->repository->getAllShortcodes(1, 2);

        $this->assertCount(1, $shortcodes);
        $this->assertSame('[code_one]', $shortcodes[0]['shortcode']);
        $this->assertSame(1, (int) $shortcodes[0]['id_shop']);
        $this->assertSame(2, (int) $shortcodes[0]['id_lang']);
        $this->assertSame('<p>FR</p>', $shortcodes[0]['content']);
    }

    public function testGetEverShortcodeUsesCacheAndInvalidation(): void
    {
        $this->createShortcode(3, '[cached]', 1);
        $this->createTranslation(3, 1, 'Cached title', '<p>Original</p>');

        $content = $this->repository->getEverShortcode('[cached]', 1, 1);
        $this->assertSame('<p>Original</p>', $content);

        $this->connection->update('ps_everblock_shortcode_lang', ['content' => '<p>Updated</p>'], [
            'id_everblock_shortcode' => 3,
            'id_lang' => 1,
        ]);

        $cached = $this->repository->getEverShortcode('[cached]', 1, 1);
        $this->assertSame('<p>Original</p>', $cached);

        $this->repository->clearCacheForShortcode('[cached]', 1);

        $fresh = $this->repository->getEverShortcode('[cached]', 1, 1);
        $this->assertSame('<p>Updated</p>', $fresh);
    }

    public function testGetShortcodeForFormAggregatesTranslations(): void
    {
        $this->createShortcode(4, '[form]', 1);
        $this->createTranslation(4, 1, 'Title EN', '<p>EN</p>');
        $this->createTranslation(4, 2, 'Titre FR', '<p>FR</p>');

        $data = $this->repository->getShortcodeForForm(4, 1);

        $this->assertNotNull($data);
        $this->assertSame(4, $data['id_everblock_shortcode']);
        $this->assertSame('[form]', $data['shortcode']);
        $this->assertArrayHasKey(1, $data['translations']);
        $this->assertArrayHasKey(2, $data['translations']);
        $this->assertSame('Title EN', $data['translations'][1]['title']);
        $this->assertSame('<p>FR</p>', $data['translations'][2]['content']);
    }

    public function testGetAllShortcodeIdsFiltersByShop(): void
    {
        $this->createShortcode(5, '[shop1]', 1);
        $this->createShortcode(6, '[shop2]', 2);

        $ids = $this->repository->getAllShortcodeIds(1);

        $this->assertCount(1, $ids);
        $this->assertSame(5, (int) $ids[0]['id_everblock_shortcode']);
    }

    private function createSchema(): void
    {
        $this->connection->executeStatement(
            'CREATE TABLE ps_everblock_shortcode (
                id_everblock_shortcode INTEGER PRIMARY KEY,
                shortcode TEXT NULL,
                id_shop INTEGER NOT NULL
            )'
        );

        $this->connection->executeStatement(
            'CREATE TABLE ps_everblock_shortcode_lang (
                id_everblock_shortcode INTEGER NOT NULL,
                id_lang INTEGER NOT NULL,
                title TEXT NULL,
                content TEXT NULL,
                PRIMARY KEY (id_everblock_shortcode, id_lang)
            )'
        );
    }

    private function createShortcode(int $id, string $shortcode, int $shopId): void
    {
        $this->connection->insert('ps_everblock_shortcode', [
            'id_everblock_shortcode' => $id,
            'shortcode' => $shortcode,
            'id_shop' => $shopId,
        ]);
    }

    private function createTranslation(int $id, int $langId, string $title, string $content): void
    {
        $this->connection->insert('ps_everblock_shortcode_lang', [
            'id_everblock_shortcode' => $id,
            'id_lang' => $langId,
            'title' => $title,
            'content' => $content,
        ]);
    }
}
