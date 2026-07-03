<?php

declare(strict_types=1);

namespace Everblock\Tools\Controller\Admin;

use Everblock\Tools\Command\ClearEverblockCacheCommand;
use Everblock\Tools\Command\DeleteAdminItemCommand;
use Everblock\Tools\Command\SaveAdminItemCommand;
use Everblock\Tools\Entity\Block;
use Everblock\Tools\Form\BlockType;
use Everblock\Tools\Form\EverblockConfigurationType;
use Everblock\Tools\Form\FaqType;
use Everblock\Tools\Form\HookType;
use Everblock\Tools\Form\PageType;
use Everblock\Tools\Form\ShortcodeType;
use Everblock\Tools\Query\GetAdminItemQuery;
use Everblock\Tools\Query\ListAdminItemsQuery;
use Everblock\Tools\Repository\BlockRepository;
use Everblock\Tools\Repository\HookRepository;
use Everblock\Tools\Service\AdminConfigurationManager;
use Everblock\Tools\Service\EverblockTools;
use Everblock\Tools\Service\ModuleTranslationManager;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;
use Language;
use Module;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EverblockAdminController extends FrameworkBundleAdminController
{
    private const SECTION_CONFIG = [
        'blocks' => [
            'title' => 'HTML Blocks',
            'form' => BlockType::class,
            'route' => 'admin_everblock_blocks',
            'legacy' => 'AdminEverBlock',
            'id' => 'id_everblock',
            'columns' => [
                'id_everblock',
                'name',
                'hook_name',
                'position',
                'only_home',
                'only_category',
                'only_category_product',
                'only_manufacturer',
                'only_supplier',
                'only_cms_category',
                'date_start',
                'date_end',
                'modal',
                'active',
            ],
            'filter_columns' => [
                'id_everblock',
                'name',
                'hook_name',
                'position',
                'only_home',
                'only_category',
                'only_category_product',
                'only_manufacturer',
                'only_supplier',
                'only_cms_category',
                'date_start',
                'date_end',
                'modal',
                'active',
            ],
            'boolean_columns' => [
                'only_home',
                'only_category',
                'only_category_product',
                'only_manufacturer',
                'only_supplier',
                'only_cms_category',
                'modal',
                'active',
            ],
            'column_labels' => [
                'id_everblock' => 'ID',
                'name' => 'Name',
                'hook_name' => 'Hook',
                'position' => 'Position',
                'only_home' => 'Home only',
                'only_category' => 'Category only',
                'only_category_product' => 'Product category only',
                'only_manufacturer' => 'Manufacturer only',
                'only_supplier' => 'Supplier only',
                'only_cms_category' => 'CMS category only',
                'date_start' => 'Date start',
                'date_end' => 'Date end',
                'modal' => 'Is modal',
                'active' => 'Status',
            ],
        ],
        'hooks' => [
            'title' => 'Hooks',
            'form' => HookType::class,
            'route' => 'admin_everblock_hooks',
            'legacy' => 'AdminEverBlockHook',
            'id' => 'id_hook',
            'columns' => ['id_hook', 'name', 'title', 'description', 'active'],
            'filter_columns' => ['id_hook', 'name', 'title', 'description', 'active'],
            'boolean_columns' => ['active'],
            'column_labels' => [
                'id_hook' => 'ID',
                'name' => 'Name',
                'title' => 'Title',
                'description' => 'Description',
                'active' => 'Active',
            ],
        ],
        'shortcodes' => [
            'title' => 'Shortcodes',
            'form' => ShortcodeType::class,
            'route' => 'admin_everblock_shortcodes',
            'legacy' => 'AdminEverBlockShortcode',
            'id' => 'id_everblock_shortcode',
            'columns' => ['id_everblock_shortcode', 'shortcode', 'title', 'content'],
            'filter_columns' => ['id_everblock_shortcode', 'shortcode', 'title', 'content'],
            'column_labels' => [
                'id_everblock_shortcode' => 'ID',
                'shortcode' => 'Shortcode',
                'title' => 'Title',
                'content' => 'Content',
            ],
        ],
        'faqs' => [
            'title' => 'FAQ',
            'form' => FaqType::class,
            'route' => 'admin_everblock_faqs',
            'legacy' => 'AdminEverBlockFaq',
            'id' => 'id_everblock_faq',
            'columns' => [
                'id_everblock_faq',
                'tag_name',
                'title',
                'content',
                'position',
                'active',
                'linked_products',
                'date_add',
                'date_upd',
            ],
            'filter_columns' => [
                'id_everblock_faq',
                'tag_name',
                'title',
                'content',
                'position',
                'active',
                'date_add',
                'date_upd',
            ],
            'boolean_columns' => ['active'],
            'column_labels' => [
                'id_everblock_faq' => 'ID',
                'tag_name' => 'FAQ tag',
                'title' => 'Title',
                'content' => 'Content',
                'position' => 'Position',
                'active' => 'Status',
                'linked_products' => 'Linked products',
                'date_add' => 'Date add',
                'date_upd' => 'Date upd',
            ],
        ],
        'pages' => [
            'title' => 'Pages',
            'form' => PageType::class,
            'route' => 'admin_everblock_pages',
            'legacy' => 'AdminEverBlockPage',
            'id' => 'id_everblock_page',
            'columns' => ['id_everblock_page', 'name', 'title', 'position', 'id_shop', 'active', 'date_add', 'date_upd'],
            'filter_columns' => ['id_everblock_page', 'name', 'title', 'position', 'id_shop', 'active', 'date_add', 'date_upd'],
            'boolean_columns' => ['active'],
            'column_labels' => [
                'id_everblock_page' => 'ID',
                'name' => 'Name',
                'title' => 'Meta title',
                'position' => 'Position',
                'id_shop' => 'Shop',
                'active' => 'Status',
                'date_add' => 'Date add',
                'date_upd' => 'Date upd',
            ],
        ],
    ];

    public function __construct(
        private CommandBusInterface $commandBus,
        private BlockRepository $blockRepository,
        private HookRepository $hookRepository,
        private FormFactoryInterface $formFactory,
        private AdminConfigurationManager $adminConfigurationManager,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function configurationAction(Request $request): Response
    {
        /** @var \Everblock $module */
        $module = Module::getInstanceByName('everblock');
        $viewContext = $this->adminConfigurationManager->getViewContext($module);
        $formOptions = [
            'banned_features' => $viewContext['banned_features'],
            'feature_choices' => $viewContext['feature_choices'],
            'feature_names' => $viewContext['feature_names'],
            'has_instagram_token' => $viewContext['has_instagram_token'],
            'holidays' => $viewContext['holidays'],
            'languages' => $viewContext['languages'],
            'shop_id' => $this->shopId(),
            'stores' => $viewContext['stores'],
            'translation_file_choices' => $this->translationFileChoices($viewContext['translation_files']),
            'translation_language_choices' => $this->translationLanguageChoices($viewContext['languages']),
        ];
        $form = $this->formFactory->createNamed('', EverblockConfigurationType::class, $this->adminConfigurationManager->getFormData($module), $formOptions);
        $form->handleRequest($request);

        if ($request->isMethod('POST') || $request->query->has('deleteEVERBLOCK_MARKER_ICON') || $request->query->has('deleteEVERWP_POSTS_BG_IMAGE')) {
            if ($request->isMethod('POST') && (!$form->isSubmitted() || !$form->isValid())) {
                $this->addFlash('error', $this->transAdmin('The configuration form could not be validated.'));

                return $this->redirectToRoute('admin_everblock_configuration');
            }

            $result = $this->adminConfigurationManager->processRequest($module);
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
            foreach ($result['success'] as $success) {
                $this->addFlash('success', $success);
            }

            return $this->redirectToRoute('admin_everblock_configuration');
        }

        return $this->render('@Modules/everblock/templates/admin/configuration.html.twig', [
            'layoutTitle' => 'Ever Block',
            'action_buttons' => EverblockConfigurationType::actionButtons(),
            'configuration_docs' => EverblockConfigurationType::docs(),
            'configuration_form' => $form->createView(),
            'configuration_tabs' => EverblockConfigurationType::tabs($viewContext['has_stores']),
            'cron_links' => $viewContext['cron_links'],
            'current_images' => $viewContext['current_images'],
            'field_tabs' => EverblockConfigurationType::fieldTabs(
                $viewContext['languages'],
                $viewContext['banned_features'],
                $viewContext['stores'],
                $viewContext['holidays'],
                $viewContext['has_instagram_token']
            ),
            'module' => $module,
            'module_version' => $viewContext['module_version'],
            'sections' => self::SECTION_CONFIG,
            'stats' => $viewContext['stats'],
            'translation_files' => $viewContext['translation_files'],
        ]);
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function downloadTranslationAction(string $file, ModuleTranslationManager $manager): Response
    {
        $module = Module::getInstanceByName('everblock');
        $path = $manager->resolveTranslationFile($module, $file);
        if ($path === null) {
            throw $this->createNotFoundException('Translation file not found.');
        }

        return new Response(
            (string) file_get_contents($path),
            200,
            [
                'Content-Type' => 'application/x-php',
                'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
            ]
        );
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function listAction(Request $request, string $section): Response
    {
        $config = $this->config($section);
        $filters = $this->extractFilters($request);
        $rows = $this->commandBus->handle(new ListAdminItemsQuery(
            $section,
            $this->shopId(),
            $this->languageId()
        ));
        $filterColumns = $config['filter_columns'] ?? $config['columns'];
        $rows = $this->applyFilters($rows, $filters, $filterColumns, $config['boolean_columns'] ?? []);

        $previewUrls = [];
        if ($section === 'blocks') {
            foreach ($rows as $row) {
                $rowId = (int) ($row[$config['id']] ?? 0);
                if ($rowId > 0) {
                    $previewUrls[$rowId] = $this->buildPreviewUrl($rowId);
                }
            }
        }

        return $this->render('@Modules/everblock/templates/admin/list.html.twig', [
            'layoutTitle' => 'Ever Block - ' . $config['title'],
            'section' => $section,
            'config' => $config,
            'filters' => $filters,
            'filter_columns' => $filterColumns,
            'rows' => $rows,
            'sections' => self::SECTION_CONFIG,
            'preview_urls' => $previewUrls,
        ]);
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function shortcodeDocumentationAction(): Response
    {
        $module = Module::getInstanceByName('everblock');

        return $this->render('@Modules/everblock/templates/admin/shortcode_documentation.html.twig', [
            'layoutTitle' => 'Ever Block - Shortcode documentation',
            'section' => 'shortcode_documentation',
            'sections' => self::SECTION_CONFIG,
            'documentation' => ShortcodeDocumentationProvider::getDocumentation($module),
        ]);
    }

    /**
     * @AdminSecurity("is_granted('create', request.get('_legacy_controller'))")
     */
    public function createAction(Request $request, string $section): Response
    {
        return $this->handleForm($request, $section, null);
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))")
     */
    public function editAction(Request $request, string $section, int $id): Response
    {
        return $this->handleForm($request, $section, $id);
    }

    /**
     * @AdminSecurity("is_granted('delete', request.get('_legacy_controller'))")
     */
    public function deleteAction(string $section, int $id): RedirectResponse
    {
        $config = $this->config($section);

        $this->commandBus->handle(new DeleteAdminItemCommand($section, $id, $this->shopId()));
        $this->addFlash('success', $this->transAdmin('Item deleted successfully.'));

        return $this->redirectToRoute($config['route']);
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))")
     */
    public function clearCacheAction(Request $request): RedirectResponse
    {
        $this->commandBus->handle(new ClearEverblockCacheCommand());
        $this->addFlash('success', $this->transAdmin('Cache cleared successfully.'));

        return $this->redirectToRoute((string) $request->query->get('redirect_route', 'admin_everblock_configuration'));
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))")
     */
    public function toggleBlockAction(int $id): RedirectResponse
    {
        $block = $this->blockRepository->find($id, $this->shopId());
        if ($block === null) {
            $this->addFlash('error', $this->transAdmin('The requested block could not be found.'));

            return $this->redirectToRoute('admin_everblock_blocks');
        }

        $this->blockRepository->setActive($id, $this->shopId(), !$block->active);
        $this->clearBlockCache($id, (int) $block->id_hook);
        $this->addFlash('success', $block->active ? $this->transAdmin('Block disabled successfully.') : $this->transAdmin('Block enabled successfully.'));

        return $this->redirectToRoute('admin_everblock_blocks');
    }

    /**
     * @AdminSecurity("is_granted('create', request.get('_legacy_controller'))")
     */
    public function duplicateBlockAction(int $id): RedirectResponse
    {
        $newId = $this->blockRepository->duplicate($id, $this->shopId(), Language::getLanguages(false));
        if ($newId <= 0) {
            $this->addFlash('error', $this->transAdmin('The block could not be duplicated.'));

            return $this->redirectToRoute('admin_everblock_blocks');
        }

        $duplicated = $this->blockRepository->find($newId, $this->shopId());
        $this->clearBlockCache($newId, $duplicated ? (int) $duplicated->id_hook : null);
        $this->addFlash('success', $this->transAdmin('Block duplicated successfully.'));

        return $this->redirectToRoute('admin_everblock_blocks_edit', ['id' => $newId]);
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))")
     */
    public function bulkBlockAction(Request $request, string $bulkAction): RedirectResponse
    {
        $ids = $this->extractBulkIds($request);
        if (empty($ids)) {
            $this->addFlash('error', $this->transAdmin('Please select at least one block.'));

            return $this->redirectToRoute('admin_everblock_blocks');
        }

        $count = 0;
        foreach ($ids as $id) {
            $block = $this->blockRepository->find($id, $this->shopId());
            if ($block === null) {
                continue;
            }

            if ($bulkAction === 'enable' || $bulkAction === 'disable') {
                $this->blockRepository->setActive($id, $this->shopId(), $bulkAction === 'enable');
                $this->clearBlockCache($id, (int) $block->id_hook);
                ++$count;
                continue;
            }

            if ($bulkAction === 'delete') {
                $this->commandBus->handle(new DeleteAdminItemCommand('blocks', $id, $this->shopId()));
                ++$count;
                continue;
            }

            if ($bulkAction === 'duplicate') {
                $newId = $this->blockRepository->duplicate($id, $this->shopId(), Language::getLanguages(false));
                if ($newId > 0) {
                    $this->clearBlockCache($newId, (int) $block->id_hook);
                    ++$count;
                }
            }
        }

        $this->addFlash('success', $this->transAdmin('%count% block(s) processed successfully.', ['%count%' => $count]));

        return $this->redirectToRoute('admin_everblock_blocks');
    }

    private function handleForm(Request $request, string $section, ?int $id): Response
    {
        $config = $this->config($section);
        $data = $this->commandBus->handle(new GetAdminItemQuery($section, $id, $this->shopId(), $this->languageId()));
        $formOptions = $this->formOptions($section);
        if ($section === 'blocks' && $id === null && empty($data['groups'])) {
            $data['groups'] = array_values(array_map('intval', $formOptions['group_choices']));
        }
        $form = $this->createForm($config['form'], $data, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if ($section === 'pages') {
                $uploadedName = $this->handlePageCoverUpload($form->get('cover_image')->getData());
                if ($uploadedName !== null) {
                    $formData['cover_image_name'] = $uploadedName;
                }
            }

            $savedId = $this->commandBus->handle(new SaveAdminItemCommand(
                $section,
                $id,
                $this->shopId(),
                $formData,
                Language::getLanguages(false)
            ));
            $this->addFlash('success', $this->transAdmin('Item saved successfully.'));

            if ($request->request->has('save_and_stay')) {
                return $this->redirectToRoute($config['route'] . '_edit', ['id' => $savedId]);
            }

            return $this->redirectToRoute($config['route']);
        }

        return $this->render('@Modules/everblock/templates/admin/form.html.twig', [
            'layoutTitle' => 'Ever Block - ' . $config['title'],
            'section' => $section,
            'config' => $config,
            'sections' => self::SECTION_CONFIG,
            'form' => $form->createView(),
            'form_tabs' => $section === 'blocks' ? BlockType::tabs() : [],
            'field_tabs' => $section === 'blocks' ? BlockType::fieldTabs(Language::getLanguages(false)) : [],
            'field_descriptions' => $section === 'blocks' ? BlockType::fieldDescriptions(Language::getLanguages(false)) : [],
            'tab_help' => $section === 'blocks' ? BlockType::tabHelp() : [],
            'tinymce_enabled' => in_array($section, ['blocks', 'shortcodes', 'faqs', 'pages'], true) && (bool) \Configuration::get('EVERBLOCK_TINYMCE'),
            'id' => $id,
            'preview_url' => ($section === 'blocks' && $id !== null && $id > 0) ? $this->buildPreviewUrl((int) $id) : null,
        ]);
    }

    private function buildPreviewUrl(int $blockId): string
    {
        if ($blockId <= 0) {
            return '';
        }

        $context = \Context::getContext();
        if (!$context || !$context->link) {
            return '';
        }

        $params = [
            'id_everblock' => $blockId,
            'id_lang' => $this->languageId(),
            'id_shop' => $this->shopId(),
            'token' => \Tools::getAdminTokenLite('AdminEverBlock'),
        ];

        return (string) $context->link->getModuleLink('everblock', 'preview', $params);
    }

    private function formOptions(string $section): array
    {
        $options = ['languages' => Language::getLanguages(false)];
        if ($section === 'blocks') {
            $options['hook_choices'] = ['Choose a hook' => 0] + $this->hookRepository->choices();
            $options['category_choices'] = $this->categoryChoices();
            $options['manufacturer_choices'] = $this->manufacturerChoices();
            $options['supplier_choices'] = $this->supplierChoices();
            $options['cms_category_choices'] = $this->cmsCategoryChoices();
            $options['group_choices'] = $this->groupChoices();
        }
        if ($section === 'pages') {
            $options['group_choices'] = $this->groupChoices();
        }

        return $options;
    }

    private function categoryChoices(): array
    {
        $choices = [];
        foreach (\Category::getCategories(false, true, false) as $category) {
            $id = (int) $category['id_category'];
            $choices[$id . ' - ' . (string) $category['name']] = $id;
        }

        return $choices;
    }

    private function manufacturerChoices(): array
    {
        $choices = [];
        foreach (\Manufacturer::getLiteManufacturersList($this->languageId()) as $manufacturer) {
            $id = (int) ($manufacturer['id'] ?? $manufacturer['id_manufacturer'] ?? 0);
            if ($id > 0) {
                $choices[(string) $manufacturer['name']] = $id;
            }
        }

        return $choices;
    }

    private function supplierChoices(): array
    {
        $choices = [];
        foreach (\Supplier::getLiteSuppliersList($this->languageId()) as $supplier) {
            $id = (int) ($supplier['id'] ?? $supplier['id_supplier'] ?? 0);
            if ($id > 0) {
                $choices[(string) $supplier['name']] = $id;
            }
        }

        return $choices;
    }

    private function cmsCategoryChoices(): array
    {
        $choices = [];
        foreach (\CMSCategory::getSimpleCategories($this->languageId()) as $cmsCategory) {
            $id = (int) $cmsCategory['id_cms_category'];
            $choices[(string) $cmsCategory['name']] = $id;
        }

        return $choices;
    }

    private function groupChoices(): array
    {
        $choices = [];
        foreach (\Group::getGroups($this->languageId()) as $group) {
            $choices[(string) $group['name']] = (int) $group['id_group'];
        }

        return $choices;
    }

    private function handlePageCoverUpload($file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg';
        $safeName = 'everblock-page-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . strtolower($extension);
        $destination = _PS_IMG_DIR_ . 'pages/';
        if (!is_dir($destination)) {
            @mkdir($destination, 0755, true);
        }
        $file->move($destination, $safeName);

        $webpUrl = EverblockTools::convertToWebP($destination . $safeName);
        if (!$webpUrl) {
            return $safeName;
        }

        return basename((string) parse_url($webpUrl, PHP_URL_PATH));
    }

    private function extractFilters(Request $request): array
    {
        $queryParameters = $request->query->all();
        $rawFilters = $queryParameters['filters'] ?? [];
        if (!is_array($rawFilters)) {
            return [];
        }

        $filters = [];
        foreach ($rawFilters as $field => $value) {
            $value = is_scalar($value) ? trim((string) $value) : '';
            if ($value !== '') {
                $filters[(string) $field] = $value;
            }
        }

        return $filters;
    }

    private function applyFilters(array $rows, array $filters, array $allowedColumns, array $booleanColumns): array
    {
        if (empty($filters)) {
            return $rows;
        }

        $allowed = array_flip($allowedColumns);
        $booleans = array_flip($booleanColumns);

        return array_values(array_filter($rows, static function (array $row) use ($filters, $allowed, $booleans): bool {
            foreach ($filters as $field => $expected) {
                if (!isset($allowed[$field])) {
                    continue;
                }

                $actual = $row[$field] ?? '';
                if (isset($booleans[$field])) {
                    if ((string) (int) (bool) $actual !== (string) (int) $expected) {
                        return false;
                    }
                    continue;
                }

                if (stripos(strip_tags((string) $actual), (string) $expected) === false) {
                    return false;
                }
            }

            return true;
        }));
    }

    private function extractBulkIds(Request $request): array
    {
        $values = array_merge($request->request->all(), $request->query->all());
        $ids = [];
        $this->collectIds($values, $ids);

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    private function collectIds(array $values, array &$ids): void
    {
        foreach ($values as $key => $value) {
            $key = (string) $key;
            if (is_array($value)) {
                if (preg_match('/(^|_)(ids?|selected|bulk_action_selected)(_|$)/i', $key)) {
                    array_walk_recursive($value, static function ($item) use (&$ids): void {
                        if (is_scalar($item)) {
                            $ids[] = (int) $item;
                        }
                    });
                } else {
                    $this->collectIds($value, $ids);
                }
                continue;
            }

            if (!is_scalar($value)) {
                continue;
            }

            if (preg_match('/(^|_)(ids?|selected|bulk_action_selected)(_|$)/i', $key)) {
                $ids[] = (int) $value;
            }
        }
    }

    private function clearBlockCache(?int $blockId = null, ?int $hookId = null): void
    {
        Block::clearCache($blockId, $this->shopId(), Language::getLanguages(false), $hookId !== null && $hookId > 0 ? [$hookId] : []);
    }

    private function transAdmin(string $message, array $parameters = []): string
    {
        return $this->translator->trans($message, $parameters, 'Modules.Everblock.Admin');
    }

    private function translationLanguageChoices(array $languages): array
    {
        $choices = [];
        foreach ($languages as $language) {
            $isoCode = (string) ($language['iso_code'] ?? '');
            if ($isoCode === '') {
                continue;
            }
            $label = trim((string) ($language['name'] ?? $isoCode));
            $choices[$label . ' (' . $isoCode . ')'] = $isoCode;
        }

        return $choices;
    }

    private function translationFileChoices(array $files): array
    {
        $choices = ['Choose a file' => ''];
        foreach ($files as $file) {
            if (empty($file['name'])) {
                continue;
            }
            $choices[(string) $file['name']] = (string) $file['name'];
        }

        return $choices;
    }

    private function config(string $section): array
    {
        if (!isset(self::SECTION_CONFIG[$section])) {
            throw $this->createNotFoundException(sprintf('Unknown Everblock admin section "%s".', $section));
        }

        return self::SECTION_CONFIG[$section];
    }

    private function shopId(): int
    {
        return (int) \Context::getContext()->shop->id;
    }

    private function languageId(): int
    {
        return (int) \Context::getContext()->language->id;
    }
}
