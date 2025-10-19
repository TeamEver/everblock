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

namespace Everblock\Tools\Controller\Admin;

use EverBlockClass;
use Everblock\Tools\Form\Admin\EverBlockFormHandler;
use Everblock\Tools\Grid\Filters\EverBlockFilters;
use Everblock\Tools\Service\EverBlockManager;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;
use Module;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Presenter\GridPresenterInterface;
use PrestaShop\PrestaShop\Core\Search\Builder\FiltersBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EverBlockController extends FrameworkBundleAdminController
{
    /** @var GridFactoryInterface */
    private $gridFactory;

    /** @var GridPresenterInterface */
    private $gridPresenter;

    /** @var EverBlockManager */
    private $manager;

    /** @var EverBlockFormHandler */
    private $formHandler;

    /** @var LegacyContext */
    private $legacyContext;

    /** @var FiltersBuilderInterface|null */
    private $filtersBuilder;

    public function __construct(
        GridFactoryInterface $gridFactory,
        GridPresenterInterface $gridPresenter,
        EverBlockManager $manager,
        EverBlockFormHandler $formHandler,
        LegacyContext $legacyContext,
        ?FiltersBuilderInterface $filtersBuilder = null
    ) {
        $this->gridFactory = $gridFactory;
        $this->gridPresenter = $gridPresenter;
        $this->manager = $manager;
        $this->formHandler = $formHandler;
        $this->legacyContext = $legacyContext;
        $this->filtersBuilder = $filtersBuilder;
    }

    public function indexAction(Request $request)
    {
        $filters = $this->resolveFilters($request);

        if ($request->isMethod('POST')) {
            $bulkPayload = $this->extractBulkActionData($request);
            if (null !== $bulkPayload) {
                [$bulkAction, $selection] = $bulkPayload;
                $this->handleBulkAction($bulkAction, $selection);

                return $this->redirectToRoute('admin_everblock_index', $request->query->all());
            }
        }

        $grid = $this->gridFactory->getGrid($filters);
        $gridView = $this->gridPresenter->present($grid);

        $context = $this->legacyContext->getContext();
        $module = Module::getInstanceByName('everblock');

        return $this->render('@Modules/everblock/views/templates/admin/everblock/index.html.twig', [
            'grid' => $gridView,
            'stats' => $this->getModuleStatistics(),
            'module_version' => $module ? $module->version : '',
            'module_name' => $module ? $module->displayName : 'Everblock',
            'header_links' => $this->getHeaderLinks($module),
            'shortcode_docs' => $module ? ShortcodeDocumentationProvider::getDocumentation($module) : [],
            'can_add_block' => $context->employee && $context->employee->can('add', 'AdminEverBlock'),
        ]);
    }

    public function createAction(Request $request)
    {
        list($form, $block) = $this->formHandler->handle($request);

        if ($block instanceof EverBlockClass) {
            $this->addFlash('success', $this->trans('Block successfully created.', 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('admin_everblock_edit', [
                'everBlockId' => (int) $block->id,
            ]);
        }

        return $this->render('@Modules/everblock/views/templates/admin/everblock/form.html.twig', [
            'form' => $form->createView(),
            'tabs' => $this->getFormTabs(),
            'is_edit' => false,
        ]);
    }

    public function editAction(Request $request, int $everBlockId)
    {
        list($form, $block) = $this->formHandler->handle($request, $everBlockId);

        if ($block instanceof EverBlockClass) {
            $this->addFlash('success', $this->trans('Block successfully updated.', 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('admin_everblock_edit', [
                'everBlockId' => (int) $block->id,
            ]);
        }

        return $this->render('@Modules/everblock/views/templates/admin/everblock/form.html.twig', [
            'form' => $form->createView(),
            'tabs' => $this->getFormTabs(),
            'is_edit' => true,
        ]);
    }

    public function deleteAction(int $everBlockId)
    {
        if ($this->manager->delete($everBlockId)) {
            $this->addFlash('success', $this->trans('Block deleted successfully.', 'Modules.Everblock.Admin'));
        } else {
            $this->addFlash('error', $this->trans('Unable to delete the block.', 'Modules.Everblock.Admin'));
        }

        return $this->redirectToRoute('admin_everblock_index');
    }

    public function duplicateAction(int $everBlockId)
    {
        try {
            $this->manager->duplicate($everBlockId);
            $this->addFlash('success', $this->trans('Block duplicated successfully.', 'Modules.Everblock.Admin'));
        } catch (\Exception $e) {
            $this->addFlash('error', $this->trans('Unable to duplicate the block.', 'Modules.Everblock.Admin'));
        }

        return $this->redirectToRoute('admin_everblock_index');
    }

    public function exportAction(int $everBlockId)
    {
        try {
            $sql = $this->manager->exportSql($everBlockId);
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->trans('Unable to export the block.', 'Modules.Everblock.Admin'));

            return $this->redirectToRoute('admin_everblock_index');
        }

        $response = new Response($sql);
        $response->headers->set('Content-Type', 'application/sql');
        $response->headers->set('Content-Disposition', 'attachment; filename="everblock_' . (int) $everBlockId . '.sql"');

        return $response;
    }

    public function toggleStatusAction(int $everBlockId)
    {
        if ($this->manager->toggleStatus($everBlockId)) {
            $this->addFlash('success', $this->trans('Status updated.', 'Modules.Everblock.Admin'));
        } else {
            $this->addFlash('error', $this->trans('Unable to update status.', 'Modules.Everblock.Admin'));
        }

        return $this->redirectToRoute('admin_everblock_index');
    }

    public function bulkAction(Request $request)
    {
        $bulkPayload = $this->extractBulkActionData($request);
        if (null !== $bulkPayload) {
            [$action, $selection] = $bulkPayload;
            $this->handleBulkAction($action, $selection);
        }

        return $this->redirectToRoute('admin_everblock_index');
    }

    private function handleBulkAction(string $action, array $ids): void
    {
        switch ($action) {
            case 'duplicate':
                $this->manager->bulkDuplicate($ids);
                $this->addFlash('success', $this->trans('Selected blocks duplicated.', 'Modules.Everblock.Admin'));
                break;
            case 'delete':
                $this->manager->bulkDelete($ids);
                $this->addFlash('success', $this->trans('Selected blocks deleted.', 'Modules.Everblock.Admin'));
                break;
            case 'enable':
                $this->manager->bulkToggle($ids, true);
                $this->addFlash('success', $this->trans('Selected blocks enabled.', 'Modules.Everblock.Admin'));
                break;
            case 'disable':
                $this->manager->bulkToggle($ids, false);
                $this->addFlash('success', $this->trans('Selected blocks disabled.', 'Modules.Everblock.Admin'));
                break;
            default:
                $this->addFlash('error', $this->trans('Unknown bulk action.', 'Modules.Everblock.Admin'));
        }
    }

    private function resolveFilters(Request $request): EverBlockFilters
    {
        if ($this->filtersBuilder instanceof FiltersBuilderInterface) {
            try {
                $filters = $this->filtersBuilder->buildFilters(
                    EverBlockFilters::class,
                    $request->query->all()
                );

                if ($filters instanceof EverBlockFilters) {
                    return $filters;
                }
            } catch (Throwable $exception) {
                // fall back to manual instantiation if the filters builder is not compatible
            }
        }

        return new EverBlockFilters($request->query->all());
    }

    private function extractBulkActionData(Request $request): ?array
    {
        $action = $request->request->get('bulk_action');
        $selection = $this->normalizeBulkSelection($request->request->all('bulk_selected'));

        if ($action && !empty($selection)) {
            return [$action, $selection];
        }

        $bulkFormPayloads = [];
        $bulkFormPayloads[] = $request->request->get('grid', []);
        $bulkFormPayloads[] = $request->request->get('ever_block', []);

        foreach ($bulkFormPayloads as $payload) {
            if (!is_array($payload) || empty($payload)) {
                continue;
            }

            if (isset($payload['bulk_action']) && !$action) {
                $action = (string) $payload['bulk_action'];
            }

            if (isset($payload['selected'])) {
                $selection = $this->normalizeBulkSelection($payload['selected']);
            }

            if (isset($payload['bulk_selected'])) {
                $selection = $this->normalizeBulkSelection($payload['bulk_selected']);
            }

            if (isset($payload['actions']['bulk']) && is_array($payload['actions']['bulk'])) {
                foreach ($payload['actions']['bulk'] as $bulkConfig) {
                    if (!$action && isset($bulkConfig['submit_action'])) {
                        $action = (string) $bulkConfig['submit_action'];
                    }
                    if (isset($bulkConfig['selected'])) {
                        $selection = $this->normalizeBulkSelection($bulkConfig['selected']);
                    }
                }
            }
        }

        if ($action && !empty($selection)) {
            return [$action, $selection];
        }

        return null;
    }

    private function normalizeBulkSelection($selection): array
    {
        if (!is_array($selection)) {
            return [];
        }

        if (isset($selection['data']) && is_array($selection['data'])) {
            $selection = $selection['data'];
        }

        if (isset($selection['ids']) && is_array($selection['ids'])) {
            $selection = $selection['ids'];
        }

        if (isset($selection['selected']) && is_array($selection['selected'])) {
            $selection = $selection['selected'];
        }

        $normalized = [];
        foreach ($selection as $key => $value) {
            if (is_array($value) && isset($value['id'])) {
                $normalized[] = (int) $value['id'];
                continue;
            }

            if (is_scalar($value)) {
                $normalized[] = (int) $value;
            }
        }

        return array_values(array_unique(array_filter($normalized, static function ($id) {
            return $id > 0;
        })));
    }

    private function getModuleStatistics(): array
    {
        $idShop = (int) $this->legacyContext->getContext()->shop->id;
        $stats = [
            'blocks_total' => $this->countTableRecords('everblock', 'id_shop = ' . $idShop),
            'blocks_active' => $this->countTableRecords('everblock', 'id_shop = ' . $idShop . ' AND active = 1'),
            'shortcodes' => $this->countTableRecords('everblock_shortcode', 'id_shop = ' . $idShop),
            'faqs' => $this->countTableRecords('everblock_faq', 'id_shop = ' . $idShop),
            'tabs' => $this->countTableRecords('everblock_tabs', 'id_shop = ' . $idShop),
            'flags' => $this->countTableRecords('everblock_flags', 'id_shop = ' . $idShop),
            'modals' => $this->countTableRecords('everblock_modal', 'id_shop = ' . $idShop),
            'game_sessions' => $this->countTableRecords('everblock_game_play'),
        ];

        return $stats;
    }

    private function countTableRecords(string $table, string $whereClause = ''): int
    {
        if (!$this->moduleTableExists($table)) {
            return 0;
        }

        $db = \Db::getInstance(_PS_USE_SQL_SLAVE_);
        $tableName = _DB_PREFIX_ . $table;

        $sql = 'SELECT COUNT(*) FROM `' . bqSQL($tableName) . '`';
        if ($whereClause !== '') {
            $sql .= ' WHERE ' . $whereClause;
        }

        return (int) $db->getValue($sql);
    }

    private function moduleTableExists(string $table): bool
    {
        $db = \Db::getInstance(_PS_USE_SQL_SLAVE_);
        $tableName = _DB_PREFIX_ . $table;
        $pattern = str_replace(['_', '%'], ['\\_', '\\%'], pSQL($tableName));
        $sql = sprintf("SHOW TABLES LIKE '%s'", $pattern);

        return (bool) $db->executeS($sql);
    }

    private function getHeaderLinks(?Module $module): array
    {
        $context = $this->legacyContext->getContext();
        $links = [];

        $links['modules'] = $context->link->getAdminLink('AdminModules');
        $links['module_configuration'] = $context->link->getAdminLink('AdminModules', true, [], ['configure' => 'everblock']);
        $links['block_admin'] = $context->link->getAdminLink('AdminEverBlock');
        $links['faq_admin'] = $context->link->getAdminLink('AdminEverBlockFaq');
        $links['hook_admin'] = $context->link->getAdminLink('AdminEverBlockHook');
        $links['shortcode_admin'] = $context->link->getAdminLink('AdminEverBlockShortcode');
        $links['donation'] = 'https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE';

        return $links;
    }

    private function getFormTabs(): array
    {
        return [
            'general' => $this->trans('General', 'Modules.Everblock.Admin'),
            'targeting' => $this->trans('Targeting', 'Modules.Everblock.Admin'),
            'display' => $this->trans('Display', 'Modules.Everblock.Admin'),
            'modal' => $this->trans('Modal', 'Modules.Everblock.Admin'),
            'schedule' => $this->trans('Schedule', 'Modules.Everblock.Admin'),
        ];
    }

}
