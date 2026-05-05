<?php

declare(strict_types=1);

namespace Everblock\Tools\Repository;

use Doctrine\DBAL\Connection;

abstract class AbstractEverblockRepository
{
    protected Connection $connection;
    protected string $databasePrefix;

    public function __construct(Connection $connection, string $databasePrefix)
    {
        $this->connection = $connection;
        $this->databasePrefix = $databasePrefix;
    }

    protected function table(string $table): string
    {
        return '`' . str_replace('`', '', $this->databasePrefix . $table) . '`';
    }

    protected function normalizeNullableDate($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            return $value . ':00';
        }

        return $value;
    }

    protected function intList(array $values): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $values))));
    }

    protected function langRows(string $table, string $primary, int $id): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT * FROM ' . $this->table($table) . ' WHERE `' . $primary . '` = :id',
            ['id' => $id]
        );
    }

    protected function upsertLangRows(string $table, string $primary, int $id, array $languages, array $fieldValues): void
    {
        foreach ($languages as $language) {
            $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
            if ($langId <= 0) {
                continue;
            }

            $columns = [$primary => $id, 'id_lang' => $langId];
            foreach ($fieldValues as $field => $values) {
                $columns[$field] = is_array($values) ? ($values[$langId] ?? '') : '';
            }

            $names = array_keys($columns);
            $placeholders = array_map(static fn (string $name): string => ':' . $name, $names);
            $updates = [];
            foreach ($names as $name) {
                if ($name === $primary || $name === 'id_lang') {
                    continue;
                }
                $updates[] = '`' . $name . '` = VALUES(`' . $name . '`)';
            }

            $this->connection->executeStatement(
                'INSERT INTO ' . $this->table($table)
                . ' (`' . implode('`, `', $names) . '`) VALUES (' . implode(', ', $placeholders) . ')'
                . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates),
                $columns
            );
        }
    }
}
