<?php

namespace Everblock\Tools\Service;

use Context;
use EverBlockClass;

class EverblockQcdPageBuilderProvider
{
    /**
     * @param int $idLang
     * @param int $idShop
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableEverblocks(int $idLang, int $idShop): array
    {
        $idLang = (int) $idLang;
        $idShop = (int) $idShop;

        if ($idLang <= 0 || $idShop <= 0) {
            return [];
        }

        $blocks = EverBlockClass::getAllBlocks($idLang, $idShop);

        if (!is_array($blocks) || empty($blocks)) {
            return [];
        }

        $normalizedBlocks = [];
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $idEverblock = (int) ($block['id_everblock'] ?? 0);
            if ($idEverblock <= 0) {
                continue;
            }

            $name = trim((string) ($block['name'] ?? ''));

            $normalizedBlocks[] = [
                'id_everblock' => $idEverblock,
                'name' => $name,
                'active' => (int) ($block['active'] ?? 0),
                'id_hook' => (int) ($block['id_hook'] ?? 0),
                'position' => (int) ($block['position'] ?? 0),
                'label' => $this->buildBlockLabel($idEverblock, $name),
            ];
        }

        usort($normalizedBlocks, static function (array $first, array $second): int {
            if ((int) $first['position'] === (int) $second['position']) {
                return strcasecmp((string) $first['name'], (string) $second['name']);
            }

            return ((int) $first['position'] < (int) $second['position']) ? -1 : 1;
        });

        return $normalizedBlocks;
    }

    /**
     * @param int $idLang
     * @param int $idShop
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSelectChoices(int $idLang, int $idShop): array
    {
        $choices = [];
        foreach ($this->getAvailableEverblocks((int) $idLang, (int) $idShop) as $block) {
            $choices[] = [
                'value' => (int) $block['id_everblock'],
                'label' => (string) $block['label'],
                'name' => (string) $block['name'],
                'active' => (int) $block['active'],
                'position' => (int) $block['position'],
                'id_hook' => (int) $block['id_hook'],
            ];
        }

        return $choices;
    }

    /**
     * @param int $idLang
     * @param int $idShop
     * @param int $idEverblock
     *
     * @return array<string, mixed>|null
     */
    public function findEverblockById(int $idLang, int $idShop, int $idEverblock)
    {
        $idEverblock = (int) $idEverblock;

        if ($idEverblock <= 0) {
            return null;
        }

        foreach ($this->getAvailableEverblocks((int) $idLang, (int) $idShop) as $block) {
            if ((int) $block['id_everblock'] === $idEverblock) {
                return $block;
            }
        }

        return null;
    }

    private function buildBlockLabel(int $idEverblock, string $name): string
    {
        $cleanName = trim($name);
        if ($cleanName === '') {
            $cleanName = 'Everblock';
        }

        return sprintf('%s (#%d)', $cleanName, (int) $idEverblock);
    }

    public function resolveContextLanguageId(Context $context): int
    {
        return (int) (($context->language && isset($context->language->id)) ? $context->language->id : 0);
    }

    public function resolveContextShopId(Context $context): int
    {
        return (int) (($context->shop && isset($context->shop->id)) ? $context->shop->id : 0);
    }
}
