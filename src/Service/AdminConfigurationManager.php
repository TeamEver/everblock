<?php

declare(strict_types=1);

namespace Everblock\Tools\Service;

use Configuration;
use Context;
use Feature;
use Language;
use Store;
use Tools;

final class AdminConfigurationManager
{
    public const PERMISSION_CREATE = 'create';
    public const PERMISSION_UPDATE = 'update';
    public const PERMISSION_DELETE = 'delete';

    /**
     * Native PrestaShop permission required by each mutating operation of the configuration
     * page, mapped on what the operation really does. Keys are the submit names tested with
     * Tools::isSubmit() in processRequest(), so this map and the execution stay in sync.
     *
     * The main "Save" button is handled apart because its name embeds the module name.
     *
     * @var array<string, string>
     */
    private const OPERATION_PERMISSIONS = [
        // Destructive: removes files, truncates tables or overwrites existing data.
        'deleteEVERBLOCK_MARKER_ICON' => self::PERMISSION_DELETE,
        'deleteEVERWP_POSTS_BG_IMAGE' => self::PERMISSION_DELETE,
        'submitEmptyLogs' => self::PERMISSION_DELETE,
        'submitDropUnusedLangs' => self::PERMISSION_DELETE,
        'submitRestoreBackup' => self::PERMISSION_DELETE,
        // Replaces the whole module directory with a GitHub release: the most destructive one.
        'submitEverblockUpdate' => self::PERMISSION_DELETE,
        // Creates new catalog objects.
        'submitCreateProduct' => self::PERMISSION_CREATE,
        'submitUploadTabsFile' => self::PERMISSION_CREATE,
        // Writes configuration, files on disk or shop content.
        'submitEmptyCache' => self::PERMISSION_UPDATE,
        'submitSecureModuleFoldersWithApache' => self::PERMISSION_UPDATE,
        'submitBackupBlocks' => self::PERMISSION_UPDATE,
        'submitMigrateUrls' => self::PERMISSION_UPDATE,
        'submitGenerateModuleTranslation' => self::PERMISSION_UPDATE,
        'submitImportModuleTranslation' => self::PERMISSION_UPDATE,
        'submitUploadModuleTranslation' => self::PERMISSION_UPDATE,
        'submitRegenerateCronToken' => self::PERMISSION_UPDATE,
    ];

    private ?ModuleTranslationManager $translationManager;

    public function __construct(?ModuleTranslationManager $translationManager = null)
    {
        $this->translationManager = $translationManager;
    }

    private function getTranslationManager(): ModuleTranslationManager
    {
        if ($this->translationManager === null) {
            $this->translationManager = new ModuleTranslationManager();
        }

        return $this->translationManager;
    }

    public function getFormData(\Everblock $module): array
    {
        $data = $module->getAdminConfigurationLegacyFormValues();
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $langId = (int) $language['id_lang'];
            $data['EVEROPTIONS_TITLE_' . $langId] = $data['EVEROPTIONS_TITLE'][$langId] ?? '';
            $data['EVER_TAB_TITLE_' . $langId] = $data['EVER_TAB_TITLE'][$langId] ?? '';
            $data['EVER_TAB_CONTENT_' . $langId] = $data['EVER_TAB_CONTENT'][$langId] ?? '';
        }

        $featuresAsFlags = json_decode((string) Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
        $data['EVERPS_FEATURES_AS_FLAGS'] = is_array($featuresAsFlags) ? array_map('intval', $featuresAsFlags) : [];
        $data['EVERBLOCK_TRANSLATION_TARGET_LANG'] = (string) (Context::getContext()->language->iso_code ?? ($languages[0]['iso_code'] ?? 'en'));
        $data['EVERBLOCK_TRANSLATION_IMPORT_FILE'] = '';
        $data['EVERBLOCK_TRANSLATION_UPLOAD_FILE'] = '';
        unset(
            $data['EVEROPTIONS_TITLE'],
            $data['EVER_TAB_TITLE'],
            $data['EVER_TAB_CONTENT'],
            $data['EVERPS_FEATURES_AS_FLAGS[]']
        );

        foreach ([
            'EVERBLOCK_LOAD_FRONT_CSS',
            'EVERBLOCK_USE_OBF',
            'EVERBLOCK_TINYMCE',
            'EVERINSTA_SHOW_CAPTION',
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING',
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR',
            'EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA',
            'EVERBLOCK_STORELOCATOR_TOGGLE',
            'EVERBLOCK_SOLDOUT_FLAG',
        ] as $booleanField) {
            $defaultValue = $booleanField === 'EVERBLOCK_LOAD_FRONT_CSS' ? 1 : 0;
            $data[$booleanField] = (int) ($data[$booleanField] ?? $defaultValue);
        }

        return $data;
    }

