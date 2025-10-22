<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\Repository;

interface EverBlockRepositoryInterface
{
    /**
     * Duplicate the given block and return the newly created identifier.
     */
    public function duplicate(int $everBlockId): int;

    /**
     * Toggle the status of the given block and returns the new status.
     */
    public function toggleStatus(int $everBlockId): bool;

    /**
     * Force block status to the given state.
     */
    public function updateStatus(int $everBlockId, bool $enabled): void;

    /**
     * Remove the block permanently.
     */
    public function delete(int $everBlockId): void;
}
