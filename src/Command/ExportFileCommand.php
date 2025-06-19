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
            $r = 2;
            foreach ($dataSet as $block) {
                if ($block['categories']
                    && $block['categories'] != 'false'
                    && Validate::isJson($block['categories'])
                ) {
                    $categories = implode(',', json_decode($block['categories']));
                } else {
                    $categories = '';
                }
                if ($block['groups']
                    && $block['groups'] != 'false'
                    && Validate::isJson($block['groups'])
                ) {
                    $groups = implode(',', json_decode($block['groups']));
                } else {
                    $groups = '';
                }
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, $r, $block['id_everblock']);
                $spreadsheet->getActiveSheet()->getStyle("A" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("A" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("A" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(2, $r, $block['id_shop']);
                $spreadsheet->getActiveSheet()->getStyle("B" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("B" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("B" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(3, $r, $block['id_lang']);
                $spreadsheet->getActiveSheet()->getStyle("C" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("C" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("C" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(4, $r, $block['id_hook']);
                $spreadsheet->getActiveSheet()->getStyle("D" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("D" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("D" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(5, $r, $block['name']);
                $spreadsheet->getActiveSheet()->getStyle("E" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("E" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("E" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(6, $r, $block['only_home']);
                $spreadsheet->getActiveSheet()->getStyle("F" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("F" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("F" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(7, $r, $block['only_category']);
                $spreadsheet->getActiveSheet()->getStyle("G" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("G" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("G" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(8, $r, $block['only_category_product']);
                $spreadsheet->getActiveSheet()->getStyle("H" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("H" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("H" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(9, $r, $block['device']);
                $spreadsheet->getActiveSheet()->getStyle("I" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("I" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("I" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(10, $r, $categories);
                $spreadsheet->getActiveSheet()->getStyle("J" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("J" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("J" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(11, $r, $groups);
                $spreadsheet->getActiveSheet()->getStyle("K" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("K" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("K" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(12, $r, $block['background']);
                $spreadsheet->getActiveSheet()->getStyle("L" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("L" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("L" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(13, $r, $block['css_class']);
                $spreadsheet->getActiveSheet()->getStyle("M" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("M" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("M" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("M")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(14, $r, $block['data_attribute']);
                $spreadsheet->getActiveSheet()->getStyle("N" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("N" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("N" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("N")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(15, $r, $block['bootstrap_class']);
                $spreadsheet->getActiveSheet()->getStyle("O" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("O" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("O" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("O")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(16, $r, $block['position']);
                $spreadsheet->getActiveSheet()->getStyle("P" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("P" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("P" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("P")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(17, $r, $block['date_start']);
                $spreadsheet->getActiveSheet()->getStyle("Q" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("Q" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("Q" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("Q")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(18, $r, $block['date_end']);
                $spreadsheet->getActiveSheet()->getStyle("R" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("R" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("R" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("R")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(19, $r, $block['active']);
                $spreadsheet->getActiveSheet()->getStyle("S" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("S" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("S" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("S")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(20, $r, $block['content']);
                $spreadsheet->getActiveSheet()->getStyle("T" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("T" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("T" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("T")->setAutoSize(true);

                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(21, $r, $block['custom_code']);
                $spreadsheet->getActiveSheet()->getStyle("U" . $r)->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("U" . $r)->getFont()->setName('Arial');
                $spreadsheet->getActiveSheet()->getStyle("U" . $r)->getFont()->setSize(9);
                $spreadsheet->getActiveSheet()->getColumnDimension("U")->setAutoSize(true);

                $r++;
            }
            $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'id_everblock')
            ->setCellValue('B1', 'id_shop')
            ->setCellValue('C1', 'id_lang')
            ->setCellValue('D1', 'name')
            ->setCellValue('E1', 'id_hook')
            ->setCellValue('F1', 'only_home')
            ->setCellValue('G1', 'only_category')
            ->setCellValue('H1', 'only_category_product')
            ->setCellValue('I1', 'device')
            ->setCellValue('J1', 'categories')
            ->setCellValue('K1', 'groups')
            ->setCellValue('L1', 'background')
            ->setCellValue('M1', 'css_class')
            ->setCellValue('N1', 'data_attribute')
            ->setCellValue('O1', 'bootstrap_class')
            ->setCellValue('P1', 'position')
            ->setCellValue('Q1', 'date_start')
            ->setCellValue('R1', 'date_end')
            ->setCellValue('S1', 'active')
            ->setCellValue('T1', 'content')
            ->setCellValue('U1', 'custom_code');
            $spreadsheet->getActiveSheet()->setAutoFilter('A1:U1');
            // Rename sheet
            $spreadsheet->getActiveSheet()->setTitle(\Tools::substr($reportName, 0, 31));

            //Text bold in first row
            $spreadsheet->getActiveSheet()->getStyle('A1:U1')->getFont()->setBold(true);

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

            $spreadsheet->getActiveSheet()->getStyle('A1:U1')->applyFromArray($styleArray);

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
