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
 */

namespace Everblock\Tools\Service;

use Configuration;
use Context;
use Db;
use EverBlockClass;
use EverblockTools;
use Everblock\Tools\Service\EverblockCache;
use Group;
use Hook;
use Language;
use Module;
use PrestaShopException;
use Tools;

class EverBlockManager
{
    public function duplicate(int $idEverBlock): EverBlockClass
    {
        $source = new EverBlockClass($idEverBlock);
        if (!$source->id) {
            throw new PrestaShopException('Everblock not found');
        }

        $newBlock = new EverBlockClass();
        $fields = $source->getFields();
        foreach ($fields as $field => $value) {
            if (in_array($field, ['id_everblock', 'id'])) {
                continue;
            }
            $newBlock->{$field} = $value;
        }

        $newBlock->active = false;
        $newBlock->id = null;
        $newBlock->id_everblock = null;

        $langFields = [];
        foreach ($source->getFieldsLang() as $langRow) {
            $langFields[(int) $langRow['id_lang']] = $langRow;
        }
        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $newBlock->content[$idLang] = isset($langFields[$idLang]['content']) ? $langFields[$idLang]['content'] : '';
            $newBlock->custom_code[$idLang] = isset($langFields[$idLang]['custom_code']) ? $langFields[$idLang]['custom_code'] : '';
        }

        if (!$newBlock->add()) {
            throw new PrestaShopException('Unable to duplicate everblock');
        }

        return $newBlock;
    }

    public function exportSql(int $idEverBlock): string
    {
        $sql = EverblockTools::exportBlockSQL($idEverBlock);
        if (!$sql) {
            throw new PrestaShopException('Unable to export block SQL');
        }

        return $sql;
    }

    public function toggleStatus(int $idEverBlock): bool
    {
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'everblock`'
            . ' SET `active` = (1 - `active`)' .
            ' WHERE `id_everblock` = ' . (int) $idEverBlock
        );
    }

    public function updateBlock(array $data, ?int $idEverBlock = null): EverBlockClass
    {
        $context = Context::getContext();
        $block = $idEverBlock ? new EverBlockClass($idEverBlock) : new EverBlockClass();

        $block->name = $data['name'];
        $block->id_shop = (int) $context->shop->id;
        $block->id_hook = (int) $data['id_hook'];
        $block->only_home = (int) $data['only_home'];
        $block->only_category = (int) $data['only_category'];
        $block->only_category_product = (int) $data['only_category_product'];
        $block->only_manufacturer = (int) $data['only_manufacturer'];
        $block->only_supplier = (int) $data['only_supplier'];
        $block->only_cms_category = (int) $data['only_cms_category'];
        $block->obfuscate_link = (int) $data['obfuscate_link'];
        $block->add_container = (int) $data['add_container'];
        $block->lazyload = (int) $data['lazyload'];
        $block->categories = json_encode($data['categories']);
        $block->manufacturers = json_encode($data['manufacturers']);
        $block->suppliers = json_encode($data['suppliers']);
        $block->cms_categories = json_encode($data['cms_categories']);
        $block->position = (int) $data['position'];
        $block->background = pSQL($data['background']);
        $block->css_class = pSQL($data['css_class']);
        $block->data_attribute = pSQL($data['data_attribute']);
        $block->bootstrap_class = pSQL($data['bootstrap_class']);
        $block->device = (int) $data['device'];
        $block->delay = (int) $data['delay'];
        $block->timeout = (int) $data['timeout'];
        $block->modal = (int) $data['modal'];
        $block->date_start = pSQL($data['date_start']);
        $block->date_end = pSQL($data['date_end']);
        $block->active = (int) $data['active'];

        $groups = $data['groups'];
        if (empty($groups)) {
            $groups = [];
            foreach (Group::getGroups((int) $context->language->id, (int) $context->shop->id) as $group) {
                $groups[] = (int) $group['id_group'];
            }
        }
        $block->groups = json_encode($groups);

        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $originalContent = $data['content'][$idLang] ?? '';
            $convertedContent = EverblockTools::convertImagesToWebP($originalContent);
            $block->content[$idLang] = $convertedContent;
            $block->custom_code[$idLang] = $data['custom_code'][$idLang] ?? '';
        }

        if ($block->id) {
            $result = $block->update();
        } else {
            $result = $block->add();
        }

        if (!$result) {
            throw new PrestaShopException('Unable to save everblock');
        }

        $module = Module::getInstanceByName('everblock');
        if ($module) {
            $hookName = Hook::getNameById($block->id_hook);
            if ($hookName) {
                $module->registerHook($hookName);
            }
        }

        if ((bool) Configuration::get('EVERPSCSS_CACHE')) {
            $this->clearCache();
        }

        return $block;
    }

    public function delete(int $idEverBlock): bool
    {
        $block = new EverBlockClass($idEverBlock);
        if (!$block->id) {
            return false;
        }

        return (bool) $block->delete();
    }

    public function bulkDelete(array $ids): void
    {
        foreach ($ids as $id) {
            $this->delete((int) $id);
        }
    }

    public function bulkDuplicate(array $ids): void
    {
        foreach ($ids as $id) {
            $this->duplicate((int) $id);
        }
    }

    public function bulkToggle(array $ids, bool $enabled): void
    {
        foreach ($ids as $id) {
            $block = new EverBlockClass((int) $id);
            if (!$block->id) {
                continue;
            }
            $block->active = $enabled;
            $block->save();
        }
    }

    public function clearCache(): void
    {
        Tools::clearAllCache();

        $patterns = [
            'EverBlockClass_getAllBlocks_',
            'EverBlockClass_getBlocks_',
            'EverBlockClass_getBootstrapColClass_',
            'everblock-id_hook-',
            'everblock_google_reviews_',
        ];

        foreach ($patterns as $pattern) {
            EverblockCache::cacheDropByPattern($pattern);
        }
    }
}
