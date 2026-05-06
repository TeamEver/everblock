<?php

declare(strict_types=1);

namespace Everblock\Tools\Service;

use Configuration;
use Context;
use Feature;
use Language;
use Store;
use Tools;
use ZipArchive;

final class AdminConfigurationManager
{
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
        ];
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

        $moduleMessages = $module->getAdminConfigurationMessages();

        return [
            'errors' => array_merge($moduleMessages['errors'], $errors),
            'success' => array_merge($moduleMessages['success'], $success),
        ];
    }

    public function processUpdate(\Everblock $module, GithubReleaseChecker $releaseChecker): array
    {
        $errors = [];
        $success = [];

        if (!$releaseChecker->isEverblockUpdateAvailable()) {
            $errors[] = $module->l('Ever Block is already up to date.');
            return ['errors' => $errors, 'success' => $success];
        }

        $release = $releaseChecker->getLatestEverblockRelease();
        if (!$release || empty($release['tag_name'])) {
            $errors[] = $module->l('Unable to fetch the latest release details.');
            return ['errors' => $errors, 'success' => $success];
        }

        $tagName = (string) $release['tag_name'];
        $downloadUrl = 'https://github.com/TeamEver/everblock/archive/refs/tags/' . rawurlencode($tagName) . '.zip';
        $tmpBase = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'everblock_update' . DIRECTORY_SEPARATOR;
        if (!is_dir($tmpBase) && !@mkdir($tmpBase, 0755, true)) {
            $errors[] = $module->l('Unable to prepare the temporary update directory.');
            return ['errors' => $errors, 'success' => $success];
        }

        $tempFile = tempnam($tmpBase, 'everblock_');
        if (!$tempFile) {
            $errors[] = $module->l('Unable to create the update archive.');
            return ['errors' => $errors, 'success' => $success];
        }
        $zipPath = $tempFile . '.zip';
        @rename($tempFile, $zipPath);

        if (!Tools::copy($downloadUrl, $zipPath)) {
            @unlink($zipPath);
            $errors[] = $module->l('Unable to download the latest release archive.');
            return ['errors' => $errors, 'success' => $success];
        }

        $extractDir = $tmpBase . 'extract_' . uniqid('', true) . DIRECTORY_SEPARATOR;
        if (!@mkdir($extractDir, 0755, true)) {
            @unlink($zipPath);
            $errors[] = $module->l('Unable to prepare the extraction directory.');
            return ['errors' => $errors, 'success' => $success];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->removeDirectory($extractDir);
            @unlink($zipPath);
            $errors[] = $module->l('Unable to open the downloaded archive.');
            return ['errors' => $errors, 'success' => $success];
        }
        $zip->extractTo($extractDir);
        $zip->close();
        @unlink($zipPath);

        $sourceDir = $this->locateEverblockSource($extractDir);
        if (!$sourceDir) {
            $this->removeDirectory($extractDir);
            $errors[] = $module->l('Unable to locate the module files in the archive.');
            return ['errors' => $errors, 'success' => $success];
        }

        if (!$this->replaceModuleFiles($sourceDir, $module->name)) {
            $this->removeDirectory($extractDir);
            $errors[] = $module->l('Unable to install the latest release.');
            return ['errors' => $errors, 'success' => $success];
        }

        $this->removeDirectory($extractDir);
        $success[] = $module->l('Ever Block has been updated. The page will now reload.');

        return ['errors' => $errors, 'success' => $success];
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

    private function locateEverblockSource(string $extractDir)
    {
        if (file_exists($extractDir . 'everblock.php')) {
            return $extractDir;
        }

        $entries = @scandir($extractDir);
        if (!is_array($entries)) {
            return null;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $candidate = $extractDir . $entry . DIRECTORY_SEPARATOR;
            if (is_dir($candidate) && file_exists($candidate . 'everblock.php')) {
                return $candidate;
            }
        }

        return null;
    }

    private function replaceModuleFiles(string $sourceDir, string $moduleName): bool
    {
        $targetDir = rtrim(_PS_MODULE_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $moduleName;
        $backupDir = $targetDir . '_backup_' . date('YmdHis');

        if (@rename($targetDir, $backupDir)) {
            if (@rename($sourceDir, $targetDir)) {
                $this->removeDirectory($backupDir);
                return true;
            }
            @rename($backupDir, $targetDir);
            return false;
        }

        return $this->recursiveCopy($sourceDir, $targetDir);
    }

    private function recursiveCopy(string $sourceDir, string $targetDir): bool
    {
        if (!is_dir($sourceDir)) {
            return false;
        }
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) {
            return false;
        }

        $entries = scandir($sourceDir);
        if (!is_array($entries)) {
            return false;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $entry;
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($sourcePath)) {
                if (!$this->recursiveCopy($sourcePath, $targetPath)) {
                    return false;
                }
                continue;
            }
            if (!@copy($sourcePath, $targetPath)) {
                return false;
            }
        }

        return true;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $entries = scandir($dir);
        if (!is_array($entries)) {
            return;
        }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
