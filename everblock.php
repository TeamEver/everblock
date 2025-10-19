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

require_once('vendor/autoload.php');

use ArrayObject;
use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use Context;
use Everblock\Tools\Application\Command\EverBlock\EverBlockTranslationCommand;
use Everblock\Tools\Application\Command\EverBlock\UpsertEverBlockCommand;
use Everblock\Tools\Application\EverBlockApplicationService;
use Everblock\Tools\Checkout\EverblockCheckoutStep;
use Everblock\Tools\Service\Domain\EverBlockDomainService;
use Everblock\Tools\Service\Domain\EverBlockFlagDomainService;
use Everblock\Tools\Service\Domain\EverBlockModalDomainService;
use Everblock\Tools\Service\Domain\EverBlockShortcodeDomainService;
use Everblock\Tools\Service\Domain\EverBlockTabDomainService;
use Everblock\Tools\Service\EverBlockFaqProvider;
use Everblock\Tools\Entity\EverBlock;
use Everblock\Tools\Entity\EverBlockFlag;
use Everblock\Tools\Entity\EverBlockTranslation;
use Everblock\Tools\Entity\EverBlockModal;
use Everblock\Tools\Entity\EverBlockTab;
use Everblock\Tools\Entity\EverBlockTabTranslation;
use Everblock\Tools\Repository\EverBlockRepository;
use Everblock\Tools\Service\EverBlockProvider;
use Everblock\Tools\Service\EverBlockShortcodeProvider;
use Everblock\Tools\Service\Legacy\EverblockToolsService;
use Everblock\Tools\Service\EverBlockTabProvider;
use Everblock\Tools\Service\EverblockPrettyBlocks;
use Everblock\Tools\Service\EverblockCache;
use Everblock\Tools\Shortcode\ShortcodeRenderer;
use Everblock\Tools\Service\ImportFile;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use Everblock\Tools\Service\EverBlockModalProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ScssPhp\ScssPhp\Compiler;

class Everblock extends Module
{
    private $html;
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
    private ?EverBlockProvider $everBlockProvider = null;
    private ?EverBlockDomainService $everBlockDomainService = null;
    private ?EverBlockApplicationService $everBlockApplicationService = null;
    private ?EverBlockFaqProvider $everBlockFaqProvider = null;
    private ?EverBlockFlagDomainService $everBlockFlagDomainService = null;
    private ?EverBlockTabDomainService $everBlockTabDomainService = null;
    private ?EverBlockTabProvider $everBlockTabProvider = null;
    private ?EverBlockRepository $everBlockRepository = null;
    private ?EverBlockModalProvider $everBlockModalProvider = null;
    private ?EverBlockShortcodeProvider $everBlockShortcodeProvider = null;
    private ?EverBlockShortcodeDomainService $everBlockShortcodeDomainService = null;
    private ?EverBlockModalDomainService $everBlockModalDomainService = null;
    private ?ShortcodeRenderer $shortcodeRenderer = null;
    private ?EverblockToolsService $legacyToolsService = null;
    private bool $dependenciesBootstrapped = false;

    public function __construct(
        ?EverBlockProvider $everBlockProvider = null,
        ?EverBlockDomainService $everBlockDomainService = null,
        ?EverBlockApplicationService $everBlockApplicationService = null,
        ?EverBlockFaqProvider $everBlockFaqProvider = null,
        ?EverBlockFlagDomainService $everBlockFlagDomainService = null,
        ?EverBlockTabDomainService $everBlockTabDomainService = null,
        ?EverBlockTabProvider $everBlockTabProvider = null,
        ?EverBlockRepository $everBlockRepository = null,
        ?EverBlockModalProvider $everBlockModalProvider = null,
        ?EverBlockModalDomainService $everBlockModalDomainService = null,
        ?EverBlockShortcodeProvider $everBlockShortcodeProvider = null,
        ?EverBlockShortcodeDomainService $everBlockShortcodeDomainService = null,
        ?ShortcodeRenderer $shortcodeRenderer = null,
        ?EverblockToolsService $legacyToolsService = null
    ) {
        $this->name = 'everblock';
        $this->tab = 'front_office_features';
        $this->version = '8.0.4';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->everBlockProvider = $everBlockProvider;
        $this->everBlockDomainService = $everBlockDomainService;
        $this->everBlockApplicationService = $everBlockApplicationService;
        $this->everBlockFaqProvider = $everBlockFaqProvider;
        $this->everBlockFlagDomainService = $everBlockFlagDomainService;
        $this->everBlockTabDomainService = $everBlockTabDomainService;
        $this->everBlockTabProvider = $everBlockTabProvider;
        $this->everBlockRepository = $everBlockRepository;
        $this->everBlockModalProvider = $everBlockModalProvider;
        $this->everBlockModalDomainService = $everBlockModalDomainService;
        $this->everBlockShortcodeProvider = $everBlockShortcodeProvider;
        $this->everBlockShortcodeDomainService = $everBlockShortcodeDomainService;
        $this->shortcodeRenderer = $shortcodeRenderer;
        $this->legacyToolsService = $legacyToolsService;
        parent::__construct();
        $this->displayName = $this->l('Ever Block');
        $this->description = $this->l('Add HTML block everywhere !');
        $this->confirmUninstall = $this->l('Do yo really want to uninstall this module ?');
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        if ($this->everBlockShortcodeProvider instanceof EverBlockShortcodeProvider) {
            EverblockPrettyBlocks::setShortcodeProvider($this->everBlockShortcodeProvider);
            EverblockPrettyBlocks::setLegacyToolsService($this->getLegacyToolsService());
            $this->getLegacyToolsService()->setShortcodeProvider($this->everBlockShortcodeProvider);
        }
        $this->bootstrapDependencies();
    }

    private function bootstrapDependencies(): void
    {
        if ($this->dependenciesBootstrapped) {
            return;
        }

        $container = $this->resolveContainer();

        if ($container instanceof ContainerInterface) {
            if (null === $this->everBlockProvider) {
                $this->everBlockProvider = $this->resolveService($container, EverBlockProvider::class);
            }

            if (null === $this->everBlockDomainService) {
                $this->everBlockDomainService = $this->resolveService($container, EverBlockDomainService::class);
            }

            if (null === $this->everBlockApplicationService) {
                $this->everBlockApplicationService = $this->resolveService($container, EverBlockApplicationService::class);
            }

            if (null === $this->everBlockFaqProvider) {
                $this->everBlockFaqProvider = $this->resolveService($container, EverBlockFaqProvider::class);
            }

            if (null === $this->everBlockFlagDomainService) {
                $this->everBlockFlagDomainService = $this->resolveService($container, EverBlockFlagDomainService::class);
            }

            if (null === $this->everBlockTabDomainService) {
                $this->everBlockTabDomainService = $this->resolveService($container, EverBlockTabDomainService::class);
            }

            if (null === $this->everBlockTabProvider) {
                $this->everBlockTabProvider = $this->resolveService($container, EverBlockTabProvider::class);
            }

            if (null === $this->everBlockRepository) {
                $this->everBlockRepository = $this->resolveService($container, EverBlockRepository::class);
            }

            if (null === $this->everBlockModalProvider) {
                $this->everBlockModalProvider = $this->resolveService($container, EverBlockModalProvider::class);
            }

            if (null === $this->everBlockModalDomainService) {
                $this->everBlockModalDomainService = $this->resolveService($container, EverBlockModalDomainService::class);
            }

            if (null === $this->everBlockShortcodeProvider) {
                $this->everBlockShortcodeProvider = $this->resolveService($container, EverBlockShortcodeProvider::class);
            }

            if (null === $this->everBlockShortcodeDomainService) {
                $this->everBlockShortcodeDomainService = $this->resolveService($container, EverBlockShortcodeDomainService::class);
            }

            if (null === $this->shortcodeRenderer) {
                $this->shortcodeRenderer = $this->resolveService($container, ShortcodeRenderer::class);
            }

            if (null === $this->legacyToolsService) {
                $this->legacyToolsService = $this->resolveService($container, EverblockToolsService::class);
            }
        }

        if ($this->everBlockShortcodeProvider instanceof EverBlockShortcodeProvider) {
            EverblockPrettyBlocks::setShortcodeProvider($this->everBlockShortcodeProvider);
            $this->getLegacyToolsService()->setShortcodeProvider($this->everBlockShortcodeProvider);
        }

        $this->dependenciesBootstrapped = true;
    }

