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
declare(strict_types=1);

namespace Everblock\Tools\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Mise à jour d'Ever Block depuis la dernière release GitHub (TeamEver/everblock), partagée par
 * le back-office, la commande everblock:update et l'outil MCP de vérification.
 *
 * Étapes : release la plus récente → archive du tag → extraction dans var/ (même disque que
 * modules/, pour un remplacement par renommage) → contrôle de l'archive (everblock.php, version
 * égale au tag) → copie de sauvegarde du module dans var/everblock_backups/ → remplacement du
 * dossier → reprise des fichiers générés par la boutique (CSS/JS personnalisés, médias, imports)
 * → retour à la sauvegarde si le remplacement échoue. Les scripts upgrade/ sont lancés ensuite
 * par PrestaShop (prestashop:module upgrade everblock), dans un autre processus : celui-ci a
 * déjà chargé l'ancienne classe du module.
 *
 * Toute modification locale du module (code non publié sur GitHub) est remplacée par la release.
 */
final class ModuleUpdater
{
    private const REPOSITORY_ARCHIVE = 'https://github.com/TeamEver/everblock/archive/refs/tags/%s.zip';
    private const KEEP_BACKUPS = 3;

    /** Dossiers dont les fichiers générés par la boutique sont repris après remplacement. */
    private const PRESERVED_DIRS = ['views/', 'input/', 'output/'];

    /** Jamais repris d'une ancienne version : du code exécutable. */
    private const NEVER_PRESERVED = ['php', 'phtml', 'phar', 'tpl', 'twig', 'yml'];

    public function __construct(
        private readonly \Everblock $module,
        private readonly GithubReleaseChecker $releaseChecker,
    ) {
    }

    /**
     * @return array{current: string, latest: ?string, publishedAt: ?string, notes: string, updateAvailable: bool, reachable: bool}
     */
    public function status(bool $refresh = false): array
    {
        $release = $this->releaseChecker->getLatestEverblockRelease($refresh);

        return [
            'current' => (string) $this->module->version,
            'latest' => $release ? ltrim((string) $release['tag_name'], 'vV') : null,
            'publishedAt' => $release['published_at'] ?? null,
            'notes' => $release ? mb_substr(trim((string) ($release['body'] ?? '')), 0, 4000) : '',
            'updateAvailable' => $release !== null && $this->releaseChecker->isEverblockUpdateAvailable(),
            'reachable' => $release !== null,
        ];
    }

    /**
     * @return array{errors: list<string>, success: list<string>, from: string, to: ?string, backup: ?string, preserved: int}
     */
    public function update(bool $reinstall = false): array
    {
        $result = ['errors' => [], 'success' => [], 'from' => (string) $this->module->version, 'to' => null, 'backup' => null, 'preserved' => 0];
        $release = $this->releaseChecker->getLatestEverblockRelease(true);
        if (!$release || empty($release['tag_name'])) {
            $result['errors'][] = 'Unable to fetch the latest release details from GitHub.';

            return $result;
        }
        $tagName = (string) $release['tag_name'];
        $result['to'] = ltrim($tagName, 'vV');
        if (!$reinstall && !$this->releaseChecker->isEverblockUpdateAvailable()) {
            $result['errors'][] = sprintf('Ever Block is already up to date (installed %s, latest release %s).', $result['from'], $result['to']);

            return $result;
        }
        if (!class_exists(\ZipArchive::class)) {
            $result['errors'][] = 'The PHP zip extension is required.';

            return $result;
        }

        $workDir = $this->varDir('everblock_update') . 'run_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . DIRECTORY_SEPARATOR;
        try {
            if (!@mkdir($workDir, 0755, true)) {
                throw new \RuntimeException('Unable to prepare the update directory in var/.');
            }
            $zipPath = $workDir . 'release.zip';
            if (!\Tools::copy(sprintf(self::REPOSITORY_ARCHIVE, rawurlencode($tagName)), $zipPath) || !is_file($zipPath) || filesize($zipPath) < 1024) {
                throw new \RuntimeException('Unable to download the release archive.');
            }
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('Unable to open the downloaded archive.');
            }
            $extractDir = $workDir . 'extract' . DIRECTORY_SEPARATOR;
            $zip->extractTo($extractDir);
            $zip->close();

            $sourceDir = $this->locateSource($extractDir);
            if ($sourceDir === null) {
                throw new \RuntimeException('Unable to locate the module files in the archive.');
            }
            $this->assertRelease($sourceDir, $result['to']);

            $targetDir = rtrim(_PS_MODULE_DIR_, '/\\') . DIRECTORY_SEPARATOR . $this->module->name;
            $backupDir = $this->varDir('everblock_backups') . $this->module->name . '_' . $result['from'] . '_' . date('YmdHis');
            if (!$this->copyTree($targetDir, $backupDir)) {
                throw new \RuntimeException('Unable to back up the current module, nothing was changed.');
            }
            $result['backup'] = $backupDir;

            if (!$this->swap($sourceDir, $targetDir, $workDir)) {
                throw new \RuntimeException('Unable to replace the module files, the previous version has been restored.');
            }
            $result['preserved'] = $this->restoreGeneratedFiles($backupDir, $targetDir);
            $this->pruneBackups();
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            $result['success'][] = sprintf('Ever Block files updated from %s to %s.', $result['from'], $result['to']);
        } catch (\Throwable $exception) {
            $result['errors'][] = $exception->getMessage();
            \PrestaShopLogger::addLog('everblock update: ' . $exception->getMessage(), 3);
        } finally {
            $this->removeTree($workDir);
        }

