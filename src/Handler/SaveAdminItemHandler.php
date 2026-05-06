<?php

declare(strict_types=1);

namespace Everblock\Tools\Handler;

use Everblock\Tools\Command\SaveAdminItemCommand;
use Everblock\Tools\Entity\Block;
use Everblock\Tools\Entity\Faq;
use Everblock\Tools\Entity\Page;
use Everblock\Tools\Entity\Shortcode;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\FaqRepository;
use Everblock\Tools\Repository\HookRepository;
use Everblock\Tools\Repository\PageRepository;
use Everblock\Tools\Repository\ShortcodeRepository;
use Everblock\Tools\Service\EverblockCache;
use Everblock\Tools\Service\EverblockTools;
use Tools;

final class SaveAdminItemHandler
{
    public function __construct(
        private readonly BlockRepository $blockRepository,
        private readonly ShortcodeRepository $shortcodeRepository,
        private readonly FaqRepository $faqRepository,
        private readonly PageRepository $pageRepository,
        private readonly HookRepository $hookRepository
    ) {
    }

    public function handle(SaveAdminItemCommand $command): int
    {
        $previous = $command->id ? $this->previousItem($command) : null;
        $id = match ($command->section) {
            'blocks' => $this->saveBlock($command),
            'shortcodes' => $this->saveShortcode($command),
            'faqs' => $this->saveFaq($command),
            'pages' => $this->savePage($command),
            'hooks' => $this->hookRepository->save($command->id, $command->data),
            default => 0,
        };

        $this->clearObjectCache($command, $id, $previous);

        return $id;
    }

    private function saveBlock(SaveAdminItemCommand $command): int
    {
        $block = $command->id ? $this->blockRepository->find($command->id, $command->shopId) : new Block();
        $block ??= new Block();
        $data = $command->data;
        $block->id = $command->id;
        $block->id_everblock = $command->id;
        $block->id_shop = $command->shopId;
        $block->name = (string) ($data['name'] ?? '');
        $block->id_hook = (int) ($data['id_hook'] ?? 0);
        foreach (['only_home', 'only_category', 'only_category_product', 'only_manufacturer', 'only_supplier', 'only_cms_category', 'obfuscate_link', 'add_container', 'lazyload', 'modal', 'active'] as $boolField) {
            $block->{$boolField} = !empty($data[$boolField]);
        }
        foreach (['device', 'position', 'delay', 'timeout'] as $intField) {
            $block->{$intField} = (int) ($data[$intField] ?? 0);
        }
        foreach (['categories', 'manufacturers', 'suppliers', 'cms_categories', 'groups'] as $field) {
            $block->{$field} = json_encode(array_values(array_map('intval', (array) ($data[$field] ?? []))));
        }
        foreach (['background', 'css_class', 'data_attribute', 'bootstrap_class', 'date_start', 'date_end'] as $field) {
            $value = $data[$field] ?? null;
            $block->{$field} = $value !== null && $value !== '' ? (string) $value : null;
        }
        $block->content = $this->localized($data, 'content', $command->languages, true);
        $block->custom_code = $this->localized($data, 'custom_code', $command->languages, true);

        $id = $this->blockRepository->save($block, $command->languages);
        $this->registerModuleInHook((int) $block->id_hook);

        return $id;
    }

    private function saveShortcode(SaveAdminItemCommand $command): int
    {
        $shortcode = $command->id ? $this->shortcodeRepository->find($command->id, $command->shopId) : new Shortcode();
        $shortcode ??= new Shortcode();
        $shortcode->id = $command->id;
        $shortcode->id_everblock_shortcode = $command->id;
        $shortcode->id_shop = $command->shopId;
        $shortcode->shortcode = (string) ($command->data['shortcode'] ?? '');
        $shortcode->title = $this->localized($command->data, 'title', $command->languages);
        $shortcode->content = $this->localized($command->data, 'content', $command->languages, true);

        return $this->shortcodeRepository->save($shortcode, $command->languages);
    }

    private function saveFaq(SaveAdminItemCommand $command): int
    {
        $faq = $command->id ? $this->faqRepository->find($command->id, $command->shopId) : new Faq();
        $faq ??= new Faq();
        $faq->id = $command->id;
        $faq->id_everblock_faq = $command->id;
        $faq->id_shop = $command->shopId;
        $faq->tag_name = (string) ($command->data['tag_name'] ?? '');
        $faq->position = (int) ($command->data['position'] ?? 0);
        $faq->active = !empty($command->data['active']);
        $faq->title = $this->localized($command->data, 'title', $command->languages);
        $faq->content = $this->localized($command->data, 'content', $command->languages, true);

        return $this->faqRepository->save($faq, $command->languages);
    }