    private function resolveContainer(): ?ContainerInterface
    {
        try {
            if (method_exists($this, 'getContainer')) {
                $container = $this->getContainer();
            } elseif (method_exists(Module::class, 'getContainer')) {
                $container = Module::getContainer();
            } else {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        return $container instanceof ContainerInterface ? $container : null;
    }

    private function resolveService(ContainerInterface $container, string $serviceId): ?object
    {
        if (!$container->has($serviceId)) {
            return null;
        }

        $service = $container->get($serviceId);

        return $service instanceof $serviceId ? $service : null;
    }

    public function getEverBlockProvider(): ?EverBlockProvider
    {
        $this->bootstrapDependencies();

        return $this->everBlockProvider;
    }

    public function getEverBlockDomainService(): EverBlockDomainService
    {
        $this->bootstrapDependencies();

        if (!$this->everBlockDomainService instanceof EverBlockDomainService) {
            throw new \RuntimeException('EverBlockDomainService service is not available.');
        }

        return $this->everBlockDomainService;
    }

    public function getEverBlockApplicationService(): EverBlockApplicationService
    {
        $this->bootstrapDependencies();

        if (!$this->everBlockApplicationService instanceof EverBlockApplicationService) {
            throw new \RuntimeException('EverBlockApplicationService service is not available.');
        }

        return $this->everBlockApplicationService;
    }

    public function getLegacyToolsService(): EverblockToolsService
    {
        $this->bootstrapDependencies();

        if (!$this->legacyToolsService instanceof EverblockToolsService) {
            $this->legacyToolsService = new EverblockToolsService();
        }

        if (isset($this->context) && $this->context instanceof Context && isset($this->context->smarty)) {
            $this->context->smarty->assign('everblockToolsService', $this->legacyToolsService);
        }

        return $this->legacyToolsService;
    }

    public function getEverBlockFaqProvider(): ?EverBlockFaqProvider
    {
        $this->bootstrapDependencies();

        return $this->everBlockFaqProvider;
    }

    public function getEverBlockShortcodeProvider(): ?EverBlockShortcodeProvider
    {
        $this->bootstrapDependencies();

        return $this->everBlockShortcodeProvider;
    }

    public function getShortcodeRenderer(): ShortcodeRenderer
    {
        $this->bootstrapDependencies();

        if (!$this->shortcodeRenderer instanceof ShortcodeRenderer) {
            throw new \RuntimeException('ShortcodeRenderer service is not available.');
        }

        return $this->shortcodeRenderer;
    }

    public function getEverBlockShortcodeDomainService(): EverBlockShortcodeDomainService
    {
        $this->bootstrapDependencies();

        if (!$this->everBlockShortcodeDomainService instanceof EverBlockShortcodeDomainService) {
            throw new \RuntimeException('EverBlockShortcodeDomainService service is not available.');
        }

        return $this->everBlockShortcodeDomainService;
    }

    public function getEverBlockRepository(): ?EverBlockRepository
    {
        $this->bootstrapDependencies();

        return $this->everBlockRepository;
    }

    public function getEverBlockFlagDomainService(): EverBlockFlagDomainService
    {
        $this->bootstrapDependencies();

        if (!$this->everBlockFlagDomainService instanceof EverBlockFlagDomainService) {
            throw new \RuntimeException('EverBlockFlagDomainService service is not available.');
        }

        return $this->everBlockFlagDomainService;
    }

    public function getEverBlockTabDomainService(): EverBlockTabDomainService
    {
        $this->bootstrapDependencies();

        if (!$this->everBlockTabDomainService instanceof EverBlockTabDomainService) {
            throw new \RuntimeException('EverBlockTabDomainService service is not available.');
        }

        return $this->everBlockTabDomainService;
    }

    public function getEverBlockTabProvider(): ?EverBlockTabProvider
    {
        $this->bootstrapDependencies();

        return $this->everBlockTabProvider;
    }

    public function getEverBlockModalProvider(): ?EverBlockModalProvider
    {
        $this->bootstrapDependencies();

        return $this->everBlockModalProvider;
    }

    public function getEverBlockModalDomainService(): EverBlockModalDomainService
    {
        $this->bootstrapDependencies();

        if (!$this->everBlockModalDomainService instanceof EverBlockModalDomainService) {
            throw new \RuntimeException('EverBlockModalDomainService service is not available.');
        }

        return $this->everBlockModalDomainService;
    }

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

    public function install()
    {
        Configuration::updateValue('EVERBLOCK_TINYMCE', 1);
        Configuration::updateValue('EVERPSCSS_P_LLOREM_NUMBER', 5);
        Configuration::updateValue('EVERPSCSS_S_LLOREM_NUMBER', 5);
        Configuration::updateValue('EVERPS_TAB_NB', 5);
        Configuration::updateValue('EVERPS_FLAG_NB', 5);
        Configuration::updateValue('EVERWP_API_URL', '');
        Configuration::updateValue('EVERWP_API_USER', '');
        Configuration::updateValue('EVERWP_API_PWD', '');
        Configuration::updateValue('EVERWP_POST_NBR', 3);
        Configuration::updateValue('EVER_SOLDOUT_COLOR', '#ff0000');
        Configuration::updateValue('EVER_SOLDOUT_TEXTCOLOR', '#ffffff');
        Configuration::updateValue('EVERINSTA_SHOW_CAPTION', 0);
        Configuration::updateValue('EVERBLOCK_CONTACT_MAX_UPLOAD_SIZE', 2097152);
        Configuration::updateValue(
            'EVERBLOCK_CONTACT_ALLOWED_EXTENSIONS',
            json_encode(['pdf', 'jpg', 'jpeg', 'png']),
            true
        );
        Configuration::updateValue(
            'EVERBLOCK_CONTACT_ALLOWED_MIME_TYPES',
            json_encode(['application/pdf', 'image/jpeg', 'image/png']),
            true
        );
        Configuration::updateValue(
            'EVERPS_FEATURES_AS_FLAGS',
            json_encode([1]),
            true
        );
        Configuration::updateValue('EVERBLOCK_SOLDOUT_FLAG', 0);
        Configuration::updateValue('EVERBLOCK_LOW_STOCK_THRESHOLD', 5);
        Configuration::updateValue('EVERBLOCK_STORELOCATOR_TOGGLE', 0);
        Configuration::updateValue('EVERBLOCK_GOOGLE_API_KEY', '');
        Configuration::updateValue('EVERBLOCK_GOOGLE_PLACE_ID', '');
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_LIMIT', 5);
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING', 0);
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_SORT', 'most_relevant');
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING', 1);
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR', 1);
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA', 1);
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL', $this->l('Read all reviews on Google'));
        Configuration::updateValue('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL', '');
        // Install SQL
        $sql = [];
        include dirname(__FILE__) . '/sql/install.php';
        // Hook actionGetEverBlockBefore
        if (!Hook::getIdByName('actionGetEverBlockBefore')) {
            $hook = new Hook();
            $hook->name = 'actionGetEverBlockBefore';
            $hook->title = 'Before block is rendered';
            $hook->description = 'This hook triggers before block is rendered';
            $hook->save();
        }
        // Hook actionEverBlockChangeShortcodeBefore
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeBefore')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeBefore';
            $hook->title = 'Before block shortcodes are rendered';
            $hook->description = 'This hook triggers before every block shortcode is rendered';
            $hook->save();
        }
        // Hook actionEverBlockChangeShortcodeBefore
        if (!Hook::getIdByName('actionEverBlockChangeShortcodeAfter')) {
            $hook = new Hook();
            $hook->name = 'actionEverBlockChangeShortcodeAfter';
            $hook->title = 'After block shortcodes are rendered';
            $hook->description = 'This hook triggers after every block shortcode is rendered';
            $hook->save();
        }
        // Hook displayBeforeRenderingShortcodes
        if (!Hook::getIdByName('displayBeforeRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayBeforeRenderingShortcodes';
            $hook->title = 'Before rendering shortcodes';
            $hook->description = 'This hook triggers before shortcodes are rendered';
            $hook->save();
        }
        // Hook displayAfterRenderingShortcodes
        if (!Hook::getIdByName('displayAfterRenderingShortcodes')) {
            $hook = new Hook();
            $hook->name = 'displayAfterRenderingShortcodes';
            $hook->title = 'After rendering shortcodes';
            $hook->description = 'This hook triggers after shortcodes are rendered';
            $hook->save();
        }
        // Hook displayFakeHook
        if (!Hook::getIdByName('displayFakeHook')) {
            $hook = new Hook();
            $hook->name = 'displayFakeHook';
            $hook->title = 'Fake hook';
            $hook->description = 'Ne pas afficher ce hook en front, il sera utilisé pour du contenu asynchrone';
            $hook->save();
        }
        // Hook beforeRenderingEverblockSpecialEvent
        if (!Hook::getIdByName('beforeRenderingEverblockSpecialEvent')) {
            $hook = new Hook();
            $hook->name = 'beforeRenderingEverblockSpecialEvent';
            $hook->title = 'Before rendering special event block';
            $hook->description = 'This hook triggers before special event block is rendered';
            $hook->save();
        }
        $installed = parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionRegisterBlock')
            && $this->registerHook('beforeRenderingEverblockSpecialEvent')
            && $this->installModuleTab('AdminEverBlockParent', 'IMPROVE', $this->l('Ever Block'))
            && $this->installModuleTab('AdminEverBlockConfiguration', 'AdminEverBlockParent', $this->l('Configuration'), 'everblock_admin_configuration')
            && $this->installModuleTab('AdminEverBlock', 'AdminEverBlockParent', $this->l('HTML Blocks'), 'everblock_admin_blocks')
            && $this->installModuleTab('AdminEverBlockHook', 'AdminEverBlockParent', $this->l('Hooks'), 'everblock_admin_hooks')
            && $this->installModuleTab('AdminEverBlockShortcode', 'AdminEverBlockParent', $this->l('Shortcodes'), 'everblock_admin_shortcodes')
            && $this->installModuleTab('AdminEverBlockFaq', 'AdminEverBlockParent', $this->l('FAQ'), 'everblock_admin_faq');

        if ($installed) {
            $this->importLegacyTranslations();
        }

        return $installed;
    }

    public function uninstall()
    {
        // Uninstall SQL
        $sql = [];
        include dirname(__FILE__) . '/sql/uninstall.php';
        Configuration::deleteByName('EVERPSCSS_CACHE');
        Configuration::deleteByName('EVERPSCSS_LINKS');
        Configuration::deleteByName('EVERPSJS_LINKS');
        Configuration::deleteByName('EVERPSCSS_P_LLOREM_NUMBER');
        Configuration::deleteByName('EVERPSCSS_S_LLOREM_NUMBER');
        Configuration::deleteByName('EVERBLOCK_TINYMCE');
        Configuration::deleteByName('EVERWP_API_URL');
        Configuration::deleteByName('EVERWP_API_USER');
        Configuration::deleteByName('EVERWP_API_PWD');
        Configuration::deleteByName('EVERWP_POST_NBR');
        Configuration::deleteByName('EVER_SOLDOUT_COLOR');
        Configuration::deleteByName('EVER_SOLDOUT_TEXTCOLOR');
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
        return (parent::uninstall()
            && $this->uninstallModuleTab('AdminEverBlockParent')
            && $this->uninstallModuleTab('AdminEverBlockConfiguration', 'everblock_admin_configuration')
            && $this->uninstallModuleTab('AdminEverBlock', 'everblock_admin_blocks')
            && $this->uninstallModuleTab('AdminEverBlockHook', 'everblock_admin_hooks')
            && $this->uninstallModuleTab('AdminEverBlockShortcode', 'everblock_admin_shortcodes')
            && $this->uninstallModuleTab('AdminEverBlockFaq', 'everblock_admin_faq'));
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

    private function importLegacyTranslations()
    {
        $legacyDir = dirname(__FILE__) . '/translations';

        if (!is_dir($legacyDir)) {
            return;
        }

        $defaultMap = $this->loadDefaultLegacyTranslations($legacyDir);

        if (empty($defaultMap)) {
            return;
        }

        foreach (Language::getLanguages(false) as $language) {
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

    private function loadDefaultLegacyTranslations($legacyDir)
    {
        $candidates = ['en.php', 'gb.php', 'us.php'];

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
        $candidates = [$iso . '.php'];

        if ($iso === 'en') {
            $candidates[] = 'gb.php';
            $candidates[] = 'us.php';
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
        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . "translation` (`id_lang`, `domain`, `key`, `translation`, `theme`, `modified`)"
            . " VALUES ("
            . (int) $idLang . ", '" . pSQL($domain) . "', '" . pSQL($source) . "', '" . pSQL($translation, true) . "', NULL, 0)"
            . " ON DUPLICATE KEY UPDATE `translation` = VALUES(`translation`), `modified` = VALUES(`modified`)"
        );
    }

    protected function installModuleTab($tabClass, $parent, $tabName, ?string $routeName = null, array $routeParams = [])
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int) Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if (property_exists($tab, 'route_name')) {
            $tab->route_name = $routeName;
        }
        if (property_exists($tab, 'route_params')) {
            $tab->route_params = $routeParams;
        }
        if ($tabClass == 'AdminEverBlockParent') {
            $tab->icon = 'icon-team-ever';
        }
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = $tabName;
        }
        return $tab->add();
    }

    protected function uninstallModuleTab($tabClass, ?string $routeName = null)
    {
        $tabId = (int) Tab::getIdFromClassName($tabClass);

        if (!$tabId && $routeName && method_exists(Tab::class, 'getCollectionFromModule')) {
            $collection = Tab::getCollectionFromModule($this->name);

            if (is_array($collection)) {
                foreach ($collection as $tab) {
                    if (!isset($tab->id)) {
                        continue;
                    }
                    if ($tabClass && isset($tab->class_name) && $tab->class_name === $tabClass) {
                        $tabId = (int) $tab->id;
                        break;
                    }
                    if ($routeName && isset($tab->route_name) && $tab->route_name === $routeName) {
                        $tabId = (int) $tab->id;
                        break;
                    }
                }
            }
        }

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
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) $this->getLegacyToolsService()->moduleDirectoryExists('prettyblocks') === true
        ) {
            if (!Hook::getIdByName('beforeRenderingEverblockProductHighlight')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockProductHighlight';
                $hook->title = 'Before rendering product highlight block';
                $hook->description = 'This hook triggers before product highlight block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockCategoryTabs')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockCategoryTabs';
                $hook->title = 'Before rendering category tabs block';
                $hook->description = 'This hook triggers before category tabs block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockCategoryPrice')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockCategoryPrice';
                $hook->title = 'Before rendering category price block';
                $hook->description = 'This hook triggers before category price block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockLookbook')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockLookbook';
                $hook->title = 'Before rendering lookbook block';
                $hook->description = 'This hook triggers before lookbook block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockFlashDeals')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockFlashDeals';
                $hook->title = 'Before rendering flash deals block';
                $hook->description = 'This hook triggers before flash deals block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockCategoryProducts')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockCategoryProducts';
                $hook->title = 'Before rendering category products block';
                $hook->description = 'This hook triggers before category products block is rendered';
                $hook->save();
            }
            if (!Hook::getIdByName('beforeRenderingEverblockSpecialEvent')) {
                $hook = new Hook();
                $hook->name = 'beforeRenderingEverblockSpecialEvent';
                $hook->title = 'Before rendering special event block';
                $hook->description = 'This hook triggers before special event block is rendered';
                $hook->save();
            }
            $this->registerHook('beforeRenderingEverblockProductHighlight');
            $this->registerHook('beforeRenderingEverblockCategoryTabs');
            $this->registerHook('beforeRenderingEverblockCategoryPrice');
            $this->registerHook('beforeRenderingEverblockLookbook');
            $this->registerHook('beforeRenderingEverblockFlashDeals');
            $this->registerHook('beforeRenderingEverblockCategoryProducts');
            $this->registerHook('beforeRenderingEverblockSpecialEvent');
            $this->registerHook('beforeRenderingEverblockEverblock');
        } else {
            $this->unregisterHook('beforeRenderingEverblockProductHighlight');
            $this->unregisterHook('beforeRenderingEverblockCategoryTabs');
            $this->unregisterHook('beforeRenderingEverblockCategoryPrice');
            $this->unregisterHook('beforeRenderingEverblockLookbook');
            $this->unregisterHook('beforeRenderingEverblockFlashDeals');
            $this->unregisterHook('beforeRenderingEverblockCategoryProducts');
            $this->unregisterHook('beforeRenderingEverblockSpecialEvent');
            $this->unregisterHook('beforeRenderingEverblockEverblock');
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
        $this->registerHook('actionObjectEverBlockClassUpdateAfter');
        $this->registerHook('actionObjectEverBlockClassDeleteAfter');
        $this->registerHook('actionObjectEverblockFaqUpdateAfter');
        $this->registerHook('actionObjectEverblockFaqDeleteAfter');
        $this->registerHook('actionObjectEverBlockFlagsUpdateAfter');
        $this->registerHook('actionObjectEverBlockFlagsDeleteAfter');
        $this->registerHook('displayWrapperBottom');
        $this->registerHook('displayWrapperTop');
        $this->updateProductFlagsHook();
        $this->registerHook('actionEmailAddAfterContent');
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) $this->getLegacyToolsService()->moduleDirectoryExists('prettyblocks') === true
        ) {
            $this->registerHook('actionRegisterBlock');
            $this->registerHook('beforeRenderingEverblockProductSelector');
            $this->registerHook('beforeRenderingEverblockCategoryProducts');
        } else {
            $this->unregisterHook('actionRegisterBlock');
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

            if (!$needHook) {
                try {
                    $flagDomainService = $this->getEverBlockFlagDomainService();
                    if ($flagDomainService->hasFlagsForShop($idShop)) {
                        $needHook = true;
                    }
                } catch (\RuntimeException $exception) {
                    PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
                }
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

    public function getContent()
    {
        $this->createUpgradeFile();
        $this->secureModuleFolder();
        $this->getLegacyToolsService()->checkAndFixDatabase();
        $this->checkHooks();
        $this->html = '';

        if (Tools::isSubmit('deleteEVERBLOCK_MARKER_ICON')) {
            $icon = Configuration::get('EVERBLOCK_MARKER_ICON');
            if ($icon) {
                $path = _PS_MODULE_DIR_ . $this->name . '/views/img/' . $icon;
                if (file_exists($path)) {
                    @unlink($path);
                }
                Configuration::deleteByName('EVERBLOCK_MARKER_ICON');
                $this->postSuccess[] = $this->l('Marker icon removed.');
            }
        }

        if (((bool) Tools::isSubmit('submit' . $this->name . 'Module')) == true) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }

        if ((bool) Tools::isSubmit('submitUploadTabsFile') === true) {
            $this->uploadTabsFile();
        }
        if ((bool) Tools::isSubmit('submitUploadBlocksFile') === true) {
            $this->uploadBlocksFile();
        }
        if ((bool) Tools::isSubmit('submitUploadSvg') === true) {
            $this->uploadSvgFile();
        }
        if ((bool) Tools::isSubmit('submitEmptyCache') === true) {
            $this->emptyAllCache();
        }
        if ((bool) Tools::isSubmit('submitEmptyLogs') === true) {
            $purged = $this->getLegacyToolsService()->purgeNativePrestashopLogsTable();
            if ((bool) $purged === true) {
                $this->postSuccess[] = $this->l('Log tables emptied');
            } else {
                $this->postErrors[] = $this->l('Log tables NOT emptied');
            }
        }
        if ((bool) Tools::isSubmit('submitDropUnusedLangs') === true) {
            $dropped = $this->getLegacyToolsService()->dropUnusedLangs();
            if (is_array($dropped)
                && isset($dropped['postErrors'])
                && count($dropped['postErrors']) > 0
            ) {
                foreach ($dropped['postErrors'] as $postErrors) {
                    $this->postErrors[] = $postErrors;
                }
            }
            if (is_array($dropped)
                && isset($dropped['querySuccess'])
                && count($dropped['querySuccess']) > 0
            ) {
                foreach ($dropped['querySuccess'] as $querySuccess) {
                    $this->postSuccess[] = $querySuccess;
                }
            }
        }
        if ((bool) Tools::isSubmit('submitSecureModuleFoldersWithApache') === true) {
            $secured = $this->getLegacyToolsService()->secureModuleFoldersWithApache();
            if (is_array($secured)
                && isset($secured['postErrors'])
                && count($secured['postErrors']) > 0
            ) {
                foreach ($secured['postErrors'] as $postErrors) {
                    $this->postErrors[] = $postErrors;
                }
            }
            if (is_array($secured)
                && isset($secured['querySuccess'])
                && count($secured['querySuccess']) > 0
            ) {
                foreach ($secured['querySuccess'] as $querySuccess) {
                    $this->postSuccess[] = $querySuccess;
                }
            }
        }

        if ((bool) Tools::isSubmit('submitBackupBlocks') === true) {
            $backuped = $this->getLegacyToolsService()->exportModuleTablesSQL();
            $configBackuped = $this->getLegacyToolsService()->exportConfigurationSQL();
            if ((bool) $backuped === true && (bool) $configBackuped === true) {
                $this->postSuccess[] = $this->l('Backup done');
            } else {
                $this->postErrors[] = $this->l('Backup failed');
            }
        }
        if ((bool) Tools::isSubmit('submitRestoreBackup') === true) {
            $restored = $this->getLegacyToolsService()->restoreModuleTablesFromBackup();
            if ((bool) $restored === true) {
                $this->postSuccess[] = $this->l('Restore done');
            } else {
                $this->postErrors[] = $this->l('Restore failed');
            }
        }
        if ((bool) Tools::isSubmit('submitCreateProduct') === true) {
            $created = $this->getLegacyToolsService()->generateProducts(
                (int) $this->context->shop->id
            );
            if ((bool) $created === true) {
                $this->postSuccess[] = $this->l('Products creation done');
            } else {
                $this->postErrors[] = $this->l('Products creation failed');
            }
        }
        if ((bool) Tools::isSubmit('submitMigrateUrls') === true
            && Tools::getValue('EVERPS_OLD_URL')
            && Tools::getValue('EVERPS_NEW_URL')
        ) {
            $migration = $this->getLegacyToolsService()->migrateUrls(
                Tools::getValue('EVERPS_OLD_URL'),
                Tools::getValue('EVERPS_NEW_URL'),
                (int) $this->context->shop->id
            );
            if (is_array($migration)
                && isset($migration['postErrors'])
                && count($migration['postErrors']) > 0
            ) {
                foreach ($migration['postErrors'] as $postErrors) {
                    $this->postErrors[] = $postErrors;
                }
            }
            if (is_array($migration)
                && isset($migration['querySuccess'])
                && count($migration['querySuccess']) > 0
            ) {
                foreach ($migration['querySuccess'] as $querySuccess) {
                    $this->postSuccess[] = $querySuccess;
                }
            }
        }
        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminEverBlockConfiguration')
        );

        return '';
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name . 'Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        return $helper->generateForm($this->getConfigForm());
    }


    protected function getConfigForm()
    {
        $step_position = [
            [
                'id_position' => 1,
                'name' => $this->l('After login'),
            ],
            [
                'id_position' => 2,
                'name' => $this->l('After address form'),
            ],
            [
                'id_position' => 3,
                'name' => $this->l('After shipping form'),
            ],
        ];
        $features = Feature::getFeatures(
            (int) $this->context->language->id
        );

        $tabs = [
            'settings' => $this->l('Réglages'),
            'stats' => $this->l('Statistiques'),
            'meta_tools' => $this->l('Meta Tools'),
            'google_maps' => $this->l('Google Maps'),
            'migration' => $this->l('Migration des URL'),
            'tools' => $this->l('Outils'),
            'files' => $this->l('Gestionnaire de fichiers'),
            'flags' => $this->l('Flags'),
        ];

        $isPrettyBlocksEnabled = (bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) $this->getLegacyToolsService()->moduleDirectoryExists('prettyblocks') === true;

        if ($isPrettyBlocksEnabled) {
            $tabs['prettyblock'] = $this->l('Prettyblock');
        }

        $tabs['holiday'] = $this->l('Holiday opening hours by store');

        $tabs['cron'] = $this->l('Tâches crons');

        $form = [
            'form' => [
                'tabs' => $tabs,
                'input' => [],
                'buttons' => [],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $docTemplates = [
            'settings' => 'settings.tpl',
            'stats' => 'stats.tpl',
            'meta_tools' => 'meta_tools.tpl',
            'google_maps' => 'google_maps.tpl',
            'migration' => 'migration.tpl',
            'tools' => 'tools.tpl',
            'files' => 'files.tpl',
            'flags' => 'flags.tpl',
            'holiday' => 'holiday.tpl',
            'cron' => 'cron.tpl',
        ];

        if ($isPrettyBlocksEnabled) {
            $docTemplates['prettyblock'] = 'prettyblock.tpl';
        }

        foreach ($docTemplates as $tab => $template) {
            $docPath = $this->local_path . 'views/templates/admin/config/docs/' . $template;

            if (!Tools::file_exists_cache($docPath)) {
                continue;
            }

            $form['form']['input'][] = [
                'type' => 'html',
                'name' => 'documentation_' . $tab,
                'tab' => $tab,
                'html_content' => $this->context->smarty->fetch($docPath),
            ];
        }

        $form['form']['input'][] = [
            'type' => 'html',
            'name' => 'anchor_everblock_tools',
            'html_content' => '<span id="everblock_tools"></span>',
            'tab' => 'tools',
        ];

        $toolButtons = [
            [
                'name' => 'submitEmptyCache',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Empty cache'),
            ],
            [
                'name' => 'submitEmptyLogs',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Empty logs'),
            ],
            [
                'name' => 'submitDropUnusedLangs',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Drop unused langs'),
            ],
            [
                'name' => 'submitSecureModuleFoldersWithApache',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Secure all modules folders using Apache'),
            ],
            [
                'name' => 'submitBackupBlocks',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Backup all blocks'),
            ],
            [
                'name' => 'submitRestoreBackup',
                'type' => 'submit',
                'class' => 'btn btn-light',
                'icon' => 'process-icon-refresh',
                'title' => $this->l('Restore backup'),
            ],
        ];

        foreach ($toolButtons as $button) {
            $button['tab'] = 'tools';
            $form['form']['buttons'][] = $button;
        }

        $settingsInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_settings',
                'html_content' => '<span id="everblock_settings"></span>',
            ],
            [
                'type' => 'text',
                'lang' => true,
                'label' => $this->l('New order step title'),
                'desc' => $this->l('Please specify new order step title'),
                'hint' => $this->l('If not set, new order step won\'t be shown'),
                'name' => 'EVEROPTIONS_TITLE',
                'required' => true,
            ],
            [
                'type' => 'select',
                'label' => $this->l('New order step position'),
                'desc' => $this->l('Please select new order step position'),
                'hint' => $this->l('Will impact position of the new order step'),
                'name' => 'EVEROPTIONS_POSITION',
                'options' => [
                    'query' => $step_position,
                    'id' => 'id_position',
                    'name' => 'name',
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Maintenance password'),
                'desc' => $this->l('If entered, you will have a password entry form on the maintenance page'),
                'hint' => $this->l('People with the password will be able to access the store in maintenance mode'),
                'name' => 'EVERBLOCK_MAINTENANCE_PSSWD',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Empty cache on saving ?'),
                'desc' => $this->l('Set yes to empty cache on saving'),
                'hint' => $this->l('Else cache will not be emptied'),
                'name' => 'EVERPSCSS_CACHE',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('No'),
                    ],
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Use module cache system instead of Prestashop native cache ?'),
                'desc' => $this->l('Set yes to use module cache, this will generate cache files on your server'),
                'hint' => $this->l('Else Prestashop native cache will be used'),
                'name' => 'EVERBLOCK_CACHE',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('No'),
                    ],
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Enable front-office script for obfuscation ?'),
                'desc' => $this->l('Will load JS file to manage obfuscated links'),
                'hint' => $this->l('Leave it to "No" if you already have a script that manages obfuscated links'),
                'name' => 'EVERBLOCK_USE_OBF',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Enable slick slider scripts ?'),
                'desc' => $this->l('Set yes to enable slick scripts for carousels'),
                'hint' => $this->l('Else carousels wont work'),
                'name' => 'EVERBLOCK_USE_SLICK',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('No'),
                    ],
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Extends TinyMCE on blocks management ?'),
                'desc' => $this->l('Set yes to extends TinyMCE on blocs management'),
                'hint' => $this->l('Else TinyMCE will be default'),
                'name' => 'EVERBLOCK_TINYMCE',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('No'),
                    ],
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Disable automatic conversion of images to webp format'),
                'desc' => $this->l('Will disable automatic conversion of images to webp format in HTML blocks'),
                'hint' => $this->l('If the setting is changed to no, images will not be converted to webp format'),
                'name' => 'EVERBLOCK_DISABLE_WEBP',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
        ];

        foreach ($settingsInputs as $input) {
            $input['tab'] = 'settings';
            $form['form']['input'][] = $input;
        }

        $metaToolsInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_meta_tools',
                'html_content' => '<span id="everblock_meta_tools"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Instagram access token'),
                'desc' => $this->l('Add here your Instagram access token'),
                'hint' => $this->l('Without access token, you wont be able to show Instagram slider'),
                'name' => 'EVERINSTA_ACCESS_TOKEN',
            ],
        ];

        foreach ($metaToolsInputs as $input) {
            $input['tab'] = 'meta_tools';
            $form['form']['input'][] = $input;
        }

        $wordpressInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_wordpress',
                'html_content' => '<span id="everblock_wordpress"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('WordPress API URL'),
                'desc' => $this->l('Full REST API endpoint for posts'),
                'hint' => $this->l('Example: https://example.com/wp-json/wp/v2/posts'),
                'name' => 'EVERWP_API_URL',
            ],
            [
                'type' => 'text',
                'label' => $this->l('WordPress API user'),
                'name' => 'EVERWP_API_USER',
            ],
            [
                'type' => 'text',
                'label' => $this->l('WordPress API password'),
                'name' => 'EVERWP_API_PWD',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Number of blog posts to display'),
                'name' => 'EVERWP_POST_NBR',
            ],
        ];

        foreach ($wordpressInputs as $input) {
            $input['tab'] = 'meta_tools';
            $form['form']['input'][] = $input;
        }

        $googleReviewsInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_google_reviews',
                'html_content' => '<span id="everblock_google_reviews"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Google Places API key'),
                'desc' => $this->l('API key used to retrieve reviews from Google Places.'),
                'name' => 'EVERBLOCK_GOOGLE_API_KEY',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Google Place ID'),
                'desc' => $this->l('Place identifier for your business listing.'),
                'name' => 'EVERBLOCK_GOOGLE_PLACE_ID',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Maximum number of reviews'),
                'desc' => $this->l('Number of reviews to display (minimum 1).'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_LIMIT',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Minimum rating to display'),
                'desc' => $this->l('Only reviews with a rating equal or above this value will be shown.'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING',
            ],
            [
                'type' => 'select',
                'label' => $this->l('Reviews sort order'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_SORT',
                'options' => [
                    'query' => [
                        [
                            'id' => 'most_relevant',
                            'name' => $this->l('Most relevant'),
                        ],
                        [
                            'id' => 'newest',
                            'name' => $this->l('Most recent'),
                        ],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Show overall rating'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'everblock_google_reviews_show_rating_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'everblock_google_reviews_show_rating_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Show reviewer photos'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'everblock_google_reviews_show_avatar_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'everblock_google_reviews_show_avatar_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Show call-to-action button'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'everblock_google_reviews_show_cta_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'everblock_google_reviews_show_cta_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('CTA label'),
                'desc' => $this->l('Text displayed on the button linking to Google.'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL',
            ],
            [
                'type' => 'text',
                'label' => $this->l('CTA link override'),
                'desc' => $this->l('Leave empty to use the Google listing URL.'),
                'name' => 'EVERBLOCK_GOOGLE_REVIEWS_CTA_URL',
            ],
        ];

        foreach ($googleReviewsInputs as $input) {
            $input['tab'] = 'meta_tools';
            $form['form']['input'][] = $input;
        }

        $markerIcon = Configuration::get('EVERBLOCK_MARKER_ICON');
        $googleMapsInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_google_maps',
                'html_content' => '<span id="everblock_google_maps"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Google Map API key (CMS page only)'),
                'desc' => $this->l('Add here your Google Map API key'),
                'hint' => $this->l('Without API key, auto complete wont work'),
                'name' => 'EVERBLOCK_GMAP_KEY',
            ],
            [
                'type' => 'file',
                'label' => $this->l('Store locator marker icon'),
                'desc' => $this->l('Upload an SVG icon used as the Google Maps marker'),
                'hint' => $this->l('Only SVG files are allowed'),
                'name' => 'EVERBLOCK_MARKER_ICON',
                'display_image' => true,
                'image' => $markerIcon
                    ? $this->context->link->getBaseLink(null, null) . 'modules/' . $this->name . '/views/img/' . $markerIcon
                    : false,
                'delete_url' => $this->context->link->getAdminLink('AdminModules')
                    . '&configure=' . $this->name . '&deleteEVERBLOCK_MARKER_ICON=1',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Display map toggle button'),
                'desc' => $this->l('Add a button next to the store search to hide or show the map.'),
                'name' => 'EVERBLOCK_STORELOCATOR_TOGGLE',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'everblock_storelocator_toggle_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'everblock_storelocator_toggle_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
        ];

        foreach ($googleMapsInputs as $input) {
            $input['tab'] = 'google_maps';
            $form['form']['input'][] = $input;
        }

        $designInputs = [
            [
                'type' => 'switch',
                'label' => $this->l('Show Sold out flag'),
                'desc' => $this->l('Display a Sold out flag when product stock is empty and orders are not allowed'),
                'hint' => $this->l('Combinations and stock settings will be checked'),
                'name' => 'EVERBLOCK_SOLDOUT_FLAG',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Title for global catalog tab'),
                'desc' => $this->l('This text will be your global catalog tab title'),
                'hint' => $this->l('Leaving empty will hide tab'),
                'name' => 'EVER_TAB_TITLE',
                'lang' => true,
                'required' => true,
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Text shown on global catalog tab'),
                'desc' => $this->l('This text will be show on all products'),
                'hint' => $this->l('Leaving empty will hide tab'),
                'name' => 'EVER_TAB_CONTENT',
                'lang' => true,
                'required' => true,
                'autoload_rte' => true,
            ],
            [
                'type' => 'html',
                'name' => 'anchor_everblock_feature_colors',
                'html_content' => '<span id="everblock_feature_colors"></span>',
            ],
        ];

        $toolsInputs = [
            [
                'type' => 'textarea',
                'label' => $this->l('Code CSS personnalisé'),
                'desc' => $this->l('Add here your custom CSS rules'),
                'hint' => $this->l('Webdesigners here can manage CSS rules'),
                'name' => 'EVERPSCSS',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Javascript / jQuery personnalisé'),
                'desc' => $this->l('Add here your custom Javascript rules'),
                'hint' => $this->l('Webdesigners here can manage Javascript rules'),
                'name' => 'EVERPSJS',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Liens CSS personnalisés'),
                'desc' => $this->l('Add here your custom CSS links, one per line'),
                'hint' => $this->l('Add one link per line, must be CSS'),
                'name' => 'EVERPSCSS_LINKS',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Liens javascript personnalisés'),
                'desc' => $this->l('Add here your custom JS links, one per line'),
                'hint' => $this->l('Add one link per line, must be JS'),
                'name' => 'EVERPSJS_LINKS',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Header scripts'),
                'desc' => $this->l('Add here your custom header scripts'),
                'hint' => $this->l('Header scripts like Clarity can be added here'),
                'name' => 'EVERPS_HEADER_SCRIPTS',
            ],
        ];

        foreach ($designInputs as $input) {
            if ($input['name'] === 'anchor_everblock_feature_colors') {
                $input['tab'] = 'flags';
            } else {
                $input['tab'] = 'settings';
            }
            $form['form']['input'][] = $input;
        }

        foreach ($toolsInputs as $input) {
            $input['tab'] = 'tools';
            $form['form']['input'][] = $input;
        }

        $bannedFeatures = json_decode(Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
        if (!is_array($bannedFeatures)) {
            $bannedFeatures = [];
        }

        foreach ($bannedFeatures as $bannedFeature) {
            $featureId = (int) $bannedFeature;
            $feature = new Feature((int) $featureId);
            $idLang = (int) Context::getContext()->language->id;
            $featureName = isset($feature->name[$idLang]) ? $feature->name[$idLang] : $this->l('Unnamed feature');

            $form['form']['input'][] = [
                'type' => 'color',
                'label' => $this->l('Background color for Feature: ') . $featureName,
                'name' => 'EVERPS_FEATURE_COLOR_' . $featureId,
                'size' => 20,
                'required' => false,
                'tab' => 'flags',
            ];

            $form['form']['input'][] = [
                'type' => 'color',
                'label' => $this->l('Text color for Feature: ') . $featureName,
                'name' => 'EVERPS_FEATURE_TEXTCOLOR_' . $featureId,
                'size' => 20,
                'required' => false,
                'tab' => 'flags',
            ];
        }

        $form['form']['input'][] = [
            'type' => 'html',
            'name' => 'anchor_everblock_soldout_colors',
            'html_content' => '<span id="everblock_soldout_colors"></span>',
            'tab' => 'flags',
        ];

        $form['form']['input'][] = [
            'type' => 'color',
            'label' => $this->l('Background color for Sold out flag'),
            'name' => 'EVER_SOLDOUT_COLOR',
            'size' => 20,
            'required' => false,
            'tab' => 'flags',
        ];

        $form['form']['input'][] = [
            'type' => 'color',
            'label' => $this->l('Text color for Sold out flag'),
            'name' => 'EVER_SOLDOUT_TEXTCOLOR',
            'size' => 20,
            'required' => false,
            'tab' => 'flags',
        ];

        if (Configuration::get('EVERINSTA_ACCESS_TOKEN')) {
            $instagramInputs = [
                [
                    'type' => 'html',
                    'name' => 'anchor_everblock_instagram',
                    'html_content' => '<span id="everblock_instagram"></span>',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Instagram profile link'),
                    'desc' => $this->l('Add here your Instagram profile URL'),
                    'hint' => $this->l('This will set a custom link to your Instagram profile'),
                    'name' => 'EVERINSTA_LINK',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Display Instagram post text'),
                    'desc' => $this->l('Show caption text below images'),
                    'name' => 'EVERINSTA_SHOW_CAPTION',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
            ];

            foreach ($instagramInputs as $input) {
                $input['tab'] = 'meta_tools';
                $form['form']['input'][] = $input;
            }
        }

        if ($isPrettyBlocksEnabled) {
            $prettyBlocksInputs = [
                [
                    'type' => 'html',
                    'name' => 'anchor_everblock_prettyblocks',
                    'html_content' => '<span id="everblock_prettyblocks"></span>',
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Upload custom SVG'),
                    'desc' => $this->l('Upload your own SVG icon for PrettyBlocks'),
                    'hint' => $this->l('Only SVG files are allowed'),
                    'name' => 'CUSTOM_SVG',
                    'display_image' => false,
                    'required' => false,
                ],
            ];

            foreach ($prettyBlocksInputs as $input) {
                $input['tab'] = 'prettyblock';
                $form['form']['input'][] = $input;
            }

            $form['form']['input'][] = [
                'type' => 'html',
                'name' => 'submitUploadSvgButton',
                'html_content' => sprintf(
                    '<button type="submit" name="%s" class="btn btn-default pull-right"><i class="process-icon-download"></i> %s</button>',
                    'submitUploadSvg',
                    htmlspecialchars($this->l('Upload SVG'), ENT_QUOTES, 'UTF-8')
                ),
                'tab' => 'prettyblock',
            ];
        }

        $importInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_file_management',
                'html_content' => '<span id="everblock_file_management"></span>',
            ],
            [
                'type' => 'file',
                'label' => $this->l('Upload Excel tabs file'),
                'desc' => $this->l('Will upload Excel tabs file and import datas into this module'),
                'hint' => $this->l('You can then import this file in order to set up your tabs in bulk on the product sheets'),
                'name' => 'TABS_FILE',
                'display_image' => false,
                'required' => false,
            ],
        ];

        foreach ($importInputs as $input) {
            $input['tab'] = 'files';
            $form['form']['input'][] = $input;
        }

        $form['form']['input'][] = [
            'type' => 'html',
            'name' => 'submitUploadTabsFileButton',
            'html_content' => sprintf(
                '<button type="submit" name="%s" class="btn btn-default pull-right"><i class="process-icon-download"></i> %s</button>',
                'submitUploadTabsFile',
                htmlspecialchars($this->l('Upload file'), ENT_QUOTES, 'UTF-8')
            ),
            'tab' => 'files',
        ];

        $advancedInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_advanced',
                'html_content' => '<span id="everblock_advanced"></span>',
            ],
            [
                'type' => 'select',
                'class' => 'chosen',
                'multiple' => true,
                'label' => $this->l('Features as flags'),
                'desc' => $this->l('Please select features used for flags'),
                'hint' => $this->l('The selected features will be converted into product flags'),
                'name' => 'EVERPS_FEATURES_AS_FLAGS[]',
                'required' => false,
                'options' => [
                    'query' => $features,
                    'id' => 'id_feature',
                    'name' => 'name',
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Number of fictitious products to create during product generation'),
                'desc' => $this->l('Will generate this number of dummy products when generating'),
                'hint' => $this->l('Default will be 5'),
                'name' => 'EVERPS_DUMMY_NBR',
                'lang' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Default number of paragraphs when [llorem] shortcode is detected'),
                'desc' => $this->l('Will generate this number of paragraphs when the [llorem] shortcode is detected'),
                'hint' => $this->l('Leaving this value blank will generate five paragraphs'),
                'name' => 'EVERPSCSS_P_LLOREM_NUMBER',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Default number of sentences per paragraphs when [llorem] shortcode is detected'),
                'desc' => $this->l('Will generate this number of sentences per paragraphs when the [llorem] shortcode is detected'),
                'hint' => $this->l('Leaving this value blank will generate five sentences per paragraphs'),
                'name' => 'EVERPSCSS_S_LLOREM_NUMBER',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Number of flags for products'),
                'desc' => $this->l('Specify here the number of flags you want to have on products'),
                'hint' => $this->l('The default value is 1 and cannot be less than 1'),
                'name' => 'EVERPS_FLAG_NB',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Number of tabs for the product page'),
                'desc' => $this->l('Specify here the number of tabs you want to have on the product page'),
                'hint' => $this->l('The default value is 1 and cannot be less than 1'),
                'name' => 'EVERPS_TAB_NB',
            ],
        ];

        foreach ($advancedInputs as $input) {
            if ($input['name'] === 'EVERPS_FEATURES_AS_FLAGS[]'
                || $input['name'] === 'EVERPS_FLAG_NB'
            ) {
                $input['tab'] = 'flags';
            } else {
                $input['tab'] = 'settings';
            }
            $form['form']['input'][] = $input;
        }

        $migrationInputs = [
            [
                'type' => 'html',
                'name' => 'anchor_everblock_migration',
                'html_content' => '<span id="everblock_migration"></span>',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Migration : Old URL'),
                'desc' => $this->l('If an old URL and a new one are entered, the module will be able to change the old URL to the new one in the database.'),
                'hint' => $this->l('Leave empty for no use'),
                'name' => 'EVERPS_OLD_URL',
                'lang' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Migration : New URL'),
                'desc' => $this->l('If an old URL and a new one are entered, the module will be able to change the old URL to the new one in the database.'),
                'hint' => $this->l('Leave empty for no use'),
                'name' => 'EVERPS_NEW_URL',
                'lang' => false,
            ],
        ];

        foreach ($migrationInputs as $input) {
            $input['tab'] = 'migration';
            $form['form']['input'][] = $input;
        }

        $form['form']['buttons'][] = [
            'name' => 'submitMigrateUrls',
            'type' => 'submit',
            'class' => 'btn btn-light',
            'icon' => 'process-icon-refresh',
            'title' => $this->l('Migrate URLS'),
            'tab' => 'migration',
        ];

        $form['form']['buttons'][] = [
            'name' => 'submitCreateProduct',
            'type' => 'submit',
            'class' => 'btn btn-light',
            'icon' => 'process-icon-refresh',
            'title' => $this->l('Create fake products'),
            'tab' => 'settings',
        ];

        $stores = Store::getStores((int) $this->context->language->id);
        $holidayInputs = [];
        if (!empty($stores)) {
            $holidays = $this->getLegacyToolsService()->getFrenchHolidays((int) date('Y'));
            foreach ($stores as $store) {
                foreach ($holidays as $date) {
                    $holidayInputs[] = [
                        'type' => 'text',
                        'label' => sprintf(
                            $this->l('Holiday hours for %s on %s'),
                            $store['name'],
                            $date
                        ),
                        'name' => 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $store['id_store'] . '_' . $date,
                    ];
                }
            }
        }
        if (!empty($holidayInputs)) {
            array_unshift($holidayInputs, [
                'type' => 'html',
                'name' => 'anchor_everblock_holiday',
                'html_content' => '<span id="everblock_holiday"></span>',
            ]);
            foreach ($holidayInputs as $input) {
                $input['tab'] = 'holiday';
                $form['form']['input'][] = $input;
            }
        }

        $cronLinks = [];
        $cronToken = $this->encrypt($this->name . '/evercron');
        foreach ($this->allowedActions as $action) {
            $cronLinks[$action] = $this->context->link->getModuleLink(
                $this->name,
                'cron',
                [
                    'action' => $action,
                    'evertoken' => $cronToken,
                ]
            );
        }

        if (!empty($cronLinks)) {
            $cronHtml = '<div class="everblock-cron-links">';
            foreach ($cronLinks as $action => $link) {
                $cronHtml .= '<p><a class="btn btn-info" target="_blank" rel="noopener noreferrer" href="' .
                    htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">' .
                    htmlspecialchars($this->l('Cron for'), ENT_QUOTES, 'UTF-8') . ' ' .
                    htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '</a></p>';
            }
            $cronHtml .= '</div>';

            $form['form']['input'][] = [
                'type' => 'html',
                'name' => 'everblock_cron_links',
                'html_content' => $cronHtml,
                'tab' => 'cron',
            ];
        }

        $form['form']['input'] = $this->moveDocumentationToTabEnd($form['form']['input']);

        return [$form];
    }

    private function moveDocumentationToTabEnd(array $inputs)
    {
        $documentationByTab = [];
        $orphanDocumentation = [];
        $nonDocumentationInputs = [];

        foreach ($inputs as $input) {
            if (!isset($input['name']) || strpos($input['name'], 'documentation_') !== 0) {
                $nonDocumentationInputs[] = $input;
                continue;
            }

            if (isset($input['tab'])) {
                $documentationByTab[$input['tab']][] = $input;
            } else {
                $orphanDocumentation[] = $input;
            }
        }

        if (empty($documentationByTab) && empty($orphanDocumentation)) {
            return $inputs;
        }

        $orderedInputs = [];
        $countNonDocumentation = count($nonDocumentationInputs);

        foreach ($nonDocumentationInputs as $index => $input) {
            $orderedInputs[] = $input;

            if (!isset($input['tab'])) {
                continue;
            }

            $tab = $input['tab'];

            if (empty($documentationByTab[$tab])) {
                continue;
            }

            $isLastForTab = true;

            for ($nextIndex = $index + 1; $nextIndex < $countNonDocumentation; $nextIndex++) {
                if (isset($nonDocumentationInputs[$nextIndex]['tab'])
                    && $nonDocumentationInputs[$nextIndex]['tab'] === $tab
                ) {
                    $isLastForTab = false;
                    break;
                }
            }

            if ($isLastForTab) {
                foreach ($documentationByTab[$tab] as $documentationInput) {
                    $orderedInputs[] = $documentationInput;
                }

                unset($documentationByTab[$tab]);
            }
        }

        foreach ($documentationByTab as $documentationInputs) {
            foreach ($documentationInputs as $documentationInput) {
                $orderedInputs[] = $documentationInput;
            }
        }

        foreach ($orphanDocumentation as $documentationInput) {
            $orderedInputs[] = $documentationInput;
        }

        return $orderedInputs;
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
            'EVERWP_API_USER' => Configuration::get('EVERWP_API_USER'),
            'EVERWP_API_PWD' => Configuration::get('EVERWP_API_PWD'),
            'EVERWP_POST_NBR' => Configuration::get('EVERWP_POST_NBR'),
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
            'EVERPSCSS_CACHE' => Configuration::get('EVERPSCSS_CACHE'),
            'EVERBLOCK_CACHE' => Configuration::get('EVERBLOCK_CACHE'),
            'EVERBLOCK_USE_OBF' => Configuration::get('EVERBLOCK_USE_OBF'),
            'EVERBLOCK_USE_SLICK' => Configuration::get('EVERBLOCK_USE_SLICK'),
            'EVERBLOCK_SOLDOUT_FLAG' => Configuration::get('EVERBLOCK_SOLDOUT_FLAG'),
            'EVER_SOLDOUT_COLOR' => Configuration::get('EVER_SOLDOUT_COLOR'),
            'EVER_SOLDOUT_TEXTCOLOR' => Configuration::get('EVER_SOLDOUT_TEXTCOLOR'),
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
            'EVERBLOCK_DISABLE_WEBP' => Configuration::get('EVERBLOCK_DISABLE_WEBP'),
            'EVERPS_OLD_URL' => '',
            'EVERPS_NEW_URL' => '',
            'EVER_TAB_CONTENT' => $this->getConfigInMultipleLangs('EVER_TAB_CONTENT'),
            'EVER_TAB_TITLE' => $this->getConfigInMultipleLangs('EVER_TAB_TITLE'),
            'EVERPS_TAB_NB' => Configuration::get('EVERPS_TAB_NB'),
            'EVERPS_FLAG_NB' => Configuration::get('EVERPS_FLAG_NB'),
            'TABS_FILE' => '',
            'BLOCKS_FILE' => '',
            'CUSTOM_SVG' => '',
        ];
        $stores = Store::getStores((int) $this->context->language->id);
        $holidays = $this->getLegacyToolsService()->getFrenchHolidays((int) date('Y'));
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
            'tabs' => $this->countTableRecords('everblock_tabs', 'id_shop = ' . $idShop),
            'flags' => $this->countTableRecords('everblock_flags', 'id_shop = ' . $idShop),
            'modals' => $this->countTableRecords('everblock_modal', 'id_shop = ' . $idShop),
            'game_sessions' => $this->countTableRecords('everblock_game_play'),
        ];

        return $stats;
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
        if (!empty($custom_css)) {
            $handle_css = fopen(
                $custom_css,
                'w+'
            );
            fclose($handle_css);
        }
        if (!empty($compressedCss)) {
            $handle_css = fopen(
                $compressedCss,
                'w+'
            );
            fclose($handle_css);
        }
        // Create JS file if need
        if (!empty($custom_js)) {
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
            'EVERPSCSS_CACHE',
            Tools::getValue('EVERPSCSS_CACHE')
        );
        Configuration::updateValue(
            'EVERBLOCK_CACHE',
            Tools::getValue('EVERBLOCK_CACHE')
        );
        Configuration::updateValue(
            'EVERBLOCK_USE_OBF',
            Tools::getValue('EVERBLOCK_USE_OBF')
        );
        Configuration::updateValue(
            'EVERBLOCK_USE_SLICK',
            Tools::getValue('EVERBLOCK_USE_SLICK')
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
        if (Tools::getValue('EVERINSTA_ACCESS_TOKEN')
            && !empty(Tools::getValue('EVERINSTA_ACCESS_TOKEN'))
        ) {
            $this->getLegacyToolsService()->refreshInstagramToken();
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
        Configuration::updateValue(
            'EVERWP_API_USER',
            Tools::getValue('EVERWP_API_USER')
        );
        Configuration::updateValue(
            'EVERWP_API_PWD',
            Tools::getValue('EVERWP_API_PWD')
        );
        Configuration::updateValue(
            'EVERWP_POST_NBR',
            Tools::getValue('EVERWP_POST_NBR')
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
        $stores = Store::getStores((int) $this->context->language->id);
        $holidays = $this->getLegacyToolsService()->getFrenchHolidays((int) date('Y'));
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
            'EVERBLOCK_DISABLE_WEBP',
            Tools::getValue('EVERBLOCK_DISABLE_WEBP')
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
        if ((bool) Tools::getValue('EVERPSCSS_CACHE') === true) {
            $this->emptyAllCache();
        }
        $stores = $this->getLegacyToolsService()->getStoreLocatorData();
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
            $gmapScript = $this->getLegacyToolsService()->generateGoogleMapScript($markers);
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
        Tools::clearAllCache();
        $this->postSuccess[] = $this->l('Cache has been cleared');
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path . 'views/css/ever.css');
        if (Tools::getValue('id_' . $this->name)
            || Tools::getIsset('add' . $this->name)
            || Tools::getValue('configure') == $this->name
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
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/codemirror.min.js',
                'all'
            );
            $this->context->controller->addJS(
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.1/mode/javascript/javascript.min.js',
                'all'
            );
            if (Tools::getValue('configure') == $this->name) {
                $this->context->controller->addJs($this->_path . 'views/js/admin.js');
            }
            if ((bool) Configuration::get('EVERBLOCK_TINYMCE') === true
                && Tools::getValue('configure') != $this->name
            ) {
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
        try {
            $context = Context::getContext();
            $txt = $this->getShortcodeRenderer()->render($txt, $context, $this);
            $params['html'] = $txt;
            return $params['html'];
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'Ever Block hookActionOutputHTMLBefore : ' . $e->getMessage()
            );
            $this->getLegacyToolsService()->setLog(
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
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) $this->getLegacyToolsService()->moduleDirectoryExists('prettyblocks') === true
        ) {
            if (Tools::getValue('id_product')) {
                $idObj = (int) Tools::getValue('id_product');
                $objectName = 'Product';
            }
            if (Tools::getValue('id_category')) {
                $idObj = (int) Tools::getValue('id_category');
                $objectName = 'Category';
            }
            if (Tools::getValue('id_manufacturer')) {
                $idObj = (int) Tools::getValue('id_manufacturer');
                $objectName = 'Manufacturer';
            }
            if (Tools::getValue('id_supplier')) {
                $idObj = (int) Tools::getValue('id_supplier');
                $objectName = 'Supplier';
            }
            if (Tools::getValue('id_cms')) {
                $idObj = (int) Tools::getValue('id_cms');
                $objectName = 'Cms';
            }
            if (isset($idObj) && isset($objectName)) {
                $this->context->smarty->assign(array(
                    'idObj' => $idObj,
                    'objectName' => $objectName,
                    'zone' => 'displayWrapperTop',
                ));
                return $this->display(__FILE__, 'views/templates/hook/prettyblocks.tpl');
            }
        }
    }

    public function hookDisplayWrapperBottom()
    {
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) $this->getLegacyToolsService()->moduleDirectoryExists('prettyblocks') === true
        ) {
            if (Tools::getValue('id_product')) {
                $idObj = (int) Tools::getValue('id_product');
                $objectName = 'Product';
            }
            if (Tools::getValue('id_category')) {
                $idObj = (int) Tools::getValue('id_category');
                $objectName = 'Category';
            }
            if (Tools::getValue('id_manufacturer')) {
                $idObj = (int) Tools::getValue('id_manufacturer');
                $objectName = 'Manufacturer';
            }
            if (Tools::getValue('id_supplier')) {
                $idObj = (int) Tools::getValue('id_supplier');
                $objectName = 'Supplier';
            }
            if (Tools::getValue('id_cms')) {
                $idObj = (int) Tools::getValue('id_cms');
                $objectName = 'Cms';
            }
            if (isset($idObj) && isset($objectName)) {
                $this->context->smarty->assign(array(
                    'idObj' => $idObj,
                    'objectName' => $objectName,
                    'zone' => 'displayWrapperBottom',
                ));
                return $this->display(__FILE__, 'views/templates/hook/prettyblocks.tpl');
            }
        }
    }

    public function hookActionCheckoutRender($params)
    {
        $stepTitle = $this->getConfigInMultipleLangs('EVEROPTIONS_TITLE');
        if (!$stepTitle[$this->context->language->id]
            || empty($stepTitle[$this->context->language->id])
        ) {
            return;
        }
        $this->translator = Context::getContext()->getTranslator();

        /** @var CheckoutProcess $process */
        $process = $params['checkoutProcess'];
        $steps = $process->getSteps();

        $everStep = new EverblockCheckoutStep(
            $this->context,
            $this->translator,
            Module::getInstanceByName($this->name)
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
                if (!$checkoutSessionData || empty($checkoutSessionData)) {
                    return;
                }
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
                    return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            $this->getLegacyToolsService()->setLog(
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
            $this->getLegacyToolsService()->setLog(
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
            $this->getLegacyToolsService()->setLog(
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
            $renderer = $this->getShortcodeRenderer();
            $params['template_txt'] = $renderer->render($params['template_txt'], $context, $this);
            $params['template_html'] = $renderer->render($params['template_html'], $context, $this);
            return $params;
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            $this->getLegacyToolsService()->setLog(
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
                            $this->local_path . 'views/templates/admin/pdf.tpl'
                        );
                        $params['templateVars']['{order_options}'] = $optionsHtml;
                    }
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
                $this->getLegacyToolsService()->setLog(
                    $this->name . date('y-m-d'),
                    $e->getMessage()
                );
            }
        }
        return $params;
    }

    public static function getCartSessionDatas($idCart)
    {
        $sql = new DbQuery;
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
        $tabsNumber = max((int) Configuration::get('EVERPS_TAB_NB'), 1);
        $flagsNumber = max((int) Configuration::get('EVERPS_FLAG_NB'), 1);
        
        $everpstabs = [];
        $tabServiceAvailable = true;

        try {
            $tabDomainService = $this->getEverBlockTabDomainService();
            $everpstabs = $this->mapTabsForAdmin(
                $tabDomainService->getTabsForAdmin($productId, (int) $this->context->shop->id)
            );
        } catch (\RuntimeException $exception) {
            $tabServiceAvailable = false;
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        }

        if (!$tabServiceAvailable) {
            $tabProvider = $this->getEverBlockTabProvider();
            if ($tabProvider instanceof EverBlockTabProvider) {
                $everpstabs = $tabProvider->getTabsForAdmin($productId, (int) $this->context->shop->id);
            }
        }

        try {
            $flagDomainService = $this->getEverBlockFlagDomainService();
            $everpsflags = $this->mapFlagsForAdmin(
                $flagDomainService->getFlagsForAdmin($productId, (int) $this->context->shop->id)
            );
        } catch (\RuntimeException $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
            $everpsflags = [];
        }

        $tabsData = [];
        for ($i = 1; $i <= $tabsNumber; $i++) {
            $tabsData[$i] = null;
            foreach ($everpstabs as $everpstab) {
                $tabId = (int) $this->getItemValue($everpstab, 'id_tab');
                if ($tabId === $i) {
                    $tabsData[$i] = $everpstab;
                    break;
                }
            }
        }

        $flagsData = [];
        for ($i = 1; $i <= $flagsNumber; $i++) {
            $flagsData[$i] = null;
            foreach ($everpsflags as $everpsflag) {
                $flagId = (int) $this->getItemValue($everpsflag, 'id_flag');
                if ($flagId === $i) {
                    $flagsData[$i] = $everpsflag;
                    break;
                }
            }
        }

        $everAjaxUrl = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'token' => Tools::getAdminTokenLite('AdminModules')]);

        $this->smarty->assign([
            'tabsData' => $tabsData,
            'flagsData' => $flagsData,
            'default_language' => $this->context->employee->id_lang,
            'ever_languages' => Language::getLanguages(false),
            'ever_ajax_url' => $everAjaxUrl,
            'ever_product_id' => $productId,
            'tabsRange' => range(1, $tabsNumber),
            'flagsRange' => range(1, $flagsNumber),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/productTab.tpl');
    }

    public function hookDisplayAdminProductsMainStepLeftColumnBottom($params)
    {
        if (empty($params['id_product'])) {
            return;
        }
        $modalDomainService = $this->getEverBlockModalDomainService();
        $modalEntity = $modalDomainService->getOrCreateForProduct(
            (int) $params['id_product'],
            (int) $this->context->shop->id
        );

        $contents = [];
        foreach (Language::getLanguages(false) as $language) {
            $translation = $modalEntity->getTranslation((int) $language['id_lang']);
            $contents[(int) $language['id_lang']] = $translation?->getContent();
        }

        $modal = (object) [
            'id_everblock_modal' => $modalEntity->getId(),
            'id_product' => $modalEntity->getProductId(),
            'id_shop' => $modalEntity->getShopId(),
            'file' => $modalEntity->getFile(),
            'content' => $contents,
        ];

        $fileUrl = '';
        $fileName = '';
        if (!empty($modal->file)) {
            $fileUrl = $this->context->link->getBaseLink() . 'img/cms/' . $modal->file;
            $fileName = basename((string) $modal->file);
        }
        $this->smarty->assign([
            'modal' => $modal,
            'modal_file_url' => $fileUrl,
            'modal_file_name' => $fileName,
            'ever_languages' => Language::getLanguages(false),
        ]);
        return $this->display(__FILE__, 'views/templates/admin/productModal.tpl');
    }

    public function hookActionObjectEverBlockClassDeleteAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockClass_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'fetchInstagramImages';
        EverblockCache::cacheDropByPattern($cachePattern);
        $provider = $this->getEverBlockProvider();
        if ($provider instanceof EverBlockProvider) {
            $provider->clearCache();
        }
    }

    public function hookActionObjectEverBlockClassUpdateAfter($params)
    {
        $cachePattern = $this->name . '-id_hook-';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockClass_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'fetchInstagramImages';
        EverblockCache::cacheDropByPattern($cachePattern);
        $provider = $this->getEverBlockProvider();
        if ($provider instanceof EverBlockProvider) {
            if (isset($params['object']->id_hook)) {
                $provider->clearCacheForHook((int) $params['object']->id_hook);
            } else {
                $provider->clearCache();
            }
        }
    }

    public function hookActionObjectEverBlockFlagsDeleteAfter($params)
    {
        $cachePattern = $this->name . 'EverblockFlagsClass_getByIdProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cachePattern = 'EverBlockFlags_getBlocks_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cacheId = $this->name . 'NeedProductFlagsHook_' . (int) $this->context->shop->id;
        EverblockCache::cacheDrop($cacheId);
        try {
            $flagDomainService = $this->getEverBlockFlagDomainService();
            $flag = $params['object'] ?? null;
            $productId = (int) ($flag->id_product ?? 0);
            $shopId = (int) ($flag->id_shop ?? ($this->context->shop->id ?? 0));
            if ($productId > 0 && $shopId > 0) {
                $flagDomainService->clearCacheForProduct($productId, $shopId);
            } elseif ($shopId > 0) {
                $flagDomainService->clearCacheForShop($shopId);
            } else {
                $flagDomainService->clearCache();
            }
        } catch (\RuntimeException $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        }
        $this->updateProductFlagsHook();
    }

    public function hookActionObjectEverBlockFlagsUpdateAfter($params)
    {
        $cachePattern = $this->name . 'EverblockFlagsClass_getByIdProduct_';
        EverblockCache::cacheDropByPattern($cachePattern);
        $cacheId = $this->name . 'NeedProductFlagsHook_' . (int) $this->context->shop->id;
        EverblockCache::cacheDrop($cacheId);
        try {
            $flagDomainService = $this->getEverBlockFlagDomainService();
            $flag = $params['object'] ?? null;
            $productId = (int) ($flag->id_product ?? 0);
            $shopId = (int) ($flag->id_shop ?? ($this->context->shop->id ?? 0));
            if ($productId > 0 && $shopId > 0) {
                $flagDomainService->clearCacheForProduct($productId, $shopId);
            } elseif ($shopId > 0) {
                $flagDomainService->clearCacheForShop($shopId);
            } else {
                $flagDomainService->clearCache();
            }
        } catch (\RuntimeException $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        }
        $this->updateProductFlagsHook();
    }

    public function hookActionObjectEverblockFaqDeleteAfter($params)
    {
        $provider = $this->getEverBlockFaqProvider();
        if ($provider instanceof EverBlockFaqProvider) {
            $faq = $params['object'] ?? null;
            $shopId = (int) ($faq->id_shop ?? ($this->context->shop->id ?? 0));
            if ($shopId > 0) {
                $provider->clearCacheForShop($shopId);
            } else {
                $provider->clearCache();
            }

            if (isset($faq->tag_name) && $faq->tag_name !== '') {
                $provider->clearCacheForTag($shopId, (string) $faq->tag_name);
            }
        }
    }

    public function hookActionObjectEverblockFaqUpdateAfter($params)
    {
        $provider = $this->getEverBlockFaqProvider();
        if ($provider instanceof EverBlockFaqProvider) {
            $faq = $params['object'] ?? null;
            $shopId = (int) ($faq->id_shop ?? ($this->context->shop->id ?? 0));
            if ($shopId > 0) {
                $provider->clearCacheForShop($shopId);
            } else {
                $provider->clearCache();
            }

            if (isset($faq->tag_name) && $faq->tag_name !== '') {
                $provider->clearCacheForTag($shopId, (string) $faq->tag_name);
            }
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
            $tabProvider = $this->getEverBlockTabProvider();
            $tabDomainService = null;
            $existingTabsById = [];

            try {
                $tabDomainService = $this->getEverBlockTabDomainService();
                foreach ($tabDomainService->getTabsForAdmin((int) $params['object']->id, (int) $context->shop->id) as $existingTab) {
                    if ($existingTab instanceof EverBlockTab) {
                        $existingTabsById[$existingTab->getTabId()] = $existingTab;
                    }
                }
            } catch (\RuntimeException $exception) {
                PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
                $tabDomainService = null;
            }

            $tabsRange = range(1, $tabsNumber);
            foreach ($tabsRange as $tab) {
                $translations = [];
                foreach (Language::getLanguages(true) as $language) {
                    $tabTitle = Tools::getValue((int) $tab . '_everblock_title_' . $language['id_lang']);
                    if ($tabTitle && !Validate::isCleanHtml($tabTitle)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Title is not valid'),
                            ]
                        ));
                    }

                    $tabContent = Tools::getValue((int) $tab . '_everblock_content_' . $language['id_lang']);
                    if ($tabContent && !Validate::isCleanHtml($tabContent)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Content is not valid'),
                            ]
                        ));
                    }

                    $translations[$language['id_lang']] = [
                        'title' => $tabTitle,
                        'content' => $tabContent,
                    ];
                }

                if ($tabDomainService instanceof EverBlockTabDomainService) {
                    $tabEntity = $existingTabsById[$tab] ?? new EverBlockTab();
                    $tabEntity->setProductId((int) $params['object']->id);
                    $tabEntity->setShopId((int) $context->shop->id);
                    $tabEntity->setTabId((int) $tab);

                    $finalTranslations = $this->buildTabTranslationsArray($tabEntity);
                    foreach ($translations as $languageId => $data) {
                        $finalTranslations[(int) $languageId] = $data;
                    }

                    $this->applyTranslationsToTab($tabEntity, $finalTranslations, (int) $context->shop->id);
                    $existingTabsById[$tab] = $tabDomainService->save($tabEntity, $finalTranslations);

                    continue;
                }

                if ($tabProvider instanceof EverBlockTabProvider) {
                    $tabProvider->saveTab(
                        (int) $params['object']->id,
                        (int) $context->shop->id,
                        (int) $tab,
                        $translations
                    );
                }
            }

            // Traitement des flags
            try {
                $flagDomainService = $this->getEverBlockFlagDomainService();
            } catch (\RuntimeException $exception) {
                $flagDomainService = null;
                PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
            }
            $flagsNumber = (int) Configuration::get('EVERPS_FLAG_NB');
            if ($flagsNumber < 1) {
                $flagsNumber = 1;
                Configuration::updateValue('EVERPS_FLAG_NB', 1);
            }
            $flagsRange = range(1, $flagsNumber);
            foreach ($flagsRange as $flag) {
                $translations = [];
                foreach (Language::getLanguages(true) as $language) {
                    $flagTitle = Tools::getValue((int) $flag . '_everflag_title_' . $language['id_lang']);
                    if ($flagTitle && !Validate::isCleanHtml($flagTitle)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Title is not valid'),
                            ]
                        ));
                    }

                    $flagContent = Tools::getValue((int) $flag . '_everflag_content_' . $language['id_lang']);
                    if ($flagContent && !Validate::isCleanHtml($flagContent)) {
                        die(json_encode(
                            [
                                'return' => false,
                                'error' => $this->l('Content is not valid'),
                            ]
                        ));
                    }

                    $translations[$language['id_lang']] = [
                        'title' => $flagTitle,
                        'content' => $flagContent,
                    ];
                }

                if ($flagDomainService instanceof EverBlockFlagDomainService) {
                    $flagEntity = new EverBlockFlag();
                    $flagEntity->setProductId((int) $params['object']->id);
                    $flagEntity->setShopId((int) $context->shop->id);
                    $flagEntity->setFlagId((int) $flag);

                    $flagDomainService->save($flagEntity, $translations);
                }
            }

            // Modal management
            $modalDomainService = $this->getEverBlockModalDomainService();
            $modalEntity = $modalDomainService->getOrCreateForProduct(
                (int) $params['object']->id,
                (int) $context->shop->id
            );

            $contents = [];
            foreach (Language::getLanguages(true) as $language) {
                $content = Tools::getValue('everblock_modal_content_' . $language['id_lang']);
                if ($content && !Validate::isCleanHtml($content)) {
                    die(json_encode([
                        'return' => false,
                        'error' => $this->l('Content is not valid'),
                    ]));
                }

                $contents[(int) $language['id_lang']] = (string) $content;
            }

            $existingFile = $modalEntity->getFile();

            if (Tools::getValue('everblock_modal_file_delete')) {
                if (!empty($existingFile)) {
                    $oldFile = _PS_IMG_DIR_ . 'cms/' . $existingFile;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }

                $modalEntity->setFile(null);
                $existingFile = null;
            }

            if (isset($_FILES['everblock_modal_file']) && is_uploaded_file($_FILES['everblock_modal_file']['tmp_name'])) {
                $dir = _PS_IMG_DIR_ . 'cms/everblockmodal/';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                if (!empty($existingFile)) {
                    $oldFile = _PS_IMG_DIR_ . 'cms/' . $existingFile;
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $ext = pathinfo($_FILES['everblock_modal_file']['name'], PATHINFO_EXTENSION);
                $name = uniqid('everblock_modal_') . '.' . $ext;
                if (move_uploaded_file($_FILES['everblock_modal_file']['tmp_name'], $dir . $name)) {
                    $modalEntity->setFile('everblockmodal/' . $name);
                }
            }

            $modalEntity->setProductId((int) $params['object']->id);
            $modalEntity->setShopId((int) $context->shop->id);

            $translations = $modalDomainService->buildTranslations($modalEntity, $contents);
            $modalDomainService->save($modalEntity, $translations);
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            $this->getLegacyToolsService()->setLog(
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
        $shopId = (int) $this->context->shop->id;
        $productId = (int) $params['object']->id;

        $tabDeletionHandled = true;
        try {
            $this->getEverBlockTabDomainService()->deleteByProduct($productId, $shopId);
        } catch (\RuntimeException $exception) {
            $tabDeletionHandled = false;
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        }

        if (!$tabDeletionHandled) {
            $tabProvider = $this->getEverBlockTabProvider();
            if ($tabProvider instanceof EverBlockTabProvider) {
                $tabProvider->deleteTabsByProduct($productId, $shopId);
            }
        }

        try {
            $this->getEverBlockFlagDomainService()->deleteByProduct($productId, $shopId);
        } catch (\RuntimeException $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        }
    }

    public function hookActionProductFlagsModifier($params)
    {
        try {
            $productId = (int) $params['product']['id_product'];
            $shopId = (int) Context::getContext()->shop->id;
            $languageId = (int) Context::getContext()->language->id;
            // Current product flags
            try {
                $everpsflags = $this->getEverBlockFlagDomainService()->getFlags($productId, $shopId, $languageId);
            } catch (\RuntimeException $exception) {
                PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
                $everpsflags = [];
            }

            foreach ($everpsflags as $everpsflag) {
                $flagId = (int) $this->getItemValue($everpsflag, 'id_flag');
                $title = $this->getItemValue($everpsflag, 'title');
                $content = $this->getItemValue($everpsflag, 'content');
                if ($flagId > 0 && !empty($title) && !empty($content)) {
                    $params['flags']['custom-flag-' . $flagId] = [
                        'type' => 'custom-flag ' . $flagId,
                        'label' => strip_tags((string) $content),
                        'module' => $this->name,
                    ];
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
            $this->getLegacyToolsService()->setLog(
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
        $everpstabs = [];
        $tabServiceAvailable = true;

        try {
            $tabDomainService = $this->getEverBlockTabDomainService();
            $everpstabs = $tabDomainService->getTabs(
                (int) $product->id,
                (int) $context->shop->id,
                (int) $context->language->id
            );
        } catch (\RuntimeException $exception) {
            $tabServiceAvailable = false;
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        }

        if (!$tabServiceAvailable) {
            $tabProvider = $this->getEverBlockTabProvider();
            if ($tabProvider instanceof EverBlockTabProvider) {
                $everpstabs = $tabProvider->getTabs(
                    (int) $product->id,
                    (int) $context->shop->id,
                    (int) $context->language->id
                );
            }
        }

        foreach ($everpstabs as $everpstab) {
            $title = (string) $this->getItemValue($everpstab, 'title');
            $content = $this->getItemValue($everpstab, 'content');
            if (!empty($title) || !empty($content)) {
                $tab[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                    ->setTitle($title)
                    ->setContent((string) $content);
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
        if (!empty($title) && !empty($content)) {
            $tab[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                ->setTitle($title)
                ->setContent($content);
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
        $link = new Link();
        if (Validate::isLoadedObject($customer)) {
            $everToken = $this->encrypt($this->name . '/everlogin');
            $this->context->smarty->assign(array(
                'login_customer' => $customer,
                'lastname' => $customer->lastname,
                'firstname' => $customer->firstname,
                'base_uri' => __PS_BASE_URI__,
                'login_link' => $link->getModuleLink(
                    $this->name,
                    'everlogin',
                    [
                        'id_ever_customer' => $customer->id,
                        'evertoken' => $everToken,
                        'ever_id_cart' => Cart::lastNoneOrderedCart($customer->id),
                    ]
                )
            ));
        }
        $this->context->smarty->assign([
            $this->name . '_dir' => $this->_path . 'views/img/',
            'evertoken' => $everToken,
            'base_uri' => __PS_BASE_URI__,
        ]);
        return $this->display(__FILE__, 'views/templates/admin/customerConnect.tpl');
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
            /** @var \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection $bar */
            $bar = $params['actions_bar_buttons_collection'];
            $bar->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-info', ['href' => $connectLink, 'target' => '_blank'], $this->l('Connect to customer account')
                )
            );
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
        // Drop cache if needed
        $blockService = $this->getEverBlockDomainService();
        $blockService->cleanBlocksCacheOnDate(
            (int) $context->language->id,
            (int) $context->shop->id
        );
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
        $cacheId = $this->name
        . '-id_hook-'
        . (int) $id_hook
        . '-controller-'
        . trim(Tools::getValue('controller'))
        . '-hookName-'
        . trim($hookName)
        . '-idObj-'
        . (int) $idObj
        . '-idLang-'
        . (int) $context->language->id
        . '-idShop-'
        . (int) $context->shop->id
        . '-idCurrency-'
        . (int) $context->currency->id
        . '-device-'
        . (int) $context->getDevice()
        . $position;
        if (!EverblockCache::isCacheStored(str_replace('|', '-', $cacheId))) {
            if (isset($context->customer->id) && $context->customer->id) {
                $id_entity = (int) $context->customer->id;
            } else {
                $id_entity = false;
            }
            $everblock = $blockService->getBlocks(
                (int) $id_hook,
                (int) $context->language->id,
                (int) $context->shop->id
            );
            $currentBlock = [];
            foreach ($everblock as $block) {
                if ((bool) $block['modal'] === true
                    && (bool) $this->getLegacyToolsService()->isBot() === true
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
                    if (!in_array((int) Tools::getValue('id_category'), $categories)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only manufacturer pages
                if ((bool) $block['only_manufacturer'] === true
                    && Tools::getValue('controller') === 'manufacturer'
                ) {
                    $manufacturers = json_decode($block['manufacturers']);
                    if (!in_array((int) Tools::getValue('id_manufacturer'), $manufacturers)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only supplier pages
                if ((bool) $block['only_supplier'] === true
                    && Tools::getValue('controller') === 'supplier'
                ) {
                    $suppliers = json_decode($block['suppliers']);
                    if (!in_array((int) Tools::getValue('id_supplier'), $suppliers)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
                }
                // Only CMS category pages
                if ((bool) $block['only_cms_category'] === true
                    && Tools::getValue('controller') === 'cms'
                    && Tools::getValue('id_cms_category')
                ) {
                    $cms_categories = json_decode($block['cms_categories']);
                    if (!in_array((int) Tools::getValue('id_cms_category'), $cms_categories)) {
                        $continue = true;
                    } else {
                        $continue = false;
                    }
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
                $customerGroups = Customer::getGroupsStatic(
                    (int) $id_entity
                );
                $allowedGroups = json_decode($block['groups'], true);
                if (isset($customerGroups)
                    && !empty($allowedGroups)
                    && !array_intersect($allowedGroups, $customerGroups)
                ) {
                    continue;
                }
                if ((bool) $block['obfuscate_link'] === true) {
                    $block['content'] = $this->getLegacyToolsService()->obfuscateText(
                        $block['content']
                    );
                }
                if ((bool) $block['lazyload'] === true) {
                    $block['content'] = $this->getLegacyToolsService()->addLazyLoadToImages(
                        $block['content']
                    );
                }
                if (in_array($method, $this->bypassedControllers)) {
                    $block['content'] = strip_tags($block['content']);
                    $context->smarty->assign([
                        'is_bypassed' => true,
                    ]);
                }
                $currentBlock[] = ['block' => $block];
            }
            Hook::exec(
                'actionRenderBlockBefore',
                [
                    'everhook' => trim($method),
                    $this->name => &$currentBlock,
                    'args' => $args,
                ]
            );
            if ((bool) Module::isInstalled('prettyblocks') === true
                && (bool) Module::isEnabled('prettyblocks') === true
                && (bool) $this->getLegacyToolsService()->moduleDirectoryExists('prettyblocks') === true
            ) {
                $context->smarty->assign([
                    'prettyblocks_installed' => true,
                ]);
            }   
            $context->smarty->assign([
                'everhook' => trim($method),
                $this->name => $currentBlock,
                'args' => $args,
            ]);
            $tpl = $this->display(__FILE__, $this->name . '.tpl');
            $cached = EverblockCache::cacheStore(
                str_replace('|', '-', $cacheId),
                $tpl
            );
            return $tpl;
        }
        return EverblockCache::cacheRetrieve(
            str_replace('|', '-', $cacheId)
        );
    }

    public function hookDisplayHeader()
    {
        if (Tools::getValue('eac')
            && Validate::isInt(Tools::getValue('eac'))
        ) {
            $this->getLegacyToolsService()->addToCartByUrl(
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
                $presentedProducts = $this->getLegacyToolsService()->everPresentProducts(
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
        // Register your CSS file
        $this->context->controller->registerStylesheet(
            'module-' . $this->name . '-css',
            'modules/' . $this->name . '/views/css/' . $this->name . '.css',
            ['media' => 'all', 'priority' => 200]
        );
        $this->context->controller->registerStylesheet(
            'module-' . $this->name . '-quill-css',
            'modules/' . $this->name . '/views/css/quill.css',
            ['media' => 'all', 'priority' => 200]
        );
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
        if ((bool) Configuration::get('EVERBLOCK_USE_SLICK') === true) {
            $this->context->controller->registerStylesheet(
                'module-slick-min-css',
                'modules/' . $this->name . '/views/css/slick-min.css',
                ['media' => 'all', 'priority' => 200]
            );
            $this->context->controller->registerStylesheet(
                'module-slick-theme-min-css',
                'modules/' . $this->name . '/views/css/slick-theme-min.css',
                ['media' => 'all', 'priority' => 200]
            );
            $this->context->controller->registerJavascript(
                'module-slick-min-js',
                'modules/' . $this->name . '/views/js/slick-min.js',
                ['position' => 'bottom', 'priority' => 200]
            );
            Media::addJsDef([
                'everblock_slick' => true,
            ]);
        }
        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-js',
            'modules/' . $this->name . '/views/js/' . $this->name . '.js',
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

    public function hookActionRegisterBlock($params)
    {
        return EverblockPrettyBlocks::getEverPrettyBlocks(
            $this->context,
            $this->getEverBlockProvider()
        );
    }

    public function checkLatestEverModuleVersion(): bool
    {
        $upgrade_link = 'https://upgrade.team-ever.com/upgrade.php?module=' . $this->name . '&version=' . $this->version;
        try {
            $handle = curl_init($upgrade_link);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                curl_close($handle);
                return false;
            }
            curl_close($handle);
            if ($response && Tools::version_compare($response, $this->version, '>')) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Unable to check latest ' . $this->displayName . ' version');
            $this->getLegacyToolsService()->setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
            return false;
        }
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
        $licenceHeader = $this->getLegacyToolsService()->getPhpLicenceHeader();
        $upgradeFunction = $this->getLegacyToolsService()->getUpgradeMethod($this->version);
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
            $this->html .= $this->displayConfirmation($this->l('File has been imported'));
        }
    }

    protected function uploadBlocksFile()
    {
        if (isset($_FILES['BLOCKS_FILE'])
            && isset($_FILES['BLOCKS_FILE']['tmp_name'])
            && !empty($_FILES['BLOCKS_FILE']['tmp_name'])
        ) {
            $filename = $_FILES['BLOCKS_FILE']['name'];
            $exploded_filename = explode('.', $filename);
            $ext = end($exploded_filename);
            if (Tools::strtolower($ext) != 'xlsx') {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['BLOCKS_FILE']['tmp_name'], $tmp_name)
            ) {
                return false;
            }

            copy($tmp_name, _PS_MODULE_DIR_ . $this->name . '/input/blocks.xlsx');
            $this->processBlocksFile();
            $this->html .= $this->displayConfirmation($this->l('File has been imported'));
        }
    }

    protected function uploadSvgFile()
    {
        if (isset($_FILES['CUSTOM_SVG'])
            && isset($_FILES['CUSTOM_SVG']['tmp_name'])
            && !empty($_FILES['CUSTOM_SVG']['tmp_name'])
        ) {
            $filename = $_FILES['CUSTOM_SVG']['name'];
            $exploded_filename = explode('.', $filename);
            $ext = end($exploded_filename);
            if (Tools::strtolower($ext) != 'svg') {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                || !move_uploaded_file($_FILES['CUSTOM_SVG']['tmp_name'], $tmp_name)
            ) {
                return false;
            }
            $content = file_get_contents($tmp_name);
            if ($content === false) {
                return false;
            }
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            if (!$dom->loadXML($content, LIBXML_NONET)) {
                $this->postErrors[] = $this->l('Error : File is not valid.');
                return false;
            }
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            foreach ($xpath->query('//*[translate(local-name(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="script" or translate(local-name(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="foreignobject"]') as $node) {
                $node->parentNode->removeChild($node);
            }
            foreach ($xpath->query('//@*') as $attr) {
                if (stripos($attr->nodeName, 'on') === 0 || preg_match('/^\s*javascript:/i', $attr->nodeValue)) {
                    $attr->ownerElement->removeAttributeNode($attr);
                }
            }

            $clean = $dom->saveXML();
            $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename);
            file_put_contents(_PS_MODULE_DIR_ . $this->name . '/views/img/svg/' . $safeName, $clean);
            unlink($tmp_name);
            $this->html .= $this->displayConfirmation($this->l('File has been uploaded'));
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

    protected function processBlocksFile()
    {
        $blocksFile = _PS_MODULE_DIR_ . $this->name . '/input/blocks.xlsx';
        if (!file_exists($blocksFile)) {
            return;
        }
        $file = new ImportFile($blocksFile);
        $lines = $file->getLines();
        foreach ($lines as $line) {
            $this->createBlockFromExcel($line);
        }
        unlink($blocksFile);
        Tools::clearAllCache();
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
            $tabDomainService = $this->getEverBlockTabDomainService();
            $existingTabs = $tabDomainService->getTabsForAdmin(
                (int) $line['id_product'],
                (int) $id_shop
            );

            $tabEntity = null;
            foreach ($existingTabs as $existingTab) {
                if ($existingTab instanceof EverBlockTab && $existingTab->getTabId() === (int) $line['id_tab']) {
                    $tabEntity = $existingTab;
                    break;
                }
            }

            if (!$tabEntity instanceof EverBlockTab) {
                $tabEntity = new EverBlockTab();
            }

            $tabEntity->setTabId((int) $line['id_tab']);
            $tabEntity->setProductId((int) $line['id_product']);
            $tabEntity->setShopId((int) $id_shop);

            $translations = $this->buildTabTranslationsArray($tabEntity);

            foreach (Language::getLanguages(false, $id_shop) as $lang) {
                $langId = (int) $lang['id_lang'];
                $titleKey = 'title_' . $lang['iso_code'];
                $contentKey = 'content_' . $lang['iso_code'];

                if (!array_key_exists($langId, $translations)) {
                    $translations[$langId] = [
                        'title' => null,
                        'content' => null,
                    ];
                }

                if (isset($line[$titleKey]) && $line[$titleKey] !== '') {
                    $translations[$langId]['title'] = $line[$titleKey];
                }

                if (isset($line[$contentKey]) && $line[$contentKey] !== '') {
                    $translations[$langId]['content'] = $line[$contentKey];
                }
            }

            $this->applyTranslationsToTab($tabEntity, $translations, (int) $id_shop);
            $tabDomainService->save($tabEntity, $translations);
            Tools::clearAllCache();
        } catch (\RuntimeException $exception) {
            PrestaShopLogger::addLog($this->name . ' | ' . $exception->getMessage());
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
            $this->getLegacyToolsService()->setLog(
                $this->name . date('y-m-d'),
                $e->getMessage()
            );
        }
    }

    protected function createBlockFromExcel($line)
    {
        if (!isset($line['name']) || !Validate::isGenericName($line['name'])) {
            $this->postErrors[] = $this->l('Missing or invalid name column');
            return;
        }
        if (!isset($line['hook']) || !Validate::isHookName($line['hook'])) {
            $this->postErrors[] = $this->l('Missing or invalid hook column');
            return;
        }
        $idHook = (int) Hook::getIdByName($line['hook']);
        if (!$idHook) {
            PrestaShopLogger::addLog($this->name . ' | ' . $this->l('Hook not found') . ' : ' . $line['hook']);
            return;
        }
        if (!isset($line['id_shop']) || !Validate::isInt($line['id_shop'])) {
            $this->postErrors[] = $this->l('Missing or invalid id_shop column');
            return;
        }
        if (!isset($line['id_lang']) || !Validate::isInt($line['id_lang'])) {
            $this->postErrors[] = $this->l('Missing or invalid id_lang column');
            return;
        }

        $applicationService = $this->getEverBlockApplicationService();

        $shopId = (int) $line['id_shop'];
        $languageId = (int) $line['id_lang'];

        $position = $this->parseUnsignedIntField($line, 'position');
        $active = $this->parseBooleanField($line, 'active', true);
        $onlyHome = $this->parseBooleanField($line, 'only_home');
        $onlyCategory = $this->parseBooleanField($line, 'only_category');
        $onlyCategoryProduct = $this->parseBooleanField($line, 'only_category_product');
        $onlyManufacturer = $this->parseBooleanField($line, 'only_manufacturer');
        $onlySupplier = $this->parseBooleanField($line, 'only_supplier');
        $onlyCmsCategory = $this->parseBooleanField($line, 'only_cms_category');
        $obfuscateLink = $this->parseBooleanField($line, 'obfuscate_link');
        $addContainer = $this->parseBooleanField($line, 'add_container');
        $lazyload = $this->parseBooleanField($line, 'lazyload');
        $modal = $this->parseBooleanField($line, 'modal');
        $device = $this->parseUnsignedIntField($line, 'device', 0);
        $delay = $this->parseUnsignedIntField($line, 'delay');
        $timeout = $this->parseUnsignedIntField($line, 'timeout');
        $background = $this->parseStringField($line, 'background', true);
        $cssClass = $this->parseStringField($line, 'css_class');
        $dataAttribute = $this->parseStringField($line, 'data_attribute');
        $bootstrapClass = $this->parseStringField($line, 'bootstrap_class');
        $categories = $this->parseCsvField($line, 'categories');
        $groups = $this->parseCsvField($line, 'groups');
        $manufacturers = $this->parseCsvField($line, 'manufacturers');
        $suppliers = $this->parseCsvField($line, 'suppliers');
        $cmsCategories = $this->parseCsvField($line, 'cms_categories');
        $dateStart = $this->parseDateTimeField($line, 'date_start');
        $dateEnd = $this->parseDateTimeField($line, 'date_end');

        $translations = [];
        if (array_key_exists('content', $line) || array_key_exists('custom_code', $line)) {
            $content = isset($line['content']) ? (string) $line['content'] : '';
            $customCode = isset($line['custom_code']) ? (string) $line['custom_code'] : '';
            $translations[] = new EverBlockTranslationCommand(
                $languageId,
                $content,
                $customCode
            );
        }

        $command = new UpsertEverBlockCommand(
            null,
            trim((string) $line['name']),
            $idHook,
            $shopId,
            $onlyHome,
            $onlyCategory,
            $onlyCategoryProduct,
            $onlyManufacturer,
            $onlySupplier,
            $onlyCmsCategory,
            $obfuscateLink,
            $addContainer,
            $lazyload,
            $groups,
            $categories,
            $manufacturers,
            $suppliers,
            $cmsCategories,
            $background,
            $cssClass,
            $dataAttribute,
            $bootstrapClass,
            $position,
            $device,
            $delay,
            $timeout,
            $modal,
            $dateStart,
            $dateEnd,
            $active,
            $translations
        );

        try {
            $applicationService->save($command);
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog($this->name . ' | ' . $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $line
     */
    private function parseBooleanField(array $line, string $key, bool $default = false): bool
    {
        if (!array_key_exists($key, $line)) {
            return $default;
        }

        if (!Validate::isBool($line[$key])) {
            return $default;
        }

        return (bool) $line[$key];
    }

    /**
     * @param array<string, mixed> $line
     */
    private function parseUnsignedIntField(array $line, string $key, ?int $default = null): ?int
    {
        if (!array_key_exists($key, $line) || !Validate::isUnsignedInt($line[$key])) {
            return $default;
        }

        return (int) $line[$key];
    }

    /**
     * @param array<string, mixed> $line
     */
    private function parseStringField(array $line, string $key, bool $isColor = false): string
    {
        if (!array_key_exists($key, $line)) {
            return '';
        }

        $value = trim((string) $line[$key]);

        if ($isColor) {
            return Validate::isColor($value) ? $value : '';
        }

        return Validate::isString($value) ? $value : '';
    }

    /**
     * @param array<string, mixed> $line
     *
     * @return array<int>
     */
    private function parseCsvField(array $line, string $key): array
    {
        if (!array_key_exists($key, $line) || !Validate::isString($line[$key])) {
            return [];
        }

        $raw = trim((string) $line[$key]);

        if ($raw === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $raw));
        $parts = array_filter($parts, static fn ($value) => $value !== '');

        return array_map('intval', $parts);
    }

    /**
     * @param array<string, mixed> $line
     */
    private function parseDateTimeField(array $line, string $key): ?DateTimeImmutable
    {
        if (!array_key_exists($key, $line)) {
            return null;
        }

        $value = trim((string) $line[$key]);

        if ($value === '' || !Validate::isDateFormat($value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
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
            $repository = $this->getEverBlockRepository();
            if (!$repository instanceof EverBlockRepository) {
                $state['content'] = '';
                continue;
            }

            $block = $repository->findById($idEverblock, (int) $this->context->shop->id);
            if (!$block instanceof EverBlock) {
                $state['content'] = '';
                continue;
            }

            $translation = $this->resolveEverBlockTranslation($block, (int) $this->context->language->id);
            $state['content'] = $translation ? (string) $translation->getContent() : '';
        }
        unset($state);

        // Les données retournées sont disponibles dans $block.extra
        return ['states' => $states];
    }

    private function resolveEverBlockTranslation(EverBlock $block, int $languageId): ?EverBlockTranslation
    {
        foreach ($block->getTranslations() as $translation) {
            if ($translation instanceof EverBlockTranslation && $translation->getLanguageId() === $languageId) {
                return $translation;
            }
        }

        return null;
    }

    public function hookBeforeRenderingEverblockCategoryTabs($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['id_category'])) {
                    continue;
                }
                $limit = isset($state['nb_products']) ? (int) $state['nb_products'] : 0;
                if ($limit <= 0) {
                    $limit = (int) Configuration::get('PS_PRODUCTS_PER_PAGE');
                }
                $rawProducts = $this->getLegacyToolsService()->getProductsByCategoryId(
                    (int) $state['id_category'],
                    $limit
                );
                $presented = $this->getLegacyToolsService()->everPresentProducts(
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
                        $this->context
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
            $presented = $this->getLegacyToolsService()->everPresentProducts(
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
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['product'])) {
                    continue;
                }
                $idProduct = $state['product']['id'];
                if ($idProduct <= 0) {
                    continue;
                }
                $presented = $this->getLegacyToolsService()->everPresentProducts([
                    $idProduct,
                ], $this->context);
                if (!empty($presented)) {
                    $products[$key] = reset($presented);
                }
            }
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
                $presented = $this->getLegacyToolsService()->everPresentProducts($ids, $this->context);
                if (!empty($presented)) {
                    $products[$key] = $presented;
                }
            }
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockSpecialEvent($params)
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
                $presented = $this->getLegacyToolsService()->everPresentProducts($ids, $this->context);
                if (!empty($presented)) {
                    $products[$key] = $presented;
                }
            }
        }

        return ['products' => $products];
    }

    public function hookBeforeRenderingEverblockFlashDeals($params)
    {
        $deals = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            $idCustomer = (int) $this->context->customer->id;
            $groupIds = Customer::getGroupsStatic($idCustomer);
            if (empty($groupIds)) {
                $groupIds = [(int) Configuration::get('PS_UNIDENTIFIED_GROUP')];
            }
            $groupIds = array_map('intval', $groupIds);
            $groupList = implode(',', $groupIds);
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['product']['id'])) {
                    continue;
                }
                $idProduct = (int) $state['product']['id'];
                if ($idProduct <= 0) {
                    continue;
                }
                $endDate = Db::getInstance()->getValue(
                    'SELECT MIN(cr.date_to) FROM ' . _DB_PREFIX_ . 'cart_rule cr '
                    . 'INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_product_rule_group crprg ON cr.id_cart_rule = crprg.id_cart_rule '
                    . 'INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_product_rule crpr ON crprg.id_product_rule_group = crpr.id_product_rule_group '
                    . 'INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_product_rule_value crprv ON crpr.id_product_rule = crprv.id_product_rule '
                    . 'LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule_group crg ON cr.id_cart_rule = crg.id_cart_rule '
                    . 'WHERE cr.active = 1 AND cr.date_to > NOW() AND crprv.id_item = ' . $idProduct
                    . ' AND (cr.id_customer = 0 OR cr.id_customer = ' . $idCustomer . ')'
                    . ' AND (crg.id_group IS NULL OR crg.id_group IN (' . $groupList . '))'
                );
                if (!$endDate) {
                    $endDate = Db::getInstance()->getValue(
                        'SELECT MIN(`to`) FROM ' . _DB_PREFIX_ . 'specific_price '
                        . 'WHERE id_product = ' . $idProduct . ' AND `to` > NOW()'
                        . ' AND (id_customer = 0 OR id_customer = ' . $idCustomer . ')'
                        . ' AND (id_group = 0 OR id_group IN (' . $groupList . '))'
                    );
                }
                if (!$endDate) {
                    continue;
                }
                $presented = $this->getLegacyToolsService()->everPresentProducts([
                    $idProduct,
                ], $this->context);
                if (!empty($presented)) {
                    $product = reset($presented);
                    $product['end_date'] = $endDate;
                    $deals[$key] = $product;
                }
            }
        }

        if (empty($deals)) {
            return false;
        }

        return ['deals' => $deals];
    }

    public function hookBeforeRenderingEverblockGuidedSelector($params)
    {
        $states = $params['block']['states'] ?? [];

        foreach ($states as &$state) {
            $question = isset($state['question']) ? trim($state['question']) : '';
            $state['question'] = $question;
            $state['key'] = Tools::link_rewrite($question);

            $answers = [];
            $lines = preg_split("/(\r\n|\r|\n)/", $state['answers'] ?? '');
            foreach ($lines as $line) {
                $parts = explode('|', $line);
                $text = trim($parts[0] ?? '');
                if ($text === '') {
                    continue;
                }
                $link = trim($parts[1] ?? '');
                $answers[] = [
                    'text' => $text,
                    'link' => $link,
                    'value' => Tools::link_rewrite($text),
                ];
            }
            $state['answers'] = $answers;
        }
        unset($state);

        return ['states' => $states];
    }

    public function hookBeforeRenderingEverblockLookbook($params)
    {
        return [];
    }

    public function hookBeforeRenderingEverblockCategoryProducts($params)
    {
        $products = [];
        if (!empty($params['block']['states']) && is_array($params['block']['states'])) {
            foreach ($params['block']['states'] as $key => $state) {
                if (empty($state['category']['id'])) {
                    continue;
                }
                $idCategory = (int) $state['category']['id'];
                if ($idCategory <= 0) {
                    continue;
                }
                $limit = !empty($state['product_limit']) ? (int) $state['product_limit'] : 4;
                $includeSub = !empty($state['include_subcategories']);
                $categoryProducts = $this->getLegacyToolsService()->getProductsByCategoryId(
                    $idCategory,
                    $limit,
                    'id_product',
                    'ASC',
                    $includeSub
                );
                if (!empty($categoryProducts)) {
                    $ids = array_column($categoryProducts, 'id_product');
                    $presented = $this->getLegacyToolsService()->everPresentProducts($ids, $this->context);
                    if (!empty($presented)) {
                        $products[$key] = $presented;
                    }
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

        $modalDomainService = $this->getEverBlockModalDomainService();
        $modalEntity = $modalDomainService->findEntityByProduct(
            $idProduct,
            (int) $this->context->shop->id
        );

        if (!$modalEntity instanceof EverBlockModal) {
            return;
        }
        $idLang = (int) $this->context->language->id;

        $translation = $modalEntity->getTranslation($idLang);
        $content = $translation ? $translation->getContent() : '';

        $hasContent = !empty($content);

        $file = $modalEntity->getFile();
        $hasFile = !empty($file);

        if (!$hasContent && !$hasFile) {
            return;
        }

        $this->smarty->assign([
            'everblock_modal_id' => (int) $modalEntity->getId(),
            'everblock_modal_file' => $file,
            'everblock_modal_content' => $content ?? '',
        ]);

        return $this->fetch('module:everblock/views/templates/hook/modal.tpl');
    }

    /**
     * @param mixed $item
     */
    private function getItemValue($item, string $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        if ($item instanceof \ArrayObject) {
            return $item[$key] ?? null;
        }

        if (is_object($item) && isset($item->$key)) {
            return $item->$key;
        }

        return null;
    }

    /**
     * @param EverBlockTab[] $tabs
     *
     * @return ArrayObject<int, mixed>[]
     */
    private function mapTabsForAdmin(array $tabs): array
    {
        $mapped = [];

        foreach ($tabs as $tab) {
            if (!$tab instanceof EverBlockTab) {
                continue;
            }

            $titles = [];
            $contents = [];

            foreach ($tab->getTranslations() as $translation) {
                if ($translation instanceof EverBlockTabTranslation) {
                    $languageId = $translation->getLanguageId();
                    $titles[$languageId] = $translation->getTitle();
                    $contents[$languageId] = $translation->getContent();
                }
            }

            $mapped[] = new ArrayObject([
                'id_everblock_tabs' => $tab->getId() ?? 0,
                'id_product' => $tab->getProductId(),
                'id_shop' => $tab->getShopId(),
                'id_tab' => $tab->getTabId(),
                'title' => $titles,
                'content' => $contents,
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        return $mapped;
    }

    /**
     * @param EverBlockFlag[] $flags
     *
     * @return ArrayObject<int, mixed>[]
     */
    private function mapFlagsForAdmin(array $flags): array
    {
        $mapped = [];

        foreach ($flags as $flag) {
            if (!$flag instanceof EverBlockFlag) {
                continue;
            }

            $titles = [];
            $contents = [];

            foreach ($flag->getTranslations() as $translation) {
                $languageId = $translation->getLanguageId();
                $titles[$languageId] = $translation->getTitle();
                $contents[$languageId] = $translation->getContent();
            }

            $mapped[] = new ArrayObject([
                'id_everblock_flags' => $flag->getId() ?? 0,
                'id_product' => $flag->getProductId(),
                'id_shop' => $flag->getShopId(),
                'id_flag' => $flag->getFlagId(),
                'title' => $titles,
                'content' => $contents,
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        return $mapped;
    }

    /**
     * @return array<int, array{title: string|null, content: string|null}>
     */
    private function buildTabTranslationsArray(EverBlockTab $tab): array
    {
        $translations = [];

        foreach ($tab->getTranslations() as $translation) {
            if ($translation instanceof EverBlockTabTranslation) {
                $translations[$translation->getLanguageId()] = [
                    'title' => $translation->getTitle(),
                    'content' => $translation->getContent(),
                ];
            }
        }

        return $translations;
    }

    /**
     * @param array<int, array{title: string|null, content: string|null}> $translations
     */
    private function applyTranslationsToTab(EverBlockTab $tab, array $translations, int $shopId): void
    {
        foreach ($translations as $languageId => $data) {
            $languageId = (int) $languageId;
            $translation = $tab->getTranslation($languageId, $shopId);

            if (!$translation instanceof EverBlockTabTranslation) {
                $translation = new EverBlockTabTranslation($tab, $languageId, $shopId);
            }

            $translation->setTitle($data['title'] ?? null);
            $translation->setContent($data['content'] ?? null);
            $tab->addTranslation($translation);
        }
    }

    public function encrypt($data)
    {
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            return Tools::hash($data);
        }

        return Tools::encrypt($data);
    }
}
