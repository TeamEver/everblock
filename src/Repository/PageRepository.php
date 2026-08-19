<?php

declare(strict_types=1);

namespace Everblock\Tools\Repository;

use Everblock\Tools\Entity\Page;

final class PageRepository extends AbstractEverblockRepository
{
    public function list(int $shopId, int $langId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT p.*, pl.name, pl.title, pl.link_rewrite
            FROM ' . $this->table('everblock_page') . ' p
            LEFT JOIN ' . $this->table('everblock_page_lang') . ' pl
                ON p.id_everblock_page = pl.id_everblock_page AND pl.id_lang = :langId
            WHERE p.id_shop = :shopId
            ORDER BY p.position ASC, p.id_everblock_page DESC',
            ['shopId' => $shopId, 'langId' => $langId]
        );
    }

    public function find(int $id, ?int $shopId = null, ?int $langId = null): ?Page
    {
        $where = 'id_everblock_page = :id';
        $params = ['id' => $id];
        if ($shopId !== null && $shopId > 0) {
            $where .= ' AND id_shop = :shopId';
            $params['shopId'] = $shopId;
        }

        $row = $this->connection->fetchAssociative(
            'SELECT * FROM ' . $this->table('everblock_page') . ' WHERE ' . $where,
            $params
        );
        if (!$row) {
            return null;
        }

        return Page::fromDatabase($row, $this->langRows('everblock_page_lang', 'id_everblock_page', $id), $langId);
    }

    public function findPages(
        int $langId,
        int $shopId,
        bool $onlyActive = true,
        int $page = 1,
        ?int $perPage = null,
        ?int $fallbackLangId = null
    ): array
    {
        $where = 'p.id_shop = :shopId';
        if ($onlyActive) {
            $where .= ' AND p.active = 1';
        }
        $fallbackLangId = $fallbackLangId !== null && $fallbackLangId > 0 ? $fallbackLangId : $langId;
        $limitSql = '';
        if ($perPage !== null && $perPage > 0) {
            $limitSql = ' LIMIT ' . (int) $perPage . ' OFFSET ' . max(0, ($page - 1) * $perPage);
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT p.*,
                :langId AS id_lang,
                COALESCE(NULLIF(pl.name, \'\'), fallback_pl.name, \'\') AS name,
                COALESCE(NULLIF(pl.title, \'\'), fallback_pl.title, \'\') AS title,
                COALESCE(NULLIF(pl.meta_description, \'\'), fallback_pl.meta_description, \'\') AS meta_description,
                COALESCE(NULLIF(pl.short_description, \'\'), fallback_pl.short_description, \'\') AS short_description,
                COALESCE(NULLIF(pl.link_rewrite, \'\'), fallback_pl.link_rewrite, \'\') AS link_rewrite,
                COALESCE(NULLIF(pl.content, \'\'), fallback_pl.content, \'\') AS content
            FROM ' . $this->table('everblock_page') . ' p
            LEFT JOIN ' . $this->table('everblock_page_lang') . ' pl
                ON p.id_everblock_page = pl.id_everblock_page AND pl.id_lang = :langId
            LEFT JOIN ' . $this->table('everblock_page_lang') . ' fallback_pl
                ON p.id_everblock_page = fallback_pl.id_everblock_page AND fallback_pl.id_lang = :fallbackLangId
            WHERE ' . $where . '
                AND (pl.id_everblock_page IS NOT NULL OR fallback_pl.id_everblock_page IS NOT NULL)
            ORDER BY p.position ASC, p.date_add DESC' . $limitSql,
            ['shopId' => $shopId, 'langId' => $langId, 'fallbackLangId' => $fallbackLangId]
        );

        return array_map(static fn (array $row): Page => Page::fromDatabase($row, [], $langId), $rows);
    }

    public function getNextPosition(int $shopId): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COALESCE(MAX(position), 0) + 1 FROM ' . $this->table('everblock_page') . ' WHERE id_shop = :shopId',
            ['shopId' => $shopId]
        );
    }

    public function save(Page $page, array $languages): int
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            'id_shop' => $page->id_shop,
            'groups' => $page->groups,
            'cover_image' => $page->cover_image,
            'active' => (int) $page->active,
            'position' => $page->position,
            'date_upd' => $now,
        ];
        if (!$page->id) {
            $data['date_add'] = $page->date_add ?: $now;
        }

        $this->connection->beginTransaction();
        try {
            if ($page->id) {
                $this->connection->update($this->databasePrefix . 'everblock_page', $data, [
                    'id_everblock_page' => $page->id,
                    'id_shop' => $page->id_shop,
                ]);
                $id = (int) $page->id;
            } else {
                $this->connection->insert($this->databasePrefix . 'everblock_page', $data);
                $id = (int) $this->connection->lastInsertId();
            }

            $this->upsertLangRows('everblock_page_lang', 'id_everblock_page', $id, $languages, [
                'name' => is_array($page->name) ? $page->name : [],
                'title' => is_array($page->title) ? $page->title : [],
                'meta_description' => is_array($page->meta_description) ? $page->meta_description : [],
                'short_description' => is_array($page->short_description) ? $page->short_description : [],
                'link_rewrite' => is_array($page->link_rewrite) ? $page->link_rewrite : [],
                'content' => is_array($page->content) ? $page->content : [],
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
            $this->connection->delete($this->databasePrefix . 'everblock_page_lang', ['id_everblock_page' => $id]);
            $deleted = $this->connection->delete($this->databasePrefix . 'everblock_page', [
                'id_everblock_page' => $id,
                'id_shop' => $shopId,
            ]);
            $this->connection->commit();

            return $deleted > 0;
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }
}
