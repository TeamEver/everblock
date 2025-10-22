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

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock\DataProvider;

use Context;
use EverBlockClass;
use Everblock\PrestaShopBundle\Form\Admin\EverBlock\EverBlockType;
use Everblock\PrestaShopBundle\Form\Admin\EverBlock\Provider\EverBlockFormChoicesProvider;
use Language;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Tools;

class EverBlockFormDataProvider implements FormDataProviderInterface
{
    private $legacyContext;

    private $choicesProvider;

    public function __construct(LegacyContext $legacyContext, EverBlockFormChoicesProvider $choicesProvider)
    {
        $this->legacyContext = $legacyContext;
        $this->choicesProvider = $choicesProvider;
    }

    public function getData($id = null)
    {
        if ($id) {
            $block = new EverBlockClass((int) $id);
            if (is_array($block->content)) {
                return $this->mapBlockToData($block);
            }
        }

        return $this->getDefaultData();
    }

    public function setData($id, array $data)
    {
        return false;
    }

    public function getDefaultData(): array
    {
        $languages = $this->getLanguages();
        $content = [];
        $customCode = [];
        foreach ($languages as $language) {
            $content[(int) $language['id_lang']] = '';
            $customCode[(int) $language['id_lang']] = '';
        }

        return [
            'id' => null,
            'name' => '',
            'hook_id' => null,
            'content' => $content,
            'custom_code' => $customCode,
            'active' => true,
            'device' => 0,
            'group_ids' => $this->getAllGroupIds(),
            'only_home' => false,
            'only_category' => false,
            'only_category_product' => false,
            'category_ids' => [],
            'only_manufacturer' => false,
            'manufacturer_ids' => [],
            'only_supplier' => false,
            'supplier_ids' => [],
            'only_cms_category' => false,
            'cms_category_ids' => [],
            'obfuscate_link' => false,
            'add_container' => true,
            'lazyload' => false,
            'background' => null,
            'css_class' => null,
            'data_attribute' => null,
            'bootstrap_class' => 0,
            'position' => 0,
            'modal' => false,
            'delay' => null,
            'timeout' => null,
            'date_start' => null,
            'date_end' => null,
            'id_shop' => (int) $this->getContext()->shop->id,
        ];
    }

    public function getFormOptions(): array
    {
        return [
            'hooks' => $this->choicesProvider->getHookChoices(),
            'devices' => $this->choicesProvider->getDeviceChoices(),
            'categories' => $this->choicesProvider->getCategoryChoices(),
            'manufacturers' => $this->choicesProvider->getManufacturerChoices(),
            'suppliers' => $this->choicesProvider->getSupplierChoices(),
            'cms_categories' => $this->choicesProvider->getCmsCategoryChoices(),
            'bootstrap' => $this->choicesProvider->getBootstrapChoices(),
            'groups' => $this->choicesProvider->getGroupChoices(),
            'default_language_id' => (int) $this->getContext()->language->id,
        ];
    }

    public function getLegacyChoices(): array
    {
        return [
            'hooks' => $this->choicesProvider->getHookCollection(),
            'categories' => $this->choicesProvider->getCategoryCollection(),
            'manufacturers' => $this->choicesProvider->getManufacturerCollection(),
            'suppliers' => $this->choicesProvider->getSupplierCollection(),
            'cms_categories' => $this->choicesProvider->getCmsCategoryCollection(),
            'devices' => $this->mapChoicesToLegacyStructure($this->choicesProvider->getDeviceChoices(), 'id_device', 'name'),
            'bootstrap' => $this->mapChoicesToLegacyStructure($this->choicesProvider->getBootstrapChoices(), 'id_bootstrap', 'size'),
            'groups' => $this->choicesProvider->getGroupCollection(),
        ];
    }

    public function getLegacyFormValues($block, array $submittedData = []): array
    {
        $data = $block instanceof EverBlockClass ? $this->mapBlockToData($block) : $this->getDefaultData();

        if (!empty($submittedData)) {
            $normalized = $this->normalizeRequestData($submittedData, $block instanceof EverBlockClass ? (int) $block->id : null);
            $data = array_merge($data, $normalized);
        }

        $languages = $this->getLanguages();
        $values = [];

        $values['id_everblock'] = $data['id'];
        $values['name'] = $data['name'];
        $values['id_hook'] = $data['hook_id'];
        $values['active'] = (int) $data['active'];
        $values['device'] = $data['device'];
        $values['only_home'] = (int) $data['only_home'];
        $values['only_category'] = (int) $data['only_category'];
        $values['only_category_product'] = (int) $data['only_category_product'];
        $values['only_manufacturer'] = (int) $data['only_manufacturer'];
        $values['only_supplier'] = (int) $data['only_supplier'];
        $values['only_cms_category'] = (int) $data['only_cms_category'];
        $values['obfuscate_link'] = (int) $data['obfuscate_link'];
        $values['add_container'] = (int) $data['add_container'];
        $values['lazyload'] = (int) $data['lazyload'];
        $values['bootstrap_class'] = $data['bootstrap_class'];
        $values['position'] = $data['position'];
        $values['modal'] = (int) $data['modal'];
        $values['delay'] = $data['delay'];
        $values['timeout'] = $data['timeout'];
        $values['background'] = $data['background'];
        $values['css_class'] = $data['css_class'];
        $values['data_attribute'] = $data['data_attribute'];
        $values['date_start'] = $data['date_start'] instanceof \DateTimeInterface ? $data['date_start']->format('Y-m-d H:i:s') : '';
        $values['date_end'] = $data['date_end'] instanceof \DateTimeInterface ? $data['date_end']->format('Y-m-d H:i:s') : '';
        $values['groupBox'] = $data['group_ids'];
        foreach ($this->choicesProvider->getGroupCollection() as $group) {
            $values['groupBox_' . (int) $group['id_group']] = in_array((int) $group['id_group'], $data['group_ids']);
        }

        $values['categories[]'] = $data['category_ids'];
        $values['manufacturers[]'] = $data['manufacturer_ids'];
        $values['suppliers[]'] = $data['supplier_ids'];
        $values['cms_categories[]'] = $data['cms_category_ids'];

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $values['content_' . $idLang] = $data['content'][$idLang];
            $values['custom_code_' . $idLang] = $data['custom_code'][$idLang];
        }

