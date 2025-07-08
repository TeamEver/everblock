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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PrestaShop\PrestaShop\Adapter\LegacyContext as ContextAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Hook;
use Validate;

class ExportFileCommand extends Command
{
    const SUCCESS = 0;
    const FAILURE = 1;
    const INVALID = 2;
    const ABORTED = 3;
    
    protected $filename;

    private $allowedActions = [
        'getrandomcomment',
        'blocks'
    ];

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('everblock:tools:export');
        $this->setDescription('Export Ever Block datas into xlsx file');
        $this->addArgument('action', InputArgument::OPTIONAL, sprintf('Action to execute (Allowed actions: %s).', implode(' / ', $this->allowedActions)));
        $this->addArgument('idshop id', InputArgument::OPTIONAL, 'Shop ID');
        $this->addArgument('lang id', InputArgument::OPTIONAL, 'Language ID');
        $help = sprintf("Allowed actions: %s\n", implode(' / ', $this->allowedActions));
        $this->setHelp($help);
        $this->logFile = _PS_ROOT_DIR_ . '/var/logs/log-everblock-export-' . date('Y-m-d') . '.log';
        $this->module = \Module::getInstanceByName('everblock');
        $this->filename = _PS_MODULE_DIR_ . 'everblock/output/everblock.xlsx';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        $idShop = $input->getArgument('idshop id');
        $idLang = $input->getArgument('lang id');
        if (!in_array($action, $this->allowedActions)) {
            $output->writeln('<comment>Unkown action</comment>');
            return self::ABORTED;
        }
        $context = (new ContextAdapter())->getContext();
        $context->employee = new \Employee(1);
        if (Validate::isInt($idShop)) {
            $shop = new \Shop(
                (int) $idShop
            );
            if (!Validate::isLoadedObject($shop)) {
                $output->writeln('<comment>Shop not found</comment>');
                return self::ABORTED;
            }
        } else {
            $shop = $context->shop;
            if (!Validate::isLoadedObject($shop)) {
                $shop = new \Shop((int) \Configuration::get('PS_SHOP_DEFAULT'));
                $idShop = $shop->id;
            } else {
                $output->writeln('<comment>Shop not found</comment>');
                return self::ABORTED;
            }
        }
        \Shop::setContext($shop::CONTEXT_SHOP, $idShop);

        if ($action === 'getrandomcomment') {
            $output->writeln(
                $this->getRandomFunnyComment($output)
            );
            return self::SUCCESS;
        }
        // Fine, let's output XLSX files
        $creator = \Configuration::get('PS_SHOP_NAME');
        $title = $action;
        $reportName = $action;
        if ($action === 'blocks') {
            $dataSet = $this->getAllBlocks(
                (int) $idShop,
                (int) $idLang
            );
            $spreadsheet = new Spreadsheet();
            // Set properties
            $spreadsheet->getProperties()->setCreator($creator)
                                         ->setLastModifiedBy($creator)
                                         ->setTitle($title)
                                         ->setSubject($title)
                                         ->setDescription($title)
                                         ->setCategory($title);
            $spreadsheet->setActiveSheetIndex(0);
            $row = 2;
            $headers = [
                'id_everblock',
                'id_shop',
                'id_lang',
                'name',
                'hook',
                'only_home',
                'only_category',
                'only_category_product',
                'only_manufacturer',
                'only_supplier',
                'only_cms_category',
                'obfuscate_link',
                'add_container',
                'lazyload',
                'device',
                'categories',
                'manufacturers',
                'suppliers',
                'cms_categories',
                'groups',
                'background',
                'css_class',
                'data_attribute',
                'bootstrap_class',
                'position',
                'modal',
                'delay',
                'timeout',
                'date_start',
                'date_end',
                'active',
                'content',
                'custom_code',
            ];
            foreach ($dataSet as $block) {
                $values = [
                    $block['id_everblock'],
                    $block['id_shop'],
                    $block['id_lang'],
                    $block['name'],
                    Hook::getNameById((int) $block['id_hook']),
                    $block['only_home'],
                    $block['only_category'],
                    $block['only_category_product'],
                    $block['only_manufacturer'],
                    $block['only_supplier'],
                    $block['only_cms_category'],
                    $block['obfuscate_link'],
                    $block['add_container'],
                    $block['lazyload'],
                    $block['device'],
                    $this->decodeField($block['categories']),
                    $this->decodeField($block['manufacturers']),
                    $this->decodeField($block['suppliers']),
                    $this->decodeField($block['cms_categories']),
                    $this->decodeField($block['groups']),
                    $block['background'],
                    $block['css_class'],
                    $block['data_attribute'],
                    $block['bootstrap_class'],
                    $block['position'],
                    $block['modal'],
                    $block['delay'],
                    $block['timeout'],
                    $block['date_start'],
                    $block['date_end'],
                    $block['active'],
                    $block['content'],
                    $block['custom_code'],
                ];
                foreach ($values as $i => $value) {
                    $col = $i + 1;
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
                    $letter = Coordinate::stringFromColumnIndex($col);
                    $spreadsheet->getActiveSheet()->getStyle($letter . $row)->getFont()->setBold(true)->setName('Arial')->setSize(9);
                    $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
                }
                $row++;
            }
            $spreadsheet->setActiveSheetIndex(0);
            foreach ($headers as $i => $header) {
                $col = $i + 1;
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $header);
            }
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $spreadsheet->getActiveSheet()->setAutoFilter('A1:' . $lastColumn . '1');
            // Rename sheet
            $spreadsheet->getActiveSheet()->setTitle(\Tools::substr($reportName, 0, 31));