    public function getViewContext(\Everblock $module): array
    {
        $context = Context::getContext();
        $idLang = (int) $context->language->id;
        $features = Feature::getFeatures($idLang);
        $featureChoices = [];
        $featureNames = [];
        foreach ($features as $feature) {
            $featureId = (int) $feature['id_feature'];
            $featureName = (string) $feature['name'];
            $featureChoices[$featureName] = $featureId;
            $featureNames[$featureId] = $featureName;
        }

        $stores = Store::getStores($idLang);
        $holidays = EverblockTools::getFrenchHolidays((int) date('Y'));
        $bannedFeatures = json_decode((string) Configuration::get('EVERPS_FEATURES_AS_FLAGS'), true);
        if (!is_array($bannedFeatures)) {
            $bannedFeatures = [];
        }
        $bannedFeatures = array_map('intval', $bannedFeatures);

        $imageBaseUrl = $context->link->getBaseLink(null, null) . 'modules/' . $module->name . '/views/img/';
        $wordpressBackground = Configuration::get('EVERWP_POSTS_BG_IMAGE');
        $markerIcon = Configuration::get('EVERBLOCK_MARKER_ICON');

        $cronLinks = [];
        $cronToken = $module->getAdminConfigurationCronToken();
        foreach ($module->getAdminConfigurationAllowedActions() as $action) {
            $cronLinks[$action] = $context->link->getModuleLink(
                $module->name,
                'cron',
                [
                    'action' => $action,
                    'evertoken' => $cronToken,
                ]
            );
        }

        return [
            'banned_features' => $bannedFeatures,
            'cron_links' => $cronLinks,
            'current_images' => [
                'EVERWP_POSTS_BG_IMAGE' => $wordpressBackground ? $imageBaseUrl . $wordpressBackground : null,
                'EVERBLOCK_MARKER_ICON' => $markerIcon ? $imageBaseUrl . $markerIcon : null,
            ],
            'feature_choices' => $featureChoices,
            'feature_names' => $featureNames,
            'has_instagram_token' => (bool) Configuration::get('EVERINSTA_ACCESS_TOKEN'),
            'has_stores' => !empty($stores),
            'holidays' => $holidays,
            'languages' => Language::getLanguages(false),
            'module_version' => $module->version,
            'stats' => $module->getAdminConfigurationModuleStatistics(),
            'stores' => $stores,
            'translation_files' => $this->getTranslationManager()->getAvailableTranslationFiles($module),
        ];
    }

    /**
     * Native PrestaShop permissions required by the operations submitted in the current request.
     *
     * Detection uses Tools::isSubmit(), exactly like processRequest(), so an operation can never
     * be executed without its permission having been evaluated — including when the submit name
     * is passed in the query string, which Tools::isSubmit() also honours.
     *
     * @return string[] Distinct permissions, 'update' as the baseline for any other write
     */
    public function getRequiredPermissions(\Everblock $module): array
    {
        $permissions = [];

        foreach (self::OPERATION_PERMISSIONS as $submitName => $permission) {
            if (Tools::isSubmit($submitName)) {
                $permissions[$permission] = $permission;
            }
        }

        if (Tools::isSubmit('submit' . $module->name . 'Module')) {
            $permissions[self::PERMISSION_UPDATE] = self::PERMISSION_UPDATE;
        }

        // Any other write reaching processRequest() still requires at least the update right.
        if ($permissions === []) {
            $permissions[self::PERMISSION_UPDATE] = self::PERMISSION_UPDATE;
        }

        return array_values($permissions);
    }

