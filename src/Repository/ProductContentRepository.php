<?php

declare(strict_types=1);

namespace Everblock\Tools\Repository;

use Everblock\Tools\Entity\Modal;
use Everblock\Tools\Entity\ProductFlag;
use Everblock\Tools\Entity\ProductTab;

final class ProductContentRepository extends AbstractEverblockRepository
{
    public function findTab(int $id, ?int $langId = null): ?ProductTab
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM ' . $this->table('everblock_tabs') . ' WHERE id_everblock_tabs = :id',
            ['id' => $id]
        );
        if (!$row) {
            return null;
        }

        return ProductTab::fromDatabase($row, $this->langRows('everblock_tabs_lang', 'id_everblock_tabs', $id), $langId);
    }

    public function findTabsByProduct(int $productId, int $shopId, ?int $langId = null): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM ' . $this->table('everblock_tabs') . '
            WHERE id_product = :productId AND id_shop = :shopId
            ORDER BY id_tab ASC, id_everblock_tabs ASC',
            ['productId' => $productId, 'shopId' => $shopId]
        );

        $tabs = [];
        foreach ($rows as $row) {
            $tabs[] = $this->findTab((int) $row['id_everblock_tabs'], $langId) ?? ProductTab::fromDatabase($row, [], $langId);
        }

        return $tabs;
    }

    public function findTabByProductAndSlot(int $productId, int $shopId, int $tabId): ?ProductTab
    {
        $id = $this->connection->fetchOne(
            'SELECT id_everblock_tabs FROM ' . $this->table('everblock_tabs') . '
            WHERE id_product = :productId AND id_shop = :shopId AND id_tab = :tabId',
            ['productId' => $productId, 'shopId' => $shopId, 'tabId' => $tabId]
        );

        return $id ? $this->findTab((int) $id) : null;
    }

    public function saveTab(ProductTab $tab, array $languages): int
    {
        $data = [
            'id_product' => $tab->id_product,
            'id_shop' => $tab->id_shop,
            'id_tab' => $tab->id_tab,
        ];

        $this->connection->beginTransaction();
        try {
            if ($tab->id) {
                $this->connection->update($this->databasePrefix . 'everblock_tabs', $data, ['id_everblock_tabs' => $tab->id]);
                $id = (int) $tab->id;
            } else {
                $this->connection->insert($this->databasePrefix . 'everblock_tabs', $data);
                $id = (int) $this->connection->lastInsertId();
            }
            $this->upsertLangRows('everblock_tabs_lang', 'id_everblock_tabs', $id, $languages, [
                'title' => is_array($tab->title) ? $tab->title : [],
                'content' => is_array($tab->content) ? $tab->content : [],
            ]);
            $this->connection->commit();

            return $id;
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function deleteTab(int $id): bool
    {
        $this->connection->delete($this->databasePrefix . 'everblock_tabs_lang', ['id_everblock_tabs' => $id]);

        return $this->connection->delete($this->databasePrefix . 'everblock_tabs', ['id_everblock_tabs' => $id]) > 0;
    }

    public function createTabForAllProducts(int $idShop, array $titles, array $contents, bool $drop = false): void
    {
        if ($drop) {
            $this->connection->executeStatement('DELETE FROM ' . $this->table('everblock_tabs_lang'));
            $this->connection->executeStatement('DELETE FROM ' . $this->table('everblock_tabs'));
        }

        $productIds = $this->connection->fetchFirstColumn('SELECT id_product FROM ' . $this->table('product'));
        foreach ($productIds as $productId) {
            $tab = new ProductTab();
            $tab->id_product = (int) $productId;
            $tab->id_shop = $idShop;
            $tab->id_tab = 0;
            $tab->title = $titles;
            $tab->content = $contents;
            $this->saveTab($tab, array_map(static fn (int $id): array => ['id_lang' => $id], array_keys($titles + $contents)));
        }
    }

    public function findFlag(int $id, ?int $langId = null): ?ProductFlag
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM ' . $this->table('everblock_flags') . ' WHERE id_everblock_flags = :id',
            ['id' => $id]
        );
        if (!$row) {
            return null;
        }

        return ProductFlag::fromDatabase($row, $this->langRows('everblock_flags_lang', 'id_everblock_flags', $id), $langId);
    }

    public function findFlagsByProduct(int $productId, int $shopId, ?int $langId = null): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM ' . $this->table('everblock_flags') . '
            WHERE id_product = :productId AND id_shop = :shopId
            ORDER BY id_flag ASC, id_everblock_flags ASC',
            ['productId' => $productId, 'shopId' => $shopId]
        );

        $flags = [];
        foreach ($rows as $row) {
            $flags[] = $this->findFlag((int) $row['id_everblock_flags'], $langId) ?? ProductFlag::fromDatabase($row, [], $langId);
        }

        return $flags;
    }

    public function findFlagByProductAndSlot(int $productId, int $shopId, int $flagId): ?ProductFlag
    {
        $id = $this->connection->fetchOne(
            'SELECT id_everblock_flags FROM ' . $this->table('everblock_flags') . '
            WHERE id_product = :productId AND id_shop = :shopId AND id_flag = :flagId',
            ['productId' => $productId, 'shopId' => $shopId, 'flagId' => $flagId]
        );

        return $id ? $this->findFlag((int) $id) : null;
    }

    public function saveFlag(ProductFlag $flag, array $languages): int
    {
        $data = [
            'id_product' => $flag->id_product,
            'id_shop' => $flag->id_shop,
            'id_flag' => $flag->id_flag,
        ];

        $this->connection->beginTransaction();
        try {
            if ($flag->id) {
                $this->connection->update($this->databasePrefix . 'everblock_flags', $data, ['id_everblock_flags' => $flag->id]);
                $id = (int) $flag->id;
            } else {
                $this->connection->insert($this->databasePrefix . 'everblock_flags', $data);
                $id = (int) $this->connection->lastInsertId();
            }
            $this->upsertLangRows('everblock_flags_lang', 'id_everblock_flags', $id, $languages, [
                'title' => is_array($flag->title) ? $flag->title : [],
                'content' => is_array($flag->content) ? $flag->content : [],
            ]);
            $this->connection->commit();

            return $id;
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function deleteFlag(int $id): bool
    {
        $this->connection->delete($this->databasePrefix . 'everblock_flags_lang', ['id_everblock_flags' => $id]);

        return $this->connection->delete($this->databasePrefix . 'everblock_flags', ['id_everblock_flags' => $id]) > 0;
    }

    public function findModal(int $id): ?Modal
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM ' . $this->table('everblock_modal') . ' WHERE id_everblock_modal = :id',
            ['id' => $id]
        );
        if (!$row) {
            return null;
        }

        return Modal::fromDatabase($row, $this->langRows('everblock_modal_lang', 'id_everblock_modal', $id));
    }

    public function findModalByProduct(int $productId, int $shopId): ?Modal
    {
        $id = $this->connection->fetchOne(
            'SELECT id_everblock_modal FROM ' . $this->table('everblock_modal') . '
            WHERE id_product = :productId AND id_shop = :shopId',
            ['productId' => $productId, 'shopId' => $shopId]
        );

        return $id ? $this->findModal((int) $id) : null;
    }

    public function saveModal(Modal $modal, array $languages): int
    {
        $data = [
            'id_product' => $modal->id_product,
            'id_shop' => $modal->id_shop,
            'file' => $modal->file,
            'button_file' => $modal->button_file,
        ];

        $this->connection->beginTransaction();
        try {
            if ($modal->id) {
                $this->connection->update($this->databasePrefix . 'everblock_modal', $data, ['id_everblock_modal' => $modal->id]);
                $id = (int) $modal->id;
            } else {
                $this->connection->insert($this->databasePrefix . 'everblock_modal', $data);
                $id = (int) $this->connection->lastInsertId();
            }
            $this->upsertLangRows('everblock_modal_lang', 'id_everblock_modal', $id, $languages, [
                'content' => $modal->content,
                'button_label' => $modal->button_label,
            ]);
            $this->connection->commit();

            return $id;
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    public function deleteModal(int $id): bool
    {
        $this->connection->delete($this->databasePrefix . 'everblock_modal_lang', ['id_everblock_modal' => $id]);

        return $this->connection->delete($this->databasePrefix . 'everblock_modal', ['id_everblock_modal' => $id]) > 0;
    }
}
