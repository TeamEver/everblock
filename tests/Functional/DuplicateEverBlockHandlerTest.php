<?php

require __DIR__ . '/../../vendor/autoload.php';

use Everblock\PrestaShopBundle\Domain\EverBlock\Command\DuplicateEverBlockCommand;
use Everblock\PrestaShopBundle\Domain\EverBlock\Command\ToggleEverBlockStatusCommand;
use Everblock\PrestaShopBundle\Domain\EverBlock\CommandHandler\DuplicateEverBlockHandler;
use Everblock\PrestaShopBundle\Domain\EverBlock\CommandHandler\ToggleEverBlockStatusHandler;
use Everblock\PrestaShopBundle\Domain\EverBlock\Repository\EverBlockRepositoryInterface;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\CannotDuplicateEverBlockException;
use Everblock\PrestaShopBundle\Domain\EverBlock\Exception\CannotToggleEverBlockStatusException;

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

class InMemoryEverBlockRepository implements EverBlockRepositoryInterface
{
    public $duplicatedIds = [];
    public $toggledIds = [];
    public $statusMap = [];
    public $deletedIds = [];

    public function duplicate(int $everBlockId): int
    {
        if ($everBlockId === 99) {
            throw new CannotDuplicateEverBlockException('boom');
        }
        $this->duplicatedIds[] = $everBlockId;

        return $everBlockId + 1000;
    }

    public function toggleStatus(int $everBlockId): bool
    {
        if ($everBlockId === 77) {
            throw new CannotToggleEverBlockStatusException('boom');
        }
        $this->toggledIds[] = $everBlockId;
        $this->statusMap[$everBlockId] = !($this->statusMap[$everBlockId] ?? false);

        return $this->statusMap[$everBlockId];
    }

    public function updateStatus(int $everBlockId, bool $enabled): void
    {
        $this->statusMap[$everBlockId] = $enabled;
    }

    public function delete(int $everBlockId): void
    {
        $this->deletedIds[] = $everBlockId;
    }
}

$repository = new InMemoryEverBlockRepository();
$duplicateHandler = new DuplicateEverBlockHandler($repository);
$toggleHandler = new ToggleEverBlockStatusHandler($repository);

$resultId = $duplicateHandler->handle(new DuplicateEverBlockCommand(5));
expect($resultId === 1005, 'Unexpected duplicated block id');
expect($repository->duplicatedIds === [5], 'Duplicate operation not recorded');

$status = $toggleHandler->handle(new ToggleEverBlockStatusCommand(5));
expect($status === true, 'Status should have been toggled to true');
$status = $toggleHandler->handle(new ToggleEverBlockStatusCommand(5));
expect($status === false, 'Status should have been toggled to false');

$exceptionThrown = false;
try {
    $duplicateHandler->handle(new DuplicateEverBlockCommand(99));
} catch (CannotDuplicateEverBlockException $exception) {
    $exceptionThrown = true;
}
expect($exceptionThrown, 'Duplicate handler should propagate repository exceptions');

echo "DuplicateEverBlockHandlerTest passed\n";
