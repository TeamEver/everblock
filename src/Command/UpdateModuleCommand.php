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

namespace Everblock\Tools\Command;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Everblock\Tools\Service\GithubReleaseChecker;
use Everblock\Tools\Service\ModuleUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Mise à jour d'Ever Block depuis GitHub, comme le bouton du back-office :
 *   php bin/console everblock:update --check     état seulement (versions, notes de version)
 *   php bin/console everblock:update             installe la dernière release puis lance ses scripts de mise à jour
 *
 * Publiée par QCD MCP Server (outil everblock-cli_everblock_update), validation dans le BO
 * par défaut comme toute commande. Remplace les fichiers du module : une modification locale
 * non publiée sur GitHub est perdue (une sauvegarde est gardée dans var/everblock_backups/).
 */
class UpdateModuleCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('everblock:update')
            ->setDescription('Update Ever Block from its latest GitHub release (TeamEver/everblock), then run its upgrade scripts. Local code changes are replaced; a backup is kept in var/everblock_backups/.')
            ->addOption('check', null, InputOption::VALUE_NONE, 'Only show the installed and latest versions, change nothing.')
            ->addOption('reinstall', null, InputOption::VALUE_NONE, 'Install the latest release even if it is not newer than the installed version.')
            ->addOption('no-upgrade', null, InputOption::VALUE_NONE, 'Replace the files without running prestashop:module upgrade.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module = \Module::getInstanceByName('everblock');
        if (!$module instanceof \Everblock) {
            $output->writeln('<error>Ever Block is not installed.</error>');

            return 1;
        }

        $updater = new ModuleUpdater($module, new GithubReleaseChecker((string) $module->version));
        $status = $updater->status(true);
        $output->writeln(sprintf('Installed version: %s', $status['current']));
        $output->writeln(sprintf('Latest release:    %s%s', $status['latest'] ?? 'unknown (GitHub unreachable)', $status['publishedAt'] ? ' (' . $status['publishedAt'] . ')' : ''));

        if ($input->getOption('check')) {
            $output->writeln($status['updateAvailable'] ? '<info>An update is available.</info>' : 'No update available.');
            if ($status['notes'] !== '') {
                $output->writeln('');
                $output->writeln($status['notes']);
            }

            return $status['reachable'] ? 0 : 1;
        }

        $result = $updater->update((bool) $input->getOption('reinstall'));
        foreach ($result['errors'] as $error) {
            $output->writeln('<error>' . $error . '</error>');
        }
        if ($result['errors'] !== []) {
            if ($result['backup']) {
                $output->writeln('Backup of the previous version: ' . $result['backup']);
            }

            return 1;
        }
        foreach ($result['success'] as $message) {
            $output->writeln('<info>' . $message . '</info>');
        }
        $output->writeln('Backup of the previous version: ' . $result['backup']);
        $output->writeln(sprintf('Shop-generated files kept: %d', $result['preserved']));

        if ($input->getOption('no-upgrade')) {
            $output->writeln('Run now: php bin/console prestashop:module upgrade everblock');

            return 0;
        }

        // Nouveau processus : celui-ci a déjà chargé l'ancienne classe du module.
        return $this->runUpgrade($output);
    }

    private function runUpgrade(OutputInterface $output): int
    {
        $console = rtrim(_PS_ROOT_DIR_, '/\\') . '/bin/console';
        $php = PHP_BINARY ?: 'php';
        if (str_contains(basename($php), 'fpm')) {
            $php = 'php';
        }
        $env = getenv('APP_ENV') ?: 'prod';
        $command = [$php, $console, 'prestashop:module', 'upgrade', 'everblock', '--env=' . $env, '--no-interaction'];

        if (!function_exists('proc_open')) {
            $output->writeln('<comment>proc_open is disabled: run php bin/console prestashop:module upgrade everblock</comment>');

            return 0;
        }
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, _PS_ROOT_DIR_);
        if (!is_resource($process)) {
            $output->writeln('<comment>Unable to start the upgrade: run php bin/console prestashop:module upgrade everblock</comment>');

            return 1;
        }
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($process);

        $output->writeln(trim((string) $stdout));
        if (trim((string) $stderr) !== '') {
            $output->writeln('<comment>' . trim((string) $stderr) . '</comment>');
        }
        $output->writeln($code === 0 ? '<info>Upgrade scripts done.</info>' : '<error>prestashop:module upgrade everblock failed (code ' . $code . ').</error>');

        return $code === 0 ? 0 : 1;
    }
}
