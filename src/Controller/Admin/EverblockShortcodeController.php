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

namespace Everblock\Tools\Controller\Admin;

use Context;
use Everblock\Tools\Form\DataProvider\ShortcodeFormDataProvider;
use Everblock\Tools\Form\Type\ShortcodeFormType;
use Everblock\Tools\Grid\Data\ShortcodeGridDataFactory;
use Everblock\Tools\Grid\Definition\Factory\ShortcodeGridDefinitionFactory;
use PrestaShopException;
use Shop;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Validate;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockShortcodeController extends BaseEverblockController
{
    /**
     * @var ShortcodeGridDefinitionFactory
     */
    private $gridDefinitionFactory;

    /**
     * @var ShortcodeGridDataFactory
     */
    private $gridDataFactory;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var ShortcodeFormDataProvider
     */
    private $formDataProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        ShortcodeGridDefinitionFactory $gridDefinitionFactory,
        ShortcodeGridDataFactory $gridDataFactory,
        FormFactoryInterface $formFactory,
        ShortcodeFormDataProvider $formDataProvider,
        RouterInterface $router,
        ?Context $context = null,
        ?\PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository $moduleRepository = null,
        ?\Symfony\Contracts\Translation\TranslatorInterface $translator = null,
        ?\Everblock\Tools\Service\Admin\NavigationBuilder $navigationBuilder = null
    ) {
        parent::__construct($context, $moduleRepository, $translator, $navigationBuilder);

        $this->gridDefinitionFactory = $gridDefinitionFactory;
        $this->gridDataFactory = $gridDataFactory;
        $this->formFactory = $formFactory;
        $this->formDataProvider = $formDataProvider;
        $this->router = $router;
    }

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('view', 'AdminEverBlockShortcode');

        $filters = $request->query->all('filters');
        if (!is_array($filters)) {
            $filters = [];
        }

        $gridDefinition = $this->gridDefinitionFactory->getDefinition();
        $gridData = $this->gridDataFactory->getData($filters);

        $content = [
            'grid_definition' => $gridDefinition,
            'grid_data' => $gridData,
            'filters' => $filters,
            'create_url' => $this->router->generate('everblock_admin_shortcodes_create'),
            'bulk_delete_url' => $this->router->generate('everblock_admin_shortcodes_bulk_delete'),
            'edit_route' => 'everblock_admin_shortcodes_edit',
            'delete_route' => 'everblock_admin_shortcodes_delete',
        ];

        return $this->renderLayout(
            $this->translate('Shortcodes', [], 'Modules.Everblock.Admin'),
            $content,
            '@Modules/everblock/templates/admin/everblock/shortcode/index.html.twig',
            [
                'page_identifier' => 'shortcodes',
            ]
        );
    }

    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('create', 'AdminEverBlockShortcode');

        if (!$this->isShopContextValid()) {
            $this->addFlash('error', $this->translate('You need to select a shop before managing shortcodes.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_shortcodes');
        }

        $formData = $this->formDataProvider->getDefaultData();
        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(ShortcodeFormType::class, $formData, $formOptions);

        $result = $this->handleForm($form, $request, $formOptions['languages']);

        if ($result['submitted'] && $result['success']) {
            $this->addFlash('success', $this->translate('The shortcode has been created successfully.', [], 'Modules.Everblock.Admin'));

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_shortcodes_edit', [
                    'shortcodeId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_shortcodes');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Create shortcode', [], 'Modules.Everblock.Admin'),
            [
                'form' => $form->createView(),
            ],
            '@Modules/everblock/templates/admin/everblock/shortcode/form.html.twig',
            [
                'page_identifier' => 'shortcodes',
            ]
        );
    }

    public function edit(int $shortcodeId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('update', 'AdminEverBlockShortcode');

        if (!$this->isShopContextValid()) {
            $this->addFlash('error', $this->translate('You need to select a shop before managing shortcodes.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_shortcodes');
        }

        try {
            $formData = $this->formDataProvider->getData($shortcodeId);
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('everblock_admin_shortcodes');
        }

        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(ShortcodeFormType::class, $formData, $formOptions);

        $shortcode = new \EverblockShortcode($shortcodeId);
        $result = $this->handleForm($form, $request, $formOptions['languages'], $shortcode);

        if ($result['submitted'] && $result['success']) {
            $this->addFlash('success', $this->translate('The shortcode has been updated successfully.', [], 'Modules.Everblock.Admin'));

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_shortcodes_edit', [
                    'shortcodeId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_shortcodes');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Edit shortcode', [], 'Modules.Everblock.Admin'),
            [
                'form' => $form->createView(),
            ],
            '@Modules/everblock/templates/admin/everblock/shortcode/form.html.twig',
            [
                'page_identifier' => 'shortcodes',
            ]
        );
    }

    public function delete(int $shortcodeId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', 'AdminEverBlockShortcode');

        $shortcode = new \EverblockShortcode($shortcodeId);
        if (!Validate::isLoadedObject($shortcode)) {
            $this->addFlash('error', $this->translate('Unable to delete this shortcode.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_shortcodes');
        }

        try {
            if ($shortcode->delete()) {
                $this->addFlash('success', $this->translate('The shortcode has been deleted successfully.', [], 'Modules.Everblock.Admin'));
            } else {
                $this->addFlash('error', $this->translate('Unable to delete this shortcode.', [], 'Modules.Everblock.Admin'));
            }
        } catch (PrestaShopException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('everblock_admin_shortcodes');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', 'AdminEverBlockShortcode');

        $ids = $request->request->all('ids');
        if (!is_array($ids)) {
            $ids = [];
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $shortcode = new \EverblockShortcode((int) $id);
            if (!Validate::isLoadedObject($shortcode)) {
                continue;
            }

            try {
                if ($shortcode->delete()) {
                    ++$deleted;
                }
            } catch (PrestaShopException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        if ($deleted > 0) {
            $this->addFlash('success', $this->translate('%count% shortcode(s) have been deleted.', ['%count%' => $deleted], 'Modules.Everblock.Admin'));
        } else {
            $this->addFlash('error', $this->translate('No shortcode was deleted.', [], 'Modules.Everblock.Admin'));
        }

        return $this->redirectToRoute('everblock_admin_shortcodes');
    }

    /**
     * @param array<int, array<string, mixed>> $languages
     *
     * @return array{submitted: bool, success: bool, errors: string[], id: int|null, stay: bool}
     */
    private function handleForm(FormInterface $form, Request $request, array $languages, ?\EverblockShortcode $shortcode = null): array
    {
        $form->handleRequest($request);

        $result = [
            'submitted' => $form->isSubmitted(),
            'success' => false,
            'errors' => [],
            'id' => null,
            'stay' => $request->request->has('stay'),
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
        if (!$shortcode || !Validate::isLoadedObject($shortcode)) {
            $shortcode = new \EverblockShortcode();
        }

        $shortcode->shortcode = (string) $data['shortcode'];
        $shortcode->id_shop = (int) $this->context->shop->id;

        $titles = (array) $data['title'];
        $contents = (array) $data['content'];

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $langName = $language['name'] ?? $language['iso_code'] ?? (string) $idLang;

            $title = trim((string) ($titles[$idLang] ?? ''));
            if ($title === '') {
                $result['errors'][] = $this->translate('Title is missing for %lang%', ['%lang%' => $langName], 'Modules.Everblock.Admin');
            } elseif (!Validate::isGenericName($title)) {
                $result['errors'][] = $this->translate('Title is invalid for %lang%', ['%lang%' => $langName], 'Modules.Everblock.Admin');
            }

            $content = (string) ($contents[$idLang] ?? '');
            if ($content === '') {
                $result['errors'][] = $this->translate('Content is missing for %lang%', ['%lang%' => $langName], 'Modules.Everblock.Admin');
            }

            $titles[$idLang] = $title;
            $contents[$idLang] = $content;
        }

        if (!empty($result['errors'])) {
            return $result;
        }

        $shortcode->title = $titles;
        $shortcode->content = $contents;

        try {
            if (!$shortcode->save()) {
                $result['errors'][] = $this->translate('An error occurred while saving the shortcode.', [], 'Modules.Everblock.Admin');

                return $result;
            }
        } catch (PrestaShopException $exception) {
            $result['errors'][] = $exception->getMessage();

            return $result;
        }

        $result['success'] = true;
        $result['id'] = (int) $shortcode->id;

        return $result;
    }

    private function isShopContextValid(): bool
    {
        if (!class_exists(Shop::class)) {
            return true;
        }

        if (!Shop::isFeatureActive()) {
            return true;
        }

        return (int) $this->context->shop->getContext() === Shop::CONTEXT_SHOP;
    }
}
