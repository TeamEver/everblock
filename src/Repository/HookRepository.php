<?php

declare(strict_types=1);

namespace Everblock\Tools\Repository;

final class HookRepository extends AbstractEverblockRepository
{
    public function listDisplayHooks(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT id_hook, name, title, description, active
            FROM ' . $this->table('hook') . '
            WHERE name NOT LIKE "action%" AND name NOT LIKE "filter%"
            ORDER BY name ASC'
        );
    }

    public function choices(): array
    {
        $choices = [];
        foreach ($this->listDisplayHooks() as $hook) {
            $label = trim((string) ($hook['title'] ?: $hook['name']));
            $choices[$label . ' (#' . (int) $hook['id_hook'] . ')'] = (int) $hook['id_hook'];
        }

        return $choices;
    }

    public function find(int $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id_hook, name, title, description, active FROM ' . $this->table('hook') . ' WHERE id_hook = :id',
            ['id' => $id]
        );

        return $row ?: null;
    }

    public function save(?int $id, array $data): int
    {
        $payload = [
            'name' => (string) ($data['name'] ?? ''),
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'position' => 1,
            'active' => !empty($data['active']) ? 1 : 0,
        ];

        if ($id !== null && $id > 0) {
            $this->connection->update($this->databasePrefix . 'hook', $payload, ['id_hook' => $id]);

            return $id;
        }

        $this->connection->insert($this->databasePrefix . 'hook', $payload);

        return (int) $this->connection->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->connection->delete($this->databasePrefix . 'hook', ['id_hook' => $id]) > 0;
    }
}
