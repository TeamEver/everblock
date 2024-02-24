<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Everblock\Tools\Command;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Everblock\Tools\Service\ImportFile;
use Validate;

class ImportFileCommand extends ContainerAwareCommand
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
        $this->setDescription('Update SEO datas for categories & products');
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
            $output->writeln(
                $this->getRandomFunnyComment($output)
            );
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
        if (!isset($line['id_everblock'])
            || !Validate::isInt($line['id_everblock'])
        ) {
            $output->writeln(
               '<error>Missing id_everblock column</error>'
            );
            return;
        }
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
        $block = new \Everblock(
            (int) $line['id_everblock'],
            (int) $line['id_lang'],
            (int) $line['id_shop']
        );
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
        if (isset($line['only_home'])) {
            if (!Validate::isBool($line['only_home'])) {
                $output->writeln(
                   '<error>only_home column is not valid : ' . $line['only_home'] . '</error>'
                );
            } else {
                $block->only_home = $line['only_home'];
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
        if (isset($line['id_hook'])) {
            if (!Validate::isBool($line['id_hook'])) {
                $output->writeln(
                   '<error>id_hook column is not valid : ' . $line['id_hook'] . '</error>'
                );
            } else {
                $block->id_hook = $line['id_hook'];
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

    /**
     * Get funny random comment
     * Can be useful for setting comment style example
     * @see https://symfony.com/doc/current/console/coloring.html
    */
    protected function getRandomFunnyComment($output)
    {
        $outputStyle = new OutputFormatterStyle('green', 'white', ['bold', 'blink']);
        $output->getFormatter()->setStyle('styled', $outputStyle);
        $funnyComments = [];
        $funnyComments[] = "<styled>
            IMPORT ENDED, HAVE A BEER
                         .sssssssss.
                   .sssssssssssssssssss
                 sssssssssssssssssssssssss
                ssssssssssssssssssssssssssss
                 @@sssssssssssssssssssssss@ss
                 |s@@@@sssssssssssssss@@@@s|s
          _______|sssss@@@@@sssss@@@@@sssss|s
        /         sssssssss@sssss@sssssssss|s
       /  .------+.ssssssss@sssss@ssssssss.|
      /  /       |...sssssss@sss@sssssss...|
     |  |        |.......sss@sss@ssss......|
     |  |        |..........s@ss@sss.......|
     |  |        |...........@ss@..........|
      \  \       |............ss@..........|
       \  '------+...........ss@...........|
        \________ .........................|
                 |.........................|
                /...........................\
               |.............................|
                  |.......................|
                      |...............|
                </styled>";
        $funnyComments[] = "<styled>
            IMPORT ENDED, MEOW
              ^~^  ,
             ('Y') )
             /   \/
            (\|||/)
            </styled>";
        $funnyComments[] = "<styled>
            IMPORT ENDED, D'OH
            ...___.._____
            ....‘/,-Y”.............“~-.
            ..l.Y.......................^.
            ./\............................_\_
            i.................... ___/“....“\
            |.................../“....“\ .....o !
            l..................].......o !__../
            .\..._..._.........\..___./...... “~\
            ..X...\/...\.....................___./
            .(. \.___......_.....--~~“.......~`-.
            ....`.Z,--........./.................\
            .......\__....(......../..........______)
            ...........\.........l......../-----~~”/
            ............Y.......\................/
            ............|........“x______.^
            ............|.............\
            ............j...............Y
            </styled>";
        $funnyComments[] = '<styled>
            |￣￣￣￣￣￣￣￣￣ |
            |      IMPORT      |
            |      ENDED!      |
            |__________________|
            (\__/) ||
            (•ㅅ•) ||
            / 　 づ"
            </styled>';
        $funnyComments[] = "<styled>
            Import (•_•)
            has been ( •_•)>⌐■-■
            ended (⌐■_■)
            </styled>";
        $funnyComments[] = "<styled>
            ......_________________________
            ....../ `---___________--------    | ============= IMPORT-ENDED-BULLET !
            ...../_==o;;;;;;;;______________|
            .....), ---.(_(__) /
            .......// (..) ), /--
            ... //___//---
            .. //___//
            .//___//
            //___//
            </styled>";
        $funnyComments[] = "<styled>
               IMPORT ENDED
           ._________________.
           |.---------------.|
           ||               ||
           ||   -._ .-.     ||
           ||   -._| | |    ||
           ||   -._|'|'|    ||
           ||   -._|.-.|    ||
           ||_______________||
           /.-.-.-.-.-.-.-.-.\
          /.-.-.-.-.-.-.-.-.-.\
         /.-.-.-.-.-.-.-.-.-.-.\
        /______/__________\___o_\ 
        \_______________________/
         </styled>";
        $k = array_rand($funnyComments);
        return $funnyComments[$k];
    }
}
