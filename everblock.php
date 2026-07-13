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
if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

spl_autoload_register(static function ($className) {
    $prefix = 'Everblock\\Tools\\';
    if (strncmp($className, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/src/Service/EverblockCache.php';

if (!function_exists('everblockRegisterLegacyAlias')) {
    function everblockRegisterLegacyAlias(string $className, string $legacyAlias, string $relativePath): void
    {
        if (class_exists($legacyAlias, false)) {
            return;
        }

        if (!class_exists($className, false)) {
            $file = __DIR__ . '/' . ltrim($relativePath, '/\\');
            if (is_file($file)) {
                require_once $file;
            }
        }

        if (!class_exists($className, false)) {
            return;
        }

        class_alias($className, $legacyAlias, false);
    }
}

everblockRegisterLegacyAlias(\Everblock\Tools\Entity\Block::class, 'EverBlockClass', 'src/Entity/Block.php');
everblockRegisterLegacyAlias(\Everblock\Tools\Entity\Shortcode::class, 'EverblockShortcode', 'src/Entity/Shortcode.php');
everblockRegisterLegacyAlias(\Everblock\Tools\Entity\ProductTab::class, 'EverblockTabsClass', 'src/Entity/ProductTab.php');
everblockRegisterLegacyAlias(\Everblock\Tools\Entity\ProductFlag::class, 'EverblockFlagsClass', 'src/Entity/ProductFlag.php');
everblockRegisterLegacyAlias(\Everblock\Tools\Entity\Faq::class, 'EverblockFaq', 'src/Entity/Faq.php');
everblockRegisterLegacyAlias(\Everblock\Tools\Entity\Modal::class, 'EverblockModal', 'src/Entity/Modal.php');
everblockRegisterLegacyAlias(\Everblock\Tools\Entity\Page::class, 'EverblockPage', 'src/Entity/Page.php');

use PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use Everblock\Tools\Checkout\EverblockCheckoutStep;
use Everblock\Tools\Service\AdminConfigurationManager;
use Everblock\Tools\Service\EverblockCache;
use Everblock\Tools\Service\QcdThirdPartyBlockRenderer;
use Everblock\Tools\Service\EverblockTools;
use Everblock\Tools\Service\ImportFile;
use Everblock\Tools\Service\ShortcodeDocumentationProvider;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Form\FormBuilderInterface;

class_exists(EverblockTools::class);

class Everblock extends Module
{
    private $postErrors = [];
    private $postSuccess = [];
    private $allowedActions = [
        'saveblocks',
        'restoreblocks',
        'removeinlinecsstags',
        'droplogs',
        'refreshtokens',
        'securewithapache',
        'fetchwordpressposts',
    ];
    private $bypassedControllers = [
        'hookDisplayInvoiceLegalFreeText',
    ];
    /** @var Module|null */
    private $qcdBuilderModule;
    /** @var bool */
    private $qcdBuilderModuleResolved = false;

    public function __construct()
    {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '9.0.1';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Ever Block');
        $this->description = $this->l('Add HTML block everywhere !');
        $this->confirmUninstall = $this->l('Do yo really want to uninstall this module ?');
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
    }

    /**
     * Intercepte dynamiquement les hooks display non déclarés explicitement.
     *
     * Cette méthode évite de devoir créer une méthode hook* pour chaque hook display,
     * tout en limitant l'exécution au front-office (hors contrôleurs bypassés).
     *
     * @param string $method Nom de la méthode appelée (ex: hookDisplayHome)
     * @param array<int, mixed> $args Arguments transmis par PrestaShop
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = [
            'front',
            'modulefront',
        ];
        $context = Context::getContext();
        if (!in_array($context->controller->controller_type, $controllerTypes) && !in_array($method, $this->bypassedControllers)) {
            return;
        }
        if (Hook::isDisplayHookName(lcfirst(str_replace('hook', '', $method)))) {
            return $this->everHook($method, $args);
        }
    }

    /**
     * Installe le module et son socle fonctionnel (config, SQL, hooks, onglets BO).
     */
    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->installConfiguration()) {
            return false;
        }

        if (!$this->installTranslations()) {
            return false;
        }

        if (!$this->installSql()) {
            return false;
        }

        if (!$this->installHooks()) {
            return false;
        }

        if (!$this->installExampleBlock()) {
            return false;
        }

        if (!$this->installTabs()) {
            return false;
        }

        return true;
    }

    /**
     * Initialise les clés de configuration nécessaires au fonctionnement du module.
     */
    private function installConfiguration(): bool
    {
        $configuration = [
            ['EVERBLOCK_LOAD_FRONT_CSS', 1],
            ['EVERBLOCK_TINYMCE', 1],
            ['EVERPSCSS_P_LLOREM_NUMBER', 5],
            ['EVERPSCSS_S_LLOREM_NUMBER', 5],
            ['EVERPS_TAB_NB', 5],
            ['EVERPS_FLAG_NB', 5],
            ['EVERWP_API_URL', ''],
            ['EVERWP_BLOG_URL', '/blog'],
            ['EVERWP_POST_NBR', 3],
            ['EVERWP_POSTS_BG_IMAGE', ''],
            ['EVER_SOLDOUT_COLOR', '#ff0000'],
            ['EVER_SOLDOUT_TEXTCOLOR', '#ffffff'],
            ['EVERINSTA_SHOW_CAPTION', 0],
            ['EVERBLOCK_CONTACT_MAX_UPLOAD_SIZE', 2097152],
            ['EVERBLOCK_CONTACT_ALLOWED_EXTENSIONS', json_encode(['pdf', 'jpg', 'jpeg', 'png']), true],
            ['EVERBLOCK_CONTACT_ALLOWED_MIME_TYPES', json_encode(['application/pdf', 'image/jpeg', 'image/png']), true],
            ['EVERPS_FEATURES_AS_FLAGS', json_encode([1]), true],
            ['EVERBLOCK_SOLDOUT_FLAG', 0],
            ['EVERBLOCK_LOW_STOCK_THRESHOLD', 5],
            ['EVERBLOCK_STORELOCATOR_TOGGLE', 0],
            ['EVERBLOCK_GOOGLE_API_KEY', ''],
            ['EVERBLOCK_GOOGLE_PLACE_ID', ''],
            ['EVERBLOCK_GOOGLE_REVIEWS_LIMIT', 5],
            ['EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING', 0],
            ['EVERBLOCK_GOOGLE_REVIEWS_SORT', 'most_relevant'],
            ['EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING', 1],
            ['EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR', 1],
            ['EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA', 1],
            ['EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL', $this->l('Read all reviews on Google')],
            ['EVERBLOCK_GOOGLE_REVIEWS_CTA_URL', ''],
            ['EVERBLOCK_PAGES_BASE_URL', 'guide'],
            ['EVERBLOCK_PAGES_PER_PAGE', 9],
            ['EVERBLOCK_FAQ_BASE_URL', 'faq'],
            ['EVERBLOCK_FAQ_PER_PAGE', 10],
        ];

        foreach ($configuration as $item) {
            $autoload = $item[2] ?? false;
            if (!Configuration::updateValue($item[0], $item[1], $autoload)) {
                return false;
            }
        }

        return true;
    }

    private function installTranslations(): bool
    {
        return $this->refreshTranslations();
    }

    public function refreshTranslations(?int $idLang = null): bool
    {
        try {
            $this->importLegacyTranslations($idLang);
        } catch (Throwable $exception) {
            PrestaShopLogger::addLog('Everblock translations import failed: ' . $exception->getMessage(), 2);
        }

        return true;
    }

    /**
     * Exécute le script SQL d'installation et crée les tables du module.
     */
    private function installSql(): bool
    {
        $sql = require dirname(__FILE__) . '/sql/install.php';
        if (!is_array($sql)) {
            return false;
        }

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Crée les hooks personnalisés du module puis enregistre les hooks natifs requis.
     */
    private function installHooks(): bool
    {
        $customHooks = [
            ['actionGetEverBlockBefore', 'Before block is rendered', 'This hook triggers before block is rendered'],
            ['actionEverBlockChangeShortcodeBefore', 'Before block shortcodes are rendered', 'This hook triggers before every block shortcode is rendered'],
            ['actionEverBlockChangeShortcodeAfter', 'After block shortcodes are rendered', 'This hook triggers after every block shortcode is rendered'],
            ['displayBeforeRenderingShortcodes', 'Before rendering shortcodes', 'This hook triggers before shortcodes are rendered'],
            ['displayAfterRenderingShortcodes', 'After rendering shortcodes', 'This hook triggers after shortcodes are rendered'],
            ['displayFakeHook', 'Fake hook', 'Ne pas afficher ce hook en front, il sera utilisé pour du contenu asynchrone'],
        ];

        foreach ($customHooks as $customHook) {
            if (!$this->createHookIfNotExists($customHook[0], $customHook[1], $customHook[2])) {
                return false;
            }
        }

        $hooksToRegister = [
            'displayHeader',
            'actionAdminControllerSetMedia',
            'actionRegisterBlock',
            'actionObjectLanguageAddAfter',
            'moduleRoutes',
        ];

        foreach ($hooksToRegister as $hookName) {
            if (!$this->registerHook($hookName)) {
                return false;
            }
        }

        if (!$this->registerQcdBuilderHooks()) {
            return false;
        }

        return true;
    }

    /**
     * Cree un bloc d'exemple desactive sur la home avec tous les shortcodes documentes.
     */
    private function installExampleBlock(): bool
    {
        $idHook = (int) Hook::getIdByName('displayHome');
        if ($idHook <= 0) {
            return false;
        }

        if (!$this->isRegisteredInHook('displayHome') && !$this->registerHook('displayHome')) {
            return false;
        }

        $content = $this->buildExampleBlockContent();
        if ($content === '') {
            return false;
        }

        $languages = Language::getLanguages(false);
        if (empty($languages) && isset($this->context->language->id)) {
            $languages = [
                ['id_lang' => (int) $this->context->language->id],
            ];
        }

        $shopIds = $this->getInstallShopIds();
        if (empty($shopIds)) {
            return false;
        }

        foreach ($shopIds as $idShop) {
            $existingBlockId = (int) Db::getInstance()->getValue(
                'SELECT `id_everblock`
                FROM `' . _DB_PREFIX_ . 'everblock`
                WHERE `id_shop` = ' . (int) $idShop . '
                  AND `name` = "' . pSQL('exemple') . '"'
            );
            if ($existingBlockId > 0) {
                continue;
            }

            $position = (int) Db::getInstance()->getValue(
                'SELECT COALESCE(MAX(`position`), 0) + 1
                FROM `' . _DB_PREFIX_ . 'everblock`
                WHERE `id_shop` = ' . (int) $idShop . '
                  AND `id_hook` = ' . (int) $idHook
            );

            if (!Db::getInstance()->insert('everblock', [
                'name' => 'exemple',
                'id_hook' => (int) $idHook,
                'only_home' => 1,
                'only_category' => 0,
                'only_category_product' => 0,
                'only_manufacturer' => 0,
                'only_supplier' => 0,
                'only_cms_category' => 0,
                'obfuscate_link' => 0,
                'add_container' => 1,
                'lazyload' => 0,
                'device' => 0,
                'id_shop' => (int) $idShop,
                'position' => $position,
                'categories' => json_encode([]),
                'manufacturers' => json_encode([]),
                'suppliers' => json_encode([]),
                'cms_categories' => json_encode([]),
                'groups' => json_encode([]),
                'background' => null,
                'css_class' => null,
                'data_attribute' => null,
                'bootstrap_class' => '0',
                'modal' => 0,
                'delay' => 0,
                'timeout' => 0,
                'date_start' => null,
                'date_end' => null,
                'active' => 0,
            ], true)) {
                return false;
            }

            $idBlock = (int) Db::getInstance()->Insert_ID();
            foreach ($languages as $language) {
                $idLang = (int) ($language['id_lang'] ?? $language['id'] ?? 0);
                if ($idLang <= 0) {
                    continue;
                }

                if (!Db::getInstance()->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . 'everblock_lang`
                    (`id_everblock`, `id_lang`, `content`, `custom_code`)
                    VALUES (
                        ' . (int) $idBlock . ',
                        ' . (int) $idLang . ',
                        "' . pSQL($content, true) . '",
                        ""
                    )'
                )) {
                    Db::getInstance()->delete('everblock_lang', '`id_everblock` = ' . (int) $idBlock);
                    Db::getInstance()->delete('everblock', '`id_everblock` = ' . (int) $idBlock . ' AND `id_shop` = ' . (int) $idShop);

                    return false;
                }
            }
        }

        return true;
    }

    private function buildExampleBlockContent(): string
    {
        $shortcodes = [];
        foreach (ShortcodeDocumentationProvider::getDocumentation($this) as $group) {
            foreach ((array) ($group['entries'] ?? []) as $entry) {
                $code = trim((string) ($entry['code'] ?? ''));
                if ($code === '' || $code === '[storelocator]') {
                    continue;
                }

                $shortcodes[] = $code;
            }
        }

        $shortcodes = array_values(array_unique($shortcodes));
        if (empty($shortcodes)) {
            return '';
        }

        return '<h2>Exemple shortcodes Ever Block</h2>' . PHP_EOL
            . implode(PHP_EOL, array_map(static function (string $shortcode): string {
                return '<p>' . $shortcode . '</p>';
            }, $shortcodes));
    }

    /**
     * @return array<int, int>
     */
    private function getInstallShopIds(): array
    {
        $shopIds = [];
        try {
            $shopIds = class_exists('Shop') ? (array) Shop::getShops(false, null, true) : [];
        } catch (Throwable $exception) {
            $shopIds = [];
        }

        if (empty($shopIds)) {
            $shopIds[] = (int) ($this->context->shop->id ?? 0);
            $shopIds[] = (int) Configuration::get('PS_SHOP_DEFAULT');
        }

        return array_values(array_unique(array_filter(array_map('intval', $shopIds))));
    }

    /**
     * Enregistre les hooks d'intégration avec QCD Builder lorsqu'ils sont disponibles.
     */
    private function registerQcdBuilderHooks(): bool
    {
        $hooksToRegister = [
            'filterQcdPageBuilderBackOfficeTargets',
            'filterQcdPageBuilderDeclarativeBlocks',
            'filterQcdPageBuilderThirdPartyBlockFrontRender',
            'filterQcdPageBuilderThirdPartyBlockFrontAssets',
        ];

        foreach ($hooksToRegister as $hookName) {
            if (!$this->isRegisteredInHook($hookName) && !$this->registerHook($hookName)) {
                return false;
            }
        }

        return true;
    }

    public function hookFilterQcdPageBuilderThirdPartyBlockFrontAssets(array $params)
    {
        $supportedTypes = [
            'everblock_select',
            'everblock_shortcode',
            'everblock_faq',
            'everblock_latest_pages',
        ];

        $rawContexts = $params['block_contexts'] ?? $params['block_types'] ?? [];
        if (!is_array($rawContexts) || empty($rawContexts)) {
            return [];
        }

        $hasEverblockBuilderBlock = false;
        foreach ($rawContexts as $rawContext) {
            $rawBlockType = '';
            if (is_array($rawContext)) {
                $rawBlockType = (string) (
                    $rawContext['block_type']
                    ?? $rawContext['type']
                    ?? $rawContext['code']
                    ?? $rawContext['normalized']['block_type']
                    ?? $rawContext['normalized']['type']
                    ?? $rawContext['normalized']['code']
                    ?? ''
                );
            } else {
                $rawBlockType = (string) $rawContext;
            }

            $blockType = Tools::strtolower(trim($rawBlockType));
            if ($blockType !== '' && preg_match('/[.:\/]/', $blockType)) {
                $parts = preg_split('/[.:\/]/', $blockType);
                if (isset($parts[0]) && $parts[0] === $this->name && isset($parts[count($parts) - 1])) {
                    $blockType = (string) $parts[count($parts) - 1];
                }
            }

            if (in_array($blockType, $supportedTypes, true)) {
                $hasEverblockBuilderBlock = true;
                break;
            }
        }

        if (!$hasEverblockBuilderBlock) {
            return [];
        }

        return [
            'stylesheets' => [
                [
                    'id' => 'module-' . $this->name . '-builder-blocks-css',
                    'path' => 'modules/' . $this->name . '/views/css/' . $this->name . '.css',
                    'options' => [
                        'media' => 'all',
                        'priority' => 200,
                    ],
                ],
            ],
            'javascripts' => [
                [
                    'id' => 'module-' . $this->name . '-builder-blocks-js',
                    'path' => 'modules/' . $this->name . '/views/js/' . $this->name . '.js',
                    'options' => [
                        'position' => 'bottom',
                        'priority' => 200,
                    ],
                ],
                [
                    'id' => 'module-' . $this->name . '-builder-blocks-slider-js',
                    'path' => 'modules/' . $this->name . '/views/js/everblock-slider.js',
                    'options' => [
                        'position' => 'bottom',
                        'priority' => 210,
                    ],
                ],
            ],
        ];
    }

    private function installTabs(): bool
    {
        $tabs = [
            ['AdminEverBlockParent', 'IMPROVE', $this->l('Ever Block'), null],
            ['AdminEverBlockConfiguration', 'AdminEverBlockParent', $this->l('Configuration'), 'admin_everblock_configuration'],
            ['AdminEverBlock', 'AdminEverBlockParent', $this->l('HTML Blocks'), 'admin_everblock_blocks'],
            ['AdminEverBlockHook', 'AdminEverBlockParent', $this->l('Hooks'), 'admin_everblock_hooks'],
            ['AdminEverBlockShortcode', 'AdminEverBlockParent', $this->l('Shortcodes'), 'admin_everblock_shortcodes'],
            ['AdminEverBlockShortcodeDocumentation', 'AdminEverBlockParent', $this->l('Shortcode documentation'), 'admin_everblock_shortcodes_documentation'],
            ['AdminEverBlockFaq', 'AdminEverBlockParent', $this->l('FAQ'), 'admin_everblock_faqs'],
            ['AdminEverBlockPage', 'AdminEverBlockParent', $this->l('Pages'), 'admin_everblock_pages'],
        ];

        foreach ($tabs as $tab) {
            if (!$this->installModuleTab($tab[0], $tab[1], $tab[2], $tab[3])) {
                return false;
            }
        }

        return true;
    }

    private function createHookIfNotExists(string $name, string $title, string $description): bool
    {
        if ((int) Hook::getIdByName($name) > 0) {
            return true;
        }

        $hook = new Hook();
        $hook->name = $name;
        $hook->title = $title;
        $hook->description = $description;

        return (bool) $hook->add();
    }

    public function uninstall()
    {
        // Uninstall SQL
        $sql = [];
        include dirname(__FILE__) . '/sql/uninstall.php';
        Configuration::deleteByName('EVERPSCSS_LINKS');
        Configuration::deleteByName('EVERPSJS_LINKS');
        Configuration::deleteByName('EVERPSCSS_P_LLOREM_NUMBER');
        Configuration::deleteByName('EVERPSCSS_S_LLOREM_NUMBER');
        Configuration::deleteByName('EVERBLOCK_TINYMCE');
        Configuration::deleteByName('EVERWP_API_URL');
        Configuration::deleteByName('EVERWP_BLOG_URL');
        Configuration::deleteByName('EVERWP_POST_NBR');
        Configuration::deleteByName('EVERWP_POSTS_BG_IMAGE');
        Configuration::deleteByName('EVER_SOLDOUT_COLOR');
        Configuration::deleteByName('EVER_SOLDOUT_TEXTCOLOR');
        Configuration::deleteByName('EVERBLOCK_LOAD_FRONT_CSS');
        Configuration::deleteByName('EVERBLOCK_SOLDOUT_FLAG');
        Configuration::deleteByName('EVERINSTA_SHOW_CAPTION');
        Configuration::deleteByName('EVERBLOCK_CONTACT_MAX_UPLOAD_SIZE');
        Configuration::deleteByName('EVERBLOCK_CONTACT_ALLOWED_EXTENSIONS');
        Configuration::deleteByName('EVERBLOCK_CONTACT_ALLOWED_MIME_TYPES');
        Configuration::deleteByName('EVERBLOCK_LOW_STOCK_THRESHOLD');
        Configuration::deleteByName('EVERBLOCK_STORELOCATOR_TOGGLE');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_API_KEY');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_PLACE_ID');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_LIMIT');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_SORT');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL');
        Configuration::deleteByName('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL');
        Configuration::deleteByName('EVERBLOCK_PAGES_BASE_URL');
        Configuration::deleteByName('EVERBLOCK_PAGES_PER_PAGE');
        Configuration::deleteByName('EVERBLOCK_FAQ_BASE_URL');
        Configuration::deleteByName('EVERBLOCK_FAQ_PER_PAGE');
        $uninstalled = (parent::uninstall()
            && $this->uninstallModuleTab('AdminEverBlockConfiguration')
            && $this->uninstallModuleTab('AdminEverBlock')
            && $this->uninstallModuleTab('AdminEverBlockHook')
            && $this->uninstallModuleTab('AdminEverBlockShortcode')
            && $this->uninstallModuleTab('AdminEverBlockShortcodeDocumentation')
            && $this->uninstallModuleTab('AdminEverBlockFaq')
            && $this->uninstallModuleTab('AdminEverBlockPage')
            && $this->uninstallModuleTab('AdminEverBlockParent'));


        return $uninstalled;
    }

    protected function registerQcdPageBuilderBackOfficeTargetsHook()
    {
        $hookName = 'filterQcdPageBuilderBackOfficeTargets';

        if (!Hook::getIdByName($hookName)) {
            $hook = new Hook();
            $hook->name = $hookName;
            $hook->title = 'QCD Page Builder back-office targets filter';
            $hook->description = 'This hook allows modules to add back-office editable targets for QCD Page Builder';

            if (!$hook->save()) {
                return false;
            }
        }

        return (bool) $this->registerHook($hookName);
    }


    public function l($string, $specific = null, $idLang = null)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            $this->getTranslationDomain($specific)
        );
    }

    private function getTranslationDomain($specific = null)
    {
        $domainKey = $specific ? $specific : $this->name;

        return sprintf('Modules.%s.%s', Tools::ucfirst($this->name), $this->normalizeDomainKey($domainKey));
    }

    private function normalizeDomainKey($key)
    {
        $key = trim((string) $key);

        if ($key === '') {
            $key = $this->name;
        }

        $key = str_replace(['-', '.'], '_', $key);
        $key = preg_replace('/[^A-Za-z0-9_]/', '', $key);
        $key = Tools::strtolower($key);

        return Tools::ucfirst($key);
    }

    public function hookActionObjectLanguageAddAfter($params): void
    {
        $language = $params['object'] ?? null;
        if ($language instanceof Language && Validate::isLoadedObject($language)) {
            $this->refreshTranslations((int) $language->id);

            return;
        }

        $this->refreshTranslations();
    }

    private function importLegacyTranslations(?int $idLang = null)
    {
        $legacyDir = dirname(__FILE__) . '/translations';

        if (!is_dir($legacyDir)) {
            return;
        }

        $defaultMap = $this->loadDefaultLegacyTranslations($legacyDir);

        if (empty($defaultMap)) {
            return;
        }

        foreach ($this->getLanguagesForTranslationImport($idLang) as $language) {
            $legacyFile = $this->resolveLegacyFileForIso($legacyDir, $language['iso_code']);

            if (!$legacyFile) {
                continue;
            }

            $legacyTranslations = $this->loadLegacyTranslationsFromFile($legacyFile);

            if (empty($legacyTranslations)) {
                continue;
            }

            foreach ($legacyTranslations as $legacyKey => $translatedValue) {
                if (!isset($defaultMap[$legacyKey])) {
                    continue;
                }

                $domain = $this->buildDomainFromLegacyKey($legacyKey);

                if (!$domain) {
                    continue;
                }

                $source = $defaultMap[$legacyKey];

                if ($source === $translatedValue || $source === '') {
                    continue;
                }

                $this->upsertTranslation(
                    (int) $language['id_lang'],
                    $domain,
                    $source,
                    $translatedValue
                );
            }
        }
    }

    private function getLanguagesForTranslationImport(?int $idLang = null): array
    {
        if ($idLang === null || $idLang <= 0) {
            return Language::getLanguages(false);
        }

        $language = new Language($idLang);
        if (!Validate::isLoadedObject($language)) {
            return [];
        }

        return [[
            'id_lang' => (int) $language->id,
            'iso_code' => (string) $language->iso_code,
        ]];
    }

    private function loadDefaultLegacyTranslations($legacyDir)
    {
        $candidates = ['en.php', 'gb.php', 'us.php', 'modern_gb.php', 'modern_en.php'];

        foreach ($candidates as $candidate) {
            $path = $legacyDir . '/' . $candidate;

            if (is_file($path)) {
                return $this->loadLegacyTranslationsFromFile($path);
            }
        }

        foreach (glob($legacyDir . '/*.php') as $file) {
            if (basename($file) === 'index.php') {
                continue;
            }

            return $this->loadLegacyTranslationsFromFile($file);
        }

        return [];
    }

    private function resolveLegacyFileForIso($legacyDir, $isoCode)
    {
        $iso = Tools::strtolower($isoCode);
        $candidates = [$iso . '.php', 'modern_' . $iso . '.php'];

        if ($iso === 'en') {
            $candidates[] = 'gb.php';
            $candidates[] = 'us.php';
            $candidates[] = 'modern_gb.php';
            $candidates[] = 'modern_us.php';
        }

        foreach ($candidates as $candidate) {
            $path = $legacyDir . '/' . $candidate;

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function loadLegacyTranslationsFromFile($path)
    {
        if (!is_file($path)) {
            return [];
        }

        $backup = isset($GLOBALS['_MODULE']) ? $GLOBALS['_MODULE'] : null;
        $GLOBALS['_MODULE'] = [];
        $_MODULE = &$GLOBALS['_MODULE'];

        include $path;

        $translations = isset($GLOBALS['_MODULE']) && is_array($GLOBALS['_MODULE'])
            ? $GLOBALS['_MODULE']
            : [];

        if ($backup !== null) {
            $GLOBALS['_MODULE'] = $backup;
        } else {
            unset($GLOBALS['_MODULE']);
        }

        return $translations;
    }

    private function buildDomainFromLegacyKey($legacyKey)
    {
        if (strpos($legacyKey, '>') === false) {
            return null;
        }

        $parts = explode('>', $legacyKey, 2);

        if (!isset($parts[1])) {
            return null;
        }

        $domainPart = $parts[1];
        $segments = explode('_', $domainPart);

        if (empty($segments)) {
            return null;
        }

        $domainKey = $segments[0];

        return sprintf('Modules.%s.%s', Tools::ucfirst($this->name), $this->normalizeDomainKey($domainKey));
    }

    private function upsertTranslation($idLang, $domain, $source, $translation)
    {
        $db = Db::getInstance();
        $where = '`id_lang` = ' . (int) $idLang
            . " AND `domain` = '" . pSQL($domain) . "'"
            . " AND `key` = '" . pSQL($source, true) . "'"
            . " AND (`theme` IS NULL OR `theme` = '')";
        $idTranslation = (int) $db->getValue(
            'SELECT `id_translation` FROM `' . _DB_PREFIX_ . 'translation` WHERE ' . $where
        );

        if ($idTranslation > 0) {
            $db->update(
                'translation',
                ['translation' => pSQL($translation, true)],
                '`id_translation` = ' . (int) $idTranslation
            );

            return;
        }

        $db->insert('translation', [
            'id_lang' => (int) $idLang,
            'domain' => pSQL($domain),
            'key' => pSQL($source, true),
            'translation' => pSQL($translation, true),
            'theme' => '',
        ], false, true, Db::INSERT);
    }

    private function installModuleTab(string $className, string $parentClassName, string $name, ?string $routeName = null): bool
    {
        $existingId = (int) Tab::getIdFromClassName($className);
        if ($existingId > 0) {
            if ($routeName) {
                $existingTab = new Tab($existingId);
                if (property_exists($existingTab, 'route_name')) {
                    $existingTab->route_name = $routeName;
                    $existingTab->save();
                }
            }

            return true;
        }

        $parentId = (int) Tab::getIdFromClassName($parentClassName);
        if ($parentId <= 0) {
            return false;
        }

        $tab = new Tab();
        $tab->active = true;
        $tab->class_name = $className;
        $tab->id_parent = $parentId;
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($routeName && property_exists($tab, 'route_name')) {
            $tab->route_name = $routeName;
        }

        if ($className === 'AdminEverBlockParent') {
            $tab->icon = 'icon-team-ever';
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = $name;
        }

        return (bool) $tab->add();
    }

    protected function uninstallModuleTab($tabClass)
    {
        $tabId = (int) Tab::getIdFromClassName($tabClass);
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);
        return $tab->delete();
    }

    public function checkHooks()
    {
        if (!Hook::getIdByName('displayEverblockExtraOrderStep')) {
            $hook = new Hook();
            $hook->name = 'displayEverblockExtraOrderStep';
            $hook->title = 'Extra order step';
            $hook->description = 'This hook is triggered on extra order step';
            $hook->save();
        }
        if (!Hook::getIdByName('actionGetEverBlockBefore')) {
            $hook = new Hook();
            $hook->name = 'actionGetEverBlockBefore';
            $hook->title = 'Before block is rendered';
            $hook->description = 'This hook triggers before block is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeBefore')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeBefore';
            $hook->title = 'Before block shortcodes are rendered';
            $hook->description = 'This hook triggers before every block shortcode is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeAfter')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeAfter';
            $hook->title = 'After block shortcodes are rendered';
            $hook->description = 'This hook triggers after every block shortcode is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayBeforeRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeRenderingShortcodes';
            $hook->title = 'Before rendering shortcodes';
            $hook->description = 'This hook triggers before shortcodes are rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayAfterRenderingShortcodes';
            $hook->title = 'After rendering shortcodes';
            $hook->description = 'This hook triggers after shortcodes are rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayFakeHook')) {
            $hook = new Hook();
            $hook->name = 'displayFakeHook';
            $hook->title = 'Fake hook';
            $hook->description = 'Ne pas afficher ce hook en front, il sera utilisé pour du contenu asynchrone';
            $hook->save();
        }
        if (!Hook::getIdByName('displayBeforeStoreLocator')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeStoreLocator';
            $hook->title = 'display before Everblock store locator';
            $hook->description = 'This hook triggers before store locator is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterStoreLocator')) {
            $hook = new Hook();
            $hook->name = 'displayAfterStoreLocator';
            $hook->title = 'display after Everblock store locator';
            $hook->description = 'This hook triggers after store locator is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterLocatorStore')) {
            $hook = new Hook();
            $hook->name = 'displayAfterLocatorStore';
            $hook->title = 'display after store content on store locator';
            $hook->description = 'This hook triggers after store content on store locator';
            $hook->save();
        }
        if (!Hook::getIdByName('displayBeforeProductMiniature')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeProductMiniature';
            $hook->title = 'display before product miniature';
            $hook->description = 'This hook triggers before product miniature is rendered';
            $hook->save();
        }
        if (!Hook::getIdByName('displayAfterProductMiniature')) {
            $hook = new Hook();
            $hook->name = 'displayAfterProductMiniature';
            $hook->title = 'display after product miniature';
            $hook->description = 'This hook triggers after product miniature is rendered';
            $hook->save();
        }

        // Vérifier si l'onglet "AdminEverBlockParent" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockParent');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockParent';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('IMPROVE');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('Ever Block');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "AdminEverBlock" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlock');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlock';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('HTML blocks management');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "Hook management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockHook');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockHook';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('Hook management');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "Shortcodes management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockShortcode');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockShortcode';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('Shortcodes management');
            }
            $tab->add();
        }
        // Vérifier si l'onglet "FAQ management" existe déjà
        $id_tab = Tab::getIdFromClassName('AdminEverBlockFaq');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockFaq';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('FAQ');
            }
            $tab->add();
        }
        $id_tab = Tab::getIdFromClassName('AdminEverBlockPage');
        if (!$id_tab) {
            $tab = new Tab();
            $tab->class_name = 'AdminEverBlockPage';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminEverBlockParent');
            $tab->position = Tab::getNewLastPosition($tab->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $this->l('Pages');
            }
            $tab->add();
        }
        $this->registerHook('displayContentWrapperTop');
        $this->registerHook('actionCmsPageFormBuilderModifier');
        $this->registerHook('actionObjectCmsUpdateAfter');
        $this->registerHook('displayMaintenance');
        $this->registerHook('displayPDFInvoice');
        $this->registerHook('displayPDFDeliverySlip');
        $this->registerHook('displayAdminOrder');
        $this->registerHook('actionCheckoutRender');
        $this->registerHook('displayOrderConfirmation');
        $this->unregisterHook('actionDispatcherBefore');
        $this->registerHook('actionGetAdminOrderButtons');
        $this->registerHook('displayAdminCustomers');
        $this->registerHook('actionCustomerLogoutBefore');
        $this->registerHook('displayAdminProductsExtra');
        $this->registerHook('displayAdminProductsMainStepLeftColumnBottom');
        $this->registerHook('actionObjectProductAddAfter');
        $this->registerHook('displayReassurance');
        $this->registerHook('actionObjectProductUpdateAfter');
        $this->registerHook('actionObjectProductDeleteAfter');
        $this->registerHook('displayProductExtraContent');
        $this->registerHook('actionOutputHTMLBefore');
        $this->registerHook('displayHeader');
        $this->registerHook('actionAdminControllerSetMedia');
        $this->registerHook('actionObjectLanguageAddAfter');
        $this->registerHook('actionObjectEverBlockClassUpdateAfter');
        $this->registerHook('actionObjectEverBlockClassDeleteAfter');
        $this->registerHook('actionObjectEverblockFaqUpdateAfter');
        $this->registerHook('actionObjectEverblockFaqDeleteAfter');
        $this->registerHook('actionObjectEverBlockFlagsUpdateAfter');
        $this->registerHook('actionObjectEverBlockFlagsDeleteAfter');
        $this->registerHook('displayWrapperBottom');
        $this->registerHook('displayWrapperTop');
        $this->registerQcdBuilderHooks();
        $this->registerStoredBlockHooks();
        $this->updateProductFlagsHook();
        $this->registerHook('actionEmailAddAfterContent');
        $this->installTabs();
    }

    protected function registerStoredBlockHooks(): void
    {
        try {
            $blocksHooks = Db::getInstance()->executeS(
                'SELECT DISTINCT h.`name`
                FROM `' . _DB_PREFIX_ . 'everblock` b
                INNER JOIN `' . _DB_PREFIX_ . 'hook` h ON h.`id_hook` = b.`id_hook`
                WHERE b.`id_hook` > 0
                  AND h.`name` NOT LIKE "action%"
                  AND h.`name` NOT LIKE "filter%"'
            );
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());

            return;
        }

        if (!is_array($blocksHooks)) {
            return;
        }

        foreach ($blocksHooks as $hook) {
            $hookName = (string) ($hook['name'] ?? '');
            if (!$hookName || !Validate::isHookName($hookName) || $this->isRegisteredInHook($hookName)) {
                continue;
            }

            $this->registerHook($hookName);
        }
    }

    protected function updateProductFlagsHook()
    {
        $idShop = (int) $this->context->shop->id;
        $cacheId = $this->name . 'NeedProductFlagsHook_' . $idShop;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $needHook = false;

            if (Configuration::get('EVERBLOCK_SOLDOUT_FLAG')) {
                $needHook = true;
            }

            $featuresAsFlags = json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
            if (!empty($featuresAsFlags)) {
                $needHook = true;
            }

            $sql = new DbQuery();
            $sql->select('id_everblock_flags');
            $sql->from('everblock_flags');
            $sql->where('id_shop = ' . (int) $idShop);
            if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
                $needHook = true;
            }

            EverblockCache::cacheStore($cacheId, $needHook);
        }

        $needHook = (bool) EverblockCache::cacheRetrieve($cacheId);

        if ($needHook) {
            $this->registerHook('actionProductFlagsModifier');
        } else {
            $this->unregisterHook('actionProductFlagsModifier');
        }
    }

    public function hookFilterQcdPageBuilderBackOfficeTargets(array $params)
    {
        if (!isset($params['targets']) || !is_array($params['targets'])) {
            $params['targets'] = [];
        }

        $targets = [
            [
                'target_type' => 'everblock',
                'target_field' => 'content',
                'controllers' => ['admineverblock'],
                'selectors' => $this->buildQcdBuilderLocalizedSelectors('content'),
                'id_resolver' => $this->buildQcdBuilderEntityResolver(
                    ['id_everblock', 'everblockId', 'id'],
                    ['input[name="id_everblock"]', 'input[name="block[id]"]', 'input[name="block[id_everblock]"]'],
                    ['input[name="block[name]"]', 'input[name="name"]']
                ),
            ],
            [
                'target_type' => 'everblock_shortcode',
                'target_field' => 'content',
                'controllers' => ['admineverblockshortcode'],
                'selectors' => $this->buildQcdBuilderLocalizedSelectors('content'),
                'id_resolver' => $this->buildQcdBuilderEntityResolver(
                    ['id_everblock_shortcode', 'shortcodeId', 'id'],
                    ['input[name="id_everblock_shortcode"]', 'input[name="shortcode[id]"]', 'input[name="shortcode[id_everblock_shortcode]"]'],
                    ['input[name="shortcode[shortcode]"]', 'input[name="shortcode"]']
                ),
            ],
            [
                'target_type' => 'everblock_faq',
                'target_field' => 'content',
                'controllers' => ['admineverblockfaq'],
                'selectors' => $this->buildQcdBuilderLocalizedSelectors('content'),
                'id_resolver' => $this->buildQcdBuilderEntityResolver(
                    ['id_everblock_faq', 'faqId', 'id'],
                    ['input[name="id_everblock_faq"]', 'input[name="faq[id]"]', 'input[name="faq[id_everblock_faq]"]'],
                    ['input[name="faq[tag_name]"]', 'input[name="tag_name"]']
                ),
            ],
            [
                'target_type' => 'everblock_page',
                'target_field' => 'content',
                'controllers' => ['admineverblockpage'],
                'selectors' => $this->buildQcdBuilderLocalizedSelectors('content'),
                'id_resolver' => $this->buildQcdBuilderEntityResolver(
                    ['id_everblock_page', 'pageId', 'id'],
                    ['input[name="id_everblock_page"]', 'input[name="page[id]"]', 'input[name="page[id_everblock_page]"]'],
                    ['input[name^="page[link_rewrite_"]', 'input[name^="page[name_"]', 'input[name^="link_rewrite_"]', 'input[name^="name_"]']
                ),
            ],
            [
                'target_type' => 'everblock_global_tab',
                'target_field' => 'content',
                'controllers' => ['admineverblockconfiguration'],
                'selectors' => $this->buildQcdBuilderLocalizedSelectors('EVER_TAB_CONTENT'),
                'id_resolver' => [
                    'input_selectors' => [],
                    'data_attributes' => ['data-everblock-qcd-target-id', 'data-id-object', 'data-id'],
                    'meta_selectors' => [],
                    'query_params' => ['id_shop'],
                    'custom_extractors' => [],
                    'draft_key' => ['enabled' => false],
                    'path_fallback' => false,
                ],
            ],
            [
                'target_type' => 'everblock_product_modal',
                'target_field' => 'content',
                'controllers' => ['adminproducts', 'adminproductscontroller'],
                'selectors' => $this->buildQcdBuilderLocalizedSelectors('everblock_modal_content'),
                'id_resolver' => $this->buildQcdBuilderProductResolver(),
            ],
        ];

        $tabsNumber = max((int) Configuration::get('EVERPS_TAB_NB'), 1);
        for ($tabNumber = 1; $tabNumber <= $tabsNumber; ++$tabNumber) {
            $targets[] = [
                'target_type' => 'everblock_product_tab',
                'target_field' => 'tab_' . $tabNumber . '_content',
                'controllers' => ['adminproducts', 'adminproductscontroller'],
                'selectors' => $this->buildQcdBuilderLocalizedSelectors($tabNumber . '_everblock_content'),
                'id_resolver' => $this->buildQcdBuilderProductResolver(),
            ];
        }

        foreach ($targets as $target) {
            $params['targets'][] = $target;
        }

        return $params;
    }

    private function buildQcdBuilderLocalizedSelectors(string $baseName): array
    {
        $selectors = [];
        foreach (Language::getLanguages(false) as $language) {
            $langId = (int) ($language['id_lang'] ?? 0);
            if ($langId <= 0) {
                continue;
            }

            $fieldName = $baseName . '_' . $langId;
            $selectors[] = 'textarea[name="' . $fieldName . '"]';
            $selectors[] = 'textarea[name$="[' . $fieldName . ']"]';
            $selectors[] = 'textarea[id="' . $fieldName . '"]';
            $selectors[] = 'textarea[id$="_' . $fieldName . '"]';
        }

        return array_values(array_unique($selectors));
    }

    private function buildQcdBuilderEntityResolver(array $queryParams, array $inputSelectors, array $draftInputSelectors): array
    {
        return [
            'input_selectors' => $inputSelectors,
            'data_attributes' => ['data-id-object', 'data-id', 'data-everblock-id'],
            'meta_selectors' => [],
            'query_params' => $queryParams,
            'custom_extractors' => ['closest_form_action_query'],
            'draft_key' => [
                'enabled' => true,
                'input_selectors' => $draftInputSelectors,
                'data_attributes' => ['data-draft-key'],
                'query_params' => ['draft_key', 'draft'],
            ],
            'path_fallback' => true,
        ];
    }

    private function buildQcdBuilderProductResolver(): array
    {
        return [
            'input_selectors' => [
                'input[name="id_product"]',
                'input[name="product[id]"]',
                'input[name="form[id_product]"]',
            ],
            'data_attributes' => [
                'data-ever-product-id',
                'data-id-product',
                'data-product-id',
                'data-id-object',
                'data-id',
            ],
            'meta_selectors' => ['meta[name="qcdpb:id_product"]', 'meta[name="product:id"]'],
            'query_params' => ['id_product', 'productId', 'id'],
            'custom_extractors' => ['closest_form_action_query'],
            'draft_key' => [
                'enabled' => true,
                'input_selectors' => ['input[name="product[reference]"]', 'input[name="form[step1][reference]"]'],
                'data_attributes' => ['data-draft-key', 'data-reference'],
                'query_params' => ['draft_key', 'draft', 'reference'],
            ],
            'path_fallback' => true,
        ];
    }

    public function hookFilterQcdPageBuilderDeclarativeBlocks(array $params)
    {
        $everblockLogo = 'modules/' . $this->name . '/views/img/svg/grid.svg';
        $shortcodeLogo = 'modules/' . $this->name . '/views/img/svg/copy.svg';
        $faqLogo = 'modules/' . $this->name . '/views/img/svg/help.svg';
        $pagesLogo = 'modules/' . $this->name . '/views/img/svg/list.svg';

        $everblockTemplate = 'views/templates/hook/everblock.tpl';
        $shortcodeTemplate = 'views/templates/hook/everblock.tpl';
        $faqTemplate = 'views/templates/hook/faq.tpl';
        $pagesTemplate = 'views/templates/front/pages.tpl';

        return [
            [
                'name' => $this->l('Everblock selection'),
                'description' => $this->l('Display a selected Everblock'),
                'code' => 'everblock_select',
                'tab' => 'general',
                'icon_path' => $everblockLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $everblockTemplate,
                ],
                'config' => [
                    'fields' => [
                        [
                            'name' => 'id_everblock',
                            'type' => 'select',
                            'label' => $this->l('Everblock'),
                            'collection' => [
                                'class' => \EverBlockClass::class,
                                'label_field' => 'name',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => $this->l('Shortcode selection'),
                'description' => $this->l('Display a selected shortcode entry'),
                'code' => 'everblock_shortcode',
                'tab' => 'general',
                'icon_path' => $shortcodeLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $shortcodeTemplate,
                ],
                'config' => [
                    'fields' => [
                        [
                            'name' => 'id_everblock_shortcode',
                            'type' => 'select',
                            'label' => $this->l('Shortcode'),
                            'collection' => [
                                'class' => \EverblockShortcode::class,
                                'label_field' => 'title',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => $this->l('FAQ selection'),
                'description' => $this->l('Display selected FAQ entries'),
                'code' => 'everblock_faq',
                'tab' => 'general',
                'icon_path' => $faqLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $faqTemplate,
                ],
                'config' => [
                    'fields' => [
                        [
                            'name' => 'faq_ids',
                            'type' => 'array',
                            'input' => 'multiselect',
                            'label' => $this->l('FAQs'),
                            'collection' => [
                                'class' => \EverblockFaq::class,
                                'label_field' => 'title',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => $this->l('Latest pages'),
                'description' => $this->l('Display latest published pages'),
                'code' => 'everblock_latest_pages',
                'tab' => 'general',
                'icon_path' => $pagesLogo,
                'need_reload' => true,
                'templates' => [
                    'default' => $pagesTemplate,
                ],
                'config' => [
                    'fields' => [
                        [
                            'name' => 'limit',
                            'type' => 'number',
                            'label' => $this->l('Number of pages'),
                            'default' => 5,
                            'min' => 1,
                            'max' => 50,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function hookFilterQcdPageBuilderThirdPartyBlockFrontRender(array $params)
    {
        $renderer = new QcdThirdPartyBlockRenderer(
            $this,
            $this->context,
            $this->getQcdBuilderModule()
        );
        $renderer->renderFromHookFilterQcdPageBuilderThirdPartyBlockFrontRender($params);
    }

    private function getQcdBuilderModule(): ?Module
    {
        if ($this->qcdBuilderModuleResolved) {
            return $this->qcdBuilderModule;
        }

        static $cachedQcdBuilderModule;
        static $cachedQcdBuilderModuleResolved = false;

        if (!$cachedQcdBuilderModuleResolved) {
            $module = Module::getInstanceByName('qcdpagebuilder');
            $cachedQcdBuilderModule = ($module instanceof Module) ? $module : null;
            $cachedQcdBuilderModuleResolved = true;
        }

        $this->qcdBuilderModule = $cachedQcdBuilderModule;
        $this->qcdBuilderModuleResolved = true;

        return $this->qcdBuilderModule;
    }

    public function renderQcdBuilderTargetField(
        string $targetType,
        int $targetId,
        string $targetField,
        string $nativeContent = '',
        ?int $idShop = null,
        ?int $idLang = null
    ): string {
        if ($targetId <= 0 || trim($targetType) === '' || trim($targetField) === '') {
            return $nativeContent;
        }

        if (!Module::isEnabled('qcdpagebuilder')) {
            return $nativeContent;
        }

        $builder = $this->getQcdBuilderModule();
        if (!$builder || !method_exists($builder, 'renderTargetField')) {
            return $nativeContent;
        }

        try {
            return (string) $builder->renderTargetField(
                $targetType,
                $targetId,
                $targetField,
                $nativeContent,
                $idShop,
                $idLang
            );
        } catch (Throwable $exception) {
            PrestaShopLogger::addLog(
                'Ever Block QCD Page Builder render failed: ' . $exception->getMessage(),
                2
            );

            return $nativeContent;
        }
    }

    public function getContent()
    {
        if ($this->isProductModalAjaxRequest()) {
            $this->ajaxProcessProductModalFile();
        }

        if ($this->isFaqSearchAjaxRequest()) {
            $this->ajaxProcessFaqSearch();
        }

        $this->createUpgradeFile();
        $this->secureModuleFolder();
        EverblockTools::checkAndFixDatabase();
        $this->checkHooks();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminEverBlockConfiguration'));

        return '';
    }

    public function getAdminConfigurationFormData(): array
    {
        return $this->getAdminConfigurationManager()->getFormData($this);
    }

    public function getAdminConfigurationViewContext(): array
    {
        return $this->getAdminConfigurationManager()->getViewContext($this);
    }

    public function processAdminConfigurationRequest(): array
    {
        return $this->getAdminConfigurationManager()->processRequest($this);
    }

    public function getAdminConfigurationLegacyFormValues(): array
    {
        return $this->getConfigFormValues();
    }

    public function getAdminConfigurationModuleStatistics(): array
    {
        return $this->getModuleStatistics();
    }

    public function getAdminConfigurationAllowedActions(): array
    {
        return $this->allowedActions;
    }

    public function getAdminConfigurationCronToken(): string
    {
        return $this->encrypt($this->name . '/evercron');
    }

    public function prepareAdminConfigurationEnvironment(): void
    {
        $this->createUpgradeFile();
        $this->secureModuleFolder();
        EverblockTools::checkAndFixDatabase();
        $this->checkHooks();
    }

    public function resetAdminConfigurationMessages(): void
    {
        $this->postErrors = [];
        $this->postSuccess = [];
    }

    public function getAdminConfigurationMessages(): array
    {
        return [
            'errors' => $this->postErrors,
            'success' => $this->postSuccess,
        ];
    }

    public function runAdminConfigurationPostValidation(): void
    {
        $this->postValidation();
    }

    public function runAdminConfigurationPostProcess(): void
    {
        $this->postProcess();
    }

    public function runAdminConfigurationTabsUpload(): void
    {
        $this->uploadTabsFile();
    }

    public function runAdminConfigurationCacheCleanup(): void
    {
        $this->emptyAllCache();
    }

    private function getAdminConfigurationManager(): AdminConfigurationManager
    {
        try {
            $container = SymfonyContainer::getInstance();
            if ($container && $container->has(AdminConfigurationManager::class)) {
                return $container->get(AdminConfigurationManager::class);
            }
        } catch (Throwable $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        }

        return new AdminConfigurationManager();
    }

    private function renderAdminTwig(string $template, array $parameters = []): string
    {
        try {
            $container = SymfonyContainer::getInstance();
            if (!$container || !$container->has('twig')) {
                return '';
            }

            return (string) $container->get('twig')->render(
                '@Modules/' . $this->name . '/templates/admin/' . $template,
                $parameters
            );
        } catch (Throwable $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());

            return '';
        }
    }

    protected function isProductModalAjaxRequest()
    {
        return Tools::getIsset('ajax')
            && Tools::getValue('ajax')
            && Tools::getValue('action') === 'EverblockProductModalFile'
            && Tools::getValue('configure') === $this->name;
    }

    protected function isFaqSearchAjaxRequest()
    {
        return Tools::getIsset('ajax')
            && Tools::getValue('ajax')
            && Tools::getValue('action') === 'EverblockSearchFaq'
            && Tools::getValue('configure') === $this->name;
    }

    protected function sanitizeModalFileName($originalName)
    {
        $originalName = basename((string) $originalName);
        $extension = Tools::strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);

        $baseName = Tools::replaceAccentedChars($baseName);
        $baseName = preg_replace('/[^A-Za-z0-9\-\. _]+/', '_', $baseName);
        $baseName = preg_replace('/_{2,}/', '_', (string) $baseName);
        $baseName = trim((string) $baseName, ' ._-');

        if ($baseName === '') {
            $baseName = 'modal_file';
        }

        if ($extension !== '') {
            return $baseName . '.' . $extension;
        }

        return $baseName;
    }

    protected function ajaxProcessFaqSearch()
    {
        $this->context->controller->ajax = true;
        header('Content-Type: application/json');

        $response = [
            'results' => [],
            'pagination' => [
                'more' => false,
            ],
        ];

        try {
            $shopId = (int) $this->context->shop->id;
            $langId = (int) $this->context->employee->id_lang;
            if ($langId <= 0) {
                $langId = (int) Configuration::get('PS_LANG_DEFAULT');
            }
            $query = Tools::getValue('q', '');
            $page = (int) Tools::getValue('page', 1);
            $limit = (int) Tools::getValue('limit', 20);
            if ($limit <= 0 || $limit > 50) {
                $limit = 20;
            }
            if ($page <= 0) {
                $page = 1;
            }

            $searchResults = EverblockFaq::searchFaqOptions($shopId, $langId, (string) $query, $page, $limit);
            $options = [];
            foreach ($searchResults['results'] as $option) {
                $label = $option['text'];
                if (empty($option['active'])) {
                    $label .= ' (' . $this->l('Inactive') . ')';
                }
                $options[] = [
                    'id' => (int) $option['id'],
                    'text' => $label,
                    'active' => (bool) $option['active'],
                    'tag_name' => $option['tag_name'],
                    'title' => $option['title'],
                ];
            }

            $response['results'] = $options;
            $response['pagination']['more'] = !empty($searchResults['has_more']);
        } catch (Exception $e) {
            $response['error'] = $this->l('Unable to fetch FAQ entries.');
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
        }

        die(json_encode($response));
    }

    protected function ensureModalDirectory($productId)
    {
        $baseDir = _PS_IMG_DIR_ . 'cms/everblockmodal/';
        if (!is_dir($baseDir) && !@mkdir($baseDir, 0755, true)) {
            throw new Exception($this->l('Unable to create modal directory.'));
        }

        $productDir = $baseDir . (int) $productId . '/';
        if (!is_dir($productDir) && !@mkdir($productDir, 0755, true)) {
            throw new Exception($this->l('Unable to create product modal directory.'));
        }

        return $productDir;
    }

    protected function isPreviewableModalFile($path)
    {
        $extension = Tools::strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $previewableExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'];

        return $extension !== '' && in_array($extension, $previewableExtensions, true);
    }

    protected function buildTimestampedUrl($url, $timestamp)
    {
        if (!$url) {
            return '';
        }

        $separator = (strpos($url, '?') === false) ? '?' : '&';

        return $url . $separator . 't=' . (int) $timestamp;
    }

    protected function cleanupModalDirectory($path)
    {
        $path = rtrim((string) $path, '/');
        $baseDir = rtrim(_PS_IMG_DIR_ . 'cms/everblockmodal', '/');

        if ($path === '' || $path === $baseDir) {
            return;
        }

        if (!is_dir($path)) {
            return;
        }

        $handle = opendir($path);
        if ($handle === false) {
            return;
        }

        $isEmpty = true;
        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $isEmpty = false;
            break;
        }
        closedir($handle);

        if ($isEmpty) {
            @rmdir($path);
        }
    }

    protected function ajaxProcessProductModalFile()
    {
        $this->context->controller->ajax = true;
        header('Content-Type: application/json');

        $target = (string) Tools::getValue('target', 'modal');
        $target = $target === 'button' ? 'button' : 'modal';
        $fileField = $target === 'button' ? 'everblock_modal_button_file' : 'everblock_modal_file';
        $fileProperty = $target === 'button' ? 'button_file' : 'file';
        $targetLabel = $target === 'button' ? $this->l('Button file') : $this->l('Modal file');

        $response = [
            'success' => false,
            'message' => $this->l('An unexpected error occurred.'),
            'file_url' => '',
            'file_name' => '',
            'file_display_name' => '',
            'file_preview_url' => '',
            'file_timestamp' => null,
            'is_image' => false,
        ];

        try {
            $productId = (int) Tools::getValue('id_product');
            if ($productId <= 0) {
                $response['message'] = $this->l('Missing product identifier.');
                die(json_encode($response));
            }

            $shopId = (int) $this->context->shop->id;
            $modal = EverblockModal::getByProductId($productId, $shopId);
            if (!is_array($modal->content)) {
                $modal->content = [];
            }
            if (!is_array($modal->button_label)) {
                $modal->button_label = [];
            }

            if ((int) Tools::getValue('delete')) {
                if (!empty($modal->{$fileProperty})) {
                    $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->{$fileProperty};
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                        $this->cleanupModalDirectory(dirname($oldFile));
                    }
                    $modal->{$fileProperty} = '';
                    if (!Validate::isLoadedObject($modal)) {
                        $languages = Language::getLanguages(true);
                        foreach ($languages as $language) {
                            $modal->content[$language['id_lang']] = $modal->content[$language['id_lang']] ?? '';
                            $modal->button_label[$language['id_lang']] = $modal->button_label[$language['id_lang']] ?? '';
                        }
                    }
                    $modal->save();
                }

                $response['success'] = true;
                $response['message'] = sprintf($this->l('%s removed successfully.'), $targetLabel);
                die(json_encode($response));
            }

            if (!isset($_FILES[$fileField]) || !is_uploaded_file($_FILES[$fileField]['tmp_name'])) {
                $response['message'] = $this->l('No file received.');
                die(json_encode($response));
            }

            $uploadedFile = $_FILES[$fileField];
            if (!empty($uploadedFile['error']) && $uploadedFile['error'] !== UPLOAD_ERR_OK) {
                $response['message'] = $this->l('Unable to upload the file.');
                die(json_encode($response));
            }

            if (!empty($modal->{$fileProperty})) {
                $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->{$fileProperty};
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                    $this->cleanupModalDirectory(dirname($oldFile));
                }
            }

            $targetDir = $this->ensureModalDirectory($productId);

            $fileName = $this->sanitizeModalFileName($uploadedFile['name']);
            $destinationPath = $targetDir . $fileName;

            if (!move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
                $response['message'] = $this->l('Unable to move the uploaded file.');
                die(json_encode($response));
            }

            if (!Validate::isLoadedObject($modal)) {
                $languages = Language::getLanguages(true);
                foreach ($languages as $language) {
                    $modal->content[$language['id_lang']] = $modal->content[$language['id_lang']] ?? '';
                    $modal->button_label[$language['id_lang']] = $modal->button_label[$language['id_lang']] ?? '';
                }
            }

            $modal->{$fileProperty} = 'everblockmodal/' . (int) $productId . '/' . $fileName;
            $modal->id_product = $productId;
            $modal->id_shop = $shopId;
            $modal->save();

            $response['success'] = true;
            $response['message'] = sprintf($this->l('%s uploaded successfully.'), $targetLabel);
            $response['file_url'] = $this->context->link->getBaseLink() . 'img/cms/' . $modal->{$fileProperty};
            $response['file_name'] = basename($modal->{$fileProperty});
            $response['file_display_name'] = preg_replace('/[\r\n]+/', '', basename($uploadedFile['name']));

            $timestamp = @filemtime($destinationPath);
            if (!$timestamp) {
                $timestamp = time();
            }

            $response['file_timestamp'] = $timestamp;
            $response['is_image'] = $this->isPreviewableModalFile($destinationPath);
            if ($response['is_image']) {
                $response['file_preview_url'] = $this->buildTimestampedUrl($response['file_url'], $timestamp);
            }
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $exception->getMessage()
            );
            $response['message'] = $this->l('An unexpected error occurred during upload.');
        }

        die(json_encode($response));
    }

    protected function getConfigFormValues()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $custom_css = Tools::file_get_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom' . $idShop . '.css'
        );
        $custom_js = Tools::file_get_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom' . $idShop . '.js'
        );
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/header-scripts-' . $this->context->shop->id . '.js';
        if (file_exists($filePath) && filesize($filePath) > 0) {
            $headerScripts = file_get_contents($filePath);
        } else {
            $headerScripts = '';
        }
        $bannedFeatures = json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
        if (!is_array($bannedFeatures)) {
            $bannedFeatures = [];
        }
        $bannedFeaturesColors = [];
        foreach ($bannedFeatures as $bannedFeature) {
            $featureId = (int) $bannedFeature;
            $bannedFeaturesColors[
                'EVERPS_FEATURE_COLOR_' . $featureId
            ] = Configuration::get('EVERPS_FEATURE_COLOR_' . $featureId);

            $bannedFeaturesColors[
                'EVERPS_FEATURE_TEXTCOLOR_' . $featureId
            ] = Configuration::get('EVERPS_FEATURE_TEXTCOLOR_' . $featureId);
        }
        $configData = [
            'EVEROPTIONS_POSITION' => Configuration::get('EVEROPTIONS_POSITION'),
            'EVEROPTIONS_TITLE' => $this->getConfigInMultipleLangs('EVEROPTIONS_TITLE'),
            'EVERBLOCK_MAINTENANCE_PSSWD' => Configuration::get('EVERBLOCK_MAINTENANCE_PSSWD'),
            'EVERINSTA_ACCESS_TOKEN' => Configuration::get('EVERINSTA_ACCESS_TOKEN'),
            'EVERINSTA_LINK' => Configuration::get('EVERINSTA_LINK'),
            'EVERINSTA_SHOW_CAPTION' => Configuration::get('EVERINSTA_SHOW_CAPTION'),
            'EVERWP_API_URL' => Configuration::get('EVERWP_API_URL'),
            'EVERWP_BLOG_URL' => Configuration::get('EVERWP_BLOG_URL'),
            'EVERWP_POST_NBR' => Configuration::get('EVERWP_POST_NBR'),
            'EVERWP_POSTS_BG_IMAGE' => Configuration::get('EVERWP_POSTS_BG_IMAGE'),
            'EVERBLOCK_GOOGLE_API_KEY' => Configuration::get('EVERBLOCK_GOOGLE_API_KEY'),
            'EVERBLOCK_GOOGLE_PLACE_ID' => Configuration::get('EVERBLOCK_GOOGLE_PLACE_ID'),
            'EVERBLOCK_GOOGLE_REVIEWS_LIMIT' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_LIMIT'),
            'EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING'),
            'EVERBLOCK_GOOGLE_REVIEWS_SORT' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SORT'),
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING'),
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR'),
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA'),
            'EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL'),
            'EVERBLOCK_GOOGLE_REVIEWS_CTA_URL' => Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL'),
            'EVERBLOCK_GMAP_KEY' => Configuration::get('EVERBLOCK_GMAP_KEY'),
            'EVERBLOCK_MARKER_ICON' => Configuration::get('EVERBLOCK_MARKER_ICON'),
            'EVERBLOCK_STORELOCATOR_TOGGLE' => Configuration::get('EVERBLOCK_STORELOCATOR_TOGGLE'),
            'EVERBLOCK_USE_OBF' => Configuration::get('EVERBLOCK_USE_OBF'),
            'EVERBLOCK_SOLDOUT_FLAG' => Configuration::get('EVERBLOCK_SOLDOUT_FLAG'),
            'EVER_SOLDOUT_COLOR' => Configuration::get('EVER_SOLDOUT_COLOR'),
            'EVER_SOLDOUT_TEXTCOLOR' => Configuration::get('EVER_SOLDOUT_TEXTCOLOR'),
            'EVERBLOCK_PAGES_BASE_URL' => Configuration::get('EVERBLOCK_PAGES_BASE_URL') ?: 'guide',
            'EVERBLOCK_PAGES_PER_PAGE' => Configuration::get('EVERBLOCK_PAGES_PER_PAGE') ?: 9,
            'EVERBLOCK_FAQ_BASE_URL' => Configuration::get('EVERBLOCK_FAQ_BASE_URL') ?: 'faq',
            'EVERBLOCK_FAQ_PER_PAGE' => Configuration::get('EVERBLOCK_FAQ_PER_PAGE') ?: 10,
            'EVERPSCSS' => $custom_css,
            'EVERPSJS' => $custom_js,
            'EVERPSCSS_LINKS' => Configuration::get('EVERPSCSS_LINKS'),
            'EVERPSJS_LINKS' => Configuration::get('EVERPSJS_LINKS'),
            'EVERPS_HEADER_SCRIPTS' => $headerScripts,
            'EVERPS_FEATURES_AS_FLAGS[]' => json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS')),
            'EVERPS_DUMMY_NBR' => Configuration::get('EVERPS_DUMMY_NBR'),
            'EVERPSCSS_P_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_P_LLOREM_NUMBER'),
            'EVERPSCSS_S_LLOREM_NUMBER' => Configuration::get('EVERPSCSS_S_LLOREM_NUMBER'),
            'EVERBLOCK_TINYMCE' => Configuration::get('EVERBLOCK_TINYMCE'),
            'EVERPS_OLD_URL' => '',
            'EVERPS_NEW_URL' => '',
            'EVER_TAB_CONTENT' => $this->getConfigInMultipleLangs('EVER_TAB_CONTENT'),
            'EVER_TAB_TITLE' => $this->getConfigInMultipleLangs('EVER_TAB_TITLE'),
            'EVERPS_TAB_NB' => Configuration::get('EVERPS_TAB_NB'),
            'EVERPS_FLAG_NB' => Configuration::get('EVERPS_FLAG_NB'),
            'TABS_FILE' => '',
        ];
        $stores = Store::getStores((int) $this->context->language->id);
        $holidays = EverblockTools::getFrenchHolidays((int) date('Y'));
        foreach ($stores as $store) {
            foreach ($holidays as $date) {
                $hoursKey = 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date;
                $configData[$hoursKey] = Configuration::get($hoursKey);
            }
        }
        $configData = array_merge($configData, $bannedFeaturesColors);
        return $configData;
    }

    protected function getModuleStatistics(): array
    {
        $idShop = (int) $this->context->shop->id;
        $stats = [
            'blocks_total' => $this->countTableRecords('everblock', 'id_shop = ' . $idShop),
            'blocks_active' => $this->countTableRecords('everblock', 'id_shop = ' . $idShop . ' AND active = 1'),
            'shortcodes' => $this->countTableRecords('everblock_shortcode', 'id_shop = ' . $idShop),
            'faqs' => $this->countTableRecords('everblock_faq', 'id_shop = ' . $idShop),
            'pages' => $this->countTableRecords('everblock_page', 'id_shop = ' . $idShop),
            'tabs' => $this->countTableRecords('everblock_tabs', 'id_shop = ' . $idShop),
            'flags' => $this->countTableRecords('everblock_flags', 'id_shop = ' . $idShop),
            'modals' => $this->countTableRecords('everblock_modal', 'id_shop = ' . $idShop),
            'game_sessions' => $this->countTableRecords('everblock_game_play'),
        ];

        return $stats;
    }

    public function getAdminModuleStatistics(): array
    {
        return $this->getModuleStatistics();
    }

    protected function countTableRecords(string $table, string $whereClause = ''): int
    {
        if (!$this->moduleTableExists($table)) {
            return 0;
        }

        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $sql = sprintf(
            'SELECT COUNT(*) FROM `%s`',
            bqSQL(_DB_PREFIX_ . $table)
        );
        if ($whereClause !== '') {
            $sql .= ' WHERE ' . $whereClause;
        }

        return (int) $db->getValue($sql);
    }

    protected function moduleTableExists(string $table): bool
    {
        $tableName = _DB_PREFIX_ . $table;
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $pattern = str_replace(['_', '%'], ['\\_', '\\%'], pSQL($tableName));
        $sql = sprintf("SHOW TABLES LIKE '%s'", $pattern);
        $result = $db->executeS($sql);

        return !empty($result);
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submit' . $this->name . 'Module')) {
            if (Tools::getValue('EVERPSCSS_P_LLOREM_NUMBER')
                && !Validate::isInt(Tools::getValue('EVERPSCSS_P_LLOREM_NUMBER'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Llorem paragraph number" is not valid'
                );
            }
            if (Tools::getValue('EVERPSCSS_S_LLOREM_NUMBER')
                && !Validate::isInt(Tools::getValue('EVERPSCSS_S_LLOREM_NUMBER'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Llorem sentences per paragraphs number" is not valid'
                );
            }
            if (!Tools::getValue('EVERPS_TAB_NB')
                || !Validate::isInt(Tools::getValue('EVERPS_TAB_NB'))
                || (int) Tools::getValue('EVERPS_TAB_NB') < 1
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Number of tabs" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOCK_TINYMCE')
                && !Validate::isBool(Tools::getValue('EVERBLOCK_TINYMCE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Extends TinyMCE" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOCK_SOLDOUT_FLAG')
                && !Validate::isBool(Tools::getValue('EVERBLOCK_SOLDOUT_FLAG'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show Sold out flag" is not valid'
                );
            }
            if (Tools::getValue('EVERWP_POST_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERWP_POST_NBR'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Number of blog posts" is not valid'
                );
            }
            $blogUrl = Tools::getValue('EVERWP_BLOG_URL');
            if (!empty($blogUrl)
                && !Validate::isUrl($blogUrl)
                && (strpos($blogUrl, '/') !== 0)
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Blog URL" must be a valid URL or start with /'
                );
            }
            if (Tools::getValue('EVERPS_FEATURES_AS_FLAGS')
                && !Validate::isArrayWithIds(Tools::getValue('EVERPS_FEATURES_AS_FLAGS'))
            ) {
                $this->postErrors[] = $this->l('Error: selected features are not valid');
            }
            if (Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_LIMIT')
                && (!Validate::isUnsignedInt(Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_LIMIT'))
                || (int) Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_LIMIT') < 1)
            ) {
                $this->postErrors[] = $this->l('Error: the field "Maximum number of reviews" is not valid');
            }
            $minRatingValue = Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING');
            if ($minRatingValue !== '' && $minRatingValue !== null) {
                if (!is_numeric($minRatingValue)) {
                    $this->postErrors[] = $this->l('Error: the field "Minimum rating to display" must be a number');
                } elseif ((float) $minRatingValue < 0 || (float) $minRatingValue > 5) {
                    $this->postErrors[] = $this->l('Error: the field "Minimum rating to display" must be between 0 and 5');
                }
            }
            $sortValue = Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_SORT');
            if ($sortValue && !in_array($sortValue, ['most_relevant', 'newest'], true)) {
                $this->postErrors[] = $this->l('Error: the field "Reviews sort order" is not valid');
            }
            $boolFields = [
                'EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING',
                'EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR',
                'EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA',
            ];
            foreach ($boolFields as $boolField) {
                $value = Tools::getValue($boolField);
                if ($value !== '' && $value !== null && !Validate::isBool($value)) {
                    $this->postErrors[] = $this->l('Error: one of the Google reviews display options is not valid');
                    break;
                }
            }
            if (Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL')
                && !Validate::isUrl(Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL'))
            ) {
                $this->postErrors[] = $this->l('Error: the field "CTA link override" must be a valid URL');
            }
        }
    }

    protected function postProcess()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $custom_css = _PS_MODULE_DIR_ . $this->name . '/views/css/custom' . $idShop . '.css';
        $custom_js = _PS_MODULE_DIR_ . $this->name . '/views/js/custom' . $idShop . '.js';
        // Compressed
        $compressedCss = _PS_MODULE_DIR_ . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $cssCode = Tools::getValue('EVERPSCSS');
        $jsCode = Tools::getValue('EVERPSJS');
        // Compress CSS code
        $compressedCssCode = $this->compressCSSCode(
            $cssCode
        );
        // Create CSS file if need
        if (!is_file($custom_css)) {
            $handle_css = fopen(
                $custom_css,
                'w+'
            );
            fclose($handle_css);
        }
        if (!is_file($compressedCss)) {
            $handle_css = fopen(
                $compressedCss,
                'w+'
            );
            fclose($handle_css);
        }
        // Create JS file if need
        if (!is_file($custom_js)) {
            $handle_js = fopen(
                $custom_js,
                'w+'
            );
            fclose($handle_js);
        }
        $tabTitle = [];
        $tabContent = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tabTitle[$lang['id_lang']] = (Tools::getValue(
                'EVER_TAB_TITLE_' . $lang['id_lang']
            ))
            ? Tools::getValue(
                'EVER_TAB_TITLE_' . $lang['id_lang']
            ) : '';
            $tabContent[$lang['id_lang']] = (Tools::getValue(
                'EVER_TAB_CONTENT_' . $lang['id_lang']
            ))
            ? Tools::getValue(
                'EVER_TAB_CONTENT_' . $lang['id_lang']
            ) : '';
        }
        Configuration::updateValue(
            'EVER_TAB_CATS',
            json_encode(Tools::getValue('EVER_TAB_CATS')),
            true
        );
        Configuration::updateValue(
            'EVER_TAB_TITLE',
            $tabTitle,
            true
        );
        Configuration::updateValue(
            'EVER_TAB_CONTENT',
            $tabContent,
            true
        );
        Configuration::updateValue(
            'EVERBLOCK_LOAD_FRONT_CSS',
            Tools::getValue('EVERBLOCK_LOAD_FRONT_CSS')
        );
        Configuration::updateValue(
            'EVERBLOCK_USE_OBF',
            Tools::getValue('EVERBLOCK_USE_OBF')
        );
        file_put_contents(
            $custom_css,
            $cssCode
        );
        file_put_contents(
            $custom_js,
            $jsCode
        );
        file_put_contents(
            $compressedCss,
            $compressedCssCode
        );
        Configuration::updateValue(
            'EVEROPTIONS_POSITION',
            Tools::getValue('EVEROPTIONS_POSITION')
        );
        $formTitle = [];
        foreach (Language::getLanguages(false) as $lang) {
            $formTitle[$lang['id_lang']] = (
                Tools::getValue('EVEROPTIONS_TITLE_' . $lang['id_lang'])
            ) ? Tools::getValue(
                'EVEROPTIONS_TITLE_' . $lang['id_lang']
            ) : '';
        }
        $headerScripts = Tools::getValue('EVERPS_HEADER_SCRIPTS');
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/header-scripts-' . $this->context->shop->id . '.js';
        file_put_contents($filePath, $headerScripts);
        $bannedFeatures = Tools::getValue('EVERPS_FEATURES_AS_FLAGS');
        Configuration::updateValue(
            'EVERPS_FEATURES_AS_FLAGS',
            json_encode($bannedFeatures),
            true
        );

        if (!empty($bannedFeatures)) {
            foreach ($bannedFeatures as $bannedFeature) {
                $featureId = (int) $bannedFeature;

                // Couleur de fond
                $bgColorKey = 'EVERPS_FEATURE_COLOR_' . $featureId;
                $bgColorValue = Tools::getValue($bgColorKey);
                Configuration::updateValue($bgColorKey, $bgColorValue);

                // Couleur du texte
                $textColorKey = 'EVERPS_FEATURE_TEXTCOLOR_' . $featureId;
                $textColorValue = Tools::getValue($textColorKey);
                Configuration::updateValue($textColorKey, $textColorValue);
            }
        }
        Configuration::updateValue(
            'EVEROPTIONS_TITLE',
            $formTitle,
            true
        );
        Configuration::updateValue(
            'EVERBLOCK_MAINTENANCE_PSSWD',
            Tools::getValue('EVERBLOCK_MAINTENANCE_PSSWD')
        );
        Configuration::updateValue(
            'EVERINSTA_ACCESS_TOKEN',
            Tools::getValue('EVERINSTA_ACCESS_TOKEN')
        );
        // Auto refresh Instagram token
        if (Tools::getValue('EVERINSTA_ACCESS_TOKEN')) {
            EverblockTools::refreshInstagramToken();
        }
        Configuration::updateValue(
            'EVERINSTA_LINK',
            Tools::getValue('EVERINSTA_LINK')
        );
        Configuration::updateValue(
            'EVERINSTA_SHOW_CAPTION',
            Tools::getValue('EVERINSTA_SHOW_CAPTION')
        );
        Configuration::updateValue(
            'EVERWP_API_URL',
            Tools::getValue('EVERWP_API_URL')
        );
        $blogUrl = trim((string) Tools::getValue('EVERWP_BLOG_URL'));
        if ($blogUrl === '') {
            $blogUrl = '/blog';
        }
        Configuration::updateValue(
            'EVERWP_BLOG_URL',
            $blogUrl
        );
        Configuration::updateValue(
            'EVERWP_POST_NBR',
            Tools::getValue('EVERWP_POST_NBR')
        );
        $pagesBaseUrl = trim((string) Tools::getValue('EVERBLOCK_PAGES_BASE_URL'));
        if ($pagesBaseUrl === '') {
            $pagesBaseUrl = 'guide';
        }
        Configuration::updateValue(
            'EVERBLOCK_PAGES_BASE_URL',
            EverblockTools::linkRewrite($pagesBaseUrl)
        );
        $pagesPerPage = (int) Tools::getValue('EVERBLOCK_PAGES_PER_PAGE');
        if ($pagesPerPage <= 0) {
            $pagesPerPage = 9;
        }
        Configuration::updateValue(
            'EVERBLOCK_PAGES_PER_PAGE',
            $pagesPerPage
        );
        $faqBaseUrl = trim((string) Tools::getValue('EVERBLOCK_FAQ_BASE_URL'));
        if ($faqBaseUrl === '') {
            $faqBaseUrl = 'faq';
        }
        Configuration::updateValue(
            'EVERBLOCK_FAQ_BASE_URL',
            EverblockTools::linkRewrite($faqBaseUrl)
        );
        $faqPerPage = (int) Tools::getValue('EVERBLOCK_FAQ_PER_PAGE');
        if ($faqPerPage <= 0) {
            $faqPerPage = 10;
        }
        Configuration::updateValue(
            'EVERBLOCK_FAQ_PER_PAGE',
            $faqPerPage
        );
        $googleReviewsLimit = (int) Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_LIMIT');
        if ($googleReviewsLimit <= 0) {
            $googleReviewsLimit = 5;
        }
        $googleReviewsMinRating = Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING');
        if ($googleReviewsMinRating === '' || $googleReviewsMinRating === null) {
            $googleReviewsMinRating = 0;
        }
        $googleReviewsMinRating = (float) $googleReviewsMinRating;
        if ($googleReviewsMinRating < 0) {
            $googleReviewsMinRating = 0;
        }
        if ($googleReviewsMinRating > 5) {
            $googleReviewsMinRating = 5;
        }
        $googleReviewsSort = Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_SORT');
        if (!in_array($googleReviewsSort, ['newest', 'most_relevant'], true)) {
            $googleReviewsSort = 'most_relevant';
        }
        $googleReviewsShowRating = Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING');
        $googleReviewsShowRating = in_array((string) $googleReviewsShowRating, ['1', 'true', 'on'], true) ? 1 : 0;
        $googleReviewsShowAvatar = Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR');
        $googleReviewsShowAvatar = in_array((string) $googleReviewsShowAvatar, ['1', 'true', 'on'], true) ? 1 : 0;
        $googleReviewsShowCta = Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA');
        $googleReviewsShowCta = in_array((string) $googleReviewsShowCta, ['1', 'true', 'on'], true) ? 1 : 0;
        $googleReviewsCtaLabel = trim((string) Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL'));
        $googleReviewsCtaUrl = trim((string) Tools::getValue('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL'));
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_API_KEY',
            Tools::getValue('EVERBLOCK_GOOGLE_API_KEY')
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_PLACE_ID',
            Tools::getValue('EVERBLOCK_GOOGLE_PLACE_ID')
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_LIMIT',
            $googleReviewsLimit
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING',
            $googleReviewsMinRating
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_SORT',
            $googleReviewsSort
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING',
            $googleReviewsShowRating
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR',
            $googleReviewsShowAvatar
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA',
            $googleReviewsShowCta
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL',
            $googleReviewsCtaLabel
        );
        Configuration::updateValue(
            'EVERBLOCK_GOOGLE_REVIEWS_CTA_URL',
            $googleReviewsCtaUrl
        );
        EverblockCache::cacheDropByPattern('everblock_google_reviews_');
        Configuration::updateValue(
            'EVERBLOCK_GMAP_KEY',
            Tools::getValue('EVERBLOCK_GMAP_KEY')
        );
        Configuration::updateValue(
            'EVERBLOCK_STORELOCATOR_TOGGLE',
            Tools::getValue('EVERBLOCK_STORELOCATOR_TOGGLE')
        );
        if (isset($_FILES['EVERBLOCK_MARKER_ICON'])
            && isset($_FILES['EVERBLOCK_MARKER_ICON']['tmp_name'])
            && !empty($_FILES['EVERBLOCK_MARKER_ICON']['tmp_name'])
        ) {
            $filename = $_FILES['EVERBLOCK_MARKER_ICON']['name'];
            $extension = Tools::strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($extension !== 'svg') {
                $this->postErrors[] = $this->l('Marker icon must be an SVG file.');
            } elseif (!($tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['EVERBLOCK_MARKER_ICON']['tmp_name'], $tmpName)
            ) {
                $this->postErrors[] = $this->l('Error while uploading marker icon.');
            } else {
                $dest = _PS_MODULE_DIR_ . $this->name . '/views/img/store-locator-marker.svg';
                copy($tmpName, $dest);
                @unlink($tmpName);
                Configuration::updateValue('EVERBLOCK_MARKER_ICON', 'store-locator-marker.svg');
            }
        }
        if (isset($_FILES['EVERWP_POSTS_BG_IMAGE'])
            && isset($_FILES['EVERWP_POSTS_BG_IMAGE']['tmp_name'])
            && !empty($_FILES['EVERWP_POSTS_BG_IMAGE']['tmp_name'])
        ) {
            $filename = $_FILES['EVERWP_POSTS_BG_IMAGE']['name'];
            $extension = Tools::strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($extension, $allowedExtensions, true)) {
                $this->postErrors[] = $this->l('WordPress background image must be a JPG, PNG, WEBP, or GIF file.');
            } elseif (!($tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['EVERWP_POSTS_BG_IMAGE']['tmp_name'], $tmpName)
            ) {
                $this->postErrors[] = $this->l('Error while uploading WordPress background image.');
            } else {
                $previous = Configuration::get('EVERWP_POSTS_BG_IMAGE');
                if ($previous) {
                    $previousPath = _PS_MODULE_DIR_ . $this->name . '/views/img/' . $previous;
                    if (file_exists($previousPath)) {
                        @unlink($previousPath);
                    }
                }
                $safeName = 'wp-posts-bg-' . time() . '.' . $extension;
                $dest = _PS_MODULE_DIR_ . $this->name . '/views/img/' . $safeName;
                copy($tmpName, $dest);
                @unlink($tmpName);
                $webpUrl = EverblockTools::convertToWebP($dest);
                if ($webpUrl) {
                    $webpPath = parse_url($webpUrl, PHP_URL_PATH);
                    $safeName = $webpPath ? basename($webpPath) : basename($webpUrl);
                }
                Configuration::updateValue('EVERWP_POSTS_BG_IMAGE', $safeName);
            }
        }
        $stores = Store::getStores((int) $this->context->language->id);
        $holidays = EverblockTools::getFrenchHolidays((int) date('Y'));
        foreach ($stores as $store) {
            foreach ($holidays as $date) {
                $hoursKey = 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date;
                Configuration::updateValue($hoursKey, Tools::getValue($hoursKey));
            }
        }
        Configuration::updateValue(
            'EVERPSCSS_LINKS',
            Tools::getValue('EVERPSCSS_LINKS')
        );
        Configuration::updateValue(
            'EVERPSJS_LINKS',
            Tools::getValue('EVERPSJS_LINKS')
        );
        Configuration::updateValue(
            'EVERPS_DUMMY_NBR',
            Tools::getValue('EVERPS_DUMMY_NBR')
        );
        Configuration::updateValue(
            'EVERPSCSS_P_LLOREM_NUMBER',
            Tools::getValue('EVERPSCSS_P_LLOREM_NUMBER')
        );
        Configuration::updateValue(
            'EVERPSCSS_S_LLOREM_NUMBER',
            Tools::getValue('EVERPSCSS_S_LLOREM_NUMBER')
        );
        Configuration::updateValue(
            'EVERBLOCK_TINYMCE',
            Tools::getValue('EVERBLOCK_TINYMCE')
        );
        Configuration::updateValue(
            'EVERBLOCK_SOLDOUT_FLAG',
            Tools::getValue('EVERBLOCK_SOLDOUT_FLAG')
        );
        Configuration::updateValue(
            'EVER_SOLDOUT_COLOR',
            Tools::getValue('EVER_SOLDOUT_COLOR')
        );
        Configuration::updateValue(
            'EVER_SOLDOUT_TEXTCOLOR',
            Tools::getValue('EVER_SOLDOUT_TEXTCOLOR')
        );
        Configuration::updateValue(
            'EVERPS_TAB_NB',
            Tools::getValue('EVERPS_TAB_NB')
        );
        Configuration::updateValue(
            'EVERPS_FLAG_NB',
            Tools::getValue('EVERPS_FLAG_NB')
        );
        $cacheId = $this->name . 'NeedProductFlagsHook_' . $idShop;
        EverblockCache::cacheDrop($cacheId);
        $this->updateProductFlagsHook();
        $stores = EverblockTools::getStoreLocatorData();
        $filename = 'store-locator-' . $idShop . '.js';
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
        if (!empty($stores) && Tools::getValue('EVERBLOCK_GMAP_KEY')) {
            $markers = [];
            $context = Context::getContext();
            $markerIcon = Configuration::get('EVERBLOCK_MARKER_ICON');
            foreach ($stores as $store) {
                $storeId = isset($store['id']) ? (int) $store['id'] : (int) $store['id_store'];
                if (!empty($store['is_open'])) {
                    $status = sprintf($this->l('Open today until %s'), $store['open_until']);
                } elseif (!empty($store['opens_at'])) {
                    $status = sprintf($this->l('Open today at %s'), $store['opens_at']);
                } else {
                    $status = $this->l('Closed');
                }
                $marker = [
                    'id' => $storeId,
                    'lat' => $store['latitude'],
                    'lng' => $store['longitude'],
                    'title' => $store['name'],
                    'address1' => $store['address1'],
                    'address2' => $store['address2'],
                    'postcode' => $store['postcode'],
                    'city' => $store['city'],
                    'phone' => $store['phone'],
                    'img' => $context->link->getBaseLink(null, null) . 'img/st/' . $storeId . '.jpg',
                    'status' => $status,
                    'cms_link' => $store['cms_link'],
                    'directions_label' => $this->l('Get directions'),
                    'hours_label' => $this->l('See hours'),
                ];
                if ($markerIcon) {
                    $marker['icon'] = $context->link->getBaseLink(null, null) . 'modules/' . $this->name . '/views/img/' . $markerIcon;
                }
                $markers[] = $marker;
            }
            $gmapScript = EverblockTools::generateGoogleMapScript($markers);
            if ($gmapScript) {
                file_put_contents($filePath, $gmapScript);
            }
        } elseif (file_exists($filePath)) {
            unlink($filePath);
        }
        $this->generateFeatureFlagsCssFile();
        $this->generateSoldOutFlagCssFile();
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    protected function generateFeatureFlagsCssFile()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/css/feature-flags-' . $idShop . '.css';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $bannedFeatures = Configuration::get('EVERPS_FEATURES_AS_FLAGS');
        if (!$bannedFeatures) {
            return;
        }

        $bannedFeatures = json_decode($bannedFeatures, true);
        if (!is_array($bannedFeatures) || empty($bannedFeatures)) {
            return;
        }

        $css = "/* Auto-generated feature flags CSS */\n";

        foreach ($bannedFeatures as $featureId) {
            $featureId = (int) $featureId;
            $bgColor = Configuration::get('EVERPS_FEATURE_COLOR_' . $featureId);
            $textColor = Configuration::get('EVERPS_FEATURE_TEXTCOLOR_' . $featureId);

            // Skip if both values are empty
            if (empty($bgColor) && empty($textColor)) {
                continue;
            }

            // Exemple de classe : .feature-flag-12
            $css .= sprintf(
                ".ever_feature_flag_%d {\n%s%s}\n",
                $featureId,
                $bgColor ? "  background-color: {$bgColor}!important;\n" : '',
                $textColor ? "  color: {$textColor}!important;\n" : ''
            );
        }

        // Écriture dans le fichier si on a généré quelque chose
        if (trim($css) !== '') {
            file_put_contents($filePath, $css);
        }
    }

    protected function generateSoldOutFlagCssFile()
    {
        $idShop = (int) Context::getContext()->shop->id;
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/css/outofstock-flag-' . $idShop . '.css';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $bgColor = Configuration::get('EVER_SOLDOUT_COLOR');
        $textColor = Configuration::get('EVER_SOLDOUT_TEXTCOLOR');

        if (empty($bgColor) && empty($textColor)) {
            return;
        }

        $css = "/* Auto-generated sold out flag CSS */\n";
        $css .= ".product-flags .out_of_stock {\n";
        if ($bgColor) {
            $css .= "  background-color: {$bgColor}!important;\n";
        }
        if ($textColor) {
            $css .= "  color: {$textColor}!important;\n";
        }
        $css .= "}\n";

        file_put_contents($filePath, $css);
    }

    protected function emptyAllCache()
    {
        EverblockCache::clearAllModuleCache();
        $this->postSuccess[] = $this->l('Everblock cache has been cleared');
    }

    public function hookActionAdminControllerSetMedia()
    {
        $controller = Tools::getValue('controller');
        $isModuleConfiguration = Tools::getValue('configure') === $this->name;
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $isSymfonyContentForm = (bool) preg_match('#/(?:modules/)?everblock/(blocks|pages|faqs|shortcodes)/(new|[0-9]+/edit)#', $requestUri);
        $isSymfonyEverblockAdmin = strpos($requestUri, '/modules/everblock/') !== false
            || (bool) preg_match('#/everblock/(blocks|pages|faqs|shortcodes|hooks|configuration|clear-cache)#', $requestUri);
        $moduleControllers = [
            'AdminEverBlock',
            'AdminEverBlockConfiguration',
            'AdminEverBlockFaq',
            'AdminEverBlockHook',
            'AdminEverBlockShortcode',
            'AdminEverBlockShortcodeDocumentation',
            'AdminEverBlockPage',
        ];
        $this->context->controller->addCss($this->_path . 'views/css/ever.css');
        if ($controller === 'AdminProducts') {
            $this->context->controller->addJs($this->_path . 'views/js/product-faq.js');
            $this->context->controller->addJs($this->_path . 'views/js/product-modal.js');
        }

        if (Tools::getValue('id_' . $this->name)
            || Tools::getIsset('add' . $this->name)
            || $isModuleConfiguration
            || $isSymfonyEverblockAdmin
            || in_array($controller, $moduleControllers, true)
            || Tools::getValue('id_' . $this->name . '_faq')
            || Tools::getIsset('add' . $this->name . '_faq')
        ) {
            $this->context->controller->addCSS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/codemirror.min.css',
                'all'
            );
            $this->context->controller->addCSS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/theme/dracula.min.css',
                'all'
            );
            $this->context->controller->addJS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/codemirror.min.js'
            );
            $this->context->controller->addJS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/mode/javascript/javascript.min.js'
            );
            $this->context->controller->addJs($this->_path . 'views/js/admin.js');
            if ((bool) Configuration::get('EVERBLOCK_TINYMCE') === true
                && !$isModuleConfiguration
                && ($isSymfonyContentForm || Tools::getValue('id_' . $this->name) || Tools::getIsset('add' . $this->name))
            ) {
                $this->context->controller->addJs(__PS_BASE_URI__ . 'js/tiny_mce/tinymce.min.js');
                $this->context->controller->addJs(__PS_BASE_URI__ . 'js/admin/tinymce.inc.js');
                $this->context->controller->addJs($this->_path . 'views/js/adminTinyMce.js');
            }
        }
    }


    public function hookActionCmsPageFormBuilderModifier($params)
    {
        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];
        $idCms = (int) $params['id'];

        $stores = Store::getStores((int) Context::getContext()->language->id);
        $choices = [];
        $selectedStoreId = null;

        foreach ($stores as $store) {
            $choices[$store['name']] = (int) $store['id_store'];

            // Vérifie si ce store est lié à cette page CMS
            $cmsLinked = (int) Configuration::get('QCD_ASSOCIATED_CMS_PAGE_ID_STORE_' . $store['id_store']);
            if ($cmsLinked === $idCms) {
                $selectedStoreId = (int) $store['id_store'];
            }
        }
        $formBuilder->add('qcd_associated_store', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
            'label' => $this->l('Associate with store'),
            'required' => false,
            'choices' => $choices,
            'placeholder' => $this->l('Select a store'),
            'data' => $selectedStoreId,
            'attr' => [
                'class' => 'form-select',
            ],
        ]);
    }

    public function hookActionObjectCmsUpdateAfter($params)
    {
        /** @var CMS $cms */
        $cms = $params['object'];
        $cmsPage = Tools::getValue('cms_page');

        if (!empty($cmsPage['qcd_associated_store'])) {
            $id_store = (int) $cmsPage['qcd_associated_store'];
            Configuration::updateValue(
                'QCD_ASSOCIATED_CMS_PAGE_ID_STORE_' . $id_store,
                (int) $cms->id,
                false, // id_lang
                $this->context->shop->id // id_shop
            );
        }
    }

    public function hookActionOutputHTMLBefore($params)
    {
        if (Tools::getValue('evermaintenancepassword')
            && Tools::getValue('evermaintenancepassword') == Configuration::get('EVERBLOCK_MAINTENANCE_PSSWD')
        ) {
            $userIp = Tools::getRemoteAddr();  // Obtenir l'IP de l'utilisateur

            // Récupérer la liste actuelle des IPs autorisées depuis la configuration
            $ips = Configuration::get('PS_MAINTENANCE_IP');

            if ($ips) {
                $ipsArray = explode(',', $ips);  // Transformer la chaîne en tableau
            } else {
                $ipsArray = [];
            }

            // Vérifier si l'IP de l'utilisateur n'est pas déjà dans la liste
            if (!in_array($userIp, $ipsArray)) {
                $ipsArray[] = $userIp;  // Ajouter l'IP de l'utilisateur au tableau
                $newIps = implode(',', $ipsArray);  // Transformer le tableau en chaîne
                Configuration::updateValue('PS_MAINTENANCE_IP', $newIps);  // Mettre à jour la configuration avec la nouvelle liste d'IPs
            }
            Tools::redirect(
                Tools::getHttpHost(true) . __PS_BASE_URI__
            );
        }
        $txt = $params['html'];
        if (!EverblockTools::hasShortcodeToken($txt)) {
            return $txt;
        }
        try {
            $context = Context::getContext();
            // @Todo : move to EverblockShortcodes
            $txt = EverblockTools::renderShortcodes($txt, $context, $this);
            $params['html'] = $txt;
            return $params['html'];
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'Ever Block hookActionOutputHTMLBefore : ' . $e->getMessage()
            );
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
            return $params['html'];
        }
    }

    public function hookDisplayMaintenance($params)
    {
        if (Configuration::get('EVERBLOCK_MAINTENANCE_PSSWD')) {
            return $this->display(__FILE__, 'views/templates/hook/maintenance.tpl');
        }
    }

    public function hookDisplayWrapperTop()
    {
        return;
    }

    public function hookDisplayWrapperBottom()
    {
        return;
    }

    public function hookActionCheckoutRender($params)
    {
        $stepTitle = $this->getConfigInMultipleLangs('EVEROPTIONS_TITLE');
        if (!$stepTitle[$this->context->language->id]
            || empty($stepTitle[$this->context->language->id])
        ) {
            return;
        }
        $translator = Context::getContext()->getTranslator();

        /** @var CheckoutProcess $process */
        $process = $params['checkoutProcess'];
        $steps = $process->getSteps();

        $everStep = new EverblockCheckoutStep(
            $this->context,
            $translator,
            $this
        );
        $everStep->setCheckoutProcess($process);
        switch ((int) Configuration::get('EVEROPTIONS_POSITION')) {
            case 1:
                $newSteps = [
                    $steps[0],
                    $everStep,
                    $steps[1],
                    $steps[2],
                    $steps[3]
                ];
                break;

            case 2:
                $newSteps = [
                    $steps[0],
                    $steps[1],
                    $everStep,
                    $steps[2],
                    $steps[3]
                ];
                break;

            case 3:
                $newSteps = [
                    $steps[0],
                    $steps[1],
                    $steps[2],
                    $everStep,
                    $steps[3]
                ];
                break;

            default:
                $newSteps = [
                    $steps[0],
                    $everStep,
                    $steps[1],
                    $steps[2],
                    $steps[3]
                ];
                break;
        }
        $process->setSteps($newSteps);
    }

    public function hookDisplayOrderDetail($params)
    {
        return $this->hookDisplayOrderConfirmation($params);
    }

    public function hookDisplayOrderConfirmation($params)
    {
        try {
            $order = $params['order'];
            $checkoutSessionData = $this->getCartSessionDatas(
                $order->id_cart
            );
            if (isset($checkoutSessionData) && $checkoutSessionData) {
                $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                if (!$checkoutSessionData) {
                    return;
                }
                $hiddenKeys = [
                    'hidden',
                    'everHide',
                    'submitCustomStep',
                    'controller',
                ];
                if (is_array($checkoutSessionData)) {
                    foreach ($checkoutSessionData as $key => $value) {
                        if (in_array($key, $hiddenKeys)) {
                            unset($checkoutSessionData[$key]);
                        }
                        if (empty($value)) {
                            unset($checkoutSessionData[$key]);
                        }
                    }
                    $this->context->smarty->assign(array(
                        'checkoutSessionData' => $checkoutSessionData,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        try {
            $order = new Order((int) $params['id_order']);
            $checkoutSessionData = $this->getCartSessionDatas(
                $order->id_cart
            );
            if (isset($checkoutSessionData) && $checkoutSessionData) {
                $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                if (is_array($checkoutSessionData) && !empty($checkoutSessionData)) {
                    $this->context->smarty->assign(array(
                        'checkoutSessionData' => $checkoutSessionData,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookDisplayPDFDeliverySlip($params)
    {
        return $this->hookDisplayPDFInvoice($params);
    }

    public function hookDisplayPDFInvoice($params)
    {
        try {
            $order = new Order((int) $params['object']->id_order);
            $checkoutSessionData = $this->getCartSessionDatas(
                $order->id_cart
            );
            if (isset($checkoutSessionData) && $checkoutSessionData) {
                $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                $hiddenKeys = [
                    'hidden',
                    'everHide',
                    'submitCustomStep',
                    'controller',
                ];
                if (is_array($checkoutSessionData) && !empty($checkoutSessionData)) {
                    foreach ($checkoutSessionData as $key => $value) {
                        if (in_array($key, $hiddenKeys)) {
                            unset($checkoutSessionData[$key]);
                        }
                        if (empty($value)) {
                            unset($checkoutSessionData[$key]);
                        }
                    }
                    $this->context->smarty->assign(array(
                        'checkoutSessionData' => $checkoutSessionData,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/pdf.tpl');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookActionEmailAddAfterContent($params)
    {
        try {
            $context = Context::getContext();
            $languageId = $params['id_lang'];
            $lang = new Language(
                (int) $languageId
            );
            $context->language = $lang;
            $params['template_txt'] = EverblockTools::renderShortcodes($params['template_txt'], $context, $this);
            $params['template_html'] = EverblockTools::renderShortcodes($params['template_html'], $context, $this);
            return $params;
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookActionEmailSendBefore($params)
    {
        if (isset($params['templateVars']['{id_order}'])) {
            try {
                $id_order = (int) $params['templateVars'] ["{id_order}"];
                $order = new Order(
                    (int) $id_order
                );
                $checkoutSessionData = $this->getCartSessionDatas(
                    $order->id_cart
                );
                if (isset($checkoutSessionData) && $checkoutSessionData) {
                    $checkoutSessionData = json_decode(json_encode($checkoutSessionData), true);
                    $hiddenKeys = [
                        'hidden',
                        'everHide',
                        'submitCustomStep',
                        'controller',
                    ];
                    if (is_array($checkoutSessionData) && !empty($checkoutSessionData)) {
                        foreach ($checkoutSessionData as $key => $value) {
                            if (in_array($key, $hiddenKeys)) {
                                unset($checkoutSessionData[$key]);
                            }
                            if (empty($value)) {
                                unset($checkoutSessionData[$key]);
                            }
                        }
                        $this->context->smarty->assign(array(
                            'checkoutSessionData' => $checkoutSessionData,
                        ));
                        $optionsHtml = $this->context->smarty->fetch(
                            $this->local_path . 'views/templates/hook/pdf.tpl'
                        );
                        $params['templateVars']['{order_options}'] = $optionsHtml;
                    }
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
                EverblockTools::setLog(
                    $this->name . date('y-m-d'),
                    $e->getMessage()
                );
            }
        }
        return $params;
    }

    public static function getCartSessionDatas($idCart)
    {
        $sql = new DbQuery();
        $sql->select('checkout_session_data');
        $sql->from(
            'cart'
        );
        $sql->where(
            'id_cart = ' . (int) $idCart
        );
        $res =  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if (!$res) {
            return;
        }
        $checkout_session_data = json_decode(
            $res
        );
        foreach ($checkout_session_data as $key => $value) {
            if ($key == 'ever-checkout-step') {
                return $value->everdata;
            }
        }
        return false;
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (!$params['id_product']) {
            return;
        }

        $productId = (int) $params['id_product'];
        $shopId = (int) $this->context->shop->id;
        $tabsNumber = max((int) Configuration::get('EVERPS_TAB_NB'), 1);
        $flagsNumber = max((int) Configuration::get('EVERPS_FLAG_NB'), 1);

        $everpstabs = EverblockTabsClass::getByIdProductInAdmin($productId, $shopId);
        $everpsflags = EverblockFlagsClass::getByIdProductInAdmin($productId, $shopId);

        $tabsData = [];
        $flagsData = [];
        for ($i = 1; $i <= $tabsNumber; $i++) {
            foreach ($everpstabs as $everpstab) {
                if (Validate::isLoadedObject($everpstab)
                    && $everpstab->id_tab == $i
                ) {
                    $tabsData[$i] = $everpstab;
                    break;
                }
            }

            if (!array_key_exists($i, $tabsData)) {
                $tabsData[$i] = null;
            }
        }

        for ($i = 1; $i <= $flagsNumber; $i++) {
            foreach ($everpsflags as $everpsflag) {
                if (Validate::isLoadedObject($everpsflag)
                    && $everpsflag->id_flag == $i
                ) {
                    $flagsData[$i] = $everpsflag;
                    break;
                }
            }

            if (!array_key_exists($i, $flagsData)) {
                $flagsData[$i] = null;
            }
        }

        $everAjaxUrl = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'token' => Tools::getAdminTokenLite('AdminModules')]);

        $faqTotalCount = EverblockFaq::countAll($shopId);
        $selectedFaqIds = EverblockFaq::getFaqIdsByProduct($productId, $shopId);
        $langId = (int) $this->context->employee->id_lang;
        if ($langId <= 0) {
            $langId = (int) Configuration::get('PS_LANG_DEFAULT');
        }
        $selectedFaqOptions = EverblockFaq::getFaqOptionsByIds($selectedFaqIds, $shopId, $langId);

        $assigns = [
            'tabsData' => $tabsData,
            'flagsData' => $flagsData,
            'default_language' => $this->context->employee->id_lang,
            'ever_languages' => Language::getLanguages(false),
            'ever_ajax_url' => $everAjaxUrl,
            'ever_product_id' => $productId,
            'tabsRange' => range(1, $tabsNumber),
            'flagsRange' => range(1, $flagsNumber),
        ];

        if ($faqTotalCount > 0) {
            $assigns['everblock_faq_selector'] = [
                'selected_options' => $selectedFaqOptions,
                'ajax_url' => $everAjaxUrl . '&ajax=1&action=EverblockSearchFaq',
                'placeholder' => $this->l('Search and attach FAQs...'),
            ];
        }

        return $this->renderAdminTwig('product_tab.html.twig', $assigns);
    }

    public function hookDisplayAdminProductsMainStepLeftColumnBottom($params)
    {
        if (empty($params['id_product'])) {
            return;
        }
        $modal = EverblockModal::getByProductId(
            (int) $params['id_product'],
            (int) $this->context->shop->id
        );
        $fileUrl = '';
        $fileName = '';
        $filePreviewUrl = '';
        $fileTimestamp = null;
        $fileIsImage = false;
        $buttonFileUrl = '';
        $buttonFileName = '';
        $buttonFilePreviewUrl = '';
        $buttonFileTimestamp = null;
        $buttonFileIsImage = false;
        if (!empty($modal->file)) {
            $fileUrl = $this->context->link->getBaseLink() . 'img/cms/' . $modal->file;
            $fileName = basename($modal->file);
            $absolutePath = _PS_IMG_DIR_ . 'cms/' . $modal->file;
            if (file_exists($absolutePath)) {
                $fileTimestamp = (int) @filemtime($absolutePath);
                if (!$fileTimestamp) {
                    $fileTimestamp = time();
                }
                $fileIsImage = $this->isPreviewableModalFile($absolutePath);
                if ($fileIsImage) {
                    $filePreviewUrl = $this->buildTimestampedUrl($fileUrl, $fileTimestamp);
                }
            }
        }
        if (!empty($modal->button_file)) {
            $buttonFileUrl = $this->context->link->getBaseLink() . 'img/cms/' . $modal->button_file;
            $buttonFileName = basename($modal->button_file);
            $absolutePath = _PS_IMG_DIR_ . 'cms/' . $modal->button_file;
            if (file_exists($absolutePath)) {
                $buttonFileTimestamp = (int) @filemtime($absolutePath);
                if (!$buttonFileTimestamp) {
                    $buttonFileTimestamp = time();
                }
                $buttonFileIsImage = $this->isPreviewableModalFile($absolutePath);
                if ($buttonFileIsImage) {
                    $buttonFilePreviewUrl = $this->buildTimestampedUrl($buttonFileUrl, $buttonFileTimestamp);
                }
            }
        }
        $everAjaxUrl = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]);

        return $this->renderAdminTwig('product_modal.html.twig', [
            'modal' => $modal,
            'modal_file_url' => $fileUrl,
            'modal_file_name' => $fileName,
            'modal_file_preview_url' => $filePreviewUrl,
            'modal_file_timestamp' => $fileTimestamp,
            'modal_file_is_image' => $fileIsImage,
            'modal_button_file_url' => $buttonFileUrl,
            'modal_button_file_name' => $buttonFileName,
            'modal_button_file_preview_url' => $buttonFilePreviewUrl,
            'modal_button_file_timestamp' => $buttonFileTimestamp,
            'modal_button_file_is_image' => $buttonFileIsImage,
            'ever_languages' => Language::getLanguages(false),
            'ever_ajax_url' => $everAjaxUrl,
            'ever_product_id' => (int) $params['id_product'],
        ]);
    }

    public function hookActionObjectEverBlockClassDeleteAfter($params)
    {
        $this->clearBlockObjectCacheFromHook($params);
    }

    public function hookActionObjectEverBlockClassUpdateAfter($params)
    {
        $this->clearBlockObjectCacheFromHook($params);
    }

    private function clearBlockObjectCacheFromHook(array $params): void
    {
        $object = $params['object'] ?? null;
        $blockId = is_object($object) && isset($object->id) ? (int) $object->id : null;
        $shopId = is_object($object) && isset($object->id_shop) ? (int) $object->id_shop : (int) $this->context->shop->id;
        $hookId = is_object($object) && isset($object->id_hook) ? (int) $object->id_hook : 0;

        EverBlockClass::clearCache($blockId, $shopId, Language::getLanguages(false), $hookId > 0 ? [$hookId] : []);
    }

    public function hookActionObjectEverBlockFlagsDeleteAfter($params)
    {
        $cachePattern = $this->name . 'EverblockFlagsClass_getByIdProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockFlags_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cacheId = $this->name . 'NeedProductFlagsHook_' . (int) $this->context->shop->id;
        EverblockCache::cacheDrop($cacheId);
        $this->updateProductFlagsHook();
    }

    public function hookActionObjectEverBlockFlagsUpdateAfter($params)
    {
        $cachePattern = $this->name . 'EverblockFlagsClass_getByIdProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cacheId = $this->name . 'NeedProductFlagsHook_' . (int) $this->context->shop->id;
        EverblockCache::cacheDrop($cacheId);
        $this->updateProductFlagsHook();
    }

    public function hookActionObjectEverblockFaqDeleteAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getAllFaq_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getFaqByTagName_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getFaqIdsByProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getProductsByFaq_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getByIds_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'fetchInstagramImages';
        EverblockCache::cacheDropByPattern($cachePattern);
        $productIds = [];
        $objectShopId = (int) $this->context->shop->id;
        if (isset($params['object']) && is_object($params['object']) && !empty($params['object']->id)) {
            $objectShopId = property_exists($params['object'], 'id_shop') ? (int) $params['object']->id_shop : (int) $this->context->shop->id;
            EverblockFaq::invalidateRelationsForFaq((int) $params['object']->id, $objectShopId);
            $products = EverblockFaq::getProductsByFaq((int) $params['object']->id, $objectShopId);
            foreach ($products as $product) {
                $productIds[] = (int) $product['id_product'];
            }
        }
        if (!empty($productIds)) {
            $this->clearProductFaqCache($productIds, $objectShopId);
        }
    }

    public function hookActionObjectEverblockFaqUpdateAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getAllFaq_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getFaqByTagName_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getFaqIdsByProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getProductsByFaq_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverblockFaq_getByIds_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $productIds = [];
        $objectShopId = (int) $this->context->shop->id;
        if (isset($params['object']) && is_object($params['object']) && !empty($params['object']->id)) {
            $objectShopId = property_exists($params['object'], 'id_shop') ? (int) $params['object']->id_shop : (int) $this->context->shop->id;
            EverblockFaq::invalidateRelationsForFaq((int) $params['object']->id, $objectShopId);
            $products = EverblockFaq::getProductsByFaq((int) $params['object']->id, $objectShopId);
            foreach ($products as $product) {
                $productIds[] = (int) $product['id_product'];
            }
        }
        if (!empty($productIds)) {
            $this->clearProductFaqCache($productIds, $objectShopId);
        }
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = ['admin', 'moduleadmin'];
        $context = Context::getContext();
        if (!in_array($context->controller->controller_type, $controllerTypes)) {
            return;
        }
        try {
            // Traitement des tabs
            $tabsNumber = (int) Configuration::get('EVERPS_TAB_NB');
            if ($tabsNumber < 1) {
                $tabsNumber = 1;
                Configuration::updateValue('EVERPS_TAB_NB', 1);
            }
            $tabsRange = range(1, $tabsNumber);
            foreach ($tabsRange as $tab) {
                $everpstabs = EverblockTabsClass::getByIdProductIdTab(
                    (int) $params['object']->id,
                    (int) $context->shop->id,
                    (int) $tab
                );
                foreach (Language::getLanguages(true) as $language) {
                    $tabTitle = Tools::getValue((int) $tab . '_everblock_title_' . $language['id_lang']);
                    if ($tabTitle && !Validate::isCleanHtml($tabTitle)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Title is not valid'),
                            ]
                        ));
                    } else {
                        $everpstabs->title[$language['id_lang']] = $tabTitle;
                    }

                    $tabContent = Tools::getValue((int) $tab . '_everblock_content_' . $language['id_lang']);
                    if ($tabContent && !Validate::isCleanHtml($tabContent)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Content is not valid'),
                            ]
                        ));
                    } else {
                        $everpstabs->content[$language['id_lang']] = $tabContent;
                    }
                }
                $everpstabs->id_tab = (int) $tab;
                $everpstabs->id_product = (int) $params['object']->id;
                $everpstabs->id_shop = (int) $context->shop->id;
                $everpstabs->save();
            }

            // Traitement des flags
            $flagsNumber = (int) Configuration::get('EVERPS_FLAG_NB');
            if ($flagsNumber < 1) {
                $flagsNumber = 1;
                Configuration::updateValue('EVERPS_FLAG_NB', 1);
            }
            $flagsRange = range(1, $flagsNumber);
            foreach ($flagsRange as $flag) {
                $everpsflags = EverblockFlagsClass::getByIdProductIdFlag(
                    (int) $params['object']->id,
                    (int) $context->shop->id,
                    (int) $flag
                );
                foreach (Language::getLanguages(true) as $language) {
                    $flagTitle = Tools::getValue((int) $flag . '_everflag_title_' . $language['id_lang']);
                    if ($flagTitle && !Validate::isCleanHtml($flagTitle)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Title is not valid'),
                            ]
                        ));
                    } else {
                        $everpsflags->title[$language['id_lang']] = $flagTitle;
                    }

                    $flagContent = Tools::getValue((int) $flag . '_everflag_content_' . $language['id_lang']);
                    if ($flagContent && !Validate::isCleanHtml($flagContent)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Content is not valid'),
                            ]
                        ));
                    } else {
                        $everpsflags->content[$language['id_lang']] = $flagContent;
                    }
                }
                $everpsflags->id_flag = (int) $flag;
                $everpsflags->id_product = (int) $params['object']->id;
                $everpsflags->id_shop = (int) $context->shop->id;
                $everpsflags->save();
            }

            // Traitement des FAQs liées
            $faqIds = Tools::getValue('everblock_faq_ids');
            if (!is_array($faqIds)) {
                $faqIds = [];
            }
            $faqIds = array_values(array_unique(array_filter(array_map('intval', $faqIds))));
            EverblockFaq::unlinkProductFaqs((int) $params['object']->id, (int) $context->shop->id);
            foreach ($faqIds as $position => $faqId) {
                EverblockFaq::linkToProduct((int) $faqId, (int) $params['object']->id, (int) $context->shop->id, (int) $position);
            }
            $this->clearProductFaqCache([(int) $params['object']->id], (int) $context->shop->id);

            // Modal management
            $modal = EverblockModal::getByProductId(
                (int) $params['object']->id,
                (int) $context->shop->id
            );
            if (!is_array($modal->content)) {
                $modal->content = [];
            }
            if (!is_array($modal->button_label)) {
                $modal->button_label = [];
            }
            foreach (Language::getLanguages(true) as $language) {
                $content = Tools::getValue('everblock_modal_content_' . $language['id_lang']);
                if ($content && !Validate::isCleanHtml($content)) {
                    die(json_encode([
                        'return' => false,
                        'error' => $this->l('Content is not valid'),
                    ]));
                }
                $modal->content[$language['id_lang']] = $content;

                $buttonLabel = Tools::getValue('everblock_modal_button_label_' . $language['id_lang']);
                if ($buttonLabel && !Validate::isCleanHtml($buttonLabel)) {
                    die(json_encode([
                        'return' => false,
                        'error' => $this->l('Button label is not valid'),
                    ]));
                }
                $modal->button_label[$language['id_lang']] = $buttonLabel;
            }
            if (Tools::getValue('everblock_modal_file_delete')) {
                if (!empty($modal->file)) {
                    $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->file;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                    $modal->file = '';
                }
            }
            if (Tools::getValue('everblock_modal_button_file_delete')) {
                if (!empty($modal->button_file)) {
                    $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->button_file;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                        $this->cleanupModalDirectory(dirname($oldFile));
                    }
                    $modal->button_file = '';
                }
            }
            $modalFilePayload = Tools::getValue('everblock_modal_file_payload');
            $modalFileOriginalName = Tools::getValue('everblock_modal_file_name');
            if (!empty($modalFilePayload) && !empty($modalFileOriginalName)) {
                $decodedPayload = base64_decode(str_replace([' ', "\r", "\n"], '', $modalFilePayload), true);
                if ($decodedPayload !== false) {
                    $targetDir = $this->ensureModalDirectory((int) $params['object']->id);
                    if (!empty($modal->file)) {
                        $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->file;
                        if (file_exists($oldFile)) {
                            @unlink($oldFile);
                            $this->cleanupModalDirectory(dirname($oldFile));
                        }
                    }
                    $fileName = $this->sanitizeModalFileName($modalFileOriginalName);
                    if (file_put_contents($targetDir . $fileName, $decodedPayload) !== false) {
                        $modal->file = 'everblockmodal/' . (int) $params['object']->id . '/' . $fileName;
                    }
                }
            } elseif (isset($_FILES['everblock_modal_file']) && is_uploaded_file($_FILES['everblock_modal_file']['tmp_name'])) {
                $targetDir = $this->ensureModalDirectory((int) $params['object']->id);
                if (!empty($modal->file)) {
                    $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->file;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                        $this->cleanupModalDirectory(dirname($oldFile));
                    }
                }
                $fileName = $this->sanitizeModalFileName($_FILES['everblock_modal_file']['name']);
                if (move_uploaded_file($_FILES['everblock_modal_file']['tmp_name'], $targetDir . $fileName)) {
                    $modal->file = 'everblockmodal/' . (int) $params['object']->id . '/' . $fileName;
                }
            }
            $buttonFilePayload = Tools::getValue('everblock_modal_button_file_payload');
            $buttonFileOriginalName = Tools::getValue('everblock_modal_button_file_name');
            if (!empty($buttonFilePayload) && !empty($buttonFileOriginalName)) {
                $decodedPayload = base64_decode(str_replace([' ', "\r", "\n"], '', $buttonFilePayload), true);
                if ($decodedPayload !== false) {
                    $targetDir = $this->ensureModalDirectory((int) $params['object']->id);
                    if (!empty($modal->button_file)) {
                        $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->button_file;
                        if (file_exists($oldFile)) {
                            @unlink($oldFile);
                            $this->cleanupModalDirectory(dirname($oldFile));
                        }
                    }
                    $fileName = $this->sanitizeModalFileName($buttonFileOriginalName);
                    if (file_put_contents($targetDir . $fileName, $decodedPayload) !== false) {
                        $modal->button_file = 'everblockmodal/' . (int) $params['object']->id . '/' . $fileName;
                    }
                }
            } elseif (isset($_FILES['everblock_modal_button_file']) && is_uploaded_file($_FILES['everblock_modal_button_file']['tmp_name'])) {
                $targetDir = $this->ensureModalDirectory((int) $params['object']->id);
                if (!empty($modal->button_file)) {
                    $oldFile = _PS_IMG_DIR_ . 'cms/' . $modal->button_file;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                        $this->cleanupModalDirectory(dirname($oldFile));
                    }
                }
                $fileName = $this->sanitizeModalFileName($_FILES['everblock_modal_button_file']['name']);
                if (move_uploaded_file($_FILES['everblock_modal_button_file']['tmp_name'], $targetDir . $fileName)) {
                    $modal->button_file = 'everblockmodal/' . (int) $params['object']->id . '/' . $fileName;
                }
            }
            $modal->id_product = (int) $params['object']->id;
            $modal->id_shop = (int) $context->shop->id;
            $modal->save();
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookActionObjectProductAddAfter($params)
    {
        $this->hookActionObjectProductUpdateAfter($params);
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $everpstabs = EverblockTabsClass::getByIdProductInAdmin(
            (int) $params['object']->id,
            (int) $this->context->shop->id
        );
        foreach ($everpstabs as $everpstab) {
            if (Validate::isLoadedObject($everpstab)) {
                $everpstab->delete();
            }
        }
        $everpsflags = EverblockFlagsClass::getByIdProductInAdmin(
            (int) $params['object']->id,
            (int) $this->context->shop->id
        );
        foreach ($everpsflags as $everpsflag) {
            if (Validate::isLoadedObject($everpsflag)) {
                $everpsflag->delete();
            }
        }

        EverblockFaq::unlinkProductFaqs((int) $params['object']->id, (int) $this->context->shop->id);
        $this->clearProductFaqCache([(int) $params['object']->id], (int) $this->context->shop->id);
    }

    public function hookActionProductFlagsModifier($params)
    {
        try {
            $productId = (int) $params['product']['id_product'];
            $shopId = (int) Context::getContext()->shop->id;
            $languageId = (int) Context::getContext()->language->id;
            // Current product flags
            $everpsflags = EverblockFlagsClass::getByIdProduct($productId, $shopId, $languageId);
            if ($everpsflags) {
                foreach ($everpsflags as $everpsflag) {
                    if (Validate::isLoadedObject($everpsflag) && $everpsflag->title && $everpsflag->content) {
                        $params['flags']['custom-flag-' . $everpsflag->id_flag] = [
                            'type' => 'custom-flag ' . $everpsflag->id_flag,
                            'label' => strip_tags($everpsflag->content),
                            'module' => $this->name,
                        ];
                    }
                }
            }
            // Product features as flags
            $bannedFeatures = $this->getFeaturesAsFlags();
            $features = $this->getFeatures($productId);
            if (!empty($features) && !empty($bannedFeatures)) {
                foreach ($features as $feature) {
                    if (in_array($feature['id_feature'], $bannedFeatures)) {
                        $params['flags'][] = array(
                            'type' => 'ever_feature_flag_' . $feature['id_feature'],
                            'label' => $feature['value'],
                            'module' => $this->name,
                            'style' => 'style="background-color:' . Configuration::get('EVERPS_FEATURE_COLOR_' . $feature['id_feature']) . ';color:#fff;"'
                        );
                    }
                }
            }
            if (Configuration::get('EVERBLOCK_SOLDOUT_FLAG')) {
                $qty = StockAvailable::getQuantityAvailableByProduct($productId, 0, $shopId);
                $allowOos = StockAvailable::outOfStock($productId, $shopId);
                if ($allowOos == 2) {
                    $allowOos = (int) Configuration::get('PS_ORDER_OUT_OF_STOCK');
                }
                if ($qty <= 0 && !$allowOos) {
                    $params['flags']['everblock_soldout'] = [
                        'type' => 'out_of_stock',
                        'label' => $this->l('Sold out'),
                        'module' => $this->name,
                    ];
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error on hookActionProductFlagsModifier : ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
            return false;
        }
    }

    protected function getFeatures($productId)
    {
        $cacheId = $this->name . '_getFeatures_' . (int) $productId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = 'SELECT fp.id_feature, fv.value
                    FROM ' . _DB_PREFIX_ . 'feature_product fp
                    JOIN ' . _DB_PREFIX_ . 'feature_value_lang fv ON fp.id_feature_value = fv.id_feature_value
                    WHERE fp.id_product = ' . (int) $productId . '
                    AND fv.id_lang = ' . (int) $this->context->language->id;
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            EverblockCache::cacheStore($cacheId, $result);
            return $result;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    protected function getFeaturesAsFlags()
    {
        $cacheId = $this->name . '_getFeaturesAsFlags_' . (int) $this->context->shop->id;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $bannedFeatures = Configuration::get('EVERPS_FEATURES_AS_FLAGS');
            if (!$bannedFeatures) {
                $bannedFeatures = [];
            } else {
                $bannedFeatures = json_decode($bannedFeatures);
            }
            EverblockCache::cacheStore($cacheId, $bannedFeatures);
            return $bannedFeatures;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    protected function getProductFaqTemplatePath(): string
    {
        return 'module:' . $this->name . '/views/templates/hook/faq.tpl';
    }

    protected function clearProductFaqCache(array $productIds = [], ?int $shopId = null): void
    {
        if (empty($productIds)) {
            EverblockCache::cacheDropByPattern('EverblockFaq_');
            return;
        }

        if ($shopId === null) {
            $shopId = (int) Context::getContext()->shop->id;
        }

        foreach ($productIds as $productId) {
            $productId = (int) $productId;
            if ($productId <= 0) {
                continue;
            }

            EverblockCache::cacheDrop('EverblockFaq_getFaqIdsByProduct_' . (int) $shopId . '_' . $productId);
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        $context = Context::getContext();
        $tab = [];
        $product = new Product(
            (int) $params['product']->id,
            false,
            (int) $context->language->id,
            (int) $context->shop->id
        );
        // Specific product tab
        $everpstabs = EverblockTabsClass::getByIdProduct(
            (int) $product->id,
            (int) $context->shop->id,
            (int) $context->language->id
        );
        foreach ($everpstabs as $everpstab) {
            if (Validate::isLoadedObject($everpstab)) {
                $title = $everpstab->title;
                $content = $this->renderQcdBuilderTargetField(
                    'everblock_product_tab',
                    (int) $product->id,
                    'tab_' . (int) $everpstab->id_tab . '_content',
                    (string) $everpstab->content,
                    (int) $context->shop->id,
                    (int) $context->language->id
                );
                if (!empty($title) || !empty($content)) {
                    $tab[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                        ->setTitle($title)
                        ->setContent($content);
                }
            }
        }
        // Global tab
        $titleLangs = $this->getConfigInMultipleLangs('EVER_TAB_TITLE');
        $title = $titleLangs[
            (int) $context->language->id
        ];
        $contentLangs = $this->getConfigInMultipleLangs('EVER_TAB_CONTENT');
        $content = $contentLangs[
            (int) $context->language->id
        ];
        $content = $this->renderQcdBuilderTargetField(
            'everblock_global_tab',
            (int) $context->shop->id,
            'content',
            (string) $content,
            (int) $context->shop->id,
            (int) $context->language->id
        );
        if (!empty($title) && !empty($content)) {
            $tab[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                ->setTitle($title)
                ->setContent($content);
        }

        $faqIds = EverblockFaq::getFaqIdsByProduct((int) $product->id, (int) $context->shop->id);
        if (!empty($faqIds)) {
            $everFaqs = EverblockFaq::getByIds(
                $faqIds,
                (int) $context->language->id,
                (int) $context->shop->id
            );
            if (!empty($everFaqs)) {
                foreach ($everFaqs as $everFaq) {
                    if (is_object($everFaq) && !empty($everFaq->id)) {
                        $everFaq->content = $this->renderQcdBuilderTargetField(
                            'everblock_faq',
                            (int) $everFaq->id,
                            'content',
                            (string) ($everFaq->content ?? ''),
                            (int) $context->shop->id,
                            (int) $context->language->id
                        );
                    }
                }
                $template = $this->getProductFaqTemplatePath();
                $this->context->smarty->assign([
                    'everFaqs' => $everFaqs,
                ]);
                $faqContent = $this->fetch($template);
                $tab[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                    ->setTitle($this->l('FAQ'))
                    ->setContent($faqContent);
            }
        }
        if (count($tab) > 0) {
            return $tab;
        }
        return false;
    }

    public function hookDisplayAdminCustomers($params)
    {
        if (isset($params['id_customer']) && $params['id_customer']) {
            $customerId = (int) $params['id_customer'];
        } else {
            $order = new Order((int) $params['id_order']);
            $customerId = (int) $order->id_customer;
        }
        $customer = new Customer(
            $customerId
        );
        if (!Validate::isLoadedObject($customer)) {
            return '';
        }

        $link = new Link();
        $everToken = $this->encrypt($this->name . '/everlogin');

        return $this->renderAdminTwig('customer_connect.html.twig', [
            'login_customer' => $customer,
            'lastname' => $customer->lastname,
            'firstname' => $customer->firstname,
            'login_link' => $link->getModuleLink(
                $this->name,
                'everlogin',
                [
                    'id_ever_customer' => $customer->id,
                    'evertoken' => $everToken,
                    'ever_id_cart' => Cart::lastNoneOrderedCart($customer->id),
                ]
            ),
            $this->name . '_dir' => $this->_path . 'views/img/',
            'evertoken' => $everToken,
            'base_uri' => __PS_BASE_URI__,
        ]);
    }

    /**
     * Add buttons to main buttons bar
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        $order = new Order(
            (int) $params['id_order']
        );
        if (Validate::isLoadedObject($order)) {
            $everToken = $this->encrypt($this->name . '/everlogin');
            $link = new Link();
            $connectLink = $link->getModuleLink(
                $this->name,
                'everlogin',
                [
                    'id_ever_customer' => $order->id_customer,
                    'evertoken' => $everToken,
                    'ever_id_cart' => Cart::lastNoneOrderedCart($order->id_customer),
                ]
            );
            if (version_compare(_PS_VERSION_, '8.0', '<')) {
                /** @var PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection $bar */
                $bar = $params['actions_bar_buttons_collection'];
                $bar->add(
                    new PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                        'btn-info',
                        ['href' => $connectLink, 'target' => '_blank'],
                        $this->l('Connect to customer account')
                    )
                );
            } else {
                /** @var PrestaShop\PrestaShop\Core\Action\ActionsBarButtonsCollection $bar */
                $bar = $params['actions_bar_buttons_collection'];
                $bar->add(
                    new PrestaShop\PrestaShop\Core\Action\ActionsBarButton(
                        'btn-info',
                        ['href' => $connectLink, 'target' => '_blank'],
                        $this->l('Connect to customer account')
                    )
                );
            }
        }
    }

    public function hookActionCustomerLogoutBefore($params)
    {
        if ($this->context->cookie->__isset('everlogin')) {
            $this->context->cookie->__unset('everlogin');
        }
    }

    public function everHook($method, $args)
    {
        $position = isset($args[0]['position']) ? (int) $args[0]['position'] : null;
        $context = Context::getContext();
        $id_hook = (int) Hook::getIdByName(lcfirst(str_replace('hook', '', $method)));
        $hookName = lcfirst(str_replace('hook', '', $method));
        $idObj = 0;
        if (Tools::getValue('id_product')) {
            $idObj = (int) Tools::getValue('id_product');
        }
        if (Tools::getValue('id_category')) {
            $idObj = (int) Tools::getValue('id_category');
        }
        if (Tools::getValue('id_manufacturer')) {
            $idObj = (int) Tools::getValue('id_manufacturer');
        }
        if (Tools::getValue('id_supplier')) {
            $idObj = (int) Tools::getValue('id_supplier');
        }
        if (Tools::getValue('id_cms')) {
            $idObj = (int) Tools::getValue('id_cms');
        }
        $everblock = EverblockClass::getBlocks(
            (int) $id_hook,
            (int) $context->language->id,
            (int) $context->shop->id
        );
        $isPreview = isset($args[0]['everblock_preview']) && (bool) $args[0]['everblock_preview'];
        $isBypassed = in_array($method, $this->bypassedControllers, true);
        $id_entity = isset($context->customer->id) && $context->customer->id ? (int) $context->customer->id : false;
        $customerGroups = $id_entity
            ? Customer::getGroupsStatic((int) $id_entity)
            : array_values(array_unique(array_filter([
                (int) Configuration::get('PS_UNIDENTIFIED_GROUP'),
                (int) Configuration::get('PS_GUEST_GROUP'),
                (int) Configuration::get('PS_CUSTOMER_GROUP'),
            ])));
        $currentBlock = [];
        $visibleBlocks = [];
        $visibleCacheIds = [];

        foreach ($everblock as $block) {
            if ((bool) $block['modal'] === true
                && (bool) EverblockTools::isBot() === true
            ) {
                continue;
            }
            if (Validate::isInt($position) && (int) $block['position'] != (int) $position) {
                continue;
            }
            // Check device
            if ((int) $block['device'] > 0
                && (int) $context->getDevice() != (int) $block['device']
            ) {
                continue;
            }
            if ((bool) $block['only_home'] === true
                && Tools::getValue('controller') != 'index'
            ) {
                continue;
            }
            // Only category management
            if ((bool) $block['only_category'] === true
                && Tools::getValue('controller') != 'category'
            ) {
                continue;
            }
            // Only manufacturer management
            if ((bool) $block['only_manufacturer'] === true
                && Tools::getValue('controller') != 'manufacturer'
            ) {
                continue;
            }
            // Only supplier management
            if ((bool) $block['only_supplier'] === true
                && Tools::getValue('controller') != 'supplier'
            ) {
                continue;
            }
            // Only CMS category management
            if ((bool) $block['only_cms_category'] === true
                && !Tools::getValue('id_cms_category')
            ) {
                continue;
            }
            $continue = false;
            // Only category pages
            if ((bool) $block['only_category'] === true
                && Tools::getValue('controller') === 'category'
            ) {
                $categories = json_decode($block['categories']);
                $continue = !is_array($categories) || !in_array((int) Tools::getValue('id_category'), $categories);
            }
            // Only manufacturer pages
            if ((bool) $block['only_manufacturer'] === true
                && Tools::getValue('controller') === 'manufacturer'
            ) {
                $manufacturers = json_decode($block['manufacturers']);
                $continue = !is_array($manufacturers) || !in_array((int) Tools::getValue('id_manufacturer'), $manufacturers);
            }
            // Only supplier pages
            if ((bool) $block['only_supplier'] === true
                && Tools::getValue('controller') === 'supplier'
            ) {
                $suppliers = json_decode($block['suppliers']);
                $continue = !is_array($suppliers) || !in_array((int) Tools::getValue('id_supplier'), $suppliers);
            }
            // Only CMS category pages
            if ((bool) $block['only_cms_category'] === true
                && Tools::getValue('controller') === 'cms'
                && Tools::getValue('id_cms_category')
            ) {
                $cms_categories = json_decode($block['cms_categories']);
                $continue = !is_array($cms_categories) || !in_array((int) Tools::getValue('id_cms_category'), $cms_categories);
            }
            // Only products pages with specific category
            if (
                Tools::getValue('id_product')
                && Tools::getValue('controller') === 'product'
                && (bool) $block['only_category_product'] === true
            ) {
                $product = new Product((int) Tools::getValue('id_product'));
                $categories = json_decode($block['categories']);
                $defaultCategory = (int) $product->id_category_default;
                if ($categories && is_array($categories)) {
                    $continue = !in_array($defaultCategory, $categories);
                }
            }
            if ((bool) $continue === true) {
                continue;
            }
            // Date start and date end management
            $now = new DateTime();
            $now = $now->format('Y-m-d H:i:s');
            if (!empty($block['date_start'])
                && $block['date_start'] !== '0000-00-00 00:00:00'
                && $block['date_start'] > $now
            ) {
                continue;
            }
            if (!empty($block['date_end'])
                && $block['date_end'] !== '0000-00-00 00:00:00'
                && $block['date_end'] < $now
            ) {
                continue;
            }
            $allowedGroups = json_decode($block['groups'], true);
            if (isset($customerGroups)
                && !empty($allowedGroups)
                && !array_intersect($allowedGroups, $customerGroups)
            ) {
                continue;
            }

            $visibleBlocks[] = $block;
            $visibleCacheIds[] = $this->buildBlockRenderCacheId($block, $method, $hookName, $context, $idObj, $position);
        }

        if (!$isPreview && !empty($visibleCacheIds)) {
            $cachedHtml = '';
            $allBlocksCached = true;
            foreach ($visibleCacheIds as $cacheId) {
                if (!EverblockCache::isCacheStored($cacheId)) {
                    $allBlocksCached = false;
                    break;
                }
                $cachedHtml .= (string) EverblockCache::cacheRetrieve($cacheId);
            }

            if ($allBlocksCached) {
                return $cachedHtml;
            }
        }

        foreach ($visibleBlocks as $index => $block) {
            if ((bool) $block['obfuscate_link'] === true) {
                $block['content'] = EverblockTools::obfuscateText(
                    $block['content']
                );
            }
            if ((bool) $block['lazyload'] === true) {
                $block['content'] = EverblockTools::addLazyLoadToImages(
                    $block['content']
                );
            }
            if ($isBypassed) {
                $block['content'] = strip_tags($block['content']);
            }
            $block['content'] = $this->renderQcdBuilderTargetField(
                'everblock',
                (int) $block['id_everblock'],
                'content',
                (string) $block['content'],
                (int) $context->shop->id,
                (int) $context->language->id
            );
            $currentBlock[] = [
                'block' => $block,
                '_everblock_cache_id' => $visibleCacheIds[$index] ?? null,
            ];
        }

        Hook::exec(
            'actionRenderBlockBefore',
            [
                'everhook' => trim($method),
                $this->name => &$currentBlock,
                'args' => $args,
            ]
        );

        return $this->renderCachedBlockItems($currentBlock, $method, $hookName, $args, $context, $idObj, $position, $isPreview, $isBypassed);
    }

    private function renderCachedBlockItems(array $items, string $method, string $hookName, array $args, Context $context, int $idObj, ?int $position, bool $isPreview, bool $isBypassed): string
    {
        $html = '';
        foreach ($items as $item) {
            if (!isset($item['block'])) {
                continue;
            }
            if (!is_array($item['block'])) {
                $html .= $this->renderBlockItems([$item], $method, $args, $isBypassed);
                continue;
            }

            $cacheId = isset($item['_everblock_cache_id']) && is_string($item['_everblock_cache_id'])
                ? $item['_everblock_cache_id']
                : $this->buildBlockRenderCacheId($item['block'], $method, $hookName, $context, $idObj, $position);
            if ($isPreview || !EverblockCache::isCacheStored($cacheId)) {
                $rendered = $this->renderBlockItems([$item], $method, $args, $isBypassed);
                if (!$isPreview) {
                    EverblockCache::cacheStore($cacheId, $rendered);
                }
                $html .= $rendered;
                continue;
            }

            $html .= (string) EverblockCache::cacheRetrieve($cacheId);
        }

        return $html;
    }

    private function renderBlockItems(array $items, string $method, array $args, bool $isBypassed): string
    {
        $this->context->smarty->assign([
            'everhook' => trim($method),
            $this->name => $items,
            'args' => $args,
            'is_bypassed' => $isBypassed,
        ]);

        return $this->display(__FILE__, $this->name . '.tpl');
    }

    private function buildBlockRenderCacheId(array $block, string $method, string $hookName, Context $context, int $idObj, ?int $position): string
    {
        $blockId = (int) ($block['id_everblock'] ?? 0);
        $fingerprintSource = json_encode($block);
        if (!is_string($fingerprintSource)) {
            $fingerprintSource = serialize($block);
        }

        return str_replace('|', '-', implode('-', [
            $this->name,
            'block',
            $blockId,
            'id_hook',
            (int) ($block['id_hook'] ?? 0),
            'version',
            EverblockCache::getObjectCacheVersion('block', $blockId),
            'controller',
            trim((string) Tools::getValue('controller')),
            'method',
            trim($method),
            'hookName',
            trim($hookName),
            'idObj',
            (int) $idObj,
            'idLang',
            (int) $context->language->id,
            'idShop',
            (int) $context->shop->id,
            'idCurrency',
            (int) $context->currency->id,
            'device',
            (int) $context->getDevice(),
            'position',
            $position === null ? 'all' : (int) $position,
            'hash',
            md5($fingerprintSource),
        ]));
    }

    public function hookDisplayHeader()
    {
        if (Tools::getValue('eac')
            && Validate::isInt(Tools::getValue('eac'))
        ) {
            EverblockTools::addToCartByUrl(
                $this->context,
                (int) Tools::getValue('id_product'),
                (int) Tools::getValue('id_product_attribute'),
                (int) Tools::getValue('qty')
            );
        }
        if (isset($this->context->controller->php_self)
            && $this->context->controller->php_self === 'product'
            && ($idProduct = (int) Tools::getValue('id_product'))
        ) {
            $cookie = $this->context->cookie;
            $viewed = $cookie->__isset('viewed')
                ? (string) $cookie->__get('viewed')
                : '';
            $viewedArray = array_filter(array_map('intval', explode(',', $viewed)));
            $viewedArray = array_diff($viewedArray, [$idProduct]);
            $viewedArray[] = $idProduct;
            if (count($viewedArray) > 20) {
                $viewedArray = array_slice($viewedArray, -20);
            }
            $cookie->__set('viewed', implode(',', $viewedArray));
        }
        // Google Shopping hack
        $modelId = (int) Tools::getValue('model_id');
        if ($modelId) {
            $modelAttributeId = (int) Tools::getValue('model_attribute_id');

            $product = new Product($modelId, true, $this->context->language->id);
            if (Validate::isLoadedObject($product)) {
                $presentedProducts = EverblockTools::everPresentProducts(
                    [$product->id],
                    $this->context
                );
                $presentedProduct = reset($presentedProducts);

                // Injection de la bonne combinaison si précisée
                if (!empty($modelAttributeId) && isset($presentedProduct['combinations'])) {
                    foreach ($presentedProduct['combinations'] as $comb) {
                        if ((int) $comb['id_product_attribute'] === $modelAttributeId) {
                            $presentedProduct['id_product_attribute'] = $modelAttributeId;
                            $presentedProduct['combination'] = $comb;
                            // Important si tu veux forcer l'affichage correct de prix/image
                            $presentedProduct['price'] = $comb['price'];
                            $presentedProduct['price_amount'] = $comb['price_amount'];
                            if (!empty($comb['images'][0])) {
                                $presentedProduct['cover'] = $comb['images'][0]; // remplace l'image principale si nécessaire
                            }
                            break;
                        }
                    }
                }

                $this->context->smarty->assign('ever_model', $presentedProduct);
            }
        }

        $idShop = (int) $this->context->shop->id;
        if ((bool) EverblockCache::getModuleConfiguration('EVERBLOCK_LOAD_FRONT_CSS') === true) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-css',
                'modules/' . $this->name . '/views/css/' . $this->name . '.css',
                ['media' => 'all', 'priority' => 200]
            );
        }
        $flagsCssFile = _PS_MODULE_DIR_ . $this->name . '/views/css/feature-flags-' . $idShop . '.css';
        if (file_exists($flagsCssFile) && filesize($flagsCssFile) > 0) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-feature-flags-css',
                'modules/' . $this->name . '/views/css/feature-flags-' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200]
            );
        }
        $soldoutCssFile = _PS_MODULE_DIR_ . $this->name . '/views/css/outofstock-flag-' . $idShop . '.css';
        if (file_exists($soldoutCssFile) && filesize($soldoutCssFile) > 0) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-soldout-flag-css',
                'modules/' . $this->name . '/views/css/outofstock-flag-' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200]
            );
        }
        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-js',
            'modules/' . $this->name . '/views/js/' . $this->name . '.js',
            ['position' => 'bottom', 'priority' => 200]
        );
        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-slider-js',
            'modules/' . $this->name . '/views/js/everblock-slider.js',
            ['position' => 'bottom', 'priority' => 200]
        );
        if ((bool) EverblockCache::getModuleConfiguration('EVERBLOCK_USE_OBF') === true) {
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-obf-js',
                'modules/' . $this->name . '/views/js/' . $this->name . '-obfuscation.js',
                ['position' => 'bottom', 'priority' => 200]
            );
        }
        $compressedCss = _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css';
        $customJs = _PS_MODULE_DIR_ . '/' . $this->name . '/views/js/custom' . $idShop . '.js';
        if (file_exists($compressedCss) && filesize($compressedCss) > 0) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-custom-compressed-css',
                'modules/' . $this->name . '/views/css/custom-compressed' . $idShop . '.css',
                ['media' => 'all', 'priority' => 200]
            );
        }
        if (file_exists($customJs) && filesize($customJs) > 0) {
            $this->context->controller->registerJavascript(
                'module-' . $this->name . '-compressed-js',
                'modules/' . $this->name . '/views/js/custom' . $idShop . '.js',
                ['position' => 'bottom', 'priority' => 200]
            );
        }
        $externalJs = EverblockCache::getModuleConfiguration('EVERPSJS_LINKS');
        $jsLinksArray = [];
        if ($externalJs) {
            $jsLinksArray = explode("\n", $externalJs);
            foreach ($jsLinksArray as $key => $value) {
                $this->context->controller->registerJavascript(
                    'module-' . $this->name . '-custom-' . (int) $key . '-js',
                    $value,
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 200]
                );
            }
        }
        $externalCss = EverblockCache::getModuleConfiguration('EVERPSCSS_LINKS');
        $cssLinksArray = [];
        if ($externalCss) {
            $cssLinksArray = explode("\n", $externalCss);
            foreach ($cssLinksArray as $key => $value) {
                $this->context->controller->registerStylesheet(
                    'module-' . $this->name . '-custom-' . (int) $key . '-js',
                    $value,
                    ['server' => 'remote', 'media' => 'all', 'priority' => 200]
                );
            }
        }
        // Do not show GMAP api KEY on Everblock cache
        $apiKey = Configuration::get('EVERBLOCK_GMAP_KEY');
        if ($apiKey && Tools::getValue('controller') == 'cms') {
            $filename = 'store-locator-' . $idShop . '.js';
            $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/' . $filename;
            if (file_exists($filePath) && filesize($filePath) > 0) {
                $this->context->controller->registerJavascript(
                    'module-' . $this->name . '-custom-gmap-js',
                    'https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&libraries=places,geometry',
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 300, 'attributes' => 'defer']
                );
                $this->context->controller->registerJavascript(
                    'module-' . $this->name . '-shop-map-js',
                    'modules/' . $this->name . '/views/js/' . $filename,
                    ['server' => 'local', 'position' => 'bottom', 'priority' => 400, 'attributes' => 'defer']
                );
            }
        }
        $contactLink = base64_encode(
            $this->context->link->getModuleLink(
                $this->name,
                'contact'
            )
        );
        $modalLink = base64_encode(
            $this->context->link->getModuleLink(
                $this->name,
                'modal'
            )
        );
        $employeeLogged = false;
        if (isset($this->context->employee) && $this->context->employee) {
            if (method_exists($this->context->employee, 'isLoggedBack')) {
                $employeeLogged = (bool) $this->context->employee->isLoggedBack();
            }
            if (!$employeeLogged && (int) $this->context->employee->id > 0) {
                $employeeLogged = true;
            }
        }
        $this->context->smarty->assign('everblock_is_employee', $employeeLogged);

        Media::addJsDef([
            'evercontact_link' => $contactLink,
            'evermodal_link' => $modalLink,
            'everblock_token' => Tools::getToken(),
            'everblock_is_employee' => $employeeLogged,
        ]);
        $filePath = _PS_MODULE_DIR_ . $this->name . '/views/js/header-scripts-' . $this->context->shop->id . '.js';
        if (file_exists($filePath) && filesize($filePath) > 0) {
            return PHP_EOL . file_get_contents($filePath) . PHP_EOL;
        }
    }

    public function hookDisplayContentWrapperTop()
    {
        return $this->display(__FILE__, 'views/templates/hook/displayEverModel.tpl');
    }

    protected function compressCSSCode($css)
    {
        // Supprime les commentaires
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Supprime les espaces inutiles
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        // Remplace les séparateurs de déclarations par des points-virgules
        $css = str_replace(';}', '}', $css);
        // Supprime les espaces inutiles entre les propriétés et les valeurs
        $css = preg_replace('/[\s]*:[\s]*(.*?)[\s]*;/', ':$1;', $css);
        return $css;
    }

    public function getConfigInMultipleLangs($key, $idShopGroup = null, $idShop = null): array
    {
        $resultsArray = [];
        foreach (Language::getIDs() as $idLang) {
            $resultsArray[$idLang] = Configuration::get($key, $idLang, $idShopGroup, $idShop);
        }
        return $resultsArray;
    }

    protected function createUpgradeFile(): bool
    {
        $currentVersion = $this->version;
        $updateDir = _PS_MODULE_DIR_ . $this->name . '/upgrade/';
        $licenceHeader = EverblockTools::getPhpLicenceHeader();
        $upgradeFunction = EverblockTools::getUpgradeMethod($this->version);
        $newFilename = 'upgrade-' . str_replace('.', '_', $currentVersion) . '.php';
        $content = $licenceHeader . PHP_EOL . PHP_EOL . $upgradeFunction . PHP_EOL;
        if (file_put_contents($updateDir . $newFilename, $content) !== false) {
            return true;
        } else {
            return false;
        }
    }

    protected function secureModuleFolder()
    {
        $moduleName = $this->name;
        $moduleAuthor = $this->author;
        $indexContent = Tools::getDefaultIndexContent();
        // Utiliser le chemin du module actuel comme point de départ
        $moduleDir = $this->getLocalPath();
        // Fonction récursive pour ajouter le fichier index.php
        static::addIndexFileRecursively($moduleDir, $indexContent);
    }

    /**
     * Ajoute le fichier index.php récursivement dans tous les répertoires et sous-répertoires
     *
     * @param string $dir Le répertoire de départ
     * @param string $indexContent Le contenu à ajouter dans le fichier index.php
     */
    protected function addIndexFileRecursively($dir, $indexContent)
    {
        // Vérifier si le fichier index.php existe, sinon le créer
        $indexPath = $dir . '/index.php';
        if (!file_exists($indexPath)) {
            file_put_contents($indexPath, $indexContent);
        }
        // Parcourir le répertoire pour trouver des sous-répertoires et appliquer la fonction récursivement
        $files = new DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isDir()) {
                $this->addIndexFileRecursively(
                    $file->getPathname(),
                    $indexContent
                );
            }
        }
    }

    protected function uploadTabsFile()
    {
        /* upload the file */
        if (isset($_FILES['TABS_FILE'])
            && isset($_FILES['TABS_FILE']['tmp_name'])
            && !empty($_FILES['TABS_FILE']['tmp_name'])
        ) {
            $filename = $_FILES['TABS_FILE']['name'];
            $exploded_filename = explode('.', $filename);
            $ext = end($exploded_filename);
            if (Tools::strtolower($ext) != 'xlsx') {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['TABS_FILE']['tmp_name'], $tmp_name)
            ) {
                return false;
            }

            copy($tmp_name, _PS_MODULE_DIR_ . $this->name . '/input/tabs.xlsx');
            $this->processTabsFile();
            $this->postSuccess[] = $this->l('File has been imported');
        }
    }

    protected function processTabsFile()
    {
        $tabsFile = _PS_MODULE_DIR_ . $this->name . '/input/tabs.xlsx';
        if (!file_exists($tabsFile)) {
            return;
        }
        $file = new ImportFile($tabsFile);
        $lines = $file->getLines();
        $headers = $file->getHeaders();
        foreach ($lines as $line) {
            $this->updateProductTabs($line);
        }
        unlink($tabsFile);
    }

    protected function updateProductTabs($line)
    {
        if (!isset($line['id_product'])
            || empty($line['id_product'])
        ) {
            $this->postErrors[] = $this->l('Missing id_product column');
            return;
        }
        $product = new Product(
            (int) $line['id_product']
        );
        if (!Validate::isLoadedObject($product)) {
            $this->postErrors[] = $this->l('Product not valid');
            return;
        }
        if (isset($line['id_shop'])
            && !empty($line['id_shop'])
        ) {
            $id_shop = $line['id_shop'];
        } else {
            $id_shop = (int) $this->context->shop->id;
        }
        if (!isset($line['id_tab'])
            || empty($line['id_tab'])
        ) {
            $this->postErrors[] = $this->l('Missing id_tab column');
            return;
        }
        try {
            $tab = EverblockTabsClass::getByIdProductIdTab(
                (int) $line['id_product'],
                (int) $id_shop,
                (int) $line['id_tab']
            );
            $tab->id_tab = (int) $line['id_tab'];
            $tab->id_product = (int) $line['id_product'];
            $tab->id_shop = (int) $id_shop;
            foreach (Language::getLanguages(false, $id_shop) as $lang) {
                $titleKey = 'title_' . $lang['iso_code'];
                $contentKey = 'content_' . $lang['iso_code'];
                // Vérifier et assigner le titre s'il existe et n'est pas vide
                if (isset($line[$titleKey]) && !empty($line[$titleKey])) {
                    $tab->title[(int) $lang['id_lang']] = $line[$titleKey];
                }
                // Vérifier et assigner le contenu s'il existe et n'est pas vide
                if (isset($line[$contentKey]) && !empty($line[$contentKey])) {
                    $tab->content[(int) $lang['id_lang']] = $line[$contentKey];
                }
            }
            $tab->save();
            EverblockCache::clearAllModuleCache();
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            EverblockTools::setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    public function hookBeforeRenderingEverblockEverblock($params)
    {
        $states = $params['block']['states'] ?? [];

        foreach ($states as &$state) {
            if (empty($state['id_everblock'])) {
                $state['content'] = '';
                continue;
            }

            $idEverblock = (int) trim(explode('-', $state['id_everblock'], 2)[0]);
            $everblock   = new EverBlockClass(
                $idEverblock,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );

            $state['content'] = Validate::isLoadedObject($everblock) ? $everblock->content : '';
        }
        unset($state);

        // Les données retournées sont disponibles dans $block.extra
        return ['states' => $states];
    }

    public function hookBeforeRenderingEverblockCategoryTabs($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['id_categories'])) {
                    continue;
                }
                $limit = isset($state['nb_products']) ? (int) $state['nb_products'] : 0;
                if ($limit <= 0) {
                    $limit = (int) Configuration::get('PS_PRODUCTS_PER_PAGE');
                }
                $orderBy = isset($state['order_by']) ? (string) $state['order_by'] : 'id_product';
                if ($orderBy === 'id') {
                    $orderBy = 'id_product';
                }
                $allowedOrderBy = ['id_product', 'date_add', 'price'];
                if (!in_array($orderBy, $allowedOrderBy, true)) {
                    $orderBy = 'id_product';
                }
                $orderWay = isset($state['order_way']) ? strtoupper((string) $state['order_way']) : 'ASC';
                $allowedOrderWay = ['ASC', 'DESC'];
                if (!in_array($orderWay, $allowedOrderWay, true)) {
                    $orderWay = 'ASC';
                }
                $rawProducts = EverblockTools::getProductsByCategoryId(
                    (int) $state['id_categories']['id'],
                    $limit,
                    $orderBy,
                    $orderWay
                );
                $presented = EverblockTools::everPresentProducts(
                    array_column($rawProducts, 'id_product'),
                    $this->context
                );
                $products[$key] = $presented;
            }
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockCategoryPrice($params)
    {
        $states = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                $info = [
                    'category_link' => '#',
                    'image_url' => '',
                    'image_width' => 0,
                    'image_height' => 0,
                    'title' => '',
                    'min_price' => false,
                ];
                if (!empty($state['category']['id'])) {
                    $idCategory = (int) $state['category']['id'];
                    $info['category_link'] = $this->context->link->getCategoryLink($idCategory);
                    if (!empty($state['image']['url'])) {
                        $info['image_url'] = $state['image']['url'];
                    } else {
                        $info['image_url'] = $this->context->link->getCatImageLink(
                            ImageType::getFormattedName('category'),
                            $idCategory
                        );
                    }
                    $category = new Category(
                        $idCategory,
                        (int) $this->context->language->id
                    );
                    $info['title'] = !empty($state['name']) ? $state['name'] : $category->name;
                    $products = $category->getProducts(
                        (int) $this->context->language->id,
                        1,
                        1,
                        'price',
                        'asc',
                        false,
                        true,
                        false,
                        1,
                        true
                    );
                    if (!empty($products)) {
                        $info['min_price'] = $products[0]['price'];
                    }
                } else {
                    $info['title'] = !empty($state['name']) ? $state['name'] : '';
                    if (!empty($state['image']['url'])) {
                        $info['image_url'] = $state['image']['url'];
                    }
                }

                if (!empty($info['image_url'])) {
                    $size = false;
                    $path = parse_url($info['image_url'], PHP_URL_PATH);
                    if ($path) {
                        $absolute = _PS_ROOT_DIR_ . (strpos($path, '/') === 0 ? $path : '/' . $path);
                        if (Tools::file_exists_no_cache($absolute)) {
                            $size = @getimagesize($absolute);
                        }
                    }
                    if (!$size) {
                        $size = @getimagesize($info['image_url']);
                    }
                    if ($size) {
                        $info['image_width'] = (int) $size[0];
                        $info['image_height'] = (int) $size[1];
                    }
                }

                $states[$key] = $info;
            }
        }

        return ['state_data' => $states];
    }

    public function hookBeforeRenderingEverblockProductHighlight($params)
    {
        $product = false;
        if (!empty($params['block']['settings']['id_product'])) {
            $presented = EverblockTools::everPresentProducts(
                [(int) $params['block']['settings']['id_product']],
                $this->context
            );
            if (!empty($presented)) {
                $product = reset($presented);
            }
        }

        return ['product' => $product];
    }

    public function hookBeforeRenderingEverblockProductSelector($params)
    {
        $products = [];

        if (empty($params['block']['states']) || !is_array($params['block']['states'])) {
            return ['products' => $products];
        }

        foreach ($params['block']['states'] as $key => $state) {
            if (empty($state['product']['id'])) {
                continue;
            }

            $idProduct = (int) $state['product']['id'];
            if ($idProduct <= 0) {
                continue;
            }

            /** CACHE KEY PAR ID PRODUIT **/
            $cacheKey = 'everblock_product_' . $idProduct;

            if (!Cache::isStored($cacheKey)) {

                /** CE QUI ÉTAIT LENT : **/
                $presented = EverblockTools::everPresentProducts(
                    [$idProduct],
                    $this->context
                );

                $product = !empty($presented) ? reset($presented) : null;

                /** STOCKAGE EN CACHE **/
                Cache::store($cacheKey, $product);
            }

            $products[$key] = Cache::retrieve($cacheKey);
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockVideoProducts($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['product_ids'])) {
                    continue;
                }
                $ids = array_filter(array_map('intval', explode(',', $state['product_ids'])));
                if (empty($ids)) {
                    continue;
                }
                $presented = EverblockTools::everPresentProducts($ids, $this->context);
                if (!empty($presented)) {
                    $products[$key] = $presented;
                }
            }
        }

        return ['products' => $products];
    }

    public function hookDisplayReassurance($params)
    {
        if (!isset($this->context->controller) || !is_object($this->context->controller)) {
            return;
        }

        if (!class_exists('ProductController') || !($this->context->controller instanceof ProductController)) {
            return;
        }

        $idProduct = (int) Tools::getValue('id_product');
        if (!$idProduct
            && property_exists($this->context->controller, 'product')
            && isset($this->context->controller->product->id)
        ) {
            $idProduct = (int) $this->context->controller->product->id;
        }

        if ($idProduct <= 0) {
            return;
        }

        $modal = EverblockModal::getByProductId(
            $idProduct,
            (int) $this->context->shop->id
        );

        // Vérifie si objet chargé
        if (!Validate::isLoadedObject($modal)) {
            return;
        }
        $idLang = (int) $this->context->language->id;

        // Cas 1 : contenu texte dispo
        $hasContent = !empty($modal->content[$idLang]);

        // Cas 2 : fichier image dispo
        $hasFile = !empty($modal->file);

        if (!$hasContent && !$hasFile) {
            return;
        }

        $buttonLabel = '';
        if (is_array($modal->button_label) && isset($modal->button_label[$idLang])) {
            $buttonLabel = (string) $modal->button_label[$idLang];
        }

        $buttonFileUrl = '';
        if (!empty($modal->button_file)) {
            $buttonFileUrl = $this->context->link->getBaseLink() . 'img/cms/' . $modal->button_file;
        }

        $this->smarty->assign([
            'everblock_modal_id' => (int) $modal->id_everblock_modal,
            'everblock_modal_file' => $modal->file,
            'everblock_modal_content' => $this->renderQcdBuilderTargetField(
                'everblock_product_modal',
                $idProduct,
                'content',
                (string) ($modal->content[$idLang] ?? ''),
                (int) $this->context->shop->id,
                $idLang
            ),
            'everblock_modal_button_label' => $buttonLabel,
            'everblock_modal_button_file_url' => $buttonFileUrl,
        ]);

        return $this->fetch('module:everblock/views/templates/hook/modal.tpl');
    }

    public function encrypt($data)
    {
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            return Tools::hash($data);
        }

        return Tools::encrypt($data);
    }

    public function hookModuleRoutes($params)
    {
        $base = Configuration::get('EVERBLOCK_PAGES_BASE_URL') ?: 'guide';
        $base = EverblockTools::linkRewrite((string) $base);

        $faqBase = Configuration::get('EVERBLOCK_FAQ_BASE_URL') ?: 'faq';
        $faqBase = EverblockTools::linkRewrite((string) $faqBase);

        return [
            'module-everblock-pages' => [
                'controller' => 'pages',
                'rule' => $base,
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
            'module-everblock-page' => [
                'controller' => 'page',
                'rule' => $base . '/{id_everblock_page}-{rewrite}',
                'keywords' => [
                    'id_everblock_page' => ['regexp' => '[0-9]+', 'param' => 'id_everblock_page'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9\pL-]+', 'param' => 'rewrite'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
            'module-everblock-faqs-list' => [
                'controller' => 'faqs',
                'rule' => $faqBase,
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
            'module-everblock-faqs-tag' => [
                'controller' => 'faqs',
                'rule' => $faqBase . '/tag/{tag}',
                'keywords' => [
                    'tag' => [
                        'regexp' => '[_a-zA-Z0-9\pL-]+',
                        'param' => 'tag',
                        'required' => true,
                    ],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                ],
            ],
        ];
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }
}