    public function processRequest(\Everblock $module): array
    {
        $module->prepareAdminConfigurationEnvironment();
        $module->resetAdminConfigurationMessages();
        $errors = [];
        $success = [];

        if (Tools::isSubmit('deleteEVERBLOCK_MARKER_ICON')) {
            $icon = Configuration::get('EVERBLOCK_MARKER_ICON');
            if ($icon) {
                $path = _PS_MODULE_DIR_ . $module->name . '/views/img/' . $icon;
                if (file_exists($path)) {
                    @unlink($path);
                }
                Configuration::deleteByName('EVERBLOCK_MARKER_ICON');
                $success[] = $module->l('Marker icon removed.');
            }
        }

        if (Tools::isSubmit('deleteEVERWP_POSTS_BG_IMAGE')) {
            $background = Configuration::get('EVERWP_POSTS_BG_IMAGE');
            if ($background) {
                $path = _PS_MODULE_DIR_ . $module->name . '/views/img/' . $background;
                if (file_exists($path)) {
                    @unlink($path);
                }
                Configuration::deleteByName('EVERWP_POSTS_BG_IMAGE');
                $success[] = $module->l('WordPress background image removed.');
            }
        }

        if (Tools::isSubmit('submit' . $module->name . 'Module')) {
            if (!isset($_POST['EVERPS_FEATURES_AS_FLAGS'])) {
                $_POST['EVERPS_FEATURES_AS_FLAGS'] = [];
            }
            $module->runAdminConfigurationPostValidation();
            if (!count($module->getAdminConfigurationMessages()['errors'])) {
                $module->runAdminConfigurationPostProcess();
            }
        }

        if (Tools::isSubmit('submitUploadTabsFile')) {
            $module->runAdminConfigurationTabsUpload();
        }
        if (Tools::isSubmit('submitEmptyCache')) {
            $module->runAdminConfigurationCacheCleanup();
        }
        if (Tools::isSubmit('submitRegenerateCronToken')) {
            $this->appendBooleanResult(
                $module->regenerateAdminConfigurationCronToken() !== '',
                $module->l('A new cron token has been generated. Update your scheduled tasks with the URLs below.'),
                $module->l('Unable to generate a new cron token.'),
                $errors,
                $success
            );
        }
        if (Tools::isSubmit('submitEmptyLogs')) {
            $purged = EverblockTools::purgeNativePrestashopLogsTable();
            $this->appendBooleanResult(
                $purged,
                $module->l('Log tables emptied'),
                $module->l('Log tables NOT emptied'),
                $errors,
                $success
            );
        }
        if (Tools::isSubmit('submitDropUnusedLangs')) {
            $this->appendToolResult(EverblockTools::dropUnusedLangs(), $errors, $success);
        }
        if (Tools::isSubmit('submitSecureModuleFoldersWithApache')) {
            $this->appendToolResult(EverblockTools::secureModuleFoldersWithApache(), $errors, $success);
        }
        if (Tools::isSubmit('submitBackupBlocks')) {
            $backuped = EverblockTools::exportModuleTablesSQL();
            $configBackuped = EverblockTools::exportConfigurationSQL();
            $this->appendBooleanResult(
                $backuped && $configBackuped,
                $module->l('Backup done'),
                $module->l('Backup failed'),
                $errors,
                $success
            );
        }
        if (Tools::isSubmit('submitRestoreBackup')) {
            $restored = EverblockTools::restoreModuleTablesFromBackup();
            $this->appendBooleanResult(
                $restored,
                $module->l('Restore done'),
                $module->l('Restore failed'),
                $errors,
                $success
            );
        }
        if (Tools::isSubmit('submitCreateProduct')) {
            $created = EverblockTools::generateProducts((int) Context::getContext()->shop->id);
            $this->appendBooleanResult(
                $created,
                $module->l('Products creation done'),
                $module->l('Products creation failed'),
                $errors,
                $success
            );
        }
        if (Tools::isSubmit('submitMigrateUrls') && Tools::getValue('EVERPS_OLD_URL') && Tools::getValue('EVERPS_NEW_URL')) {
            $this->appendToolResult(EverblockTools::migrateUrls(
                Tools::getValue('EVERPS_OLD_URL'),
                Tools::getValue('EVERPS_NEW_URL'),
                (int) Context::getContext()->shop->id
            ), $errors, $success);
        }
        if (Tools::isSubmit('submitEverblockUpdate')) {
            $this->appendMessages($this->processUpdate($module, new GithubReleaseChecker($module->version)), $errors, $success);
        }
        if (Tools::isSubmit('submitGenerateModuleTranslation')) {
            $this->appendTranslationResult(
                $this->getTranslationManager()->generateWithGoogleTranslate(
                    $module,
                    (string) Tools::getValue('EVERBLOCK_TRANSLATION_TARGET_LANG')
                ),
                $errors,
                $success
            );
        }
        if (Tools::isSubmit('submitImportModuleTranslation')) {
            $this->appendTranslationResult(
                $this->getTranslationManager()->importExistingTranslation(
                    $module,
                    (string) Tools::getValue('EVERBLOCK_TRANSLATION_TARGET_LANG'),
                    (string) Tools::getValue('EVERBLOCK_TRANSLATION_IMPORT_FILE')
                ),
                $errors,
                $success
            );
        }
        if (Tools::isSubmit('submitUploadModuleTranslation')) {
            $this->appendTranslationResult(
                $this->getTranslationManager()->uploadAndImportTranslation(
                    $module,
                    (string) Tools::getValue('EVERBLOCK_TRANSLATION_TARGET_LANG'),
                    $_FILES['EVERBLOCK_TRANSLATION_UPLOAD_FILE'] ?? []
                ),
                $errors,
                $success
            );
        }

        $moduleMessages = $module->getAdminConfigurationMessages();

        return [
            'errors' => array_merge($moduleMessages['errors'], $errors),
            'success' => array_merge($moduleMessages['success'], $success),
        ];
    }

