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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PrestaShop\PrestaShop\Adapter\LegacyContext as ContextAdapter;
use Module;
use Configuration;

class SearchReplaceCommand extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;

    protected function configure()
    {
        $this->setName('everblock:tools:search-replace');
        $this->setDescription('Search and replace a string in all database tables');
        $this->addArgument('search', InputArgument::REQUIRED, 'Expression to search');
        $this->addArgument('replace', InputArgument::REQUIRED, 'Replacement string');
        $this->addArgument('idshop', InputArgument::OPTIONAL, 'Shop ID');
        $this->setHelp('This command replaces all occurrences of a string in every table and column of the shop database.');
        $this->module = Module::getInstanceByName('everblock');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $search = $input->getArgument('search');
        $replace = $input->getArgument('replace');
        $idShop = $input->getArgument('idshop');
        if (!$idShop) {
            $idShop = (int) Configuration::get('PS_SHOP_DEFAULT');
        }

        $result = \EverblockTools::migrateUrls($search, $replace, (int) $idShop);

        foreach ($result['postErrors'] as $error) {
            $output->writeln('<error>' . $error . '</error>');
        }
        foreach ($result['querySuccess'] as $success) {
            $output->writeln('<info>' . $success . '</info>');
        }

        return count($result['postErrors']) ? self::FAILURE : self::SUCCESS;
    }
}
