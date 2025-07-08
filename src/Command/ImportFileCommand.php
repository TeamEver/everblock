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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Validate;
use Hook;
use PrestaShopLogger;

class ImportFileCommand extends Command
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
        $this->setName('everblock:tools:import');
        $this->setDescription('Import HTML blocks from xlsx file');
        $this->filename = _PS_MODULE_DIR_ . 'everblock/input/everblock.xlsx';
        $this->logFile = _PS_ROOT_DIR_ . '/var/logs/log-everblock-import-' . date('Y-m-d') . '.log';
        $help = sprintf(
            'File must be set on ' . _PS_MODULE_DIR_ . 'everblock/input/everblock.xlsx'
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
                '<info>Start Everblock update : datetime : ' . date('Y-m-d H:i:s') . '. Lines total : '.count($lines).'</info>'
            ));
            foreach ($lines as $line) {
                $this->updateEverblocks($line, $output);
            }
            $output->writeln(sprintf(
                '<comment>Everblock file updated.</comment>'
            ));
            unlink($this->filename);
            $output->writeln(sprintf(
                '<comment>Everblock file deleted. Clearing cache</comment>'
            ));
            \Tools::clearAllCache();
            $output->writeln(sprintf(
                '<comment>Cache cleared</comment>'
            ));
            return self::SUCCESS;
        } else {
            $output->writeln(sprintf(
                '<info>Everblock file does not exists</info>'
            ));
            return self::INVALID;
        }
    }

    protected function updateEverblocks($line, $output)
    {
        if (!isset($line['id_lang'])
            || !Validate::isInt($line['id_lang'])
        ) {
            $output->writeln(
               '<error>Missing id_lang column</error>'
            );
            return;
        }
        if (!isset($line['id_shop'])
            || !Validate::isInt($line['id_shop'])
        ) {
            $output->writeln(
               '<error>Missing id_shop column</error>'
            );
            return;
        }
        $create = false;
        if (isset($line['id_everblock']) && Validate::isUnsignedInt($line['id_everblock']) && (int)$line['id_everblock'] > 0) {
            $block = new \Everblock(
                (int) $line['id_everblock'],
                (int) $line['id_lang'],
                (int) $line['id_shop']
            );
            if (!Validate::isLoadedObject($block)) {
                $block = new \Everblock();
                $create = true;
            }
        } else {
            $block = new \Everblock();
            $create = true;
        }
        if ($create) {
            $block->id_shop = (int) $line['id_shop'];
            if (!isset($line['name']) || !Validate::isString($line['name'])) {
                $output->writeln('<error>name column is required for creation</error>');
                return;
            }
            if (!isset($line['hook']) || !Validate::isHookName($line['hook'])) {
                $output->writeln('<error>hook column is required for creation</error>');
                return;
            }
            $idHook = (int) Hook::getIdByName($line['hook']);
            if (!$idHook) {
                $output->writeln('<error>Hook not found: ' . $line['hook'] . '</error>');
                PrestaShopLogger::addLog('Everblock import - Hook not found: ' . $line['hook']);
                return;
            }
            $block->name = pSQL($line['name']);
            $block->id_hook = $idHook;
        }
        if (isset($line['name'])) {
            if (!Validate::isString($line['name'])) {
                $output->writeln(
                   '<error>name column is not valid : ' . $line['name'] . '</error>'
                );
            } else {
                $block->name = pSQL($line['name']);
            }
        }
        if (isset($line['active'])) {
            if (!Validate::isBool($line['active'])) {
                $output->writeln(
                   '<error>active column is not valid : ' . $line['active'] . '</error>'
                );
            } else {
                $block->active = (int) $line['active'];
            }
        }
        if (isset($line['date_start'])) {
            if (!Validate::isDateFormat($line['date_start'])) {
                $output->writeln(
                   '<error>date_start column is not valid : ' . $line['date_start'] . '</error>'
                );
            } else {
                $block->date_start = $line['date_start'];
            }
        }
        if (isset($line['date_end'])) {
            if (!Validate::isDateFormat($line['date_end'])) {
                $output->writeln(
                   '<error>date_end column is not valid : ' . $line['date_end'] . '</error>'
                );
            } else {
                $block->date_end = $line['date_end'];
            }
        }
        if (isset($line['content'])) {
            if (!Validate::isCleanHtml($line['content'])) {
                $output->writeln(
                   '<error>content column is not valid : ' . $line['content'] . '</error>'
                );
            } else {
                $block->content = $line['content'];
            }
        }
        if (isset($line['custom_code'])) {
            // huh ?
            if (!Validate::isAnything($line['custom_code'])) {
                $output->writeln(
                   '<error>custom_code column is not valid : ' . $line['custom_code'] . '</error>'
                );
            } else {
                $block->custom_code = $line['custom_code'];
            }
        }
        if (isset($line['only_category'])) {
            if (!Validate::isBool($line['only_category'])) {
                $output->writeln(
                   '<error>only_category column is not valid : ' . $line['only_category'] . '</error>'
                );
            } else {
                $block->only_category = $line['only_category'];
            }
        }
        if (isset($line['only_category_product'])) {
            if (!Validate::isBool($line['only_category_product'])) {
                $output->writeln(
                   '<error>only_category_product column is not valid : ' . $line['only_category_product'] . '</error>'
                );
            } else {
                $block->only_category_product = $line['only_category_product'];
            }
        }
        if (isset($line['hook'])) {
            if (!Validate::isHookName($line['hook'])) {
                $output->writeln(
                   '<error>hook column is not valid : ' . $line['hook'] . '</error>'
                );
            } else {
                $idHook = (int) Hook::getIdByName($line['hook']);
                if (!$idHook) {
                    $output->writeln('<error>Hook not found: ' . $line['hook'] . '</error>');
                    PrestaShopLogger::addLog('Everblock import - Hook not found: ' . $line['hook']);
                } else {
                    $block->id_hook = $idHook;
                }
            }
        }
        if (isset($line['device'])) {
            if (!Validate::isUnsignedInt($line['device'])) {
                $output->writeln(
                   '<error>device column is not valid : ' . $line['device'] . '</error>'
                );
            } else {
                $block->device = $line['device'];
            }
        }
        if (isset($line['device'])) {
            if (!Validate::isUnsignedInt($line['device'])) {
                $output->writeln(
                   '<error>device column is not valid : ' . $line['device'] . '</error>'
                );
            } else {
                $block->device = $line['device'];
            }
        }
        if (isset($line['background'])) {
            if (!Validate::isColor($line['background'])) {
                $output->writeln(
                   '<error>background column is not valid : ' . $line['background'] . '</error>'
                );
            } else {
                $block->background = $line['background'];
            }
        }
        if (isset($line['css_class'])) {
            if (!Validate::isString($line['css_class'])) {
                $output->writeln(
                   '<error>css_class column is not valid : ' . $line['css_class'] . '</error>'
                );
            } else {
                $block->css_class = $line['css_class'];
            }
        }
        if (isset($line['data_attribute'])) {
            if (!Validate::isString($line['data_attribute'])) {
                $output->writeln(
                   '<error>data_attribute column is not valid : ' . $line['data_attribute'] . '</error>'
                );
            } else {
                $block->data_attribute = $line['data_attribute'];
            }
        }
        if (isset($line['bootstrap_class'])) {
            if (!Validate::isString($line['bootstrap_class'])) {
                $output->writeln(
                   '<error>bootstrap_class column is not valid : ' . $line['bootstrap_class'] . '</error>'
                );
            } else {
                $block->bootstrap_class = $line['bootstrap_class'];
            }
        }
        if (isset($line['groups'])) {
            if (!Validate::isString($line['groups'])) {
                $output->writeln(
                   '<error>groups column is not valid : ' . $line['groups'] . '</error>'
                );
            } else {
                $groups = explode(',', $line['groups']);
                $block->groups = json_encode($groups);
            }
        }
        if (isset($line['categories'])) {
            if (!Validate::isString($line['categories'])) {
                $output->writeln(
                   '<error>categories column is not valid : ' . $line['categories'] . '</error>'
                );
            } else {
                $categories = explode(',', $line['categories']);
                $block->categories = json_encode($categories);
            }
        }
        if (isset($line['only_manufacturer'])) {
            if (!Validate::isBool($line['only_manufacturer'])) {
                $output->writeln(
                   '<error>only_manufacturer column is not valid : ' . $line['only_manufacturer'] . '</error>'
                );
            } else {
                $block->only_manufacturer = $line['only_manufacturer'];
            }
        }
        if (isset($line['only_supplier'])) {
            if (!Validate::isBool($line['only_supplier'])) {
                $output->writeln(
                   '<error>only_supplier column is not valid : ' . $line['only_supplier'] . '</error>'
                );
            } else {
                $block->only_supplier = $line['only_supplier'];
            }
        }
        if (isset($line['only_cms_category'])) {
            if (!Validate::isBool($line['only_cms_category'])) {
                $output->writeln(
                   '<error>only_cms_category column is not valid : ' . $line['only_cms_category'] . '</error>'
                );
            } else {
                $block->only_cms_category = $line['only_cms_category'];
            }
        }
        if (isset($line['manufacturers'])) {
            if (!Validate::isString($line['manufacturers'])) {
                $output->writeln(
                   '<error>manufacturers column is not valid : ' . $line['manufacturers'] . '</error>'
                );
            } else {
                $block->manufacturers = json_encode(explode(',', $line['manufacturers']));
            }
        }
        if (isset($line['suppliers'])) {
            if (!Validate::isString($line['suppliers'])) {
                $output->writeln(
                   '<error>suppliers column is not valid : ' . $line['suppliers'] . '</error>'
                );
            } else {
                $block->suppliers = json_encode(explode(',', $line['suppliers']));
            }
        }
        if (isset($line['cms_categories'])) {
            if (!Validate::isString($line['cms_categories'])) {
                $output->writeln(
                   '<error>cms_categories column is not valid : ' . $line['cms_categories'] . '</error>'
                );
            } else {
                $block->cms_categories = json_encode(explode(',', $line['cms_categories']));
            }
        }
        if (isset($line['obfuscate_link'])) {
            if (!Validate::isBool($line['obfuscate_link'])) {
                $output->writeln(
                   '<error>obfuscate_link column is not valid : ' . $line['obfuscate_link'] . '</error>'
                );
            } else {
                $block->obfuscate_link = $line['obfuscate_link'];
            }
        }
        if (isset($line['add_container'])) {
            if (!Validate::isBool($line['add_container'])) {
                $output->writeln(
                   '<error>add_container column is not valid : ' . $line['add_container'] . '</error>'
                );
            } else {
                $block->add_container = $line['add_container'];
            }
        }
        if (isset($line['lazyload'])) {
            if (!Validate::isBool($line['lazyload'])) {
                $output->writeln(
                   '<error>lazyload column is not valid : ' . $line['lazyload'] . '</error>'
                );
            } else {
                $block->lazyload = $line['lazyload'];
            }
        }
        if (isset($line['modal'])) {
            if (!Validate::isBool($line['modal'])) {
                $output->writeln(
                   '<error>modal column is not valid : ' . $line['modal'] . '</error>'
                );
            } else {
                $block->modal = $line['modal'];
            }
        }
        if (isset($line['delay'])) {
            if (!Validate::isUnsignedInt($line['delay'])) {
                $output->writeln(
                   '<error>delay column is not valid : ' . $line['delay'] . '</error>'
                );
            } else {
                $block->delay = $line['delay'];
            }
        }
        if (isset($line['timeout'])) {
            if (!Validate::isUnsignedInt($line['timeout'])) {
                $output->writeln(
                   '<error>timeout column is not valid : ' . $line['timeout'] . '</error>'
                );
            } else {
                $block->timeout = $line['timeout'];
            }
        }
        try {
            $block->save();
        } catch (Exception $e) {
            $output->writeln(
               '<error>Error on saving obj : ' . $e->getMessage() . '</error>'
            );
        }
    }

    protected function logCommand($msg)
    {
        $log  = 'Msg: '
        . $msg
        . PHP_EOL
        . date('j.n.Y')
        . PHP_EOL
        . '-------------------------'
        . PHP_EOL;

        //Save string to log, use FILE_APPEND to append.
        file_put_contents(
            $this->logFile,
            $log,
            FILE_APPEND
        );
    }
}
