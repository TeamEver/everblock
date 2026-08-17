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

    /**
     * Ecrit les lignes de langue d'une entite.
     *
     * Une valeur absente du tableau ne vide plus la colonne : la valeur deja en
     * base est conservee. Cela evite qu'un objet charge sur une seule langue,
     * puis sauvegarde, efface les traductions des autres langues.
     *
     * Une valeur scalaire n'appartient qu'a une langue : elle est appliquee a
     * $scalarLangId (par defaut la langue du contexte) et les autres langues
     * gardent leur valeur existante.
     *
     * @param array<int, array<string, mixed>> $languages
     * @param array<string, array<int, string>|string> $fieldValues
     */
    protected function upsertLangRows(string $table, string $primary, int $id, array $languages, array $fieldValues, ?int $scalarLangId = null): void
    {
        $existing = [];
        foreach ($this->langRows($table, $primary, $id) as $row) {
            $existing[(int) ($row['id_lang'] ?? 0)] = $row;
        }

        if ($scalarLangId === null) {
            $context = \Context::getContext();
            $scalarLangId = isset($context->language->id) ? (int) $context->language->id : 0;
        }
        if ($scalarLangId <= 0) {
            $scalarLangId = (int) \Configuration::get('PS_LANG_DEFAULT');
        }

        foreach ($languages as $language) {
            $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
            if ($langId <= 0) {
                continue;
            }

            $columns = [$primary => $id, 'id_lang' => $langId];
            foreach ($fieldValues as $field => $values) {
                $fallback = (string) ($existing[$langId][$field] ?? '');
                if (is_array($values)) {
                    $columns[$field] = array_key_exists($langId, $values)
                        ? (string) $values[$langId]
                        : $fallback;
                    continue;
                }

                $columns[$field] = $langId === $scalarLangId ? (string) $values : $fallback;
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
