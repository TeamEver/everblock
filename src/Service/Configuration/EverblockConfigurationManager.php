<?php

declare(strict_types=1);

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

namespace Everblock\Tools\Service\Configuration;

use PrestaShop\PrestaShop\Adapter\Configuration as ConfigurationAdapter;
use PrestaShop\PrestaShop\Adapter\Language\LanguageDataProvider;
use PrestaShop\PrestaShop\Adapter\Shop\Context as ShopContext;

class EverblockConfigurationManager
{
    private const PAGES_BASE_URL = 'EVERBLOCK_PAGES_BASE_URL';
    private const PAGES_PER_PAGE = 'EVERBLOCK_PAGES_PER_PAGE';
    private const FAQ_BASE_URL = 'EVERBLOCK_FAQ_BASE_URL';
    private const FAQ_PER_PAGE = 'EVERBLOCK_FAQ_PER_PAGE';
    private const GOOGLE_REVIEWS_CTA_LABEL = 'EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL';

    private ConfigurationAdapter $configuration;
    private ShopContext $shopContext;
    private LanguageDataProvider $languageProvider;

    public function __construct(
        ConfigurationAdapter $configuration,
        ShopContext $shopContext,
        LanguageDataProvider $languageProvider
    ) {
        $this->configuration = $configuration;
        $this->shopContext = $shopContext;
        $this->languageProvider = $languageProvider;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        [$shopGroupId, $shopId] = $this->getShopContextIds();

        return [
            'pages_base_url' => (string) $this->configuration->get(self::PAGES_BASE_URL, null, $shopGroupId, $shopId),
            'pages_per_page' => (int) $this->configuration->get(self::PAGES_PER_PAGE, null, $shopGroupId, $shopId),
            'faq_base_url' => (string) $this->configuration->get(self::FAQ_BASE_URL, null, $shopGroupId, $shopId),
            'faq_per_page' => (int) $this->configuration->get(self::FAQ_PER_PAGE, null, $shopGroupId, $shopId),
            'google_reviews_cta_label' => $this->getLocalizedConfiguration(self::GOOGLE_REVIEWS_CTA_LABEL, $shopGroupId, $shopId),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromForm(array $data): void
    {
        [$shopGroupId, $shopId] = $this->getShopContextIds();

        $this->configuration->set(self::PAGES_BASE_URL, (string) $data['pages_base_url'], $shopGroupId, $shopId);
        $this->configuration->set(self::PAGES_PER_PAGE, (int) $data['pages_per_page'], $shopGroupId, $shopId);
        $this->configuration->set(self::FAQ_BASE_URL, (string) $data['faq_base_url'], $shopGroupId, $shopId);
        $this->configuration->set(self::FAQ_PER_PAGE, (int) $data['faq_per_page'], $shopGroupId, $shopId);
        $this->configuration->set(
            self::GOOGLE_REVIEWS_CTA_LABEL,
            $data['google_reviews_cta_label'],
            $shopGroupId,
            $shopId
        );
    }

    /**
     * @return array<int, string>
     */
    private function getLocalizedConfiguration(string $key, ?int $shopGroupId, ?int $shopId): array
    {
        $values = [];
        $languages = $this->languageProvider->getLanguages(false);

        foreach ($languages as $language) {
            $languageId = (int) $language['id_lang'];
            $values[$languageId] = (string) $this->configuration->get(
                $key,
                $languageId,
                $shopGroupId,
                $shopId
            );
        }

        return $values;
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function getShopContextIds(): array
    {
        $shopId = $this->shopContext->getContextShopID();
        $shopGroupId = $this->shopContext->getContextShopGroupID();

        return [
            $shopGroupId > 0 ? $shopGroupId : null,
            $shopId > 0 ? $shopId : null,
        ];
    }
}
