<?php

namespace Everblock\PrestaShopBundle\Domain\EverBlock\CommandHandler;

use Everblock\PrestaShopBundle\Domain\EverBlock\Command\DuplicateEverBlockCommand;
use Everblock\PrestaShopBundle\Domain\EverBlock\Repository\EverBlockRepositoryInterface;

class DuplicateEverBlockHandler
{
    /**
     * @var EverBlockRepositoryInterface
     */
    private $repository;

    public function __construct(EverBlockRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(DuplicateEverBlockCommand $command): int
    {
        return $this->repository->duplicate($command->getEverBlockId());
    }
}
