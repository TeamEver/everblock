<?php

declare(strict_types=1);

namespace Everblock\Tools\Handler;

use Everblock\Tools\Query\ListAdminItemsQuery;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\FaqRepository;
use Everblock\Tools\Repository\HookRepository;
use Everblock\Tools\Repository\PageRepository;
use Everblock\Tools\Repository\ShortcodeRepository;

final class ListAdminItemsHandler
{
    public function __construct(
        private BlockRepository $blockRepository,
        private ShortcodeRepository $shortcodeRepository,
        private FaqRepository $faqRepository,
        private PageRepository $pageRepository,
        private HookRepository $hookRepository
    ) {
    }

    public function __invoke(ListAdminItemsQuery $query): array
    {
        return $this->handle($query);
    }

    public function handle(ListAdminItemsQuery $query): array
    {
        return match ($query->section) {
            'blocks' => $this->blockRepository->list($query->shopId, $query->langId),
            'shortcodes' => $this->shortcodeRepository->list($query->shopId, $query->langId),
            'faqs' => $this->faqRepository->list($query->shopId, $query->langId),
            'pages' => $this->pageRepository->list($query->shopId, $query->langId),
            'hooks' => $this->hookRepository->listDisplayHooks(),
            default => [],
        };
    }
}
