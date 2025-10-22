<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\CommandHandler;

use Everblock\PrestaShopBundle\Domain\EverBlock\Command\ToggleEverBlockStatusCommand;
use Everblock\PrestaShopBundle\Domain\EverBlock\Repository\EverBlockRepositoryInterface;

class ToggleEverBlockStatusHandler
{
    /**
     * @var EverBlockRepositoryInterface
     */
    private $repository;

    public function __construct(EverBlockRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(ToggleEverBlockStatusCommand $command): bool
    {
        return $this->repository->toggleStatus($command->getEverBlockId());
    }
}
