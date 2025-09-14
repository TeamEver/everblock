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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Db;

class CleanGameDataCommand extends Command
{
    public const SUCCESS = 0;

    protected function configure()
    {
        $this->setName('everblock:tools:clean-games');
        $this->setDescription('Remove game data for deleted PrettyBlocks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = Db::getInstance();
        $sqlCount = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'everblock_game_play g ' .
            'LEFT JOIN ' . _DB_PREFIX_ . 'prettyblocks pb ON (g.id_prettyblocks = pb.id_prettyblocks) ' .
            'WHERE pb.id_prettyblocks IS NULL';
        $count = (int) $db->getValue($sqlCount);

        if ($count === 0) {
            $output->writeln('<info>No orphan game data found</info>');
            return self::SUCCESS;
        }

        $sqlDelete = 'DELETE g FROM ' . _DB_PREFIX_ . 'everblock_game_play g ' .
            'LEFT JOIN ' . _DB_PREFIX_ . 'prettyblocks pb ON (g.id_prettyblocks = pb.id_prettyblocks) ' .
            'WHERE pb.id_prettyblocks IS NULL';
        $db->execute($sqlDelete);

        $output->writeln('<info>' . $count . ' orphan game record(s) removed</info>');

        return self::SUCCESS;
    }
}

