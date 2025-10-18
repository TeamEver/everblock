<?php

/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Everblock\Tools\Service\Domain;

use Everblock\Tools\Entity\EverBlockFaq;
use Everblock\Tools\Repository\EverBlockFaqRepository;

class EverBlockFaqDomainService
{
    public function __construct(private readonly EverBlockFaqRepository $repository)
    {
    }

    public function getAll(int $shopId, int $languageId): array
    {
        return $this->repository->getAllFaq($shopId, $languageId);
    }

    public function findByTagName(int $shopId, int $languageId, string $tagName): array
    {
        return $this->repository->getFaqByTagName($shopId, $languageId, $tagName);
    }

    public function find(int $faqId, int $shopId): ?EverBlockFaq
    {
        return $this->repository->findById($faqId, $shopId);
    }

    /**
     * @param array<int, array{title: string|null, content: string|null}> $translations
     */
    public function save(EverBlockFaq $faq, array $translations): EverBlockFaq
    {
        return $this->repository->save($faq, $translations);
    }

    public function delete(int $faqId, int $shopId): void
    {
        $this->repository->delete($faqId, $shopId);
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }
}

