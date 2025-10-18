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

namespace Everblock\Tools\Form\DataProvider;

use Context;
use Everblock\Tools\Service\Domain\EverBlockFaqDomainService;
use Language;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class FaqFormDataProvider
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var EverBlockFaqDomainService
     */
    private $faqDomainService;

    public function __construct(Context $context, EverBlockFaqDomainService $faqDomainService)
    {
        $this->context = $context;
        $this->faqDomainService = $faqDomainService;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultData(): array
    {
        $languages = $this->getLanguages();
        $titles = [];
        $contents = [];

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $titles[$idLang] = '';
            $contents[$idLang] = '';
        }

        return [
            'id_everblock_faq' => null,
            'tag_name' => '',
            'title' => $titles,
            'content' => $contents,
            'position' => 0,
            'active' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(int $faqId): array
    {
        $default = $this->getDefaultData();
        $faq = $this->faqDomainService->find($faqId, (int) $this->context->shop->id);
        if (null === $faq) {
            throw new \RuntimeException($this->trans('The requested FAQ entry cannot be found.'));
        }

        $default['id_everblock_faq'] = (int) $faq->getId();
        $default['tag_name'] = (string) ($faq->getTagName() ?? '');
        $default['position'] = (int) $faq->getPosition();
        $default['active'] = (bool) $faq->isActive();

        $titles = $default['title'];
        $contents = $default['content'];
        foreach ($faq->getTranslations() as $translation) {
            $languageId = $translation->getLanguageId();
            $titles[$languageId] = (string) ($translation->getTitle() ?? '');
            $contents[$languageId] = (string) ($translation->getContent() ?? '');
        }

        $default['title'] = $titles;
        $default['content'] = $contents;

        return $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        return [
            'languages' => $this->getLanguages(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getLanguages(): array
    {
        return Language::getLanguages(false);
    }

    private function trans(string $message): string
    {
        return $this->context->getTranslator()->trans(
            $message,
            [],
            'Modules.Everblock.Admin'
        );
    }
}
