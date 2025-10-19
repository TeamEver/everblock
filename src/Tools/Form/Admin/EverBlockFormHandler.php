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

namespace Everblock\Tools\Form\Admin;

use Everblock\Tools\Service\EverBlockManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EverBlockFormHandler
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EverBlockFormDataProvider */
    private $dataProvider;

    /** @var EverBlockManager */
    private $manager;

    public function __construct(
        FormFactoryInterface $formFactory,
        EverBlockFormDataProvider $dataProvider,
        EverBlockManager $manager
    ) {
        $this->formFactory = $formFactory;
        $this->dataProvider = $dataProvider;
        $this->manager = $manager;
    }

    public function getForm(Request $request, ?int $id = null): FormInterface
    {
        $data = $this->dataProvider->getData($id);
        $options = $this->dataProvider->getOptions();

        return $this->formFactory->create(EverBlockType::class, $data, $options);
    }

    public function handle(Request $request, ?int $id = null)
    {
        $form = $this->getForm($request, $id);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $normalized = $this->normalizeData($form->getData());
            $block = $this->manager->updateBlock($normalized, $id);

            return [$form, $block];
        }

        return [$form, null];
    }

    private function normalizeData(array $data): array
    {
        $booleanFields = [
            'only_home',
            'only_category',
            'only_category_product',
            'only_manufacturer',
            'only_supplier',
            'only_cms_category',
            'obfuscate_link',
            'add_container',
            'lazyload',
            'modal',
            'active',
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = isset($data[$field]) ? (bool) $data[$field] : false;
        }

        $collectionFields = ['categories', 'manufacturers', 'suppliers', 'cms_categories', 'groups'];
        foreach ($collectionFields as $field) {
            if (!isset($data[$field]) || !is_array($data[$field])) {
                $data[$field] = [];
            }
            $data[$field] = array_map('intval', $data[$field]);
        }

        $data['device'] = isset($data['device']) ? (int) $data['device'] : 0;
        $data['bootstrap_class'] = isset($data['bootstrap_class']) ? (int) $data['bootstrap_class'] : 0;
        $data['position'] = isset($data['position']) ? (int) $data['position'] : 0;
        $data['delay'] = isset($data['delay']) ? (int) $data['delay'] : 0;
        $data['timeout'] = isset($data['timeout']) ? (int) $data['timeout'] : 0;
        $data['date_start'] = $data['date_start'] ?: null;
        $data['date_end'] = $data['date_end'] ?: null;

        return $data;
    }
}
