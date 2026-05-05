<?php

declare(strict_types=1);

namespace Everblock\Tools\Handler;

use Everblock\Tools\Entity\Block;
use Everblock\Tools\Entity\Faq;
use Everblock\Tools\Entity\Page;
use Everblock\Tools\Entity\Shortcode;
use Everblock\Tools\Query\GetAdminItemQuery;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\FaqRepository;
use Everblock\Tools\Repository\HookRepository;
use Everblock\Tools\Repository\PageRepository;
use Everblock\Tools\Repository\ShortcodeRepository;

final class GetAdminItemHandler
{
    public function __construct(
        private readonly BlockRepository $blockRepository,
        private readonly ShortcodeRepository $shortcodeRepository,
        private readonly FaqRepository $faqRepository,
        private readonly PageRepository $pageRepository,
        private readonly HookRepository $hookRepository
    ) {
    }

    public function handle(GetAdminItemQuery $query): array
    {
        return match ($query->section) {
            'blocks' => $this->blockData($query),
            'shortcodes' => $this->shortcodeData($query),
            'faqs' => $this->faqData($query),
            'pages' => $this->pageData($query),
            'hooks' => $query->id ? (array) $this->hookRepository->find($query->id) : ['active' => true],
            default => [],
        };
    }

    private function blockData(GetAdminItemQuery $query): array
    {
        $block = $query->id ? $this->blockRepository->find($query->id, $query->shopId) : new Block();
        if (!$block instanceof Block) {
            return [];
        }

        $data = get_object_vars($block);
        foreach (['categories', 'manufacturers', 'suppliers', 'cms_categories', 'groups'] as $field) {
            $data[$field] = $this->decodeIdList($data[$field] ?? null);
        }
        if (!$query->id) {
            $data['active'] = true;
            $data['add_container'] = true;
        }
        $this->flattenLocalized($data, 'content', $block->content);
        $this->flattenLocalized($data, 'custom_code', $block->custom_code);

        return $data;
    }

    private function shortcodeData(GetAdminItemQuery $query): array
    {
        $shortcode = $query->id ? $this->shortcodeRepository->find($query->id, $query->shopId) : new Shortcode();
        if (!$shortcode instanceof Shortcode) {
            return [];
        }

        $data = get_object_vars($shortcode);
        $this->flattenLocalized($data, 'title', is_array($shortcode->title) ? $shortcode->title : []);
        $this->flattenLocalized($data, 'content', is_array($shortcode->content) ? $shortcode->content : []);

        return $data;
    }

    private function faqData(GetAdminItemQuery $query): array
    {
        $faq = $query->id ? $this->faqRepository->find($query->id, $query->shopId) : new Faq();
        if (!$faq instanceof Faq) {
            return [];
        }

        $data = get_object_vars($faq);
        $this->flattenLocalized($data, 'title', is_array($faq->title) ? $faq->title : []);
        $this->flattenLocalized($data, 'content', is_array($faq->content) ? $faq->content : []);

        return $data;
    }

    private function pageData(GetAdminItemQuery $query): array
    {
        $page = $query->id ? $this->pageRepository->find($query->id, $query->shopId) : new Page();
        if (!$page instanceof Page) {
            return [];
        }

        $data = get_object_vars($page);
        $data['group_ids'] = $page->getAllowedGroups();
        foreach (['name', 'title', 'meta_description', 'short_description', 'link_rewrite', 'content'] as $field) {
            $this->flattenLocalized($data, $field, is_array($page->{$field}) ? $page->{$field} : []);
        }

        return $data;
    }

    private function flattenLocalized(array &$data, string $field, array $values): void
    {
        foreach ($values as $langId => $value) {
            $data[$field . '_' . (int) $langId] = $value;
        }
    }

    private function decodeIdList($value): array
    {
        if (is_array($value)) {
            return array_values(array_map('intval', $value));
        }
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode((string) $value, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_map('intval', $decoded));
    }
}