        return $values;
    }

    public function getDocumentationInputs(): array
    {
        $templates = [
            EverBlockType::TAB_GENERAL => 'general.tpl',
            EverBlockType::TAB_TARGETING => 'targeting.tpl',
            EverBlockType::TAB_DISPLAY => 'display.tpl',
            EverBlockType::TAB_MODAL => 'modal.tpl',
            EverBlockType::TAB_SCHEDULE => 'schedule.tpl',
        ];

        $inputs = [];
        $context = $this->getContext();

        foreach ($templates as $tab => $template) {
            $path = _PS_MODULE_DIR_ . 'everblock/views/templates/admin/block/docs/' . $template;
            if (!Tools::file_exists_cache($path)) {
                continue;
            }

            $inputs[] = [
                'type' => 'html',
                'name' => 'documentation_' . $tab,
                'tab' => $tab,
                'html_content' => $context->smarty->fetch($path),
            ];
        }

        return $inputs;
    }

    public function normalizeRequestData(array $requestData, $id = null): array
    {
        $cleanData = $this->getDefaultData();

        if ($id) {
            $cleanData = array_merge($cleanData, ['id' => (int) $id]);
        }

        if (isset($requestData['id_everblock'])) {
            $cleanData['id'] = (int) $requestData['id_everblock'];
        }

        $cleanData['name'] = isset($requestData['name']) ? (string) $requestData['name'] : '';
        $cleanData['hook_id'] = isset($requestData['id_hook']) ? (int) $requestData['id_hook'] : null;

        $cleanData['active'] = $this->toBool($requestData, 'active');
        $cleanData['device'] = isset($requestData['device']) && $requestData['device'] !== '' ? (int) $requestData['device'] : 0;
        $cleanData['only_home'] = $this->toBool($requestData, 'only_home');
        $cleanData['only_category'] = $this->toBool($requestData, 'only_category');
        $cleanData['only_category_product'] = $this->toBool($requestData, 'only_category_product');
        $cleanData['only_manufacturer'] = $this->toBool($requestData, 'only_manufacturer');
        $cleanData['only_supplier'] = $this->toBool($requestData, 'only_supplier');
        $cleanData['only_cms_category'] = $this->toBool($requestData, 'only_cms_category');
        $cleanData['obfuscate_link'] = $this->toBool($requestData, 'obfuscate_link');
        $cleanData['add_container'] = $this->toBool($requestData, 'add_container', true);
        $cleanData['lazyload'] = $this->toBool($requestData, 'lazyload');
        $cleanData['modal'] = $this->toBool($requestData, 'modal');

        $cleanData['category_ids'] = $this->toIntArray($requestData, 'categories');
        $cleanData['manufacturer_ids'] = $this->toIntArray($requestData, 'manufacturers');
        $cleanData['supplier_ids'] = $this->toIntArray($requestData, 'suppliers');
        $cleanData['cms_category_ids'] = $this->toIntArray($requestData, 'cms_categories');

        $cleanData['group_ids'] = $this->toIntArray($requestData, 'groupBox');
        if (empty($cleanData['group_ids'])) {
            $cleanData['group_ids'] = $this->getAllGroupIds();
        }

        $cleanData['background'] = $this->emptyToNull($requestData, 'background');
        $cleanData['css_class'] = $this->emptyToNull($requestData, 'css_class');
        $cleanData['data_attribute'] = $this->emptyToNull($requestData, 'data_attribute');
        $cleanData['bootstrap_class'] = isset($requestData['bootstrap_class']) && $requestData['bootstrap_class'] !== '' ? (int) $requestData['bootstrap_class'] : 0;
        $cleanData['position'] = isset($requestData['position']) && $requestData['position'] !== '' ? (int) $requestData['position'] : 0;
        $cleanData['delay'] = isset($requestData['delay']) && $requestData['delay'] !== '' ? (int) $requestData['delay'] : null;
        $cleanData['timeout'] = isset($requestData['timeout']) && $requestData['timeout'] !== '' ? (int) $requestData['timeout'] : null;

        $cleanData['date_start'] = $this->parseDate($requestData, 'date_start');
        $cleanData['date_end'] = $this->parseDate($requestData, 'date_end');

        $cleanData['id_shop'] = (int) $this->getContext()->shop->id;

        $cleanData['content'] = $this->extractTranslations($requestData, 'content');
        $cleanData['custom_code'] = $this->extractTranslations($requestData, 'custom_code');

        return $cleanData;
    }

    public function getLanguages(): array
    {
        return Language::getLanguages(false);
    }

    public function getContext(): Context
    {
        return $this->choicesProvider->getContext();
    }

    private function mapBlockToData(EverBlockClass $block): array
    {
        $languages = $this->getLanguages();
        $content = [];
        $customCode = [];
        foreach ($languages as $language) {
            $langId = (int) $language['id_lang'];
            $content[$langId] = isset($block->content[$langId]) ? (string) $block->content[$langId] : '';
            $customCode[$langId] = isset($block->custom_code[$langId]) ? (string) $block->custom_code[$langId] : '';
        }

        return [
            'id' => (int) $block->id,
            'name' => (string) $block->name,
            'hook_id' => (int) $block->id_hook,
            'content' => $content,
            'custom_code' => $customCode,
            'active' => (bool) $block->active,
            'device' => (int) $block->device,
            'group_ids' => $this->decodeJsonToArray($block->groups, $this->getAllGroupIds()),
            'only_home' => (bool) $block->only_home,
            'only_category' => (bool) $block->only_category,
            'only_category_product' => (bool) $block->only_category_product,
            'category_ids' => $this->decodeJsonToArray($block->categories),
            'only_manufacturer' => (bool) $block->only_manufacturer,
            'manufacturer_ids' => $this->decodeJsonToArray($block->manufacturers),
            'only_supplier' => (bool) $block->only_supplier,
            'supplier_ids' => $this->decodeJsonToArray($block->suppliers),
            'only_cms_category' => (bool) $block->only_cms_category,
            'cms_category_ids' => $this->decodeJsonToArray($block->cms_categories),
            'obfuscate_link' => (bool) $block->obfuscate_link,
            'add_container' => (bool) $block->add_container,
            'lazyload' => (bool) $block->lazyload,
            'background' => $block->background ?: null,
            'css_class' => $block->css_class ?: null,
            'data_attribute' => $block->data_attribute ?: null,
            'bootstrap_class' => (int) $block->bootstrap_class,
            'position' => (int) $block->position,
            'modal' => (bool) $block->modal,
            'delay' => $block->delay !== '' ? (int) $block->delay : null,
            'timeout' => $block->timeout !== '' ? (int) $block->timeout : null,
            'date_start' => $this->stringToDateTime($block->date_start),
            'date_end' => $this->stringToDateTime($block->date_end),
            'id_shop' => (int) $block->id_shop,
        ];
    }

    private function decodeJsonToArray($value, array $default = []): array
    {
        if (empty($value)) {
            return $default;
        }

        $decoded = json_decode((string) $value, true);
        if (!is_array($decoded)) {
            return $default;
        }

        return array_map('intval', $decoded);
    }

    private function mapChoicesToLegacyStructure(array $choices, $idKey, $labelKey): array
    {
        $collection = [];
        foreach ($choices as $value => $label) {
            $collection[] = [
                $idKey => $value,
                $labelKey => $label,
            ];
        }

        return $collection;
    }

    private function getAllGroupIds(): array
    {
        $ids = [];
        foreach ($this->choicesProvider->getGroupCollection() as $group) {
            $ids[] = (int) $group['id_group'];
        }

        return $ids;
    }

    private function toBool(array $data, string $key, bool $default = false): bool
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        $value = $data[$key];

        if (is_bool($value)) {
            return $value;
        }

        return (bool) (int) $value;
    }

    private function toIntArray(array $data, string $key): array
    {
        if (!isset($data[$key])) {
            return [];
        }

        $value = $data[$key];
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $value)));
    }

    private function emptyToNull(array $data, string $key)
    {
        if (!isset($data[$key])) {
            return null;
        }

        $value = trim((string) $data[$key]);

        return '' === $value ? null : $value;
    }

    private function parseDate(array $data, string $key)
    {
        if (!isset($data[$key])) {
            return null;
        }

        return $this->stringToDateTime($data[$key]);
    }

    private function stringToDateTime($value)
    {
        if (!$value) {
            return null;
        }

        $value = (string) $value;
        if ('' === $value || '0000-00-00 00:00:00' === $value) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);

        return $date ?: null;
    }

    private function extractTranslations(array $data, string $prefix): array
    {
        $translations = [];
        foreach ($this->getLanguages() as $language) {
            $langId = (int) $language['id_lang'];
            $key = sprintf('%s_%d', $prefix, $langId);
            $translations[$langId] = isset($data[$key]) ? (string) $data[$key] : '';
        }

        return $translations;
    }
}