    public function processUpdate(\Everblock $module, GithubReleaseChecker $releaseChecker): array
    {
        // Même procédure que la commande everblock:update (sauvegarde dans var/, reprise des
        // fichiers générés par la boutique, retour arrière en cas d'échec).
        $result = (new ModuleUpdater($module, $releaseChecker))->update();
        if ($result['errors'] === []) {
            $result['success'][] = $module->l('Ever Block has been updated. The page will now reload.');
        }

        return ['errors' => $result['errors'], 'success' => $result['success']];
    }

    private function appendToolResult($result, array &$errors, array &$success): void
    {
        if (!is_array($result)) {
            return;
        }

        foreach ($result['postErrors'] ?? [] as $error) {
            $errors[] = $error;
        }
        foreach ($result['querySuccess'] ?? [] as $message) {
            $success[] = $message;
        }
    }

    private function appendBooleanResult(bool $ok, string $successMessage, string $errorMessage, array &$errors, array &$success): void
    {
        if ($ok) {
            $success[] = $successMessage;
            return;
        }

        $errors[] = $errorMessage;
    }

    private function appendMessages(array $messages, array &$errors, array &$success): void
    {
        foreach ($messages['errors'] ?? [] as $error) {
            $errors[] = $error;
        }
        foreach ($messages['success'] ?? [] as $message) {
            $success[] = $message;
        }
    }

    private function appendTranslationResult(array $result, array &$errors, array &$success): void
    {
        if (!empty($result['success'])) {
            $message = (string) ($result['message'] ?? '');
            if (isset($result['imported'])) {
                $message .= ' ' . sprintf('%d translations saved.', (int) $result['imported']);
            }
            $success[] = trim($message);

            return;
        }

        $errors[] = (string) ($result['message'] ?? 'Translation action failed.');
    }
}
