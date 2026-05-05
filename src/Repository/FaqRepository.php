<?php

declare(strict_types=1);

namespace Everblock\Tools\Repository;

use Everblock\Tools\Entity\Faq;

final class FaqRepository extends AbstractEverblockRepository
{
    public function list(int $shopId, int $langId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT f.*, fl.title, fl.content,
                (SELECT COUNT(*) FROM ' . $this->table('everblock_faq_product') . ' fp
                 WHERE fp.id_everblock_faq = f.id_everblock_faq AND fp.id_shop = f.id_shop) AS linked_products
            FROM ' . $this->table('everblock_faq') . ' f
            LEFT JOIN ' . $this->table('everblock_faq_lang') . ' fl
                ON f.id_everblock_faq = fl.id_everblock_faq AND fl.id_lang = :langId
            WHERE f.id_shop = :shopId
            ORDER BY f.tag_name ASC, f.position ASC, f.id_everblock_faq ASC',
            ['shopId' => $shopId, 'langId' => $langId]
        );
    }

    public function find(int $id, ?int $shopId = null, ?int $langId = null): ?Faq
    {
        $where = 'id_everblock_faq = :id';
        $params = ['id' => $id];
        if ($shopId !== null && $shopId > 0) {
            $where .= ' AND id_shop = :shopId';
            $params['shopId'] = $shopId;
        }

        $row = $this->connection->fetchAssociative(
            'SELECT * FROM ' . $this->table('everblock_faq') . ' WHERE ' . $where,
            $params
        );
        if (!$row) {
            return null;
        }

        return Faq::fromDatabase($row, $this->langRows('everblock_faq_lang', 'id_everblock_faq', $id), $langId);
    }

    public function findAllActive(int $shopId, int $langId): array
    {
        return $this->hydrateMany(
            'WHERE f.id_shop = :shopId AND f.active = 1 ORDER BY f.position ASC',
            ['shopId' => $shopId],
            $langId
        );
    }

    public function findByTagName(int $shopId, int $langId, string $tagName): array
    {
        return $this->hydrateMany(
            'WHERE f.id_shop = :shopId AND f.active = 1 AND f.tag_name = :tagName ORDER BY f.position ASC',
            ['shopId' => $shopId, 'tagName' => $tagName],
            $langId
        );
    }

    public function countActiveByTagName(int $shopId, string $tagName): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->table('everblock_faq') . ' WHERE id_shop = :shopId AND active = 1 AND tag_name = :tagName',
            ['shopId' => $shopId, 'tagName' => $tagName]
        );
    }

    public function countAllActive(int $shopId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->table('everblock_faq') . ' WHERE id_shop = :shopId AND active = 1',
            ['shopId' => $shopId]
        );
    }

    public function countAll(int $shopId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->table('everblock_faq') . ' WHERE id_shop = :shopId',
            ['shopId' => $shopId]
        );
    }

    public function getFirstActiveTagName(int $shopId): ?string
    {
        $tagName = $this->connection->fetchOne(
            'SELECT tag_name FROM ' . $this->table('everblock_faq') . '
            WHERE id_shop = :shopId AND active = 1
            ORDER BY tag_name ASC, id_everblock_faq ASC',
            ['shopId' => $shopId]
        );

        return is_string($tagName) && $tagName !== '' ? $tagName : null;
    }

    public function findAllActivePaginated(int $shopId, int $langId, int $page, int $limit): array
    {
        return $this->hydrateMany(
            'WHERE f.id_shop = :shopId AND f.active = 1 ORDER BY f.tag_name ASC, f.position ASC LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, ($page - 1) * $limit),
            ['shopId' => $shopId],
            $langId
        );
    }

    public function findByTagNamePaginated(int $shopId, int $langId, string $tagName, int $page, int $limit): array
    {
        return $this->hydrateMany(
            'WHERE f.id_shop = :shopId AND f.active = 1 AND f.tag_name = :tagName ORDER BY f.position ASC LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, ($page - 1) * $limit),
            ['shopId' => $shopId, 'tagName' => $tagName],
            $langId
        );
    }

    public function findByIds(array $faqIds, int $langId, int $shopId, bool $onlyActive = true): array
    {
        $faqIds = $this->intList($faqIds);
        if (empty($faqIds)) {
            return [];
        }

        $where = 'WHERE f.id_shop = :shopId AND f.id_everblock_faq IN (' . implode(',', $faqIds) . ')';
        if ($onlyActive) {
            $where .= ' AND f.active = 1';
        }

        return $this->hydrateMany($where . ' ORDER BY FIELD(f.id_everblock_faq, ' . implode(',', $faqIds) . ')', ['shopId' => $shopId], $langId);
    }

    public function findIdsByProduct(int $productId, int $shopId): array
    {
        if ($productId <= 0) {
            return [];
        }

        return array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT id_everblock_faq FROM ' . $this->table('everblock_faq_product') . '
            WHERE id_product = :productId AND id_shop = :shopId
            ORDER BY position ASC, id_everblock_faq_product ASC',
            ['productId' => $productId, 'shopId' => $shopId]
        ));
    }

    public function findProductsByFaq(int $faqId, int $shopId): array
    {
        if ($faqId <= 0) {
            return [];
        }

        return $this->connection->fetchAllAssociative(
            'SELECT id_product, position FROM ' . $this->table('everblock_faq_product') . '
            WHERE id_everblock_faq = :faqId AND id_shop = :shopId
            ORDER BY position ASC, id_everblock_faq_product ASC',
            ['faqId' => $faqId, 'shopId' => $shopId]
        );
    }

    public function linkToProduct(int $faqId, int $productId, int $shopId, ?int $position = null): bool
    {
        if ($faqId <= 0 || $productId <= 0) {
            return false;
        }

        if ($position === null) {
            $position = (int) $this->connection->fetchOne(
                'SELECT COALESCE(MAX(position), -1) + 1 FROM ' . $this->table('everblock_faq_product') . '
                WHERE id_product = :productId AND id_shop = :shopId',
                ['productId' => $productId, 'shopId' => $shopId]
            );
        }

        $this->connection->executeStatement(
            'INSERT INTO ' . $this->table('everblock_faq_product') . '
                (id_everblock_faq, id_product, id_shop, position)
            VALUES (:faqId, :productId, :shopId, :position)
            ON DUPLICATE KEY UPDATE position = VALUES(position)',
            ['faqId' => $faqId, 'productId' => $productId, 'shopId' => $shopId, 'position' => $position]
        );

        return true;
    }

    public function unlinkProductFaqs(int $productId, int $shopId, ?array $faqIds = null): bool
    {
        if ($productId <= 0) {
            return false;
        }

        $where = 'id_product = :productId AND id_shop = :shopId';
        $params = ['productId' => $productId, 'shopId' => $shopId];
        if ($faqIds !== null) {
            $faqIds = $this->intList($faqIds);
            if (empty($faqIds)) {
                return true;
            }
            $where .= ' AND id_everblock_faq IN (' . implode(',', $faqIds) . ')';
        }

        $this->connection->executeStatement(
            'DELETE FROM ' . $this->table('everblock_faq_product') . ' WHERE ' . $where,
            $params
        );

        return true;
    }

    public function searchOptions(int $shopId, int $langId, string $query = '', int $page = 1, int $limit = 20): array
    {
        $offset = (max(1, $page) - 1) * max(1, $limit);
        $requestedLimit = max(1, $limit) + 1;
        $where = 'f.id_shop = :shopId';
        $params = ['shopId' => $shopId, 'langId' => $langId];
        if ($query !== '') {
            $where .= ' AND (f.tag_name LIKE :query OR fl.title LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT f.id_everblock_faq, f.tag_name, f.active, fl.title
            FROM ' . $this->table('everblock_faq') . ' f
            LEFT JOIN ' . $this->table('everblock_faq_lang') . ' fl
                ON f.id_everblock_faq = fl.id_everblock_faq AND fl.id_lang = :langId
            WHERE ' . $where . '
            ORDER BY f.tag_name ASC, fl.title ASC, f.id_everblock_faq ASC
            LIMIT ' . $requestedLimit . ' OFFSET ' . $offset,
            $params
        );

        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            array_pop($rows);
        }

        return [
            'results' => array_map([$this, 'buildOption'], $rows),
            'has_more' => $hasMore,
        ];
    }

    public function findOptionsByIds(array $faqIds, int $shopId, int $langId): array
    {
        $faqIds = $this->intList($faqIds);
        if (empty($faqIds)) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT f.id_everblock_faq, f.tag_name, f.active, fl.title
            FROM ' . $this->table('everblock_faq') . ' f
            LEFT JOIN ' . $this->table('everblock_faq_lang') . ' fl
                ON f.id_everblock_faq = fl.id_everblock_faq AND fl.id_lang = :langId
            WHERE f.id_shop = :shopId AND f.id_everblock_faq IN (' . implode(',', $faqIds) . ')
            ORDER BY FIELD(f.id_everblock_faq, ' . implode(',', $faqIds) . ')',
            ['shopId' => $shopId, 'langId' => $langId]
        );

        return array_map([$this, 'buildOption'], $rows);
    }

    public function save(Faq $faq, array $languages): int
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            'id_shop' => $faq->id_shop,
            'tag_name' => $faq->tag_name,
            'position' => $faq->position,
            'active' => (int) $faq->active,
            'date_upd' => $now,
        ];
        if (!$faq->id) {
            $data['date_add'] = $faq->date_add ?: $now;
        }

        $titles = is_array($faq->title) ? $faq->title : [];
        $contents = is_array($faq->content) ? $faq->content : [];

        $this->connection->beginTransaction();
        try {
            if ($faq->id) {
                $this->connection->update($this->databasePrefix . 'everblock_faq', $data, [
                    'id_everblock_faq' => $faq->id,
                    'id_shop' => $faq->id_shop,
                ]);
                $id = (int) $faq->id;
            } else {
                $this->connection->insert($this->databasePrefix . 'everblock_faq', $data);
                $id = (int) $this->connection->lastInsertId();
            }

            $this->upsertLangRows('everblock_faq_lang', 'id_everblock_faq', $id, $languages, [
                'title' => $titles,
                'content' => $contents,
            ]);

            $this->connection->commit();

            return $id;
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id, int $shopId): bool
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->delete($this->databasePrefix . 'everblock_faq_product', ['id_everblock_faq' => $id, 'id_shop' => $shopId]);
            $this->connection->delete($this->databasePrefix . 'everblock_faq_lang', ['id_everblock_faq' => $id]);
            $deleted = $this->connection->delete($this->databasePrefix . 'everblock_faq', [
                'id_everblock_faq' => $id,
                'id_shop' => $shopId,
            ]);
            $this->connection->commit();

            return $deleted > 0;
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    private function hydrateMany(string $whereSql, array $params, int $langId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT f.*, fl.title, fl.content, fl.id_lang
            FROM ' . $this->table('everblock_faq') . ' f
            INNER JOIN ' . $this->table('everblock_faq_lang') . ' fl
                ON f.id_everblock_faq = fl.id_everblock_faq AND fl.id_lang = :langId
            ' . $whereSql,
            array_merge($params, ['langId' => $langId])
        );

        return array_map(static fn (array $row): Faq => Faq::fromDatabase($row, [], $langId), $rows);
    }

    private function buildOption(array $row): array
    {
        $id = (int) ($row['id_everblock_faq'] ?? 0);
        $tagName = (string) ($row['tag_name'] ?? '');
        $title = (string) ($row['title'] ?? '');
        $parts = array_values(array_filter([$tagName, $title]));

        return [
            'id' => $id,
            'tag_name' => $tagName,
            'title' => $title,
            'active' => (bool) ($row['active'] ?? false),
            'text' => $parts ? implode(' - ', $parts) : '#' . $id,
        ];
    }
}
