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
use EverBlockClass;
use EverblockTools;
use Everblock\Tools\Bridge\Legacy\EverBlockLegacyAdapter;
use Everblock\Tools\Form\DataProvider\EverblockFormDataProvider;
use Everblock\Tools\Form\Handler\EverblockFormHandler;
use Everblock\Tools\Form\Type\EverblockFormType;
use Everblock\Tools\Grid\Data\EverblockGridDataFactory;
use Everblock\Tools\Grid\Definition\Factory\EverblockGridDefinitionFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\RouterInterface;
use Tools;
use Validate;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockBlockController extends BaseEverblockController
{
    /**
     * @var EverblockGridDefinitionFactory
     */
    private $gridDefinitionFactory;

    /**
     * @var EverblockGridDataFactory
     */
    private $gridDataFactory;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EverblockFormDataProvider
     */
    private $formDataProvider;

    /**
     * @var EverblockFormHandler
     */
    private $formHandler;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EverBlockLegacyAdapter
     */
    private $legacyAdapter;

    public function __construct(
        EverblockGridDefinitionFactory $gridDefinitionFactory,
        EverblockGridDataFactory $gridDataFactory,
        FormFactoryInterface $formFactory,
        EverblockFormDataProvider $formDataProvider,
        EverblockFormHandler $formHandler,
        RouterInterface $router,
        EverBlockLegacyAdapter $legacyAdapter,
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
        $this->formHandler = $formHandler;
        $this->router = $router;
        $this->legacyAdapter = $legacyAdapter;
    }

    public function index(Request $request): Response
    {
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
            'bulk_actions' => $gridDefinition['bulk_actions'],
            'row_actions' => $gridDefinition['row_actions'],
            'create_url' => $this->router->generate('everblock_admin_blocks_create'),
            'clear_cache_url' => $this->router->generate('everblock_admin_blocks_clear_cache'),
        ];

        return $this->renderLayout(
            $this->translate('HTML Blocks'),
            $content,
            '@Modules/everblock/templates/admin/everblock/block/index.html.twig',
            [
                'page_identifier' => 'blocks',
            ]
        );
    }

    public function create(Request $request): Response
    {
        $formData = $this->formDataProvider->getDefaultData();
        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(EverblockFormType::class, $formData, $formOptions);

        $result = $this->formHandler->handle($form, $request);

        if ($result['submitted'] && $result['success']) {
            $this->addFlash(
                'success',
                $this->translate('The block has been created successfully.')
            );

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_blocks_edit', [
                    'everblockId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_blocks');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Create block'),
            [
                'form' => $form->createView(),
                'form_tabs' => $formOptions['tabs'],
                'documentation' => $formOptions['documentation'],
            ],
            '@Modules/everblock/templates/admin/everblock/block/form.html.twig',
            [
                'page_identifier' => 'blocks',
            ]
        );
    }

    public function edit(int $everblockId, Request $request): Response
    {
        $formData = $this->formDataProvider->getData($everblockId);
        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(EverblockFormType::class, $formData, $formOptions);

        $result = $this->formHandler->handle($form, $request, new EverBlockClass($everblockId));

        if ($result['submitted'] && $result['success']) {
            $this->addFlash(
                'success',
                $this->translate('The block has been updated successfully.')
            );

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_blocks_edit', [
                    'everblockId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_blocks');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Edit block'),
            [
                'form' => $form->createView(),
                'form_tabs' => $formOptions['tabs'],
                'documentation' => $formOptions['documentation'],
            ],
            '@Modules/everblock/templates/admin/everblock/block/form.html.twig',
            [
                'page_identifier' => 'blocks',
            ]
        );
    }

    public function duplicate(int $everblockId): RedirectResponse
    {
        $source = new EverBlockClass($everblockId);
        if (!Validate::isLoadedObject($source)) {
            $this->addFlash('error', $this->translate('Unable to find the block to duplicate.'));

            return $this->redirectToRoute('everblock_admin_blocks');
        }

        $duplicate = new EverBlockClass();
        $duplicate->name = $source->name;
        $duplicate->id_hook = (int) $source->id_hook;
        $duplicate->id_shop = (int) $source->id_shop;
        $duplicate->only_home = (bool) $source->only_home;
        $duplicate->only_category = (bool) $source->only_category;
        $duplicate->only_category_product = (bool) $source->only_category_product;
        $duplicate->only_manufacturer = (bool) $source->only_manufacturer;
        $duplicate->only_supplier = (bool) $source->only_supplier;
        $duplicate->only_cms_category = (bool) $source->only_cms_category;
        $duplicate->categories = $source->categories;
        $duplicate->manufacturers = $source->manufacturers;
        $duplicate->suppliers = $source->suppliers;
        $duplicate->cms_categories = $source->cms_categories;
        $duplicate->groups = $source->groups;
        $duplicate->content = $source->content;
        $duplicate->custom_code = $source->custom_code;
        $duplicate->device = (int) $source->device;
        $duplicate->background = $source->background;
        $duplicate->css_class = $source->css_class;
        $duplicate->data_attribute = $source->data_attribute;
        $duplicate->bootstrap_class = $source->bootstrap_class;
        $duplicate->position = (int) $source->position;
        $duplicate->delay = (int) $source->delay;
        $duplicate->timeout = (int) $source->timeout;
        $duplicate->modal = (bool) $source->modal;
        $duplicate->date_start = $source->date_start;
        $duplicate->date_end = $source->date_end;
        $duplicate->active = false;
        $duplicate->obfuscate_link = (bool) $source->obfuscate_link;
        $duplicate->add_container = (bool) $source->add_container;
        $duplicate->lazyload = (bool) $source->lazyload;

        if ($duplicate->save()) {
            $this->addFlash('success', $this->translate('The block has been duplicated.'));
        } else {
            $this->addFlash('error', $this->translate('An error occurred while duplicating the block.'));
        }

        return $this->redirectToRoute('everblock_admin_blocks');
    }

    public function toggle(int $everblockId): RedirectResponse
    {
        $block = new EverBlockClass($everblockId);
        if (!Validate::isLoadedObject($block)) {
            $this->addFlash('error', $this->translate('Unable to update the block status.'));

            return $this->redirectToRoute('everblock_admin_blocks');
        }

        $block->active = (int) !$block->active;
        if ($block->save()) {
            $this->addFlash('success', $this->translate('The block status has been updated.'));
        } else {
            $this->addFlash('error', $this->translate('An error occurred while updating the block status.'));
        }

        return $this->redirectToRoute('everblock_admin_blocks');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->request->all('ids');
        if (!is_array($ids)) {
            $ids = [];
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $block = new EverBlockClass((int) $id);
            if (Validate::isLoadedObject($block) && $block->delete()) {
                ++$deleted;
            }
        }

        if ($deleted > 0) {
            $this->addFlash('success', $this->translate('%count% block(s) have been deleted.', ['%count%' => $deleted]));
        } else {
            $this->addFlash('error', $this->translate('No block was deleted.'));
        }

        return $this->redirectToRoute('everblock_admin_blocks');
    }

    public function bulkDuplicate(Request $request): RedirectResponse
    {
        $ids = $request->request->all('ids');
        if (!is_array($ids)) {
            $ids = [];
        }

        $duplicated = 0;
        foreach ($ids as $id) {
            $block = new EverBlockClass((int) $id);
            if (!Validate::isLoadedObject($block)) {
                continue;
            }

            $duplicate = $block->duplicateObject();
            if ($duplicate && $duplicate->id) {
                $duplicate->active = false;
                $duplicate->save();
                ++$duplicated;
            }
        }

        if ($duplicated > 0) {
            $this->addFlash('success', $this->translate('%count% block(s) have been duplicated.', ['%count%' => $duplicated]));
        } else {
            $this->addFlash('error', $this->translate('No block was duplicated.'));
        }

        return $this->redirectToRoute('everblock_admin_blocks');
    }

    public function export(int $everblockId): Response
    {
        $sql = EverblockTools::exportBlockSQL((int) $everblockId);

        if (!$sql) {
            $this->addFlash('error', $this->translate('An error occurred during the export.'));

            return $this->redirectToRoute('everblock_admin_blocks');
        }

        $response = new Response($sql);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('everblock_%d.sql', $everblockId)
        );
        $response->headers->set('Content-Type', 'application/sql');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function clearCache(): RedirectResponse
    {
        Tools::clearAllCache();

        if (isset($this->context->language, $this->context->shop)) {
            $this->legacyAdapter->clearCacheForLanguageAndShop(
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
        } else {
            $this->legacyAdapter->clearCache();
        }
        $this->addFlash('success', $this->translate('Cache has been cleared.'));

        return $this->redirectToRoute('everblock_admin_blocks');
    }
}
