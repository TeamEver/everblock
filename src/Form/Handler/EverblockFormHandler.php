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

namespace Everblock\Tools\Form\Handler;

use Configuration;
use Context;
use EverBlockClass;
use EverblockTools;
use Group;
use Language;
use Hook;
use Module;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Tools;
use Validate;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockFormHandler
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param EverBlockClass|null $everBlock
     *
     * @return array<string, mixed>
     */
    public function handle(FormInterface $form, Request $request, ?EverBlockClass $everBlock = null): array
    {
        $form->handleRequest($request);

        $result = [
            'submitted' => $form->isSubmitted(),
            'success' => false,
            'errors' => [],
            'id' => null,
            'stay' => (bool) $request->request->get('stay'),
        ];

        if (!$form->isSubmitted()) {
            return $result;
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $result['errors'][] = $error->getMessage();
            }

            return $result;
        }

        $data = $form->getData();

        if (!$everBlock || !Validate::isLoadedObject($everBlock)) {
            $everBlock = new EverBlockClass();
        }

        $everBlock->name = (string) $data['name'];
        $everBlock->id_shop = (int) $this->context->shop->id;
        $everBlock->id_hook = (int) $data['id_hook'];
        $everBlock->only_home = (bool) $data['only_home'];
        $everBlock->only_category = (bool) $data['only_category'];
        $everBlock->only_category_product = (bool) $data['only_category_product'];
        $everBlock->only_manufacturer = (bool) $data['only_manufacturer'];
        $everBlock->only_supplier = (bool) $data['only_supplier'];
        $everBlock->only_cms_category = (bool) $data['only_cms_category'];
        $everBlock->obfuscate_link = (bool) $data['obfuscate_link'];
        $everBlock->add_container = (bool) $data['add_container'];
        $everBlock->lazyload = (bool) $data['lazyload'];
        $everBlock->categories = json_encode(array_map('intval', (array) $data['categories']));
        $everBlock->manufacturers = json_encode(array_map('intval', (array) $data['manufacturers']));
        $everBlock->suppliers = json_encode(array_map('intval', (array) $data['suppliers']));
        $everBlock->cms_categories = json_encode(array_map('intval', (array) $data['cms_categories']));
        $everBlock->position = $data['position'] === '' ? $this->getNextPosition((int) $data['id_hook']) : (int) $data['position'];
        $everBlock->background = (string) $data['background'];
        $everBlock->css_class = (string) $data['css_class'];
        $everBlock->data_attribute = (string) $data['data_attribute'];
        $everBlock->bootstrap_class = (string) $data['bootstrap_class'];
        $everBlock->device = (int) $data['device'];
        $everBlock->delay = $data['delay'] === '' ? 0 : (int) $data['delay'];
        $everBlock->timeout = $data['timeout'] === '' ? 0 : (int) $data['timeout'];
        $everBlock->modal = (bool) $data['modal'];
        $everBlock->date_start = $this->formatDate($data['date_start'] ?? null);
        $everBlock->date_end = $this->formatDate($data['date_end'] ?? null);
        $everBlock->active = (bool) $data['active'];

        $groups = (array) $data['groupBox'];
        if (empty($groups)) {
            $groups = array_map(function (array $group) {
                return (int) $group['id_group'];
            }, Group::getGroups((int) $this->context->language->id));
        }
        $everBlock->groups = json_encode(array_map('intval', $groups));

        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $content = $data['content'][$idLang] ?? '';
            $everBlock->content[$idLang] = EverblockTools::convertImagesToWebP((string) $content);
            $everBlock->custom_code[$idLang] = (string) ($data['custom_code'][$idLang] ?? '');
        }

        try {
            if (!$everBlock->save()) {
                $result['errors'][] = $this->trans('An error occurred while saving the block.');

                return $result;
            }
        } catch (\Exception $exception) {
            $result['errors'][] = $exception->getMessage();

            return $result;
        }

        $hookName = Hook::getNameById((int) $everBlock->id_hook);
        $module = Module::getInstanceByName('everblock');
        if ($module && $hookName) {
            $module->registerHook($hookName);
        }

        if (Configuration::get('EVERPSCSS_CACHE')) {
            Tools::clearAllCache();
        }

        $result['success'] = true;
        $result['id'] = (int) $everBlock->id;

        return $result;
    }

    private function getNextPosition(int $idHook): int
    {
        $sql = new \DbQuery();
        $sql->select('MAX(position)');
        $sql->from('everblock');
        $sql->where('id_hook = ' . (int) $idHook);
        $sql->where('id_shop = ' . (int) $this->context->shop->id);
        $position = (int) \Db::getInstance()->getValue($sql);

        return $position + 1;
    }

    /**
     * @param mixed $value
     */
    private function formatDate($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return '';
    }

    private function trans(string $message): string
    {
        return $this->context->getTranslator()->trans($message, [], 'Modules.Everblock.Admin');
    }
}