            //Text bold in first row
            $spreadsheet->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);

            //Freeze first row
            $spreadsheet->getActiveSheet()->freezePane('A2');
            $styleArray = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                    'rotation' => 90,
                    'startColor' => [
                        'argb' => 'FFA0A0A0',
                    ],
                    'endColor' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['argb' => 'FFFF0000'],
                    ],
                ],
            ];

            $spreadsheet->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->applyFromArray($styleArray);

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            $writer->save(
                $this->filename
            );
            $output->writeln(sprintf(
                '<comment>File generated, you can download it on SEO module from backoffice</comment>'
            ));
            $output->writeln(
                $this->getRandomFunnyComment($output)
            );
            return self::SUCCESS;
        }
    }

    protected function getAllBlocks($idShop, $idLang): array
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('everblock_lang', 'ebl');
        $sql->leftJoin(
            'everblock',
            'eb',
            'eb.id_everblock = ebl.id_everblock'
        );
        $sql->where('eb.id_shop = ' . (int) $idShop);
        $sql->where('ebl.id_lang = ' . (int) $idLang);
        $allBlocks = \Db::getInstance()->executeS($sql);
        return $allBlocks;
    }

    protected function decodeField($json)
    {
        if ($json && $json != 'false' && Validate::isJson($json)) {
            $items = json_decode($json);
            if (is_array($items)) {
                return implode(',', $items);
            }
        }

        return '';
    }



    protected function logCommand($msg)
    {
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
            EXPORT ENDED, HAVE A BEER
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
            EXPORT ENDED, MEOW
              ^~^  ,
             ('Y') )
             /   \/
            (\|||/)
            </styled>";
        $funnyComments[] = "<styled>
            EXPORT ENDED, D'OH
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
            |      EXPORT      |
            |      ENDED!      |
            |__________________|
            (\__/) ||
            (•ㅅ•) ||
            / 　 づ"
            </styled>';
        $funnyComments[] = "<styled>
            Export (•_•)
            has been ( •_•)>⌐■-■
            ended (⌐■_■)
            </styled>";
        $funnyComments[] = "<styled>
            ......_________________________
            ....../ `---___________--------    | ============= EXPORT-ENDED-BULLET !
            ...../_==o;;;;;;;;______________|
            .....), ---.(_(__) /
            .......// (..) ), /--
            ... //___//---
            .. //___//
            .//___//
            //___//
            </styled>";
        $funnyComments[] = "<styled>
               EXPORT ENDED
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
