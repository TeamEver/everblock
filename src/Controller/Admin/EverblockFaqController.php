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
use Everblock\Tools\Entity\EverBlockFaq;
use Everblock\Tools\Form\DataProvider\FaqFormDataProvider;
use Everblock\Tools\Form\Type\FaqFormType;
use Everblock\Tools\Grid\Data\FaqGridDataFactory;
use Everblock\Tools\Grid\Definition\Factory\FaqGridDefinitionFactory;
use Everblock\Tools\Service\Domain\EverBlockFaqDomainService;
use Everblock\Tools\Service\EverBlockFaqProvider;
use Everblock\Tools\Service\Legacy\EverblockToolsService;
use Shop;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Validate;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockFaqController extends BaseEverblockController
{
    /**
     * @var FaqGridDefinitionFactory
     */
    private $gridDefinitionFactory;

    /**
     * @var FaqGridDataFactory
     */
    private $gridDataFactory;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FaqFormDataProvider
     */
    private $formDataProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EverBlockFaqProvider
     */
    private $faqProvider;

    /**
     * @var EverBlockFaqDomainService
     */
    private $faqDomainService;

    private EverblockToolsService $legacyToolsService;

    public function __construct(
        FaqGridDefinitionFactory $gridDefinitionFactory,
        FaqGridDataFactory $gridDataFactory,
        FormFactoryInterface $formFactory,
        FaqFormDataProvider $formDataProvider,
        RouterInterface $router,
        EverBlockFaqProvider $faqProvider,
        EverBlockFaqDomainService $faqDomainService,
        ?EverblockToolsService $legacyToolsService = null,
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
        $this->faqProvider = $faqProvider;
        $this->faqDomainService = $faqDomainService;
        $this->legacyToolsService = $legacyToolsService ?? new EverblockToolsService();
    }

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('view', 'AdminEverBlockFaq');

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
            'create_url' => $this->router->generate('everblock_admin_faq_create'),
            'bulk_delete_url' => $this->router->generate('everblock_admin_faq_bulk_delete'),
            'edit_route' => 'everblock_admin_faq_edit',
            'delete_route' => 'everblock_admin_faq_delete',
            'duplicate_route' => 'everblock_admin_faq_duplicate',
        ];

        return $this->renderLayout(
            $this->translate('FAQ', [], 'Modules.Everblock.Admin'),
            $content,
            '@Modules/everblock/templates/admin/everblock/faq/index.html.twig',
            [
                'page_identifier' => 'faq',
            ]
        );
    }

    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('create', 'AdminEverBlockFaq');

        if (!$this->isShopContextValid()) {
            $this->addFlash('error', $this->translate('You need to select a shop before managing FAQs.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_faq');
        }

        $formData = $this->formDataProvider->getDefaultData();
        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(FaqFormType::class, $formData, $formOptions);

        $result = $this->handleForm($form, $request, $formOptions['languages']);

        if ($result['submitted'] && $result['success']) {
            $this->addFlash('success', $this->translate('The FAQ entry has been created successfully.', [], 'Modules.Everblock.Admin'));

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_faq_edit', [
                    'faqId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_faq');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Create FAQ', [], 'Modules.Everblock.Admin'),
            [
                'form' => $form->createView(),
            ],
            '@Modules/everblock/templates/admin/everblock/faq/form.html.twig',
            [
                'page_identifier' => 'faq',
            ]
        );
    }

    public function edit(int $faqId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('update', 'AdminEverBlockFaq');

        if (!$this->isShopContextValid()) {
            $this->addFlash('error', $this->translate('You need to select a shop before managing FAQs.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_faq');
        }

        try {
            $formData = $this->formDataProvider->getData($faqId);
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('everblock_admin_faq');
        }

        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(FaqFormType::class, $formData, $formOptions);

        $faq = $this->faqDomainService->find($faqId, (int) $this->context->shop->id);
        if (!$faq instanceof EverBlockFaq) {
            $this->addFlash('error', $this->translate('Unable to update this FAQ entry.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_faq');
        }

        $result = $this->handleForm($form, $request, $formOptions['languages'], $faq);

        if ($result['submitted'] && $result['success']) {
            $this->addFlash('success', $this->translate('The FAQ entry has been updated successfully.', [], 'Modules.Everblock.Admin'));

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_faq_edit', [
                    'faqId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_faq');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Edit FAQ', [], 'Modules.Everblock.Admin'),
            [
                'form' => $form->createView(),
            ],
            '@Modules/everblock/templates/admin/everblock/faq/form.html.twig',
            [
                'page_identifier' => 'faq',
            ]
        );
    }

    public function duplicate(int $faqId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('create', 'AdminEverBlockFaq');

        $faq = $this->faqDomainService->find($faqId, (int) $this->context->shop->id);
        if (!$faq instanceof EverBlockFaq) {
            $this->addFlash('error', $this->translate('Unable to duplicate this FAQ entry.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_faq');
        }

        $duplicate = new EverBlockFaq();
        $duplicate->setShopId($faq->getShopId());
        $duplicate->setTagName($faq->getTagName());
        $duplicate->setActive($faq->isActive());
        $duplicate->setPosition($faq->getPosition());

        $translations = [];
        foreach ($faq->getTranslations() as $translation) {
            $translations[$translation->getLanguageId()] = [
                'title' => $translation->getTitle(),
                'content' => $translation->getContent(),
            ];
        }

        try {
            $saved = $this->faqDomainService->save($duplicate, $translations);
            if ($saved->getId() !== null) {
                $this->addFlash('success', $this->translate('The FAQ entry has been duplicated successfully.', [], 'Modules.Everblock.Admin'));
                $this->faqProvider->clearCacheForShop($saved->getShopId());
                if (!empty($saved->getTagName())) {
                    $this->faqProvider->clearCacheForTag($saved->getShopId(), (string) $saved->getTagName());
                }
            } else {
                $this->addFlash('error', $this->translate('Unable to duplicate this FAQ entry.', [], 'Modules.Everblock.Admin'));
            }
        } catch (\Throwable $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('everblock_admin_faq');
    }

    public function delete(int $faqId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', 'AdminEverBlockFaq');

        $faq = $this->faqDomainService->find($faqId, (int) $this->context->shop->id);
        if (!$faq instanceof EverBlockFaq) {
            $this->addFlash('error', $this->translate('Unable to delete this FAQ entry.', [], 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('everblock_admin_faq');
        }

        try {
            $this->faqDomainService->delete($faqId, $faq->getShopId());
            $this->faqProvider->clearCacheForShop($faq->getShopId());
            if (!empty($faq->getTagName())) {
                $this->faqProvider->clearCacheForTag($faq->getShopId(), (string) $faq->getTagName());
            }
            $this->addFlash('success', $this->translate('The FAQ entry has been deleted successfully.', [], 'Modules.Everblock.Admin'));
        } catch (\Throwable $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('everblock_admin_faq');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', 'AdminEverBlockFaq');

        $ids = $request->request->all('ids');
        if (!is_array($ids)) {
            $ids = [];
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $faq = $this->faqDomainService->find((int) $id, (int) $this->context->shop->id);
            if (!$faq instanceof EverBlockFaq) {
                continue;
            }

            try {
                $this->faqDomainService->delete((int) $id, $faq->getShopId());
                ++$deleted;
                $this->faqProvider->clearCacheForShop($faq->getShopId());
                if (!empty($faq->getTagName())) {
                    $this->faqProvider->clearCacheForTag($faq->getShopId(), (string) $faq->getTagName());
                }
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        if ($deleted > 0) {
            $this->addFlash('success', $this->translate('%count% FAQ entry(ies) have been deleted.', ['%count%' => $deleted], 'Modules.Everblock.Admin'));
        } else {
            $this->addFlash('error', $this->translate('No FAQ entry was deleted.', [], 'Modules.Everblock.Admin'));
        }

        return $this->redirectToRoute('everblock_admin_faq');
    }

    /**
     * @param array<int, array<string, mixed>> $languages
     *
     * @return array{submitted: bool, success: bool, errors: string[], id: int|null, stay: bool}
     */
    private function handleForm(FormInterface $form, Request $request, array $languages, ?EverBlockFaq $faq = null): array
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
        if (!$faq instanceof EverBlockFaq) {
            $faq = new EverBlockFaq();
        }

        $tagName = trim((string) ($data['tag_name'] ?? ''));
        if ($tagName === '') {
            $result['errors'][] = $this->translate('The FAQ tag is required.', [], 'Modules.Everblock.Admin');
        } elseif (preg_match('/\s/', $tagName)) {
            $result['errors'][] = $this->translate('The FAQ tag cannot contain spaces.', [], 'Modules.Everblock.Admin');
        }

        $faq->setTagName((string) preg_replace('/\s+/', '', $tagName));
        $faq->setShopId((int) $this->context->shop->id);
        $faq->setPosition((int) ($data['position'] ?? 0));
        $faq->setActive(!empty($data['active']));

        $titles = (array) ($data['title'] ?? []);
        $contents = (array) ($data['content'] ?? []);

        $defaultLangId = (int) \Configuration::get('PS_LANG_DEFAULT');

        $translations = [];

        foreach ($languages as $language) {
            $idLang = (int) ($language['id_lang'] ?? 0);
            $langName = $language['name'] ?? $language['iso_code'] ?? (string) $idLang;

            $title = trim((string) ($titles[$idLang] ?? ''));
            $content = (string) ($contents[$idLang] ?? '');

            if ($idLang === $defaultLangId) {
                if ($title === '') {
                    $result['errors'][] = $this->translate('Title is missing for %lang%', ['%lang%' => $langName], 'Modules.Everblock.Admin');
                }

                if (trim(strip_tags($content)) === '') {
                    $result['errors'][] = $this->translate('Content is missing for %lang%', ['%lang%' => $langName], 'Modules.Everblock.Admin');
                }
            }

            if ($title !== '' && !Validate::isGenericName($title)) {
                $result['errors'][] = $this->translate('Title is invalid for %lang%', ['%lang%' => $langName], 'Modules.Everblock.Admin');
            }

            if ($content !== '' && !Validate::isCleanHtml($content)) {
                $result['errors'][] = $this->translate('Content is invalid for %lang%', ['%lang%' => $langName], 'Modules.Everblock.Admin');
            }

            $normalizedContent = $content === '' ? '' : $this->legacyToolsService->convertImagesToWebP($content);

            $titles[$idLang] = $title;
            $contents[$idLang] = $normalizedContent;

            $translations[$idLang] = [
                'title' => $title,
                'content' => $normalizedContent,
            ];
        }

        if (!empty($result['errors'])) {
            return $result;
        }

        try {
            $savedFaq = $this->faqDomainService->save($faq, $translations);
            if (null === $savedFaq->getId()) {
                $result['errors'][] = $this->translate('An error occurred while saving the FAQ entry.', [], 'Modules.Everblock.Admin');

                return $result;
            }
            $faq = $savedFaq;
        } catch (\Throwable $exception) {
            $result['errors'][] = $exception->getMessage();

            return $result;
        }

        $result['success'] = true;
        $result['id'] = (int) $faq->getId();
        $this->faqProvider->clearCacheForShop($faq->getShopId());
        if (!empty($faq->getTagName())) {
            $this->faqProvider->clearCacheForTag($faq->getShopId(), (string) $faq->getTagName());
        }

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
