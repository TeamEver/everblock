<?php

namespace Everblock\Tools\Service;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Everblock\Tools\Entity\Everblock;
use Everblock\Tools\Repository\EverblockRepository;

class EverblockManager
{
    private EntityManagerInterface $entityManager;

    private EverblockRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Everblock::class);
    }

    public function listBlocks(int $shopId, int $languageId): array
    {
        $blocks = $this->repository->findAllByShopAndLanguage($shopId, $languageId);

        return array_map(function (Everblock $block) use ($languageId) {
            return $block->toArray($languageId);
        }, $blocks);
    }

    public function getBlock(int $id, int $languageId, int $shopId): ?Everblock
    {
        return $this->repository->findOneByIdWithLanguage($id, $languageId, $shopId);
    }

    public function save(Everblock $block): Everblock
    {
        $this->entityManager->persist($block);
        $this->entityManager->flush();

        return $block;
    }

    public function delete(Everblock $block): void
    {
        $this->entityManager->remove($block);
        $this->entityManager->flush();
    }

    public function duplicateLanguage(int $fromLanguageId, int $toLanguageId, int $shopId): int
    {
        $blocks = $this->repository->findAllByShopAndLanguage($shopId, $fromLanguageId);
        $duplicated = 0;

        foreach ($blocks as $block) {
            $block->duplicateTranslation($fromLanguageId, $toLanguageId);
            $this->entityManager->persist($block);
            ++$duplicated;
        }

        $this->entityManager->flush();

        return $duplicated;
    }

    public function getBlocksByHook(int $hookId, int $languageId, int $shopId): array
    {
        $blocks = $this->repository->findActiveByHook($hookId, $languageId, $shopId);

        return array_map(function (Everblock $block) use ($languageId) {
            $data = $block->toArray($languageId);
            $data['bootstrap_class'] = $block->getNormalizedBootstrapClass();

            return $data;
        }, $blocks);
    }

    public function cleanCacheOnDate(int $languageId, int $shopId): void
    {
        $blocks = $this->listBlocks($shopId, $languageId);

        foreach ($blocks as $block) {
            $now = new DateTimeImmutable();
            $start = isset($block['date_start']) && $block['date_start'] instanceof DateTimeInterface ? $block['date_start'] : null;
            $end = isset($block['date_end']) && $block['date_end'] instanceof DateTimeInterface ? $block['date_end'] : null;

            if (($start instanceof DateTimeInterface && $start > $now) || ($end instanceof DateTimeInterface && $end < $now)) {
                EverblockCache::cacheDropByPattern('everblock-id_hook-' . (int) $block['id_hook']);
            }
        }
    }
}
