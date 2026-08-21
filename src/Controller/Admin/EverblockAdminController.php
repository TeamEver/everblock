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
use Everblock\Tools\Service\EverblockPreviewBuilder;
use Everblock\Tools\Service\EverblockTools;
use Everblock\Tools\Service\EverblockUploadGuard;
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

        // POST only. The two image deletions used to be reachable through the query string, which
        // made every operation of processRequest() triggerable by a plain GET link, since
        // Tools::isSubmit() also reads $_GET. They are submit buttons of this form now.
        if ($request->isMethod('POST')) {
            // The @AdminSecurity annotation above only guarantees the "read" right, which is
            // enough to display the page but not to mutate anything. Every submitted operation
            // is therefore checked against the permission matching what it really does.
            $missingPermissions = $this->getMissingConfigurationPermissions($request, $module);
            if ($missingPermissions !== []) {
                $this->addFlash('error', $this->transAdmin(
                    'You do not have the required permission to perform this action (%permissions% needed).',
                    ['%permissions%' => implode(', ', $missingPermissions)]
                ));

                return $this->redirectToRoute('admin_everblock_configuration');
            }

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
     * Mints a fresh signed "log in as this customer" link and redirects to it.
     *
     * Generating the link here rather than in the grid HTML means the signed token is created at
     * click time: it is never rendered for rows nobody clicks, and it never sits in the back
     * office page source. The route is declared on the AdminCustomers legacy controller, so the
     * native ACL of the Customers page gates it — the same permission that
     * controllers/front/everlogin.php requires.
     *
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function customerLoginAction(int $customerId): RedirectResponse
    {
        $customer = new \Customer($customerId);
        if (!\Validate::isLoadedObject($customer)) {
            $this->addFlash('error', $this->transAdmin('The requested customer could not be found.'));

            return $this->redirectToRoute('admin_customers_index');
        }

        /** @var \Everblock|null $module */
        $module = Module::getInstanceByName('everblock');
        if (!$module instanceof \Everblock) {
            $this->addFlash('error', $this->transAdmin('The Ever Block module is not available.'));

            return $this->redirectToRoute('admin_customers_index');
        }

        try {
            $loginUrl = $module->getEverloginUrl($customer);
        } catch (\Throwable $exception) {
            $loginUrl = '';
        }

        if ($loginUrl === '') {
            $this->addFlash('error', $this->transAdmin('The secure login link could not be generated. See the PrestaShop logs for details.'));

            return $this->redirectToRoute('admin_customers_index');
        }

        return $this->redirect($loginUrl);
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

        // The preview link is built by the template with path(), like every other action of this
        // page: it is the only URL generator whose base path is guaranteed to match the admin
        // request. Link::getAdminLink() produced a URL without the admin directory here, which
        // sent the click to the front office.
        return $this->render('@Modules/everblock/templates/admin/list.html.twig', [
            'layoutTitle' => 'Ever Block - ' . $config['title'],
            'section' => $section,
            'config' => $config,
            'filters' => $filters,
            'filter_columns' => $filterColumns,
            'rows' => $rows,
            'sections' => self::SECTION_CONFIG,
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

        return $this->redirectToRoute($this->resolveRedirectRoute($request));
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
        ]);
    }

    /**
     * Renders a block preview directly in the back office.
     *
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function previewRedirectAction(int $id, Request $request): Response
    {
        $error = null;
        $block = null;
        $previewData = [
            'html' => '',
            'info' => [],
            'hook' => '',
            'assets' => [
                'stylesheets' => [],
                'js_definitions' => [],
                'javascript' => [
                    'head' => [],
                    'bottom' => [],
                ],
            ],
        ];

        try {
            $context = \Context::getContext();
            /** @var \Everblock|null $module */
            $module = Module::getInstanceByName('everblock');
            if (!$module instanceof \Everblock) {
                throw new \Exception($this->transAdmin('The Ever Block module is not available.'));
            }

            $block = new \EverBlockClass($id, $this->languageId(), $this->shopId());
            if (!\Validate::isLoadedObject($block)) {
                throw new \Exception($this->transAdmin('Unable to find the requested block.'));
            }

            $builder = new EverblockPreviewBuilder($module, $context);
            $previewData = $builder->buildPreview($block, $this->collectPreviewParameters($request));
        } catch (\Throwable $exception) {
            // Rendering can execute Smarty templates, hooks and module code. Keeping Throwable here
            // prevents a broken block from turning the preview route into a back office 500.
            $error = $exception->getMessage();

            if (class_exists('\PrestaShopLogger')) {
                \PrestaShopLogger::addLog(
                    sprintf(
                        'Everblock preview: rendering failed for block #%d (%s in %s line %d)',
                        $id,
                        get_class($exception),
                        basename($exception->getFile()),
                        (int) $exception->getLine()
                    ),
                    3
                );
            }
        }

        $response = $this->render('@Modules/everblock/templates/admin/preview.html.twig', [
            'everblock_preview_error' => $error,
            'everblock_preview_html' => $previewData['html'],
            'everblock_preview_info' => $previewData['info'],
            'everblock_preview_hook' => $previewData['hook'],
            'everblock_preview_assets' => $previewData['assets'],
            'everblock_preview_block' => $block,
            'everblock_preview_return_url' => $this->getPreviewReturnUrl($request),
            'everblock_preview_generated_at' => new \DateTimeImmutable(),
        ]);
        $this->applyPreviewNoStoreHeaders($response);

        return $response;
    }

    private function collectPreviewParameters(Request $request): array
    {
        $keys = [
            'controller',
            'page_name',
            'id_product',
            'id_category',
            'id_customer',
            'id_lang',
            'id_shop',
            'id_currency',
            'id_cms',
            'id_cms_category',
            'id_manufacturer',
            'id_supplier',
            'id_cart',
            'id_order',
            'id_order_return',
            'position',
        ];

        $params = [];
        foreach ($keys as $key) {
            $value = $request->query->get($key);
            if ($value === null || $value === '' || !is_scalar($value)) {
                continue;
            }

            if ($key === 'controller' || $key === 'page_name') {
                $params[$key] = (string) $value;
                continue;
            }

            $params[$key] = (int) $value;
        }

        if (!isset($params['controller']) || $params['controller'] === '') {
            $params['controller'] = 'index';
        }

        if (!isset($params['id_lang'])) {
            $params['id_lang'] = $this->languageId();
        }

        if (!isset($params['id_shop'])) {
            $params['id_shop'] = $this->shopId();
        }

        if (!isset($params['id_currency']) && isset(\Context::getContext()->currency->id)) {
            $params['id_currency'] = (int) \Context::getContext()->currency->id;
        }

        return $params;
    }

    private function getPreviewReturnUrl(Request $request): string
    {
        $referer = (string) $request->headers->get('referer', '');
        if ($referer !== '') {
            return $referer;
        }

        return $this->generateUrl('admin_everblock_blocks');
    }

    private function applyPreviewNoStoreHeaders(Response $response): void
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Surrogate-Control', 'no-store');
        $response->headers->set('X-Accel-Expires', '0');
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

        // img/pages/ is served by the web server: the destination extension must come from the
        // real image content. guessExtension() alone is not enough, and getClientOriginalExtension()
        // is client controlled — "cover.php" used to be stored as everblock-page-....php.
        $extension = EverblockUploadGuard::resolveSafeExtension(
            (string) $file->getPathname(),
            (string) $file->getClientOriginalName(),
            EverblockUploadGuard::PROFILE_IMAGE
        );

        if ($extension === null) {
            $this->addFlash('error', $this->transAdmin('The featured image must be a valid JPG, PNG, GIF, WEBP or AVIF image.'));

            return null;
        }

        $safeName = 'everblock-page-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = _PS_IMG_DIR_ . 'pages/';
        if (!is_dir($destination)) {
            @mkdir($destination, 0755, true);
        }
        // Secondary Apache hardening; the content whitelist above is the actual protection.
        EverblockUploadGuard::protectDirectory($destination);
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

    /**
     * Permissions required by the submitted configuration operations that the current employee
     * does not hold. Relies on the native PrestaShop voter (PageVoter) through is_granted, with
     * the legacy controller of the route as subject — same mechanism as @AdminSecurity.
     *
     * @return string[]
     */
    /**
     * Whitelists the redirect_route parameter: an unknown route name would otherwise make
     * redirectToRoute() throw a RouteNotFoundException (a 500 on an employee click).
     */
    private function resolveRedirectRoute(Request $request): string
    {
        $requested = (string) $request->query->get('redirect_route', '');
        $allowed = ['admin_everblock_configuration'];
        foreach (self::SECTION_CONFIG as $sectionConfig) {
            $allowed[] = $sectionConfig['route'];
        }

        return in_array($requested, $allowed, true) ? $requested : 'admin_everblock_configuration';
    }

    private function getMissingConfigurationPermissions(Request $request, \Everblock $module): array
    {
        // Read from the route attributes only: a query string must not be able to substitute
        // another legacy controller as the ACL subject.
        $legacyController = (string) $request->attributes->get('_legacy_controller');
        if ($legacyController === '') {
            return [AdminConfigurationManager::PERMISSION_UPDATE];
        }

        $missing = [];
        foreach ($this->adminConfigurationManager->getRequiredPermissions($module) as $permission) {
            if (!$this->isGranted($permission, $legacyController)) {
                $missing[] = $permission;
            }
        }

        return $missing;
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
