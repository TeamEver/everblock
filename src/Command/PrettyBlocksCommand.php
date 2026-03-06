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
use Symfony\Component\HttpKernel\KernelInterface;
use Module;
use Validate;
use Db;
use Everblock\Tools\Service\EverblockTools;

class PrettyBlocksCommand extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;
    public const ABORTED = 3;

    private $allowedActions = [
        'duplicate',
        'migrate-media',
    ];

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('everblock:tools:prettyblocks');
        $this->setDescription('PrettyBlocks tools');
        $this->addArgument(
            'action',
            InputArgument::REQUIRED,
            sprintf('Action to execute (Allowed actions: %s).', implode(' / ', $this->allowedActions))
        );
        $this->addArgument('from_lang', InputArgument::OPTIONAL, 'Source language ID');
        $this->addArgument('to_lang', InputArgument::OPTIONAL, 'Destination language ID');
        $help = sprintf("Allowed actions: %s\n", implode(' / ', $this->allowedActions));
        $this->setHelp($help);
        $this->module = Module::getInstanceByName('everblock');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        if (!in_array($action, $this->allowedActions)) {
            $output->writeln('<comment>Unknown action</comment>');
            return self::ABORTED;
        }

        if ($action === 'duplicate') {
            $fromLang = (int) $input->getArgument('from_lang');
            $toLang = (int) $input->getArgument('to_lang');
            if (!Validate::isUnsignedId($fromLang) || !Validate::isUnsignedId($toLang)) {
                $output->writeln('<comment>Invalid language id</comment>');
                return self::INVALID;
            }
            $this->duplicatePrettyblocks($fromLang, $toLang, $output);
            return self::SUCCESS;
        }

        if ($action === 'migrate-media') {
            $count = EverblockTools::moveAllPrettyblocksMediasToCms();
            $output->writeln('<success>' . $count . ' block(s) updated</success>');
            return self::SUCCESS;
        }

        return self::ABORTED;
    }

    private function duplicatePrettyblocks(int $fromLang, int $toLang, OutputInterface $output): void
    {
        $db = Db::getInstance();
        $blocks = $db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'prettyblocks WHERE id_lang = ' . (int) $fromLang);
        if (!$blocks) {
            $output->writeln('<comment>No PrettyBlocks found</comment>');
            return;
        }
        foreach ($blocks as $block) {
            unset($block['id_prettyblocks']);
            $block['id_lang'] = $toLang;
            $db->insert('prettyblocks', $block);
        }
        $output->writeln('<success>' . count($blocks) . ' block(s) duplicated from lang ' . $fromLang . ' to ' . $toLang . '</success>');
    }
}
