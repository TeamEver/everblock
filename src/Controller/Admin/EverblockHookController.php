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

use Hook;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Everblock\Tools\Form\DataProvider\HookFormDataProvider;
use Everblock\Tools\Form\Handler\HookFormHandler;
use Everblock\Tools\Form\Type\HookFormType;
use Everblock\Tools\Grid\Data\HookGridDataFactory;
use Everblock\Tools\Grid\Definition\Factory\HookGridDefinitionFactory;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class EverblockHookController extends BaseEverblockController
{
    /**
     * @var HookGridDefinitionFactory
     */
    private $gridDefinitionFactory;

    /**
     * @var HookGridDataFactory
     */
    private $gridDataFactory;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var HookFormDataProvider
     */
    private $formDataProvider;

    /**
     * @var HookFormHandler
     */
    private $formHandler;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        HookGridDefinitionFactory $gridDefinitionFactory,
        HookGridDataFactory $gridDataFactory,
        FormFactoryInterface $formFactory,
        HookFormDataProvider $formDataProvider,
        HookFormHandler $formHandler,
        RouterInterface $router,
        ?\Context $context = null,
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
    }

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('view', 'AdminEverBlockHook');

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
            'create_url' => $this->router->generate('everblock_admin_hooks_create'),
            'bulk_enable_url' => $this->router->generate('everblock_admin_hooks_bulk_enable'),
            'bulk_disable_url' => $this->router->generate('everblock_admin_hooks_bulk_disable'),
            'bulk_delete_url' => $this->router->generate('everblock_admin_hooks_bulk_delete'),
            'toggle_route' => 'everblock_admin_hooks_toggle',
            'edit_route' => 'everblock_admin_hooks_edit',
        ];

        return $this->renderLayout(
            $this->translate('Hooks', [], 'Modules.Everblock.Admineverblockcontroller'),
            $content,
            '@Modules/everblock/templates/admin/everblock/hook/index.html.twig',
            [
                'page_identifier' => 'hooks',
            ]
        );
    }

    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('create', 'AdminEverBlockHook');

        $formData = $this->formDataProvider->getDefaultData();
        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(HookFormType::class, $formData, $formOptions);

        $result = $this->formHandler->handle($form, $request);

        if ($result['submitted'] && $result['success']) {
            $this->addFlash('success', $this->translate('The hook has been created successfully.', [], 'Modules.Everblock.Admineverblockcontroller'));

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_hooks_edit', [
                    'hookId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_hooks');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Create hook', [], 'Modules.Everblock.Admineverblockcontroller'),
            [
                'form' => $form->createView(),
            ],
            '@Modules/everblock/templates/admin/everblock/hook/form.html.twig',
            [
                'page_identifier' => 'hooks',
            ]
        );
    }

    public function edit(int $hookId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('update', 'AdminEverBlockHook');

        try {
            $formData = $this->formDataProvider->getData($hookId);
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('everblock_admin_hooks');
        }

        $formOptions = $this->formDataProvider->getFormOptions();
        $form = $this->formFactory->create(HookFormType::class, $formData, $formOptions);

        $result = $this->formHandler->handle($form, $request, new Hook($hookId));

        if ($result['submitted'] && $result['success']) {
            $this->addFlash('success', $this->translate('The hook has been updated successfully.', [], 'Modules.Everblock.Admineverblockcontroller'));

            if ($result['stay']) {
                return $this->redirectToRoute('everblock_admin_hooks_edit', [
                    'hookId' => $result['id'],
                ]);
            }

            return $this->redirectToRoute('everblock_admin_hooks');
        }

        if ($result['submitted'] && !$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->renderLayout(
            $this->translate('Edit hook', [], 'Modules.Everblock.Admineverblockcontroller'),
            [
                'form' => $form->createView(),
            ],
            '@Modules/everblock/templates/admin/everblock/hook/form.html.twig',
            [
                'page_identifier' => 'hooks',
            ]
        );
    }

    public function toggle(int $hookId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('update', 'AdminEverBlockHook');

        $hook = new Hook($hookId);
        if (!\Validate::isLoadedObject($hook)) {
            $this->addFlash('error', $this->translate('Unable to update the hook status.', [], 'Modules.Everblock.Admineverblockcontroller'));

            return $this->redirectToRoute('everblock_admin_hooks');
        }

        $hook->active = (bool) !$hook->active;

        if ($hook->save()) {
            $this->addFlash('success', $this->translate('The hook status has been updated.', [], 'Modules.Everblock.Admineverblockcontroller'));
        } else {
            $this->addFlash('error', $this->translate('An error occurred while updating the hook status.', [], 'Modules.Everblock.Admineverblockcontroller'));
        }

        return $this->redirectToRoute('everblock_admin_hooks');
    }

    public function bulkEnable(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('update', 'AdminEverBlockHook');

        $ids = $request->request->all('ids');
        if (!is_array($ids)) {
            $ids = [];
        }

        $updated = 0;
        foreach ($ids as $id) {
            $hook = new Hook((int) $id);
            if (!\Validate::isLoadedObject($hook)) {
                continue;
            }

            if (!$hook->active) {
                $hook->active = true;
                if ($hook->save()) {
                    ++$updated;
                }
            }
        }

        if ($updated > 0) {
            $this->addFlash('success', $this->translate('%count% hook(s) have been enabled.', ['%count%' => $updated], 'Modules.Everblock.Admineverblockcontroller'));
        } else {
            $this->addFlash('error', $this->translate('No hook was enabled.', [], 'Modules.Everblock.Admineverblockcontroller'));
        }

        return $this->redirectToRoute('everblock_admin_hooks');
    }

    public function bulkDisable(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('update', 'AdminEverBlockHook');

        $ids = $request->request->all('ids');
        if (!is_array($ids)) {
            $ids = [];
        }

        $updated = 0;
        foreach ($ids as $id) {
            $hook = new Hook((int) $id);
            if (!\Validate::isLoadedObject($hook)) {
                continue;
            }

            if ($hook->active) {
                $hook->active = false;
                if ($hook->save()) {
                    ++$updated;
                }
            }
        }

        if ($updated > 0) {
            $this->addFlash('success', $this->translate('%count% hook(s) have been disabled.', ['%count%' => $updated], 'Modules.Everblock.Admineverblockcontroller'));
        } else {
            $this->addFlash('error', $this->translate('No hook was disabled.', [], 'Modules.Everblock.Admineverblockcontroller'));
        }

        return $this->redirectToRoute('everblock_admin_hooks');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', 'AdminEverBlockHook');

        $ids = $request->request->all('ids');
        if (!is_array($ids)) {
            $ids = [];
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $hook = new Hook((int) $id);
            if (!\Validate::isLoadedObject($hook)) {
                continue;
            }

            if ($hook->delete()) {
                ++$deleted;
            }
        }

        if ($deleted > 0) {
            $this->addFlash('success', $this->translate('%count% hook(s) have been deleted.', ['%count%' => $deleted], 'Modules.Everblock.Admineverblockcontroller'));
        } else {
            $this->addFlash('error', $this->translate('No hook was deleted.', [], 'Modules.Everblock.Admineverblockcontroller'));
        }

        return $this->redirectToRoute('everblock_admin_hooks');
    }
}
