<?php

declare(strict_types=1);

namespace Everblock\Tools\Service;

use Db;
use Language;
use Tools;

final class ModuleTranslationManager
{
    private const MODULE_NAME = 'everblock';

    public function getAvailableTranslationFiles(\Everblock $module): array
    {
        $dir = $this->translationsDir($module);
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        foreach (glob($dir . '/*.php') ?: [] as $path) {
            $basename = basename($path);
            if ($basename === 'index.php') {
                continue;
            }
            $isoCode = $this->isoFromFileName($basename);
            if ($isoCode === null) {
                continue;
            }
            $files[$basename] = [
                'name' => $basename,
                'iso_code' => $isoCode,
                'size' => filesize($path) ?: 0,
                'updated_at' => filemtime($path) ?: null,
            ];
        }

        ksort($files);

        return array_values($files);
    }

    public function generateWithGoogleTranslate(\Everblock $module, string $targetIso): array
    {
        $targetIso = $this->normalizeIso($targetIso);
        $language = $this->getLanguageByIso($targetIso);
        if ($language === null) {
            return [
                'success' => false,
                'message' => $module->l('Selected language is not available in this shop.'),
            ];
        }

        $sourceMap = $this->getSourceTranslations($module);
        if (empty($sourceMap)) {
            return [
                'success' => false,
                'message' => $module->l('No module translation source could be found.'),
            ];
        }

        $translatedMap = [];
        foreach ($sourceMap as $legacyKey => $source) {
            $translatedMap[$legacyKey] = $this->translateText((string) $source, $targetIso);
        }

        $fileName = $targetIso . '.php';
        $path = $this->writeTranslationFile($module, $fileName, $translatedMap);
        $imported = $this->importFileToDatabase($module, $path, (int) $language['id_lang'], $sourceMap);

        return [
            'success' => true,
            'file' => basename($path),
            'imported' => $imported,
            'message' => sprintf(
                $module->l('Translation file %s has been generated and imported.'),
                basename($path)
            ),
        ];
    }

    public function importExistingTranslation(\Everblock $module, string $targetIso, string $fileName): array
    {
        $targetIso = $this->normalizeIso($targetIso);
        $language = $this->getLanguageByIso($targetIso);
        if ($language === null) {
            return [
                'success' => false,
                'message' => $module->l('Selected language is not available in this shop.'),
            ];
        }

        $path = $this->resolveTranslationFile($module, $fileName);
        if ($path === null) {
            return [
                'success' => false,
                'message' => $module->l('Translation file not found.'),
            ];
        }

        $imported = $this->importFileToDatabase($module, $path, (int) $language['id_lang'], $this->getSourceTranslations($module));

        return [
            'success' => true,
            'file' => basename($path),
            'imported' => $imported,
            'message' => sprintf(
                $module->l('Translation file %s has been imported.'),
                basename($path)
            ),
        ];
    }

    public function uploadAndImportTranslation(\Everblock $module, string $targetIso, array $uploadedFile): array
    {
        $targetIso = $this->normalizeIso($targetIso);
        $language = $this->getLanguageByIso($targetIso);
        if ($language === null) {
            return [
                'success' => false,
                'message' => $module->l('Selected language is not available in this shop.'),
            ];
        }

        if (empty($uploadedFile['tmp_name']) || !is_uploaded_file($uploadedFile['tmp_name'])) {
            return [
                'success' => false,
                'message' => $module->l('No translation file was uploaded.'),
            ];
        }

        if (!empty($uploadedFile['error']) && $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => $module->l('Unable to upload the translation file.'),
            ];
        }

        $extension = Tools::strtolower((string) pathinfo((string) $uploadedFile['name'], PATHINFO_EXTENSION));
        if ($extension !== 'php') {
            return [
                'success' => false,
                'message' => $module->l('Translation file must be a PHP file.'),
            ];
        }

        $content = file_get_contents($uploadedFile['tmp_name']);
        if ($content === false) {
            return [
                'success' => false,
                'message' => $module->l('Unable to read the uploaded translation file.'),
            ];
        }

        $translations = $this->parseLegacyTranslations($content);
        if (empty($translations)) {
            return [
                'success' => false,
                'message' => $module->l('Uploaded file does not contain valid module translations.'),
            ];
        }

