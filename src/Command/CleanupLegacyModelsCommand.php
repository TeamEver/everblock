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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupLegacyModelsCommand extends Command
{
    protected static $defaultName = 'everblock:tools:cleanup-legacy-models';

    protected function configure(): void
    {
        $this
            ->setDescription('Remove the legacy Everblock ObjectModel classes once the migration is complete.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Delete the legacy classes instead of running in dry-run mode.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = (bool) $input->getOption('force');
        $moduleRoot = dirname(__DIR__, 2);
        $targets = [
            $moduleRoot . '/models/EverblockFlagsClass.php',
            $moduleRoot . '/models/EverblockTabsClass.php',
        ];

        $hasFailure = false;
        foreach ($targets as $file) {
            if (!file_exists($file)) {
                $output->writeln(sprintf('<info>Already removed:</info> %s', $file));
                continue;
            }

            if ($force) {
                if (@unlink($file)) {
                    $output->writeln(sprintf('<comment>Deleted:</comment> %s', $file));
                } else {
                    $output->writeln(sprintf('<error>Unable to delete:</error> %s', $file));
                    $hasFailure = true;
                }
            } else {
                $output->writeln(sprintf('<info>Pending removal:</info> %s', $file));
            }
        }

        if (!$force) {
            $output->writeln('<comment>Run the command with --force to delete the legacy ObjectModel classes.</comment>');
        }

        return $hasFailure ? Command::FAILURE : Command::SUCCESS;
    }
}