        return $result;
    }

    private function assertRelease(string $sourceDir, string $expectedVersion): void
    {
        $main = (string) @file_get_contents($sourceDir . 'everblock.php');
        if (!str_contains($main, 'class Everblock extends Module')) {
            throw new \RuntimeException('The archive does not contain the Ever Block module.');
        }
        if (preg_match("/->version\\s*=\\s*'([^']+)'/", $main, $match) && version_compare($match[1], $expectedVersion, '!=')) {
            throw new \RuntimeException(sprintf('Archive version %s does not match the release %s.', $match[1], $expectedVersion));
        }
    }

    /**
     * Remplacement par renommage (même disque), retour arrière en cas d'échec.
     */
    private function swap(string $sourceDir, string $targetDir, string $workDir): bool
    {
        $previous = $workDir . 'previous';
        if (!@rename($targetDir, $previous)) {
            return false;
        }
        if (@rename(rtrim($sourceDir, '/\\'), $targetDir)) {
            return true;
        }
        // Disques différents : copie, puis retour à l'ancienne version si elle échoue.
        if ($this->copyTree(rtrim($sourceDir, '/\\'), $targetDir)) {
            return true;
        }
        $this->removeTree($targetDir);
        @rename($previous, $targetDir);

        return false;
    }

    /**
     * Fichiers générés par la boutique dans l'ancienne version (CSS/JS personnalisés par
     * boutique, médias, exports) et absents de la release : recopiés, jamais de code.
     */
    private function restoreGeneratedFiles(string $backupDir, string $targetDir): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($backupDir, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($backupDir) + 1));
            $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
            if (in_array($extension, self::NEVER_PRESERVED, true) || file_exists($targetDir . '/' . $relative)) {
                continue;
            }
            $allowed = false;
            foreach (self::PRESERVED_DIRS as $dir) {
                $allowed = $allowed || str_starts_with($relative, $dir);
            }
            if (!$allowed) {
                continue;
            }
            $destination = $targetDir . '/' . $relative;
            if ((is_dir(dirname($destination)) || @mkdir(dirname($destination), 0755, true)) && @copy($file->getPathname(), $destination)) {
                ++$count;
            }
        }

        return $count;
    }

    private function pruneBackups(): void
    {
        $backups = glob($this->varDir('everblock_backups') . $this->module->name . '_*', GLOB_ONLYDIR) ?: [];
        sort($backups);
        foreach (array_slice($backups, 0, max(0, count($backups) - self::KEEP_BACKUPS)) as $old) {
            $this->removeTree($old);
        }
    }

    private function locateSource(string $extractDir): ?string
    {
        if (is_file($extractDir . 'everblock.php')) {
            return $extractDir;
        }
        foreach (glob($extractDir . '*', GLOB_ONLYDIR) ?: [] as $candidate) {
            if (is_file($candidate . '/everblock.php')) {
                return rtrim($candidate, '/\\') . DIRECTORY_SEPARATOR;
            }
        }

        return null;
    }

    private function varDir(string $name): string
    {
        $dir = rtrim(_PS_ROOT_DIR_, '/\\') . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }

    private function copyTree(string $source, string $target): bool
    {
        if (!is_dir($source) || (!is_dir($target) && !@mkdir($target, 0755, true))) {
            return false;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $destination = $target . DIRECTORY_SEPARATOR . substr($item->getPathname(), strlen($source) + 1);
            if ($item->isDir()) {
                if (!is_dir($destination) && !@mkdir($destination, 0755, true)) {
                    return false;
                }
            } elseif (!@copy($item->getPathname(), $destination)) {
                return false;
            }
        }

        return true;
    }

    private function removeTree(string $dir): void
    {
        $dir = rtrim($dir, '/\\');
        if ($dir === '' || !is_dir($dir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }
}