        $fileName = $targetIso . '.php';
        $path = $this->writeTranslationFile($module, $fileName, $translations);
        $imported = $this->importFileToDatabase($module, $path, (int) $language['id_lang'], $this->getSourceTranslations($module));

        return [
            'success' => true,
            'file' => basename($path),
            'imported' => $imported,
            'message' => sprintf(
                $module->l('Translation file %s has been uploaded and imported.'),
                basename($path)
            ),
        ];
    }

    public function resolveTranslationFile(\Everblock $module, string $fileName): ?string
    {
        $fileName = basename($fileName);
        if ($fileName === '' || $fileName === 'index.php' || !preg_match('/^(modern_)?[a-z]{2,3}\.php$/', $fileName)) {
            return null;
        }

        $path = $this->translationsDir($module) . '/' . $fileName;
        if (!is_file($path)) {
            return null;
        }

        return $path;
    }

    private function getSourceTranslations(\Everblock $module): array
    {
        $sourceMap = $this->loadSourceFileTranslations($module);
        foreach ($this->extractModuleSources($module) as $entry) {
            $legacyKey = $this->legacyKeyForSource($entry['source'], $entry['domain']);
            if (!isset($sourceMap[$legacyKey])) {
                $sourceMap[$legacyKey] = $entry['source'];
            }
        }

        return $sourceMap;
    }

    private function loadSourceFileTranslations(\Everblock $module): array
    {
        $dir = $this->translationsDir($module);
        $candidates = ['en.php', 'gb.php', 'us.php', 'modern_gb.php', 'modern_en.php'];
        foreach ($candidates as $candidate) {
            $path = $dir . '/' . $candidate;
            if (is_file($path)) {
                $translations = $this->parseLegacyTranslations((string) file_get_contents($path));
                if (!empty($translations)) {
                    return $translations;
                }
            }
        }

        return [];
    }

    private function extractModuleSources(\Everblock $module): array
    {
        $sources = [];
        foreach ($this->moduleFiles($module) as $path) {
            $content = file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $defaultDomain = str_contains(str_replace('\\', '/', $path), '/views/templates/')
                ? 'Modules.Everblock.Front'
                : 'Modules.Everblock.Admin';
            if (basename($path) === 'everblock.php') {
                $defaultDomain = 'Modules.Everblock.Everblock';
            }

            foreach ($this->extractQuotedMatches('/(?:->|::)l\s*\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s', $content) as $source) {
                $sources[$defaultDomain . "\n" . $source] = ['domain' => $defaultDomain, 'source' => $source];
            }
            foreach ($this->extractQuotedMatches('/transAdmin\s*\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s', $content) as $source) {
                $sources['Modules.Everblock.Admin' . "\n" . $source] = ['domain' => 'Modules.Everblock.Admin', 'source' => $source];
            }
            foreach ($this->extractTwigTranslations($content) as $entry) {
                $sources[$entry['domain'] . "\n" . $entry['source']] = $entry;
            }
            foreach ($this->extractSmartyTranslations($content) as $entry) {
                $sources[$entry['domain'] . "\n" . $entry['source']] = $entry;
            }
            foreach ($this->extractFormStrings($content) as $source) {
                $sources['Modules.Everblock.Admin' . "\n" . $source] = ['domain' => 'Modules.Everblock.Admin', 'source' => $source];
            }
        }

        return array_values(array_filter($sources, static function (array $entry): bool {
            return trim($entry['source']) !== '' && strlen($entry['source']) < 300;
        }));
    }

    private function moduleFiles(\Everblock $module): array
    {
        $root = rtrim(_PS_MODULE_DIR_, '/\\') . DIRECTORY_SEPARATOR . $module->name;
        $files = [$root . DIRECTORY_SEPARATOR . 'everblock.php'];
        foreach (['src', 'templates', 'views/templates'] as $relativeDir) {
            $dir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $fileInfo) {
                if (!$fileInfo instanceof \SplFileInfo || !$fileInfo->isFile()) {
                    continue;
                }
                $extension = $fileInfo->getExtension();
                if (in_array($extension, ['php', 'twig', 'tpl'], true)) {
                    $files[] = $fileInfo->getPathname();
                }
            }
        }

        return array_values(array_unique(array_filter($files, 'is_file')));
    }

    private function extractQuotedMatches(string $pattern, string $content): array
    {
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        $values = [];
        foreach ($matches as $match) {
            $values[] = $this->decodePhpString($match[2] ?? '', $match[1] ?? "'");
        }

        return $values;
    }

    private function extractTwigTranslations(string $content): array
    {
        preg_match_all('/([\'"])((?:\\\\.|(?!\1).)*)\1\s*\|\s*trans\s*\([^)]*?([\'"])Modules\.Everblock\.([A-Za-z0-9_]+)\3/s', $content, $matches, PREG_SET_ORDER);
        $entries = [];
        foreach ($matches as $match) {
            $domain = 'Modules.Everblock.' . $match[4];
            $entries[] = [
                'domain' => $domain,
                'source' => $this->decodePhpString($match[2], $match[1]),
            ];
        }

        return $entries;
    }

    private function extractSmartyTranslations(string $content): array
    {
        preg_match_all('/\{l\s+[^}]*s=([\'"])((?:\\\\.|(?!\1).)*)\1[^}]*d=([\'"])Modules\.Everblock\.([A-Za-z0-9_]+)\3[^}]*\}/s', $content, $matches, PREG_SET_ORDER);
        $entries = [];
        foreach ($matches as $match) {
            $entries[] = [
                'domain' => 'Modules.Everblock.' . $match[4],
                'source' => $this->decodePhpString($match[2], $match[1]),
            ];
        }

        return $entries;
    }

    private function extractFormStrings(string $content): array
    {
        preg_match_all('/[\'"](?:label|help|title|description|placeholder|data-everblock-placeholder)[\'"]\s*=>\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s', $content, $matches, PREG_SET_ORDER);
        $values = [];
        foreach ($matches as $match) {
            $values[] = $this->decodePhpString($match[2], $match[1]);
        }

        return $values;
    }

    private function translateText(string $source, string $targetIso): string
    {
        $source = trim($source);
        if ($source === '' || in_array($targetIso, ['en', 'gb', 'us'], true)) {
            return $source;
        }

        $placeholders = [];
        $protectedSource = preg_replace_callback('/(%(?:\d+\$)?[bcdeEfFgGosuxX]|%[A-Za-z0-9_]+%|\{[A-Za-z0-9_.-]+\})/', static function (array $match) use (&$placeholders): string {
            $token = ' __EBLOCK_PLACEHOLDER_' . count($placeholders) . '__ ';
            $placeholders[$token] = $match[0];

            return $token;
        }, $source);

        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl='
            . rawurlencode($this->googleTargetIso($targetIso))
            . '&dt=t&q='
            . rawurlencode((string) $protectedSource);
        $response = $this->fetchUrl($url);
        if ($response === false) {
            return $source;
        }

        $payload = json_decode($response, true);
        if (!is_array($payload) || !isset($payload[0]) || !is_array($payload[0])) {
            return $source;
        }

        $translated = '';
        foreach ($payload[0] as $segment) {
            if (is_array($segment) && isset($segment[0])) {
                $translated .= (string) $segment[0];
            }
        }

        $translated = trim($translated);
        if ($translated === '') {
            return $source;
        }

        foreach ($placeholders as $token => $placeholder) {
            $translated = str_replace(trim($token), $placeholder, $translated);
            $translated = str_replace($token, $placeholder, $translated);
        }

        return $translated;
    }

    private function fetchUrl(string $url)
    {
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            if ($curl !== false) {
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($curl, CURLOPT_TIMEOUT, 20);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                $response = curl_exec($curl);
                $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
                curl_close($curl);

                if ($response !== false && $statusCode < 400) {
                    return $response;
                }
            }
        }

        if (ini_get('allow_url_fopen')) {
            $context = stream_context_create([
                'http' => ['timeout' => 20],
                'ssl' => ['verify_peer' => true],
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response !== false) {
                return $response;
            }
        }

        return false;
    }

    private function googleTargetIso(string $iso): string
    {
        return $iso === 'gb' ? 'en' : $iso;
    }

    private function importFileToDatabase(\Everblock $module, string $path, int $idLang, array $sourceMap): int
    {
        $translations = $this->parseLegacyTranslations((string) file_get_contents($path));
        $count = 0;
        foreach ($translations as $legacyKey => $translatedValue) {
            $domain = $this->buildDomainFromLegacyKey($legacyKey);
            if ($domain === null || !isset($sourceMap[$legacyKey])) {
                continue;
            }

            $source = (string) $sourceMap[$legacyKey];
            $translation = (string) $translatedValue;
            if ($source === '' || $translation === '') {
                continue;
            }

            $this->upsertTranslation($idLang, $domain, $source, $translation);
            ++$count;
        }

        return $count;
    }

    private function upsertTranslation(int $idLang, string $domain, string $source, string $translation): void
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

    private function writeTranslationFile(\Everblock $module, string $fileName, array $translations): string
    {
        $dir = $this->translationsDir($module);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $path = $dir . '/' . basename($fileName);
        $content = "<?php\n\n";
        $content .= "global \$_MODULE;\n\n";
        foreach ($translations as $key => $value) {
            $content .= '$_MODULE[' . var_export((string) $key, true) . '] = ' . var_export((string) $value, true) . ";\n";
        }

        file_put_contents($path, $content);

        return $path;
    }

    private function parseLegacyTranslations(string $content): array
    {
        preg_match_all('/\$_MODULE\s*\[\s*([\'"])((?:\\\\.|(?!\1).)*)\1\s*\]\s*=\s*([\'"])((?:\\\\.|(?!\3).)*)\3\s*;/s', $content, $matches, PREG_SET_ORDER);
        $translations = [];
        foreach ($matches as $match) {
            $key = $this->decodePhpString($match[2], $match[1]);
            if (!str_starts_with($key, '<{' . self::MODULE_NAME . '}')) {
                continue;
            }
            $translations[$key] = $this->decodePhpString($match[4], $match[3]);
        }

        return $translations;
    }

    private function buildDomainFromLegacyKey(string $legacyKey): ?string
    {
        if (strpos($legacyKey, '>') === false) {
            return null;
        }

        $parts = explode('>', $legacyKey, 2);
        $segments = explode('_', $parts[1] ?? '');
        $domainKey = $segments[0] ?? '';
        if ($domainKey === '') {
            return null;
        }

        return sprintf('Modules.Everblock.%s', $this->normalizeDomainKey($domainKey));
    }

    private function legacyKeyForSource(string $source, string $domain): string
    {
        $domainParts = explode('.', $domain);
        $domainKey = Tools::strtolower((string) end($domainParts));
        $slug = Tools::strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '_', $source));
        $slug = trim($slug, '_');
        if ($slug === '') {
            $slug = 'text';
        }
        $slug = substr($slug, 0, 80);
        $hash = substr(sha1($domain . "\n" . $source), 0, 10);

        return '<{' . self::MODULE_NAME . '}prestashop>' . $domainKey . '_' . $slug . '_' . $hash;
    }

    private function normalizeDomainKey(string $key): string
    {
        $key = str_replace(['-', '.'], '_', trim($key));
        $key = preg_replace('/[^A-Za-z0-9_]/', '', (string) $key);
        $key = Tools::strtolower((string) $key);

        return Tools::ucfirst($key);
    }

    private function decodePhpString(string $value, string $quote): string
    {
        if ($quote === '"') {
            return stripcslashes($value);
        }

        return str_replace(['\\\\', "\\'"], ['\\', "'"], $value);
    }

    private function getLanguageByIso(string $isoCode): ?array
    {
        foreach (Language::getLanguages(false) as $language) {
            if ($this->normalizeIso((string) $language['iso_code']) === $isoCode) {
                return $language;
            }
        }

        return null;
    }

    private function isoFromFileName(string $fileName): ?string
    {
        if (!preg_match('/^(?:modern_)?([a-z]{2,3})\.php$/', $fileName, $matches)) {
            return null;
        }

        return $this->normalizeIso($matches[1]);
    }

    private function normalizeIso(string $iso): string
    {
        $iso = Tools::strtolower(trim($iso));
        $iso = preg_replace('/[^a-z]/', '', (string) $iso);

        return substr((string) $iso, 0, 3);
    }

    private function translationsDir(\Everblock $module): string
    {
        return rtrim(_PS_MODULE_DIR_, '/\\') . DIRECTORY_SEPARATOR . $module->name . DIRECTORY_SEPARATOR . 'translations';
    }
}
