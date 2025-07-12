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

use Everblock\Tools\Service\ImportFile;
use Language;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Validate;

class ImportTabCommand extends Command
{
    const SUCCESS = 0;
    const FAILURE = 1;
    const INVALID = 2;
    const ABORTED = 3;
    
    protected $filename;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('everblock:tools:import_tab');
        $this->setDescription('Update product tabs usinx xlsx');
        $this->filename = _PS_MODULE_DIR_ . 'everblock/input/everblock_tabs.xlsx';
        $this->logFile = _PS_ROOT_DIR_ . '/var/logs/log-everblock_tabs-import-' . date('Y-m-d') . '.log';
        $help = sprintf(
            'File must be set on ' . _PS_MODULE_DIR_ . 'everblock/input/everblock_tabs.xlsx'
        );
        $this->setHelp($help);
        $this->module = \Module::getInstanceByName('everblock');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (file_exists($this->filename)) {
            $file = new ImportFile($this->filename);
            $lines = $file->getLines();
            $headers = $file->getHeaders();
            $output->writeln(sprintf(
                '<info>Start Everblock tab update : datetime : ' . date('Y-m-d H:i:s') . '. Lines total : ' . count($lines) . '</info>'
            ));
            foreach ($lines as $line) {
                $this->updateEverblocksTabs($line, $output);
            }
            $output->writeln(sprintf(
                '<comment>Everblock tab file updated.</comment>'
            ));
            unlink($this->filename);
            $output->writeln(sprintf(
                '<comment>Everblock tab file deleted</comment>'
            ));
            return self::SUCCESS;
        } else {
            $output->writeln(sprintf(
                '<info>Everblock tab file does not exists</info>'
            ));
            return self::INVALID;
        }
    }

    protected function updateEverblocksTabs($line, $output)
    {
        if (!isset($line['id_product'])
            || !Validate::isInt($line['id_product'])
        ) {
            $output->writeln(
               '<error>Missing or non valid id_product column</error>'
            );
            return;
        }
        if (!isset($line['id_lang'])
            || !Validate::isInt($line['id_lang'])
        ) {
            $output->writeln(
               '<error>Missing or non valid id_lang column</error>'
            );
            return;
        }
        if (!isset($line['id_shop'])
            || !Validate::isInt($line['id_shop'])
        ) {
            $output->writeln(
               '<error>Missing or non valid id_shop column</error>'
            );
            return;
        }
        if (!isset($line['title'])
            || !Validate::isCleanHtml($line['title'])
        ) {
            $output->writeln(
               '<error>Missing or non valid title column</error>'
            );
            return;
        }
        if (!isset($line['content'])
            || !Validate::isCleanHtml($line['content'])
        ) {
            $output->writeln(
               '<error>Missing or non valid content column</error>'
            );
            return;
        }
        try {
            $tab = \EverblockTabsClass::getByIdProduct(
                $line['id_product'],
                $line['id_shop']
            );
            foreach (Language::getLanguages(false, $line['id_shop']) as $lang) {
                $tab->title[(int) $line['id_lang']] = $line['title'];
                $tab->content[(int) $line['id_lang']] = $line['content'];
            }
            $tab->id_product = (int) $line['id_product'];
            $tab->id_shop = (int) $line['id_shop'];
            $tab->save();
        } catch (Exception $e) {
            $output->writeln(
               '<error>Error on saving obj : ' . $e->getMessage() . '</error>'
            );
        }
    }

    protected function logCommand($msg)
    {
        $msg = trim($msg);
        if ($msg === '') {
            return;
        }

        $log  = 'Msg: ' . $msg . PHP_EOL .
                date('j.n.Y') . PHP_EOL .
                '-------------------------' . PHP_EOL;

        //Save string to log, use FILE_APPEND to append.
        file_put_contents(
            $this->logFile,
            $log,
            FILE_APPEND
        );
    }
}