    private function savePage(SaveAdminItemCommand $command): int
    {
        $page = $command->id ? $this->pageRepository->find($command->id, $command->shopId) : new Page();
        $page ??= new Page();
        $page->id = $command->id;
        $page->id_everblock_page = $command->id;
        $page->id_shop = $command->shopId;
        $page->groups = json_encode(array_values(array_map('intval', (array) ($command->data['group_ids'] ?? []))));
        $page->active = !empty($command->data['active']);
        $page->position = (int) ($command->data['position'] ?? 0);
        if (!empty($command->data['cover_image_name'])) {
            $page->cover_image = (string) $command->data['cover_image_name'];
        }
        foreach (['name', 'title', 'meta_description', 'short_description', 'link_rewrite', 'content'] as $field) {
            $page->{$field} = $this->localized($command->data, $field, $command->languages, $field === 'content');
        }
        foreach ($page->link_rewrite as $langId => $rewrite) {
            if (!$rewrite) {
                $rewrite = $page->name[$langId] ?? '';
            }
            $page->link_rewrite[$langId] = Tools::link_rewrite((string) $rewrite);
        }

        return $this->pageRepository->save($page, $command->languages);
    }

    private function localized(array $data, string $field, array $languages, bool $convertImages = false): array
    {
        $values = [];
        foreach ($languages as $language) {
            $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
            if ($langId > 0) {
                $value = (string) ($data[$field . '_' . $langId] ?? '');
                $values[$langId] = $convertImages ? EverblockTools::convertImagesToWebP($value) : $value;
            }
        }

        return $values;
    }

    private function previousItem(SaveAdminItemCommand $command)
    {
        return match ($command->section) {
            'blocks' => $this->blockRepository->find((int) $command->id, $command->shopId),
            'shortcodes' => $this->shortcodeRepository->find((int) $command->id, $command->shopId),
            'faqs' => $this->faqRepository->find((int) $command->id, $command->shopId),
            'pages' => $this->pageRepository->find((int) $command->id, $command->shopId),
            default => null,
        };
    }

    private function clearObjectCache(SaveAdminItemCommand $command, int $id, $previous): void
    {
        if ($command->section === 'blocks') {
            $hookIds = [];
            if ($previous instanceof Block && $previous->id_hook > 0) {
                $hookIds[] = (int) $previous->id_hook;
            }
            if (!empty($command->data['id_hook'])) {
                $hookIds[] = (int) $command->data['id_hook'];
            }

            foreach ($command->languages as $language) {
                $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
                if ($langId <= 0) {
                    continue;
                }
                EverblockCache::cacheDrop('EverBlockClass_getAllBlocks_' . $langId . '_' . $command->shopId);
                foreach (array_unique($hookIds) as $hookId) {
                    EverblockCache::cacheDrop('EverBlockClass_getBlocks_' . $hookId . '_' . $langId . '_' . $command->shopId);
                }
            }
            foreach (array_unique($hookIds) as $hookId) {
                EverblockCache::cacheDropByPattern('everblock-id_hook-' . $hookId);
            }

            return;
        }

        if ($command->section === 'shortcodes') {
            $shortcodes = [];
            if ($previous instanceof Shortcode) {
                $shortcodes[] = trim((string) $previous->shortcode);
            }
            $shortcodes[] = trim((string) ($command->data['shortcode'] ?? ''));
            foreach ($command->languages as $language) {
                $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
                if ($langId <= 0) {
                    continue;
                }
                EverblockCache::cacheDrop('EverblockShortcode_getAllShortcodes_' . $command->shopId . '_' . $langId);
                foreach (array_unique(array_filter($shortcodes)) as $shortcode) {
                    EverblockCache::cacheDrop('EverblockShortcode_getEverShortcode_' . $shortcode . '_' . $command->shopId . '_' . $langId);
                }
            }
            EverblockCache::cacheDrop('EverblockShortcode_getAllShortcodeIds_' . $command->shopId);

            return;
        }

        if ($command->section === 'faqs') {
            $tags = [];
            if ($previous instanceof Faq) {
                $tags[] = trim($previous->tag_name);
            }
            $tags[] = trim((string) ($command->data['tag_name'] ?? ''));
            foreach ($command->languages as $language) {
                $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
                if ($langId <= 0) {
                    continue;
                }
                EverblockCache::cacheDrop('EverblockFaq_getAllFaq_' . $command->shopId . '_' . $langId);
                foreach (array_unique(array_filter($tags)) as $tag) {
                    EverblockCache::cacheDrop('EverblockFaq_getFaqByTagName_' . $command->shopId . '_' . $langId . '_' . $tag);
                }
            }
            EverblockCache::cacheDrop('EverblockFaq_getFirstActiveTagName_' . $command->shopId);
            EverblockCache::cacheDropByPattern('EverblockFaq_getByIds_' . $command->shopId . '_');

            return;
        }

        if ($command->section === 'pages') {
            foreach ($command->languages as $language) {
                $langId = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
                if ($langId <= 0) {
                    continue;
                }
                EverblockCache::cacheDrop('EverblockPage_getById_' . $id . '_' . $langId . '_' . $command->shopId);
                EverblockCache::cacheDropByPattern('EverblockPage_getPages_' . $langId . '_' . $command->shopId . '_');
                EverblockCache::cacheDropByPattern('EverblockPage_countPages_' . $langId . '_' . $command->shopId . '_');
            }
        }
    }

    private function registerModuleInHook(int $hookId): void
    {
        if ($hookId <= 0) {
            return;
        }

        $hookName = \Hook::getNameById($hookId);
        if (!$hookName || !\Validate::isHookName($hookName)) {
            return;
        }

        $module = \Module::getInstanceByName('everblock');
        if (!$module instanceof \Module || $module->isRegisteredInHook($hookName)) {
            return;
        }

        $module->registerHook($hookName);
    }
}
