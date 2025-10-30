<?php

namespace Everblock\Tools\Repository;

use Doctrine\ORM\EntityRepository;
use Everblock\Tools\Entity\Everblock;

/**
 * @extends EntityRepository<Everblock>
 */
class EverblockRepository extends EntityRepository
{
    /**
     * @return list<Everblock>
     */
    public function findAllByShopAndLanguage(int $shopId, int $languageId): array
    {
        return $this->createQueryBuilder('block')
            ->addSelect('translation')
            ->leftJoin('block.translations', 'translation', 'WITH', 'translation.languageId = :languageId')
            ->andWhere('block.shopId = :shopId')
            ->setParameters([
                'languageId' => $languageId,
                'shopId' => $shopId,
            ])
            ->orderBy('block.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Everblock>
     */
    public function findActiveByHook(int $hookId, int $languageId, int $shopId): array
    {
        return $this->createQueryBuilder('block')
            ->addSelect('translation')
            ->leftJoin('block.translations', 'translation', 'WITH', 'translation.languageId = :languageId')
            ->andWhere('block.hookId = :hookId')
            ->andWhere('block.shopId = :shopId')
            ->andWhere('block.active = :active')
            ->setParameters([
                'languageId' => $languageId,
                'hookId' => $hookId,
                'shopId' => $shopId,
                'active' => true,
            ])
            ->orderBy('block.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdWithLanguage(int $id, int $languageId, int $shopId): ?Everblock
    {
        return $this->createQueryBuilder('block')
            ->addSelect('translation')
            ->leftJoin('block.translations', 'translation', 'WITH', 'translation.languageId = :languageId')
            ->andWhere('block.id = :id')
            ->andWhere('block.shopId = :shopId')
            ->setParameters([
                'languageId' => $languageId,
                'id' => $id,
                'shopId' => $shopId,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
