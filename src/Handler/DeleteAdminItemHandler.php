<?php

declare(strict_types=1);

namespace Everblock\Tools\Handler;

use Everblock\Tools\Command\DeleteAdminItemCommand;
use Everblock\Tools\Entity\Block;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\FaqRepository;
use Everblock\Tools\Repository\HookRepository;
use Everblock\Tools\Repository\PageRepository;
use Everblock\Tools\Repository\ShortcodeRepository;
use Everblock\Tools\Service\EverblockCache;

final class DeleteAdminItemHandler
{
    public function __construct(
        private BlockRepository $blockRepository,
        private ShortcodeRepository $shortcodeRepository,
        private FaqRepository $faqRepository,
        private PageRepository $pageRepository,
        private HookRepository $hookRepository
    ) {
    }

    public function __invoke(DeleteAdminItemCommand $command): bool
    {
        return $this->handle($command);
    }

    public function handle(DeleteAdminItemCommand $command): bool
    {
        $previous = match ($command->section) {
            'blocks' => $this->blockRepository->find($command->id, $command->shopId),
            'shortcodes' => $this->shortcodeRepository->find($command->id, $command->shopId),
            'faqs' => $this->faqRepository->find($command->id, $command->shopId),
            'pages' => $this->pageRepository->find($command->id, $command->shopId),
            default => null,
        };
        $deleted = match ($command->section) {
            'blocks' => $this->blockRepository->delete($command->id, $command->shopId),
            'shortcodes' => $this->shortcodeRepository->delete($command->id, $command->shopId),
            'faqs' => $this->faqRepository->delete($command->id, $command->shopId),
            'pages' => $this->pageRepository->delete($command->id, $command->shopId),
            'hooks' => $this->hookRepository->delete($command->id),
            default => false,
        };

        if ($deleted) {
            $this->clearObjectCache($command, $previous);
        }

        return $deleted;
    }

    private function clearObjectCache(DeleteAdminItemCommand $command, $previous): void
    {
        $languages = \Language::getLanguages(false);
        if ($command->section === 'blocks') {
            $hookId = $previous && isset($previous->id_hook) ? (int) $previous->id_hook : 0;
            Block::clearCache($command->id, $command->shopId, $languages, $hookId > 0 ? [$hookId] : []);

            return;
        }

        if ($command->section === 'shortcodes') {
            $shortcode = $previous && isset($previous->shortcode) ? trim((string) $previous->shortcode) : '';
            foreach ($languages as $language) {
                $langId = (int) ($language['id_lang'] ?? 0);
                if ($langId <= 0) {
                    continue;
                }
                EverblockCache::cacheDrop('EverblockShortcode_getAllShortcodes_' . $command->shopId . '_' . $langId);
                if ($shortcode !== '') {
                    EverblockCache::cacheDrop('EverblockShortcode_getEverShortcode_' . $shortcode . '_' . $command->shopId . '_' . $langId);
                }
            }
            EverblockCache::cacheDrop('EverblockShortcode_getAllShortcodeIds_' . $command->shopId);

            return;
        }

        if ($command->section === 'faqs') {
            $tag = $previous && isset($previous->tag_name) ? trim((string) $previous->tag_name) : '';
            foreach ($languages as $language) {
                $langId = (int) ($language['id_lang'] ?? 0);
                if ($langId <= 0) {
                    continue;
                }
                EverblockCache::cacheDrop('EverblockFaq_getAllFaq_' . $command->shopId . '_' . $langId);
                if ($tag !== '') {
                    EverblockCache::cacheDrop('EverblockFaq_getFaqByTagName_' . $command->shopId . '_' . $langId . '_' . $tag);
                }
            }
            EverblockCache::cacheDrop('EverblockFaq_getFirstActiveTagName_' . $command->shopId);
            EverblockCache::cacheDropByPattern('EverblockFaq_getByIds_' . $command->shopId . '_');

            return;
        }

        if ($command->section === 'pages') {
            foreach ($languages as $language) {
                $langId = (int) ($language['id_lang'] ?? 0);
                if ($langId <= 0) {
                    continue;
                }
                EverblockCache::cacheDrop('EverblockPage_getById_' . $command->id . '_' . $langId . '_' . $command->shopId);
                EverblockCache::cacheDropByPattern('EverblockPage_getPages_' . $langId . '_' . $command->shopId . '_');
                EverblockCache::cacheDropByPattern('EverblockPage_countPages_' . $langId . '_' . $command->shopId . '_');
            }
        }
    }
}
