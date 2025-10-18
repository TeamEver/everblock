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

namespace Everblock\Tools\Service;

use ArrayObject;
use Everblock\Tools\Repository\EverBlockFaqRepository;

class EverBlockFaqProvider
{
    public function __construct(private readonly EverBlockFaqRepository $repository)
    {
        if (class_exists(\EverblockFaq::class) && method_exists(\EverblockFaq::class, 'setProvider')) {
            \EverblockFaq::setProvider($this);
        }
    }

    /**
     * @return ArrayObject<int, mixed>[]
     */
    public function getAllFaq(int $shopId, int $languageId): array
    {
        return $this->hydrateFaqs($this->repository->getAllFaq($shopId, $languageId));
    }

    /**
     * @return ArrayObject<int, mixed>[]
     */
    public function getFaqByTagName(int $shopId, int $languageId, string $tagName): array
    {
        return $this->hydrateFaqs($this->repository->getFaqByTagName($shopId, $languageId, $tagName));
    }

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    public function clearCacheForShop(int $shopId): void
    {
        $this->repository->clearCacheForShop($shopId);
    }

    public function clearCacheForTag(int $shopId, string $tagName): void
    {
        $this->repository->clearCacheForTag($shopId, $tagName);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return ArrayObject<int, mixed>[]
     */
    private function hydrateFaqs(array $rows): array
    {
        $faqs = [];
        foreach ($rows as $row) {
            $row['id_everblock_faq'] = isset($row['id_everblock_faq']) ? (int) $row['id_everblock_faq'] : 0;
            $row['position'] = isset($row['position']) ? (int) $row['position'] : 0;
            $row['id_shop'] = isset($row['id_shop']) ? (int) $row['id_shop'] : 0;
            $row['active'] = isset($row['active']) ? (bool) $row['active'] : false;
            $row['tag_name'] = isset($row['tag_name']) ? (string) $row['tag_name'] : '';
            $row['title'] = isset($row['title']) ? (string) $row['title'] : '';
            $row['content'] = $row['content'] ?? '';
            $faqs[] = new ArrayObject($row, ArrayObject::ARRAY_AS_PROPS);
        }

        return $faqs;
    }
}
